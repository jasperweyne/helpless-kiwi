<?php

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Dotenv\Dotenv;

class Updater
{
    protected string $releaseUrl = 'https://api.github.com/repos/jasperweyne/helpless-kiwi/releases';
    protected ?array $env = null;

    public function run(): void
    {
        // Validate that the updater can run
        $this->checkReqs();

        // Load the environment variables
        $this->loadEnv();

        // Check if logged in
        $this->login();

        // Check if default environment parameters are set
        $this->saveEnvDefaults();

        // Check if database is configured
        $this->registerDatabase();

        // Check if updater is in progress
        $this->runUpdaterStep();

        // Check if application is up to date
        $this->needsUpdate();

        // Check if application name is confirm_update
        $this->registerName();

        // Check if mailer is configured
        $this->registerMailer();

        // Check if a security mode has been set
        $this->registerSecurity();

        // Check if OpenID Connect is configured
        $adminHelp = '';
        if ('oidc' === $this->env['SECURITY_MODE']) {
            $this->registerOidc();
            $adminHelp = '<br>Om in te loggen bij lokale accounts, <a href="/login?provider=local">klik hier</a>';
        }

        // Check if an admin account has been added
        $this->registerUser();

        // Everything checks out, let the user know
        self::render('Up-to-date!', "<p>Je draait de laatste versie van Kiwi.$adminHelp</p>");
    }

    /**
     * Run a Kiwi command.
     *
     * @param string $command The command to run
     * @param bool   $stdout  Whether to return stdout contents, returns status code if false
     *
     * @return ($stdout ? string : int)
     */
    public function cmd(string $command, bool $stdout = true): int|string
    {
        static $app = null;
        if (null === $app) {
            require_once self::path('kiwi/vendor/autoload.php');

            (new Dotenv())->bootEnv(self::path('kiwi/.env'));

            $kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
            $app = new Application($kernel);
            $app->setAutoExit(false);
        }

        // Run the command
        $result = $app->run(new StringInput($command), $output = new BufferedOutput());
        if ($stdout && 0 !== $result) {
            throw new Exception($output->fetch());
        }

        return $stdout ? $output->fetch() : $result;
    }

    /**
     * Validate the environment of the updater script.
     *
     * @throws Exception If the project's environment is somehow invalid
     */
    public function checkReqs(): void
    {
        // Check request URI
        if ('/update.php' !== $_SERVER['REQUEST_URI']) {
            throw new Exception('The server is setup incorrectly. The request URI should be /update.php, but it\'s '.$_SERVER['REQUEST_URI']);
        }

        // Check whether the path is writable
        if (!is_writable($root = self::path())) {
            throw new Exception("$root is not writable by the updater script.");
        }

        // Check whether the correct extensions are installed
        $extensions = implode(', ', array_filter(['zip', 'json', 'mysqli', 'session'], fn ($ext) => !extension_loaded($ext)));
        if (!empty($extensions)) {
            throw new Exception('The following extensions are missing, please install or enable them: '.$extensions);
        }
    }

    /**
     * Load the environment settings from the filesystem.
     */
    private function loadEnv(bool $forceLoad = false): void
    {
        if ($forceLoad || !$this->env) {
            $path = self::path('kiwi/.env.local.php');
            $this->env = file_exists($path) ? require $path : [];
        }
    }

    /**
     * Save the currently loaded environment settings to the filesystem.
     */
    private function saveEnv(): void
    {
        if (!$this->env) {
            return;
        }

        // Create export string
        $keys = array_map(fn ($x) => addslashes($x), array_keys($this->env ?? []));
        $vals = array_map(fn ($x) => addslashes($x), array_values($this->env ?? []));
        $export = implode(PHP_EOL, [
            '<?php',
            '',
            'return [',
            ...array_map(fn ($k, $v) => "    '$k' => '$v',", $keys, $vals),
            '];',
            '',
        ]);

        // Create directory and write export to disk
        if (!file_exists($dir = self::path('kiwi'))) {
            mkdir($dir);
        }

        if (false === file_put_contents(self::path('kiwi/.env.local.php'), $export)) {
            throw new Exception('Problem writing environment variables to disk');
        }
    }

    /**
     * Register and save the default environment variables necessary for Kiwi.
     */
    private function saveEnvDefaults(): void
    {
        if (!isset($this->env['APP_SECRET'])) {
            $hexchars = '0123456789abcdef';
            $random_val = '';
            for ($i = 0; $i < 32; ++$i) {
                $random_val .= $hexchars[random_int(0, strlen($hexchars) - 1)];
            }
            $this->env['APP_SECRET'] = $random_val;
            $this->env['APP_DEBUG'] = '0';
            $this->env['APP_ENV'] = 'prod';

            $this->saveEnv();
        }
    }

