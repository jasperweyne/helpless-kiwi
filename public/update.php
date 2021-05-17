<?php

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;

class Log
{
    /**
     * Write a new message to the log.
     *
     * @param string $msg The message written to the log
     */
    public static function msg(string $msg)
    {
        if (!isset($_SESSION['log'])) {
            $_SESSION['log'] = '';
        }

        $_SESSION['log'] .= str_replace("\n", '<br>', $msg).'<br>';
    }

    /**
     * Write raw console data to the log, formatted monospace.
     *
     * @param string $msg The console message written to the log
     */
    public static function console(string $msg)
    {
        self::msg("<pre>$msg</pre>");
    }

    /**
     * Read console data from the log, formatted as HTML.
     *
     * @param bool $clear Whether to clear the log after reading
     *
     * @return string The HTML formatted log
     */
    public static function read(bool $clear = false)
    {
        $log = $_SESSION['log'] ?? null;
        if ($clear) {
            unset($_SESSION['log']);
        }

        return $log;
    }
}

class IntegrationTool
{
    protected $application;
    protected $verification;

    public function __construct()
    {
        $this->application = null;
        $this->verification = false;
    }

    /**
     * Validate the environment of the updater script.
     *
     * @throws \Exception If the project's environment is somehow invalid
     */
    public function envIsValid()
    {
        // Only run once, to avoid infinite recursion
        if ($this->verification) {
            return;
        }

        // If not ran earlier, run it now and mark it
        $this->verification = true;

        // Check script file location
        if (__DIR__ !== $this->getPublicPath()) {
            throw new \Exception('The script is in the wrong location. Its direct parents should be public_html/kiwi, but it\'s currently placed in '.__DIR__);
        }

        // Check request URI
        if ('/update.php' !== $_SERVER['REQUEST_URI']) {
            throw new \Exception('The server is setup incorrectly. The request URI should be /updater.php, but it\'s '.$_SERVER['REQUEST_URI']);
        }

        // Check whether the path is writable
        if (!is_writable($this->getCommonPath())) {
            throw new \Exception($this->getCommonPath().' is not writable by the updater script.');
        }
    }

    /**
     * Get the root path of the Kiwi installation.
     *
     * @return string The root path of the Kiwi installation
     */
    public function getCommonPath(): string
    {
        if (!$this->verification) {
            $this->envIsValid();
        }

        return dirname(dirname(__DIR__));
    }

    /**
     * Get the path where this installer stores temporary files.
     *
     * @return string The path used by the Kiwi installer for temporary files
     */
    public function getInstallerPath()
    {
        return $this->getCommonPath().DIRECTORY_SEPARATOR.'install';
    }

    /**
     * Get the path where Kiwi serves its public, static files from.
     *
     * @return string The public path used by Kiwi
     */
    public function getPublicPath(string $common = null): string
    {
        return ($common ?? $this->getCommonPath()).DIRECTORY_SEPARATOR.'public_html'.DIRECTORY_SEPARATOR.'kiwi';
    }

    /**
     * Get the root path where the Kiwi source is stored.
     *
     * @return string The root path for the Kiwi source
     */
    public function getRootPath(string $common = null)
    {
        return ($common ?? $this->getCommonPath()).DIRECTORY_SEPARATOR.'kiwi';
    }

    /**
     * Check whether Kiwi was installed.
     *
     * @return string The root path for the Kiwi source
     */
    public function hasApplication()
    {
        return file_exists($this->getAutoloaderPath()) && file_exists($this->getBootstrapPath());
    }

    /**
     * Run a Kiwi command.
     *
     * @param string $command The command to run
     * @param string $stdout  The string variable where the output will be stored
     *
     * @return int The status code of the command
     */
    public function runCommand(string $command, &$stdout = null): int
    {
        // loadApplication must be called first, as it loads the autoloader
        $app = $this->loadApplication();

        // Run the command
        $input = new StringInput($command);
        $output = new BufferedOutput();
        $result = $app->run($input, $output);

        // Extract the output
        $stdout = $output->fetch();

        return $result;
    }

    /**
     * Load the Kiwi application, used for interacting with it.
     *
     * @return Application The Kiwi application
     */
    protected function loadApplication(): Application
    {
        if (!$this->application) {
            if (!$this->hasApplication()) {
                throw new \Exception('Symfony is not installed.');
            }

            include_once $this->getAutoloaderPath();
            include_once $this->getBootstrapPath();

            $kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
            $this->application = new Application($kernel);
            $this->application->setAutoExit(false);
        }

        return $this->application;
    }

    /**
     * Get the path of the Composer autoloader.
     *
     * @return string The filesystem path of the Composer autoloader
     */
    protected function getAutoloaderPath(): string
    {
        return $this->getRootPath().DIRECTORY_SEPARATOR.'vendor'.DIRECTORY_SEPARATOR.'autoload.php';
    }

    /**
     * Get the path of the Symfony bootstrap file.
     *
     * @return string The filesystem path of the Symfony bootstrap file
     */
    protected function getBootstrapPath(): string
    {
        return $this->getRootPath().DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'bootstrap.php';
    }
}

class EnvFileTool
{
    const ENV_FILE = '.env.local.php';
    protected $buffer;
    protected $path;

    public function __construct($path)
    {
        $this->path = $path;
        $this->buffer = null;
    }

    /**
     * Check whether the environment settings file exists.
     *
     * @return bool Whether the environment settings file exists
     */
    public function exists(): bool
    {
        return file_exists($this->file());
    }

    /**
     * Load the environment settings from the filesystem.
     */
    public function load(bool $forceLoad = false)
    {
        if ($forceLoad || !$this->buffer) {
            if ($this->exists()) {
                $this->buffer = require $this->file();
            } else {
                $this->buffer = [];
            }
        }
    }

    /**
     * Save the currently loaded environment settings to the filesystem.
     */
    public function save()
    {
        if (!$this->buffer) {
            return;
        }

        if (!file_exists($this->path)) {
            mkdir($this->path);
        }

        $accessfile = fopen($this->file(), 'w');
        $written = fwrite($accessfile, $this->export());
        fclose($accessfile);

        if (false === $written) {
            throw new \Exception('Problem writing environment variables to disk');
        }
    }