    private function login(): void
    {
        session_start();

        // If no password is registered yet, create one
        if (!isset($this->env['UPDATER_PASSWORD'])) {
            $result = self::form('Welkom bij de Kiwi installer', '<p>Welkom bij de Kiwi installatie. Registreer een wachtwoord voor de installer.</p>', [
                'Wachtwoord' => ['type' => 'password', 'required' => true],
                'Herhaal wachtwoord' => ['type' => 'password', 'required' => true],
            ], function ($data) {
                if ($data['Wachtwoord'] !== $data['Herhaal wachtwoord']) {
                    return ['Wachtwoorden zijn niet gelijk'];
                }
            });

            $this->env['UPDATER_PASSWORD'] = password_hash($result['Wachtwoord'], PASSWORD_BCRYPT);
            $this->saveEnv();

            $_SESSION['secret'] = $result['Wachtwoord'];
        }

        // Check if logged in
        if (password_verify($_SESSION['secret'] ?? '', $this->env['UPDATER_PASSWORD'])) {
            return;
        }

        // Not logged in, show login screen
        $result = self::form('Log in', '<p>Welkom bij de Kiwi installatie. Log in om door te gaan.</p>', [
            'Wachtwoord' => ['type' => 'password', 'required' => true, 'filter' => fn ($pass) => password_verify($pass, $this->env['UPDATER_PASSWORD'])],
        ]);

        $_SESSION['secret'] = $result['Wachtwoord'];
    }

    private function registerDatabase()
    {
        if (isset($this->env['DATABASE_URL'])) {
            return;
        }

        $result = self::form('Database Configuratie', '<p>Geef de verbindingsinstellingen van je MySQL of MariaDB database op. Dit moet een lege database zijn. Als je niet zeker weet wat je instellingen zijn, neem dan contact op met je host.</p>', [
            'Database Gebruiker' => ['placeholder' => 'username', 'required' => true],
            'Database Wachtwoord' => ['placeholder' => 'password'],
            'Database Host' => ['placeholder' => 'localhost', 'required' => true],
            'Database Naam' => ['placeholder' => 'kiwi', 'required' => true],
        ], function ($data) {
            // Check whether a connection can be made
            [$user, $pass, $host, $name] = array_values($data);
            $connection = @mysqli_connect($host, $user, $pass);
            if (!$connection) {
                return ["Couldn't connect to $host with user $user"];
            }

            // Check whether database exists
            $res = mysqli_query($connection, 'SHOW DATABASES');
            $found = false;
            try {
                while ($row = mysqli_fetch_assoc($res)) {
                    if ($row['Database'] === $name) {
                        $found = true;
                        break;
                    }
                }
            } finally {
                $res->close();
                $connection->close();
            }

            if (!$found) {
                return ["Credentials are correct, but database '$name' was not found"];
            }
        });

        [$user, $pass, $host, $name] = array_values($result);
        $this->env['DATABASE_URL'] = "mysql://$user:$pass@$host:3306/$name";
        $this->saveEnv();
    }

    private function registerName(): void
    {
        if (isset($this->env['ORG_NAME'])) {
            return;
        }

        $result = self::form('Naam organisatie', '<p>Stel de naam in van je organisatie.</p>', [
            'Naam Organisatie' => ['required' => 'true'],
        ]);

        $this->env['ORG_NAME'] = $result['Naam Organisatie'];
        $this->saveEnv();
    }

    private function registerMailer(): void
    {
        if (isset($this->env['MAILER_URL'])) {
            return;
        }

        $result = self::form('E-mail configuratie', '', [
            'email_type' => [
                'type' => 'radio',
                'options' => [
                    'smtp' => 'SMTP e-mail',
                    'noemail' => 'Geen e-mail',
                ],
            ],
            'Swiftmailer URL' => ['filter' => fn ($x) => '' === $x || filter_var($x, FILTER_VALIDATE_URL)],
            'E-mailadres verzender' => ['filter' => fn ($x) => '' === $x || filter_var($x, FILTER_VALIDATE_EMAIL), 'type' => 'email'],
        ]);

        if ('smtp' === $result['email_type']) {
            $this->env['MAILER_URL'] = trim($result['Swiftmailer URL']);
            $this->env['DEFAULT_FROM'] = trim($result['E-mailadres verzender']);
        } else {
            $this->env['MAILER_URL'] = 'null://localhost';
        }
        $this->saveEnv();
    }