    /**
     * Check whether an environment variable has been set.
     */
    public function hasVar(string $var): bool
    {
        if (!$this->buffer) {
            $this->load();
        }

        return isset($this->buffer[$var]);
    }

    /**
     * Get the value of an environment variable.
     *
     * @param string $var The environment variable to retrieve
     *
     * @return string The environment variable
     */
    public function getVar(string $var)
    {
        if (!$this->buffer) {
            $this->load();
        }

        return $this->buffer[$var] ?? null;
    }

    /**
     * Set the value of an environment variable.
     *
     * @param string $var   The environment variable
     * @param string $value The value of the environment variable
     *
     * @return self
     */
    public function setVar(string $var, string $value)
    {
        if (!$this->buffer) {
            $this->load();
        }

        $this->buffer[$var] = $value;

        return $this;
    }

    /**
     * Register and save the default environment variables necessary for Kiwi.
     */
    public function defaults()
    {
        // Application environment
        $this->setVar('APP_DEBUG', '0');
        $this->setVar('APP_ENV', 'prod');

        // App secret
        if (!$this->hasVar('APP_SECRET')) {
            $hexchars = '0123456789abcdef'; 
            $random_val = '';
            for ($i = 0; $i < 32; ++$i) {
                $random_val .= $hexchars[random_int(0, strlen($hexchars) - 1)];
            }
            $this->setVar('APP_SECRET', $random_val);
        }

        // Userprovider Key
        if (!$this->hasVar('USERPROVIDER_KEY')) {
            $hexchars = '0123456789abcdef'; 
            $random_val = '';
            for ($i = 0; $i < 32; ++$i) {
                $random_val .= $hexchars[random_int(0, strlen($hexchars) - 1)];
            }
            $this->setVar('USERPROVIDER_KEY', $random_val);
        }

        $this->save();
    }

    /**
     * Export the environment variables as a PHP file formatted string.
     *
     * @return string The contents of the PHP file
     */
    protected function export(): string
    {
        $data = PHP_EOL;
        foreach ($this->buffer ?? [] as $key => $value) {
            $escKey = addslashes($key);
            $escVal = addslashes($value);
            $data .= "    '$escKey' => '$escVal',".PHP_EOL;
        }

        return '<?php'.PHP_EOL.PHP_EOL."return [$data];".PHP_EOL;
    }

    /**
     * Get the environment variable file path.
     *
     * @return string The environment variable file path
     */
    private function file(): string
    {
        return $this->path.DIRECTORY_SEPARATOR.self::ENV_FILE;
    }
}

class DatabaseTool
{
    protected $integration;
    protected $user;
    protected $pass;
    protected $host;
    protected $name;

    public function __construct($url, IntegrationTool $integration = null)
    {
        $this->integration = $integration;

        // Check if a database connection is configured
        $matches = [];
        if (!preg_match('/^\w+:\/\/(\w*):(\w*)@([\w\.]*):\d+\/(\w+)/', $url, $matches)) {
            throw new \Exception('Invalid database URL');
        }

        // Extract the database variables
        $this->user = $matches[1];
        $this->pass = $matches[2];
        $this->host = $matches[3];
        $this->name = $matches[4];
    }

    /**
     * Check whether the supplied database exists.
     *
     * @param bool $createIfEmpty Create the database if it doesn't exist'
     *
     * @return bool Whether the database exists
     */
    public function exists($createIfEmpty = false)
    {
        // Try connect to the database host
        $connection = @mysqli_connect($this->host, $this->user, $this->pass);
        if (!$connection) {
            return false;
        }

        $found = false;
        try {
            // Check whether database exists
            $res = mysqli_query($connection, 'SHOW DATABASES');
            while ($row = mysqli_fetch_assoc($res)) {
                if ($row['Database'] == $this->name) {
                    $found = true;
                    break;
                }
            }
            $res->close();
            $connection->next_result();

            // Create database if it doesn't exist
            if (!$found && $createIfEmpty) {
                $sql = 'CREATE DATABASE '.$this->name;
                if (!$connection->query($sql)) {
                    throw new \Exception('Error creating database: '.$connection->error);
                }

                Log::msg('Database created successfully.');
                $found = true;
            }
        } finally {
            $connection->close();
        }

        return $found;
    }

    /**
     * Check whether any unexecuted migrations are available.
     *
     * @return bool Whether any unexecuted migrations are available
     */
    public function canMigrate()
    {
        return 0 !== $this->integration->runCommand('doctrine:migrations:up-to-date');
    }

    /**
     * Run the available database migrations.
     */
    public function migrateDb()
    {
        $output = '';
        $result = $this->integration->runCommand('doctrine:migrations:migrate -n --allow-no-migration', $output);

        if (0 !== $result) {
            throw new \Exception($output);
        }
    }

    /**
     * Check whether a local user account exists.
     *
     * @return bool Whether at least one local user account exists
     */
    public function hasAccount()
    {
        $output = '';
        $result = $this->integration->runCommand('app:has-account', $output);

        if (0 !== $result) {
            throw new \Exception($output);
        }

        return '1' == $output;
    }

    /**
     * Create a local user account with administrator rights.
     */
    public function createAccount(string $email, string $name, string $password)
    {
        $output = '';
        $result = $this->integration->runCommand("app:create-account '$email' '$name' '$password' --admin", $output);

        if (0 !== $result) {
            throw new \Exception($output);
        }
    }

    /**
     * Create a backup of the database.
     *
     * @param string $path Path to where the backup should be stored
     */
    public function createDump(string $path)
    {
        $db = new mysqli($this->host, $this->user, $this->pass, $this->name);
        $dump = new MySQLDump($db);
        $dump->save($path);
    }

    /**
     * Restore a backup of the database.
     *
     * @param string $path Path to the backup
     */
    public function restoreDump(string $path)
    {
        $db = new mysqli($this->host, $this->user, $this->pass, $this->name);
        $dump = new MySQLImport($db);
        $dump->load($path);
    }
}

class DownloadTool
{
    protected $server;
    protected $user;
    protected $repository;
    protected $releases;
    protected $checksums;

    /**
     * Init the updater with remote repository information.
     *
     * @param string $user       user name
     * @param string $repository repository name
     * @param string $server     (optional) server name. Default: Github
     *                           useful for Github Enterprise using Github API v3
     */
    public function __construct(string $user, string $repository, string $server = 'https://api.github.com/')
    {
        $this->user = $user;
        $this->repository = $repository;
        $this->server = $server;
        $this->releases = false;
        $this->checksums = [];
    }

    /**
     * Download archive for the given version directly from Github.
     *
     * @param string $archive path to the downloaded archive
     *
     * @return misc FALSE on failure, path to archive on success
     */
    public function downloadVersion(string $version, string $archive)
    {
        if (!$this->downloadAndVerifyAsset($version, 'kiwi.zip', $archive)) {
            throw new \Exception('Download failed.');
        }

        return $archive;
    }

    /**
     * Return the list of releases from the remote (in the Github API v3 format)
     * See: http://developer.github.com/v3/repos/releases/.
     *
     * @param bool $forceFetch force (re)fetching
     *
     * @return array list of releases and their information
     */
    protected function getReleases(bool $forceFetch = false)
    {
        if ($forceFetch) {
            $this->releases = false;
        }

        //load releases only once
        if (false === $this->releases) {
            $url = $this->server.'repos/'.$this->user.'/'.$this->repository.'/releases';
            $releaseData = json_decode($this->downloadContent($url), true);

            $this->releases = [];
            foreach ($releaseData as $release) {
                //skip pre-releases
                if (!$release['prerelease']) {
                    $this->releases[$release['tag_name']] = $release;
                }
            }
        }

        return $this->releases;
    }

    /**
     * Return the latest remote version number.
     *
     * @return string version number (or false if no result)
     */
    public function getLatestVersion()
    {
        $this->getReleases();
        $latest = false;
        if (!empty($this->releases)) {
            reset($this->releases);
            $latest = current($this->releases);
        }

        return $latest['tag_name'];
    }

    /**
     * Get link for a specified asset of a given version.
     *
     * @param string $version version tag
     * @param string $name    asset name
     *
     * @return string URL to asset
     */
    protected function getAssetUrl(string $version, string $name)
    {
        $this->getReleases();

        // Find the asset by name
        foreach ($this->releases[$version]['assets'] ?? [] as $asset) {
            if ($asset['name'] == $name) {
                return $asset['browser_download_url'];
            }
        }

        // version or asset not found
        return false;
    }

    /**
     * Check if given version is up-to-date with the remote
     * This method assumes the versions are formatted as dates.
     *
     * @param string $version version tag
     *
     * @return bool true if $version >= latest remote version
     */
    public function isUpToDate(?string $version)
    {
        // Retrieve latest release
        $this->getReleases();
        reset($this->releases);
        $latest = current($this->releases);

        // Convert version strings to dates
        $currentVersion = date_create_from_format('Y-m-d', $version ?? '');
        $latestVersion = date_create_from_format('Y-m-d', $latest['tag_name'] ?? '');

        // If conversion failed and raw strings are not equal, assume out of date
        if ((!$currentVersion || !$latestVersion) && $latest['tag_name'] !== $version) {
            return false;
        }

        return $currentVersion >= $latestVersion;
    }

    /**
     * Validate whether the server satisfies the version requirements.
     *
     * @param string $version release version number
     *
     * @return mixed true if compatible, a string of the required PHP version or an array of the missing extensions
     */
    public function isCompatible(string $version)
    {
        $requirements = $this->getServerRequirements($version);

        // Check PHP version, if specified
        $php = $requirements['php'] ?? null;
        if ($php && version_compare(PHP_VERSION, $php, '<')) {
            return $php;
        }

        // Check PHP extensions
        $missing = [];
        foreach ($requirements['extensions'] as $extension) {
            if (!extension_loaded($extension)) {
                $missing[] = $extension;
            }
        }

        if (!empty($missing)) {
            return $missing;
        }

        // All checks passed, server is compatible
        return true;
    }

    /**
     * Get the server requirements of a release.
     *
     * @param string $version release version number
     *
     * @return array php version and extension requirements
     */
    protected function getServerRequirements(string $version)
    {
        $requirementData = json_decode($this->downloadAndVerifyAsset($version, 'requirements.json'), true);

        $required = ['extensions' => []];
        foreach ($requirementData as $requirement => $reqVersion) {
            if ('php' === $requirement) {
                $required['php'] = $reqVersion;
            } elseif (preg_match('/ext-([\w-]+)/', $requirement, $matches)) {
                $required['extensions'][] = $matches[1];
            }
        }

        return $required;
    }

    /**
     * Download and verify a release asset for a version.
     *
     * @param string $version release version number
     * @param string $name    asset name
     * @param string $path    the file destination path
     *
     * @return string asset contents
     */
    protected function downloadAndVerifyAsset(string $version, string $name, ?string $path = null)
    {
        $url = $this->getAssetUrl($version, $name);
        $contents = $this->downloadContent($url, $path);

        $chksums = $this->getChecksums($version);
        if ($path && $chksums[$name] !== hash_file('sha512', $path)) {
            unlink($path);
            throw new \Exception('Invalid data downloaded');
        }

        return $contents;
    }

    /**
     * Get the asset checksums for a version.
     *
     * @param string $version release version number
     *
     * @return array A key value array of the asset checksums, provided by the server
     */
    public function getChecksums(string $version)
    {
        if (!array_key_exists($version, $this->checksums)) {
            $url = $this->getAssetUrl($version, 'hashes.txt');
            $checksumData = trim($this->downloadContent($url));

            // Read data
            $this->checksums[$version] = [];
            foreach (explode("\n", $checksumData) as $rule) {
                list($asset, $checksum) = preg_split('/\s+/', $rule);
                $this->checksums[$version][$asset] = $checksum;
            }
        }

        return $this->checksums[$version];
    }