    private function registerSecurity(): void
    {
        if (isset($this->env['SECURITY_MODE'])) {
            return;
        }

        $result = self::form('Accountbeheer', '<p>Stel in hoe je accounts wilt beheren.</p>', [
            'security' => [
                'type' => 'radio',
                'options' => [
                    'local' => 'Alleen Kiwi accounts',
                    'oidc' => 'OpenID Connect accounts',
                ],
            ],
        ]);

        $this->env['SECURITY_MODE'] = $result['security'];
        $this->saveEnv();
    }

    private function registerOidc(): void
    {
        if (isset($this->env['OIDC_ADDRESS'])) {
            return;
        }

        $result = self::form('OpenID Connect configuratie', '<p>Stel de verbindingsinstellingen voor de OpenID Connect accounts in.</p>', [
            'Client ID' => ['required' => true],
            'Client Secret' => ['required' => true],
            'OpenID Connect Issuer URL' => ['required' => true, 'filter' => fn ($x) => filter_var($x, FILTER_VALIDATE_URL)],
        ]);

        $this->env['OIDC_SECRET'] = $result['Client Secret'];
        $this->env['OIDC_ID'] = $result['Client ID'];
        $this->env['OIDC_ADDRESS'] = $result['OpenID Connect Issuer URL'];
        $this->saveEnv();
    }

    private function registerUser(): void
    {
        if (!empty($this->cmd('app:has-account'))) {
            return;
        }

        $result = self::form('Nieuw Account toevoegen', '<p>Voeg een nieuw administrator account toe.</p>', [
            'Admin Email' => ['required' => true, 'type' => 'email'],
            'Admin Naam' => ['required' => true],
            'Admin Wachtwoord' => ['required' => true, 'type' => 'password'],
        ]);

        $this->cmd("app:create-account '{$result['Admin Email']}' '{$result['Admin Naam']}' '{$result['Admin Wachtwoord']}' --admin");
    }

    private function needsUpdate(): void
    {
        // Check whether an update needs to be installed
        $latest = $this->latest();
        if ($latest === ($this->env['INSTALLED_VERSION'] ?? null)) {
            return;
        }

        // Setup message
        $msg = file_exists(self::path('kiwi/vendor/autoload.php')) ? "Versie $latest is uitgebracht." : 'Welkom.';

        // Check if server is compatible with new version
        if ($reqs = $this->compat($latest)) {
            $problem = match (true) {
                is_string($reqs) => "Je draait een verouderde versie van PHP. Update je PHP versie naar $reqs om door te kunnen gaan.",
                is_array($reqs) => "Je mist de volgende PHP extensies om versie $latest van Kiwi te kunnen installeren:\n\n".implode("\n", $reqs),
                default => "Er is een onbekend probleem om versie $latest Kiwi te kunnen installeren.",
            };

            self::render('Installatie', "<p>$msg $problem</p>");
        }

        // Server configuration checks out, show update screen
        self::form('Installatie', "<p>$msg Klik hier om de installatie te starten.</p>");

        // Download version
        $msg = 'install/update.zip bestand al aanwezig';
        if (!file_exists(self::path('install/update.zip'))) {
            file_put_contents(self::path('install/update.zip'), $this->asset($latest, 'kiwi.zip'));
            $msg = "$latest gedownload";
        }

        // Start update process
        touch(self::path('update.log'));
        header('Refresh: 1');
        self::render('Installatie', "<p>$msg, installatie aan het starten...</p>");
    }

    private function runUpdaterStep(): void
    {
        $this->transition();

        // Check if the updater is running a process
        if (!file_exists($logFile = self::path('update.log'))) {
            return;
        }

        // Lock the install process
        $log = fopen($logFile, 'a+');
        if (!flock($log, LOCK_EX | LOCK_NB)) {
            self::render('Error!', 'Another instance is currently executing the updater');
        }

        // Let the updater execute a single step and cancel the process if an error occurs
        try {
            $update = $this->install($this->latest(), $archive = self::path('install/update.zip'));
            $status = $update->current();
        } catch (Throwable $e) {
            fclose($log);
            unlink($logFile);

            throw $e;
        }

        // Write the progress to log and release the lock
        fwrite($log, $status.PHP_EOL);
        fclose($log);

        // If the updater is finished, remove the logfile and update archive and continue
        if (!$update->valid()) {
            unlink($logFile);
            unlink($archive);

            return;
        }

        header('Refresh: 1');
        $completeLog = file_get_contents($logFile);
        self::render('Aan het installeren...', "<pre>$completeLog</pre>");
    }