    /**
     * Perform a request to Github API.
     *
     * @param string $url URL to get
     *
     * @return string Github's response
     */
    protected function downloadContent(string $url, string $path = null)
    {
        //use curl if possible
        if (function_exists('curl_version')) {
            $file = null;
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_USERAGENT, 'helpless-kiwi');
            if ($path) {
                if (!file_exists(dirname($path))) {
                    mkdir(dirname($path));
                }
                touch($path);
                $file = fopen($path, 'w');
                curl_setopt($ch, CURLOPT_FILE, $file);
                curl_setopt($ch, CURLOPT_HEADER, 0);
            }
            $content = curl_exec($ch);
            curl_close($ch);
            if ($path) {
                fclose($file);
            }
        } else {
            //fallback - might raise issues
            $content = file_get_contents($url);
            if ($path) {
                if (!file_exists(dirname($path))) {
                    mkdir(dirname($path));
                }
                touch($path);
                file_put_contents($path, $content);
            }
        }

        if (empty($content)) {
            throw new \Exception('Fetch data from Github failed. You might be behind a proxy.');
        }

        return $content;
    }
}

class ArchiveTool
{
    /**
     * Extract the content.
     *
     * @param string $path archive path
     *
     * @return string name (not path!) of the subdirectory where files where extracted
     *                should look like <user>-<repository>-<lastCommitHash>
     */
    public static function extractArchive($path, $dest)
    {
        $directory = '';
        $zip = new ZipArchive();
        if (true === $zip->open($path)) {
            $stat = $zip->statIndex(0);
            $directory = substr($stat['name'], 0, strlen($stat['name']) - 1);
            $zip->extractTo($dest);
            $zip->close();
        } else {
            throw new \Exception('Archive extraction failed. The file might be corrupted and you should download it again.');
        }

        return $directory;
    }

    public static function compressArchive($path, $dest)
    {
        $backup = new ZipArchive();
        $backup->open($dest, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $dirit = new RecursiveDirectoryIterator($path);
        $files = new RecursiveIteratorIterator($dirit,
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($path) + 1);

                // Add current file to archive
                $backup->addFile($filePath, $relativePath);
            }
        }

        // Zip archive will be created only after closing object
        $backup->close();
        unset($dirit);
        unset($files);
    }

    /**
     * Recursively move all files from $source directory into $destination directory.
     *
     * @param string $source      source directory from which files and subdirectories will be taken
     * @param string $destination destination directory where files and subdirectories will be put
     *
     * @return bool execution status
     */
    public static function moveFilesRecursive($source, $destination)
    {
        $result = true;

        if (!is_dir($source)) {
            return false;
        }

        if (!file_exists($destination)) {
            mkdir($destination);
        }

        $files = scandir($source);
        foreach ($files as $file) {
            if (in_array($file, ['.', '..'])) {
                continue;
            }

            if (is_dir($source.DIRECTORY_SEPARATOR.$file)) {
                $result = self::moveFilesRecursive(
                    $source.DIRECTORY_SEPARATOR.$file,
                    $destination.DIRECTORY_SEPARATOR.$file
                );
            } else {
                $result = copy(
                    $source.DIRECTORY_SEPARATOR.$file,
                    $destination.DIRECTORY_SEPARATOR.$file
                );
                unlink($source.DIRECTORY_SEPARATOR.$file);
            }

            if (!$result) {
                break;
            }
        }

        rmdir($source);

        return $result;
    }

    public static function removeFolderRecursive($path)
    {
        if (!file_exists($path)) {
            return;
        }

        $it = new RecursiveDirectoryIterator($path, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($path);
    }
}

class UpdaterTool
{
    const FILES_BACKUP = 'files.zip';
    const DATABASE_BACKUP = 'database.sql';

    protected $integration;
    protected $database;
    protected $download;
    protected $env;
    protected $break;

    public function __construct(
        IntegrationTool $integration,
        DatabaseTool $database,
        DownloadTool $download,
        EnvFileTool $env,
        callable $break = null
    ) {
        $this->integration = $integration;
        $this->database = $database;
        $this->download = $download;
        $this->env = $env;
        $this->break = $break;
    }

    public function beginProcess(string $process)
    {
        $this->lock();

        // cleanup earlier installation data if necessary
        switch ($process) {
            case 'update':
                mkdir($this->integration->getInstallerPath());
                break;
            case 'backup':
                ArchiveTool::removeFolderRecursive($this->getBackupPath());
                mkdir($this->getBackupPath());
                break;
            case 'revert':
                break;
            default:
                throw new \Exception('Unknown process type: '.$process);
        }

        // extend timelimit
        if ('backup' !== $process) {
            $accessfile = fopen($this->integration->getPublicPath().DIRECTORY_SEPARATOR.'.htaccess', 'w');
            $access = '#Extend execution time
<IfModule mod_php5.c>
php_value max_execution_time 0
</IfModule>';
            fwrite($accessfile, $access);
            fclose($accessfile);
        }

        // set maintanance mode, indicating installation
        $installationfile = $this->getInstallerFile();
        if (file_exists($installationfile)) {
            throw new \Exception('Updater is already running a process');
        }
        file_put_contents($installationfile, $process);

        $this->unlock();
    }

    public function run()
    {
        // Check if the updater is running a process
        $installationfile = $this->getInstallerFile();
        if (file_exists($installationfile)) {
            $process = trim(file_get_contents($installationfile));
            try {
                // Lock the install process
                $this->lock();

                // Execute the current process
                switch ($process) {
                    case 'backup':
                        $this->backup();
                        break;
                    case 'update':
                        $this->update();
                        break;
                    case 'revert':
                        $this->revert();
                        break;
                    default:
                        throw new \Exception('Unknown updater process started');
                }
            } catch (\Throwable $e) {
                Log::msg('Stopped, updater is put in revert mode');
                file_put_contents($installationfile, 'revert');
                throw $e;
            } finally {
                // Always unlock the installation on exceptions and completion
                // Note: this is not called when exit()/die is called
                $this->unlock();
            }

            unlink($installationfile);

            return true;
        }

        return false;
    }

    public function backupExists()
    {
        $filesBackup = $this->getBackupPath(self::FILES_BACKUP);
        $databaseBackup = $this->getBackupPath(self::DATABASE_BACKUP);

        return file_exists($filesBackup) || file_exists($databaseBackup);
    }

    protected function backup()
    {
        $filesBackup = $this->getBackupPath(self::FILES_BACKUP);
        $databaseBackup = $this->getBackupPath(self::DATABASE_BACKUP);

        // Check if backup can be made
        if ($this->integration->hasApplication()) {
            // Check if file backup can be/has been made
            if (!file_exists($filesBackup)) {
                ArchiveTool::compressArchive($this->integration->getCommonPath(), $filesBackup);
                $this->break('File structure backup created');
            }

            // Check if database backup has been made
            if (!file_exists($databaseBackup)) {
                $this->database->createDump($databaseBackup);
                $this->break('Database backup created');
            }
        }
    }

    protected function revert()
    {
        $filesBackup = $this->getBackupPath(self::FILES_BACKUP);
        $databaseBackup = $this->getBackupPath(self::DATABASE_BACKUP);

        // Check if database backup has been made
        if (file_exists($databaseBackup)) {
            $this->database->restoreDump($databaseBackup);
            unlink($databaseBackup);
            $this->break('Restored database backup');
        }

        // Check if file backup can be/has been made
        if (file_exists($filesBackup)) {
            ArchiveTool::extractArchive($filesBackup, $this->integration->getCommonPath());
            file_put_contents($this->getInstallerFile(), 'revert');
            unlink($filesBackup);
            $this->break('Restored file structure backup');
        }
    }

    protected function update()
    {
        // Download variables
        $latest = $this->download->getLatestVersion();
        $latestSafe = str_replace(['/', '\\', '.'], '_', $latest);
        $downloadfile = $this->integration->getInstallerPath().DIRECTORY_SEPARATOR.$latestSafe.'.zip';
        $notinstalledmarker = $this->integration->getInstallerPath().DIRECTORY_SEPARATOR.$latestSafe.'_isnotinstalled';

        // Download version files
        if (!file_exists($downloadfile)) {
            // Check if version is downloaded
            $downloadSuccess = $this->download->downloadVersion($latest, $downloadfile);
            if (!$downloadSuccess) {
                throw new \Exception("Problem when downloaded version $latest from Github");
            }
            touch($notinstalledmarker);
            $this->break("Downloaded version $latest from Github");
        }

        // Install version files
        if (file_exists($notinstalledmarker)) {
            $this->installVersion($latest, $downloadfile);
            unlink($notinstalledmarker);
            $this->break('Kiwi installation was performed with extraction folder contents');
        }

        // Run migrations if present and cleanup
        if ($this->database->canMigrate()) {
            $this->database->migrateDb();
            $this->break('New database migrations were applied');
        }

        // Installation finished succesfully, remove downloaded archive
        unlink($downloadfile);
        $this->env->setVar('INSTALLED_VERSION', $latest);
        $this->env->save();
    }

    protected function installVersion($version, $file)
    {
        $versionSafe = str_replace(['/', '\\', '.'], '_', $version);
        $extractpath = $this->integration->getInstallerPath().DIRECTORY_SEPARATOR.$versionSafe;

        // Check if download is unpacked
        if (!file_exists($extractpath)) {
            ArchiveTool::extractArchive($file, $extractpath);
            $this->break("Unpacked version $version to extraction folder");
        }

        // Check if uploads can be moved
        $uploadspath = $this->integration->getPublicPath().DIRECTORY_SEPARATOR.'uploads';
        $uploadsextractpath = $this->integration->getPublicPath($extractpath).DIRECTORY_SEPARATOR.'uploads';
        if (file_exists($uploadspath)) {
            ArchiveTool::moveFilesRecursive($uploadspath, $uploadsextractpath);
            $this->break('Moved uploads to extraction folder');
        }

        // Remove root files (excluding the environment variables)
        if ($this->integration->hasApplication()) {
            $this->env->load(true);
            ArchiveTool::removeFolderRecursive($this->integration->getRootPath());
            $this->env->save();
            $this->break('Removed previous installation');
        }

        // Remove public folder (including this installer) and move in the new version
        $installerfile = $this->getInstallerFile();
        $process = trim(file_get_contents($installerfile));
        ArchiveTool::removeFolderRecursive($this->integration->getPublicPath());
        ArchiveTool::moveFilesRecursive($extractpath, $this->integration->getCommonPath());
        file_put_contents($installerfile, $process);
    }

    protected function break(string $message = null)
    {
        if ($message) {
            Log::msg($message);
        }

        if ($this->break) {
            $this->unlock();
            call_user_func($this->break);
            exit;
        }
    }

    protected function lock()
    {
        $lockfile = $this->integration->getCommonPath().DIRECTORY_SEPARATOR.'installer.lock';
        if (file_exists($lockfile)) {
            throw new \Exception('Installer already locked.');
        }

        return touch($lockfile);
    }

    protected function unlock()
    {
        $lockfile = $this->integration->getCommonPath().DIRECTORY_SEPARATOR.'installer.lock';
        if (!file_exists($lockfile)) {
            return true;
        }

        return unlink($lockfile);
    }

    private function getBackupPath(string $file = null)
    {
        return $this->integration->getInstallerPath().DIRECTORY_SEPARATOR.'backup'.($file ? DIRECTORY_SEPARATOR.$file : '');
    }

    private function getInstallerFile()
    {
        return $this->integration->getPublicPath().DIRECTORY_SEPARATOR.'enable-maintenance.txt';
    }
}

class Form
{
    protected $name;
    protected $fields;

    public function __construct($name)
    {
        $this->name = $name;
        $this->fields = [];
    }

    public function add($field, $type, $options = [])
    {
        $this->fields[$field] = array_merge([
            'label' => $field,
            'type' => $type,
            'required' => false,
            'placeholder' => null,
            'filter' => null,
        ], $options);

        return $this;
    }

    public function isSubmitted()
    {
        return 'POST' == $_SERVER['REQUEST_METHOD'] && $this->name == $_POST['action'];
    }

    public function isValid()
    {
        return empty($this->getErrors());
    }