    private function transition()
    {
        $latestSafe = str_replace(['/', '\\', '.'], '_', $this->latest());
        if (file_exists($unpackedmarker = self::path("install/{$latestSafe}_unpacked"))) {
            rename(self::path("install/{$latestSafe}.zip"), self::path('install/update.zip'));
            unlink($unpackedmarker);
            file_put_contents(self::path('update.log'), $_SESSION['log'] ?? '');
            unset($_SESSION['log']);
        }
    }

    private function install(string $version, string $archive)
    {
        $installingFiles = $version !== ($this->env['INSTALLED_VERSION'] ?? null);
        if ($installingFiles && !file_exists($disabler = self::path('public_html/kiwi/enable-maintenance.txt'))) {
            touch($disabler);
            yield 'Put kiwi in maintenance mode';
        }

        // Check if utilities folder exist
        if (!file_exists($backups = self::path('install'))) {
            mkdir($backups);
        }

        // Create a backup, but only if Kiwi was already installed (at the start of this process)
        if ($installingFiles && file_exists(self::path('kiwi/vendor/autoload.php'))) {
            if (!file_exists($backups = self::path('install/backup'))) {
                mkdir($backups);
            }

            // Check if file backup can be/has been made
            $today = date('Y-m-d');
            if (!file_exists($filesBackup = self::path("install/backup/$today.zip"))) {
                $this->backup($filesBackup);
                yield 'File structure backup created';
            }

            // Check if database backup has been made
            if (!file_exists($databaseDump = self::path("install/backup/$today.sql.gz"))) {
                require_once self::path('kiwi/vendor/autoload.php');
                $dumper = new MySQLDump($this->database($this->env['DATABASE_URL'] ?? ''));
                $dumper->save($databaseDump);
                yield 'Database backup created';
            }
        }

        // Check if uploads exist, if so move them
        $uploads = self::path('public_html/kiwi/uploads');
        $uploadsTemp = self::path('install/uploads');
        if ($installingFiles && file_exists($uploads)) {
            rename($uploads, $uploadsTemp);
            yield 'Temporarily moved uploads to update folder';
        }

        if ($installingFiles && file_exists(self::path('kiwi/vendor/autoload.php'))) {
            // Remove previous version application contents
            $this->rmdir(self::path('kiwi'));
            $this->saveEnv(); // Only file that remains should be kiwi/.env.local.php
            yield 'Removed previous installation';
        }

        // Remove previous installation files and replace it with the new the extracted installation
        if ($installingFiles) {
            // Remove remaining files from previous version (including this updater, it will still be in PHP memory)
            $this->rmdir(self::path('public_html'));
            $this->rmdir(self::path('kiwi'));

            // Install files of the new version
            $this->extract($archive, self::path());
            touch($disabler);

            // Reinsert local files
            $this->env['INSTALLED_VERSION'] = $version;
            $this->saveEnv();
            yield 'Kiwi update extracted';
        }

        if (file_exists($uploadsTemp)) {
            rename($uploadsTemp, $uploads);
            yield 'Restored uploads';
        }

        // Run migrations if present and cleanup
        if (0 !== $this->cmd('doctrine:migrations:up-to-date', stdout: false)) {
            $this->cmd('doctrine:migrations:migrate -n --allow-no-migration --all-or-nothing');
            yield 'New database migrations were applied';
        }

        unlink($disabler);

        return 'Update installation completed';
    }

    private function backup(string $path): void
    {
        $zip = new ZipArchive();
        if (true !== $error = $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            throw new Exception("Couldn't create zip $path with error $error");
        }

        $files = [
            ...new RecursiveIteratorIterator(new RecursiveDirectoryIterator(self::path('kiwi'))),
            ...new RecursiveIteratorIterator(new RecursiveDirectoryIterator(self::path('public_html'))),
        ];

        foreach ($files as $file) {
            if (!$file->isDir()) {
                $zip->addFile($file->getRealPath(), str_replace(self::path(), '', $file->getRealPath()));
            }
        }

        $zip->close();
    }

    private function database(string $uri): mysqli
    {
        $matches = [];
        if (!preg_match('/^\w+:\/\/(\w*):(\w*)@([\w\.]*):\d+\/(\w+)/', $uri, $matches)) {
            throw new Exception("Couln\'t parse database URL from environment variables");
        }
        [, $user, $pass, $host, $name] = $matches;

        return new mysqli($host, $user, $pass, $name);
    }