    public function getErrors(): array
    {
        if (!$this->isSubmitted()) {
            return [];
        }

        $errors = [];
        foreach ($this->fields as $field => $opts) {
            if ($opts['required'] && !isset($_POST[$field])) {
                $errors[$field] = $opts['label'].' is required';
            }

            if ($_POST[$field] && $opts['filter'] && !filter_var($_POST[$field], $opts['filter'])) {
                $errors[$field] = $opts['label'].' does not have a valid argument';
            }

            if ('radio' === $opts['type'] && !in_array($_POST[$field], array_keys($opts['options']))) {
                $errors[$field] = $opts['label'].' has an invalid value';
            }
        }

        return $errors;
    }

    public function getData($field = null)
    {
        if ($field) {
            return $_POST[$field] ?? null;
        }

        // Extract data from POST
        $data = [];
        foreach ($this->fields as $field => $opts) {
            $data[$field] = $_POST[$field] ?? null;
        }

        return $data;
    }

    public function render()
    {
        // Render fields
        $rendered = '';
        $required = false;
        foreach ($this->fields as $field => $opts) {
            switch ($opts['type']) {
                case 'text':
                case 'email':
                case 'password':
                    $rendered .= $this->renderTextual($field, $opts);
                    $required |= $opts['required'];
                    break;
                case 'radio':
                    $rendered .= $this->renderMultiple($field, $opts);
                    break;
                default:
                    throw new \Exception('Unknown form type');
            }
        }

        if ($required) {
            $rendered .= '<p>Velden met een <sup>*</sup> zijn verplicht</p>';
        }

        // Return complete form
        return '<form role="form" method="post"><input type="hidden" name="action" value="'.$this->name.'" />'.$rendered.'<input type="submit" class="button grow" value="Bevestig" /></form>';
    }

    protected function renderTextual(string $field, array $opts): string
    {
        $required_label = '';
        $required_tag = '';

        // Required field
        if ($opts['required']) {
            $required_label = '<sup>*</sup>';
            $required_tag = 'required';
        }

        // Render
        return '
        <div class="form-group">
            <label for="'.$field.'">'.$opts['label'].$required_label.'</label>
            <input
                class="form-control"
                type="'.$opts['type'].'"
                id="'.$field.'"
                name="'.$field.'"
                placeholder="'.($opts['placeholder'] ?? '').'"
                value="'.($_POST[$field] ?? null).'"
                '.$required_tag.' />
        </div>
        ';
    }

    protected function renderMultiple(string $field, array $opts): string
    {
        $optionsRendered = '';
        foreach ($opts['options'] as $option => $label) {
            $prefill = $option === ($_POST[$field] ?? null) ? 'checked ' : '';
            $optionsRendered .= '
                <label class="radio-inline">
                <input
                    type="'.$opts['type'].'"
                    name="'.$field.'"
                    value="'.$option.'"
                    '.$prefill.'
                >'.$label.'</label>
            ';
        }

        return '<div class="form-group">'.$optionsRendered.'</div>';
    }
}

class UserInterface
{
    protected $integration;
    protected $env;

    public function __construct()
    {
        // Check if script location is correct
        $this->integration = new IntegrationTool();
        $this->integration->envIsValid();

        // Setup base dependencies
        $this->env = new EnvFileTool($this->integration->getRootPath());
    }

    public function run()
    {
        // Check if login is possible
        $this->env->hasVar('UPDATER_PASSWORD') || $this->registerPassword();

        // Check if logged in
        $this->isLoggedIn() || $this->login();

        // Check if default environment parameters are set
        $this->env->defaults();

        // Check if database is configured
        $this->env->hasVar('DATABASE_URL') || $this->registerDatabase();

        // Check if database is valid
        $database = new DatabaseTool($this->env->getVar('DATABASE_URL'), $this->integration);
        $database->exists(true) || $this->registerDatabase();

        // Check if updater is in progress
        $download = new DownloadTool('jasperweyne', 'helpless-kiwi');
        $updater = new UpdaterTool($this->integration, $database, $download, $this->env, function () {
            header('Refresh: 1');
            UserInterface::render('Voortgang', Log::read());
        });

        // Check if updater has finished process
        if ($updater->run()) {
            UserInterface::render('Success', Log::read(true).'<a class="button grow" href="./update.php">Doorgaan</a>');
        }

        // Check if application is up to date
        $download->isUpToDate($this->env->getVar('INSTALLED_VERSION')) || $this->update($updater, $download);

        // Check if application name is confirm_update
        $this->env->hasVar('ORG_NAME') || $this->registerName();

        // Check if mailer is configured
        $this->env->hasVar('MAILER_URL') || $this->registerMailer();

        // Check if a security mode has been set
        $this->env->hasVar('SECURITY_MODE') || $this->registerSecurity();

        // Check if bunny is configured or admin account has been added
        if ('bunny' === $this->env->getVar('SECURITY_MODE')) {
            $this->env->hasVar('BUNNY_URL') || $this->registerBunny();
        } elseif ('local' === $this->env->getVar('SECURITY_MODE')) {
            $database->hasAccount() || $this->registerUser($database);
        }

        // Everything checks out, let the user know
        $this->render('Up-to-date!', '<p>Je draait de laatste versie van Kiwi.</p>');
    }

    /**
     * Check whether the current user (session) is authenticated.
     */
    protected function isLoggedIn(): bool
    {
        if (!$this->env->hasVar('UPDATER_PASSWORD')) {
            return false;
        }

        session_start();

        return password_verify($_SESSION['secret'] ?? '', $this->env->getVar('UPDATER_PASSWORD'));
    }

    protected function registerPassword()
    {
        $form = new Form('register');
        $form->add('password', 'password', [
            'label' => 'Wachtwoord',
            'required' => true,
        ]);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->getData('password');
            $this->env->setVar('UPDATER_PASSWORD', password_hash($password, PASSWORD_BCRYPT));
            $this->env->save();
            $_SESSION['secret'] = $password;

            return;
        }