    public function extract(string $path, string $dest): void
    {
        $zip = new ZipArchive();
        if (!$zip->open($path)) {
            throw new Exception('Archive extraction failed. The file might be corrupted and you should download it again.');
        }
        $zip->extractTo($dest);
        $zip->close();
    }

    public function rmdir(string $path): bool
    {
        if (!file_exists($path)) {
            return true;
        }

        foreach (array_diff(scandir($path), ['.', '..']) as $file) {
            is_dir("$path/$file") ? $this->rmdir("$path/$file") : unlink("$path/$file");
        }

        return rmdir($path);
    }

    /**
     * Return the latest remote version number.
     *
     * @return string version number (or false if no result)
     */
    public function latest(): ?string
    {
        static $latest = null;
        $latest ??= array_keys($this->releases())[0] ?? null;

        return $latest;
    }

    /**
     * Return the list of releases from the remote (in the Github API v3 format)
     * See: http://developer.github.com/v3/repos/releases/.
     *
     * @return array list of releases and their information
     */
    public function releases(): array
    {
        static $releases = null;
        if (null === $releases) {
            $releases = json_decode(self::get($this->releaseUrl), true);
            $releases = array_filter($releases, fn ($r) => is_string($r['tag_name'] ?? null) && empty($r['prerelease']));
            $releases = array_combine(array_map(fn ($r) => $r['tag_name'], $releases), $releases);
        }

        return $releases;
    }

    /**
     * Validate whether this server satisfies the version requirements of a Kiwi version.
     *
     * @param string $version release version number
     *
     * @return mixed a string of the required PHP version, an array of the missing extensions or null if compatible
     */
    public function compat(string $version)
    {
        $requirements = json_decode($this->asset($version, 'requirements.json'), true);

        $phpReq = $requirements['php'] ?? null;
        if ($phpReq && version_compare(PHP_VERSION, $phpReq, '<')) {
            return $phpReq;
        }

        $extensionReqs = array_filter(array_keys($requirements), fn ($x) => str_starts_with($x, 'ext-'));
        $missingExts = array_filter(array_map(fn ($x) => substr($x, strlen('ext-')), $extensionReqs), fn ($x) => !extension_loaded($x));

        return $missingExts ?: null;
    }

    /**
     * Download and verify a release asset for a version.
     *
     * @param string $version release version number
     * @param string $name    asset name
     *
     * @return string asset contents
     */
    public function asset(string $version, string $name): string
    {
        // Retrieve asset information
        $assets = array_filter($this->releases()[$version]['assets'] ?? [], fn ($a) => $a['name'] === $name);
        if (false === $asset = reset($assets)) {
            throw new Exception("Asset $name for version $version couldn't be found.");
        }

        // Retrieve data
        $data = self::get($asset['browser_download_url']);

        // Verify data
        if ('hashes.txt' !== $name) {
            static $checksums = [];
            if (!isset($checksums[$version])) {
                $hashes = trim($this->asset($version, 'hashes.txt'));
                $parsed = array_map(fn ($line) => preg_split('/\s+/', $line), explode("\n", $hashes));
                $checksums[$version] = array_combine(array_column($parsed, 0), array_column($parsed, 1));
            }

            if (hash('sha512', $data) !== $checksums[$version][$name] ?? '') {
                throw new Exception('Invalid data downloaded');
            }
        }

        return $data;
    }

    /**
     * Perform a HTTP GET request.
     *
     * @param string $url URL to get
     *
     * @return string Raw response data
     */
    public static function get(string $url): string
    {
        if (function_exists('curl_version')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_USERAGENT, 'helpless-kiwi');
            $content = curl_exec($ch);
            curl_close($ch);
        } else {
            $content = file_get_contents($url);
        }

        if (false === $content) {
            throw new Exception("HTTP GET request to $url failed. You might be behind a proxy.");
        }

        return $content;
    }

    /**
     * Get a path, relative to the root path of the Kiwi installation.
     *
     * @param string $dest The relative path, without a leading slash
     *
     * @return string The full path
     */
    public static function path(string $dest = ''): string
    {
        assert(DIRECTORY_SEPARATOR === '/');
        $common = dirname(dirname(__DIR__));
        if (__DIR__ !== "$common/public_html/kiwi") {
            throw new Exception('The script is in the wrong location. Its location should end with "/public_html/kiwi/update.php", but it\'s current location is '.__FILE__);
        }

        return "$common/$dest";
    }

    public static function form(string $title, string $content, array $fields = [], ?callable $filter = null): array
    {
        // Set defaults
        $fields = array_map(fn ($opts) => array_merge([
            'type' => 'text',
            'required' => false,
            'placeholder' => null,
            'filter' => null,
        ], $opts), $fields);

        // Check if this form was submitted
        $errors = [];
        if ('POST' === $_SERVER['REQUEST_METHOD'] && $title === $_POST['action']) {
            // Check if any errors occurred
            foreach ($fields as $field => $opts) {
                if ($opts['required'] && !isset($_POST[base64_encode($field)])) {
                    $errors[] = "$field is required";
                }

                if (isset($_POST[base64_encode($field)]) && isset($opts['filter']) && !$opts['filter']($_POST[base64_encode($field)])) {
                    $errors[] = "$field does not have a valid argument";
                }

                if ('radio' === $opts['type'] && !in_array($_POST[base64_encode($field)], array_keys($opts['options']))) {
                    $errors[] = "$field has an invalid value";
                }
            }

            // If no errors occurred per field, build the input data
            if (empty($errors)) {
                $data = array_combine(array_keys($fields), array_map(fn ($f) => $_POST[base64_encode($f)] ?? null, array_keys($fields)));

                // If also no errors occurred for the whole form, success, return data
                if (null === $filter || empty($errors = $filter($data))) {
                    return $data;
                }
            }
        }

        // Render fields
        $rendered = implode(array_map(fn (string $field, array $opts) => match ($opts['type'] ?? 'text') {
            'text' => self::textField($field, $opts),
            'email' => self::textField($field, $opts),
            'password' => self::textField($field, $opts),
            'radio' => self::radioField($field, $opts),
            default => throw new Exception('Unknown form type'),
        }, array_keys($fields), $fields));

        // Render complete form
        $errors = $errors ? '<p class="alert alert-warning">'.join(', ', $errors).'</p>' : '';
        $required = count(array_filter($fields, fn ($opts) => $opts['required'] ?? false)) ? '<p>Velden met een <span class="text-danger">*</span> zijn verplicht</p>' : '';
        $form = '<form role="form" method="post"><input type="hidden" name="action" value="'.$title.'" />'.$rendered.$required.'<input type="submit" class="btn btn-success" value="Bevestig" /></form>';

        self::render($title, $errors.$content.$form);
    }

    private static function textField(string $field, array $opts): string
    {
        $required_label = '';
        $required_tag = '';

        // Required field
        if ($opts['required']) {
            $required_label = '<sup class="text-danger">*</sup>';
            $required_tag = 'required';
        }

        // Render
        return '
        <div class="form-group">
            <label for="'.$field.'">'.$field.$required_label.'</label>
            <input
                class="form-control"
                type="'.$opts['type'].'"
                id="'.$field.'"
                name="'.base64_encode($field).'"
                placeholder="'.($opts['placeholder'] ?? '').'"
                value="'.($_POST[base64_encode($field)] ?? null).'"
                '.$required_tag.' />
        </div>
        ';
    }

    private static function radioField(string $field, array $opts): string
    {
        $optionsRendered = '';
        foreach ($opts['options'] as $option => $label) {
            $prefill = $option === ($_POST[$field] ?? null) ? 'checked ' : '';
            $optionsRendered .= '
                <label class="radio-inline">
                <input
                    type="'.$opts['type'].'"
                    name="'.base64_encode($field).'"
                    value="'.$option.'"
                    '.$prefill.'
                >'.$label.'</label>
            ';
        }

        return '<div class="form-group">'.$optionsRendered.'</div>';
    }

    public static function render(string $title, string $content): never
    {
        ?>
<!DOCTYPE HTML>
<html lang="nl">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">
    <title>Helpless Kiwi &mdash; <?php echo $title; ?></title>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="card card-default w-100 mt-4 shadow">
                <div class="card-header">
                    <h3 class="panel-title">Helpless Kiwi &mdash; <?php echo $title; ?></h3>
                </div>
                <div class="card-body">
                    <?php echo $content; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
        <?php
        exit;
    }
}

set_time_limit(0);
set_exception_handler(function (Throwable $e) {
    Updater::render('Probleem!', '<pre>'.$e->getMessage()."\n".$e->getTraceAsString().'</pre><a href="revert.php">Zet backup terug met backuptool</a>');
});

$updater = new Updater();
$updater->run();