        $error = join(', ', $form->getErrors());
        $this->render('Welkom bij de Kiwi installer', '<p>Welkom bij de Kiwi installatie. Registreer een wachtwoord voor de installer.</p>'.$form->render(), $error);
    }

    protected function login()
    {
        $form = new Form('login');
        $form->add('password', 'password', [
            'label' => 'Wachtwoord',
            'required' => true,
        ]);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->getData('password');
            if (password_verify($password, $this->env->getVar('UPDATER_PASSWORD'))) {
                $_SESSION['secret'] = $password;

                return;
            }
        }

        $error = join(', ', $form->getErrors());
        $this->render('Welkom bij de Kiwi installer', '<p>Welkom bij de Kiwi installatie. Log in om door te gaan.</p>'.$form->render(), $error);
    }

    protected function registerDatabase()
    {
        $form = new Form('login');
        $form
            ->add('db_type', 'radio', [
                'options' => [
                    'mariadb-10.5.8' => 'MariaDB',
                    '5.7' => 'MySQL',
                ],
            ])
            ->add('db_user', 'text', ['label' => 'Database Gebruiker', 'required' => true])
            ->add('db_pass', 'text', ['label' => 'Database Wachtwoord', 'required' => true])
            ->add('db_host', 'text', ['label' => 'Database Host', 'required' => true])
            ->add('db_name', 'text', ['label' => 'Database Naam', 'required' => true])
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $db_user = trim($form->getData('db_user'));
            $db_pass = trim($form->getData('db_pass'));
            $db_host = trim($form->getData('db_host'));
            $db_name = trim($form->getData('db_name'));
            $db_type = $form->getData('db_type');

            $url = "mysql://$db_user:$db_pass@$db_host:3306/$db_name?serverVersion=$db_type";
            $database = new DatabaseTool($url);
            if ($database->exists()) {
                $this->env->setVar('DATABASE_URL', $url);
                $this->env->save();

                return;
            }
        }

        $error = join(', ', $form->getErrors());
        $this->render('Database Configuratie', '<p>Welkom by de Kiwi installatie. Registreer een wachtwoord voor de installer.</p>'.$form->render(), $error);
    }

    protected function registerName()
    {
        $form = new Form('name');
        $form->add('name', 'text', [
            'label' => 'Naam Organisatie',
            'required' => true,
        ]);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->env->setVar('ORG_NAME', $form->getData('name'));
            $this->env->save();

            return;
        }

        $error = join(', ', $form->getErrors());
        $this->render('Naam organisatie', '<p>Stel de naam in van je organisatie.</p>'.$form->render(), $error);
    }

    protected function registerMailer()
    {
        $form = new Form('email');
        $form
            ->add('email_type', 'radio', [
                'options' => [
                    'smtp' => 'SMTP e-mail',
                    'noemail' => 'Geen e-mail',
                ],
            ])
            ->add('mailer_url', 'text', [
                'label' => 'Swiftmailer URL',
                'filter' => FILTER_VALIDATE_URL,
            ])
            ->add('mailer_email', 'email', [
                'label' => 'E-mailadres verzender',
                'filter' => FILTER_VALIDATE_EMAIL,
            ])
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $mailer_url = trim($form->getData('mailer_url'));
            $mailer_email = trim($form->getData('mailer_email'));
            $email_type = $form->getData('email_type');

            if ('smtp' === $email_type) {
                $this->env->setVar('MAILER_URL', $mailer_url);
                $this->env->setVar('DEFAULT_FROM', $mailer_email);
            } else {
                $this->env->setVar('MAILER_URL', 'null://localhost');
            }
            $this->env->save();

            return;
        }

        $error = join(', ', $form->getErrors());
        $this->render('E-mail configuratie', $form->render(), $error);
    }

    protected function registerSecurity()
    {
        $form = new Form('security');
        $form
            ->add('security', 'radio', [
                'options' => [
                    'local' => 'Lokale userdata',
                    'bunny' => 'Bunny',
                ],
            ])
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->env->setVar('SECURITY_MODE', $form->getData('security'));
            $this->env->save();

            return;
        }

        $error = join(', ', $form->getErrors());
        $this->render('Naam organisatie', '<p>Stel in hoe je accounts wilt beheren.</p>'.$form->render(), $error);
    }

    protected function registerBunny()
    {
        $form = new Form('bunny');
        $form
            ->add('app_id', 'text', [
                'label' => 'App ID',
                'required' => true,
            ])
            ->add('app_secret', 'text', [
                'label' => 'App Secret',
                'required' => true,
            ])
            ->add('bunny_url', 'text', [
                'label' => 'Bunny URL',
                'required' => true,
                'filter' => FILTER_VALIDATE_URL,
            ])
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->env->setVar('BUNNY_SECRET', $form->getData('app_secret'));
            $this->env->setVar('BUNNY_ID', $form->getData('app_id'));
            $this->env->setVar('BUNNY_URL', $form->getData('bunny_url'));
            $this->env->save();

            return;
        }

        $error = join(', ', $form->getErrors());
        $this->render('Spooky Bunny configuratie', '<p>Stel de verbindingsinstellingen voor Bunny in.</p>'.$form->render(), $error);
    }

    protected function registerUser(DatabaseTool $database)
    {
        $form = new Form('user');
        $form
            ->add('admin_email', 'email', [
                'label' => 'Admin Email',
                'required' => true,
            ])
            ->add('admin_name', 'text', [
                'label' => 'Admin Naam',
                'required' => true,
            ])
            ->add('admin_pass', 'password', [
                'label' => 'Admin Wachtwoord',
                'required' => true,
            ])
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData('admin_email');
            $name = $form->getData('admin_name');
            $pass = $form->getData('admin_pass');
            $database->createAccount($email, $name, $pass);

            return;
        }

        $error = join(', ', $form->getErrors());
        $this->render('Nieuw Account toevoegen', '<p>Voeg een nieuw administrator account toe.</p>'.$form->render(), $error);
    }

    protected function update(UpdaterTool $updater, DownloadTool $download)
    {
        // Get latest version
        $latest = $download->getLatestVersion();

        // Setup base messages
        $installed = $this->env->hasVar('INSTALLED_VERSION');

        $message = 'Welkom!';
        $title = 'Installer';

        if ($installed) {
            $message = "Er is een nieuwe versie van Kiwi beschikbaar, versie $latest.";
            $title = 'Updater';
        }

        // Check if server is compatible with new version
        $compatible = $download->isCompatible($latest);
        if (true !== $compatible) {
            $problem = 'Er is een onbekend probleem om deze te kunnen installeren.';

            if (is_array($compatible)) {
                $problem = "Je mist de volgende PHP extensies om versie $latest van Kiwi te kunnen installeren:\n\n";
                $problem .= implode("\n", $compatible);
            } elseif (is_string($compatible)) {
                $problem = "Je draait een verouderde versie van PHP. Update je PHP versie naar $compatible om door te kunnen gaan.";
            }

            $this->render($title, "<p>$message $problem</p>");
        }

        // Backup
        $actionMsg = 'Klik hier om te installeren.';
        $process = 'update';
        if ($updater->backupExists()) {
            $actionMsg .= "\nEr is een backup aanwezig, controleer zelf of deze recent is of verwijder hem en start de updater opnieuw om een nieuwe backup te maken!.";
        } elseif ($installed) {
            $actionMsg = 'Klik hier om eerst een backup te maken.';
            $process = 'backup';
        }

        // Server configuration checks out, show update screen
        $form = new Form('update');
        if ($form->isSubmitted() && $form->isValid()) {
            $updater->beginProcess($process);
            Log::msg("Starting $process process...");
            header('Refresh: 1');
            UserInterface::render('Voortgang', Log::read());
        }

        $this->render($title, "<p>$message $actionMsg</p>".$form->render());
    }

    public static function render(string $title, string $step, string $error = null)
    {
        //region HTML_HEADER?>
<!DOCTYPE HTML>
<html lang="nl">
<link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css">
<style>

    body {
        background: url('/img/bg.png');
    }

    #digidecs {
        border: 1px #ccc solid;
        background: #fdfdfd;
        margin: 20px;
        box-shadow: 0px 0px 15px #999;
    }

    sup {
        color: red;
    }

    main, .bottom {
        clear: both;
        position: relative;
    }

    .bottom, .container {
        max-width: 750rem;
        margin-right: auto;
        margin-left: auto;
        padding: 0 6;
    }

    .section {
        padding-top: 6;
        padding-bottom: 12;
    }
    .row, .bottom {
        margin-bottom: 12;
    }
    .bottom {
        text-align: center;
        font-size: 0;
    }
    .bottom ul {
        margin: 0;
    }
    .bottom li {
        list-style: none;
        display: inline-block;
        white-space: nowrap;
        color: #c6538c;
        font-size: rem-calc(12);
    }
    .bottom li:not(:last-child):after {
        content: '\2022';
        padding: 0 5px;
    }

    .button, button {
    background-color: gray;
    color: white;
    display: inline-block !important;
    padding: 0.5rem 1rem;
    border-radius: 1;
    cursor: pointer;
    line-height: 1.5;
    }
    .button.disabled, button.disabled, .button[disabled], button[disabled] {
        cursor: not-allowed;
    }
    .button.confirm, button.confirm, .button.add, button.add, .button[type=submit], button[type=submit] {
        background-color: #0c0;
        color: white;
    }
    .button.confirm.disabled, button.confirm.disabled, .button.add.disabled, button.add.disabled, .button[type=submit].disabled, button[type=submit].disabled, .button.confirm[disabled], button.confirm[disabled], .button.add[disabled], button.add[disabled], .button[type=submit][disabled], button[type=submit][disabled] {
        background-color: #beb;
    }
    .button.warning, button.warning {
        background-color: #f80;
        color: white;
    }
    .button.warning.disabled, button.warning.disabled, .button.warning[disabled], button.warning[disabled] {
        background-color: #beb;
    }
    .button.deny, button.deny, .button.delete, button.delete {
        background-color: #c00;
        color: white;
    }
    .button.deny.disabled, button.deny.disabled, .button.delete.disabled, button.delete.disabled, .button.deny[disabled], button.deny[disabled], .button.delete[disabled], button.delete[disabled] {
        background-color: #ebb;
    }
    .button.grow, button.grow {
        position: relative;
    }
    .button.grow, button.grow, .button.grow span, button.grow span, .button.grow .content, button.grow .content {
        transition: all 0.5s ease-in-out 0.5s, opacity 0.1s ease-in-out 0.9s;
    }
    .button.grow .content, button.grow .content {
        z-index: 5;
        position: absolute;
        left: 0;
        bottom: 0;
        clip-path: circle(0% at 0% 100%);
        border-radius: 1;
        opacity: 0;
    }
    .button.grow:active, button.grow:active, .button.grow:hover, button.grow:hover, .button.grow:focus, button.grow:focus, .button.grow:active span, button.grow:active span, .button.grow:hover span, button.grow:hover span, .button.grow:focus span, button.grow:focus span, .button.grow:active .content, button.grow:active .content, .button.grow:hover .content, button.grow:hover .content, .button.grow:focus .content, button.grow:focus .content {
        transition: all 0.5s ease-in-out, opacity 0.1s ease-in-out;
    }
    .button.grow:active span, button.grow:active span, .button.grow:hover span, button.grow:hover span, .button.grow:focus span, button.grow:focus span {
        opacity: 0;
    }
    .button.grow:active .content, button.grow:active .content, .button.grow:hover .content, button.grow:hover .content, .button.grow:focus .content, button.grow:focus .content {
        clip-path: circle(75%);
        opacity: 1;
    }

</style>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=yes">
    <title>Helpless Kiwi &mdash; <?php echo $title; ?></title>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-lg-offset-3 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">
                <div id="digidecs" class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Helpless Kiwi &mdash; <?php echo $title; ?></h3>
                    </div>
                    <div class="panel-body">
                        <?php if ($error) {
            echo '<p class="alert alert-warning">'.$error.' </p> <br>';
        } ?>
<?php
//endregion HTML_HEADER
//region HTML_FORMS
echo $step;
        //endregion HTML_FORMS
//region HTML_FOOTER?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
<?php

//endregion HTML_FOOTER
        exit;
    }
}

set_time_limit(0);
set_exception_handler(function (\Throwable $e) {
    Log::console($e->getMessage()."\n".$e->getTraceAsString());
    UserInterface::render('Probleem!', Log::read(true).'<a class="button grow" href="./update.php">Herstart updater</a>');
});

$application = new UserInterface();
$application->run();
