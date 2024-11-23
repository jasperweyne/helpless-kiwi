<?php

class Reverter
{
    public function run(): void
    {
        // Validate that the reverter can run
        $this->checkReqs();

        // Check if logged in
        $this->login();

        // Run the reverter
        $this->revert();
    }

    /**
     * Load the environment settings from the filesystem.
     */
    private function env(?string $key = null): mixed
    {
        static $env = null;
        if (null === $env) {
            $path = self::path('kiwi/.env.local.php');
            $env = file_exists($path) ? require $path : [];
        }

        return $key ? $env[$key] : $env;
    }

    /**
     * Validate the environment of the updater script.
     *
     * @throws Exception If the project's environment is somehow invalid
     */
    public function checkReqs(): void
    {
        // Check request URI
        if ('/revert.php' !== $_SERVER['REQUEST_URI']) {
            throw new Exception('The server is setup incorrectly. The request URI should be /revert.php, but it\'s '.$_SERVER['REQUEST_URI']);
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

    private function login(): void
    {
        // If no password is registered yet, create one
        if (null === $passwd = $this->env('UPDATER_PASSWORD')) {
            header('Location: update.php');
            exit;
        }

        // Check if logged in
        session_start();
        if (password_verify($_SESSION['secret'] ?? '', $passwd)) {
            return;
        }

        // Not logged in, show login screen
        $result = self::form('Log in', '<p>Welkom bij de Kiwi backuptool. Log in om door te gaan.</p>', [
            'Wachtwoord' => ['type' => 'password', 'required' => true, 'filter' => fn ($pass) => password_verify($pass, $passwd)],
        ]);

        $_SESSION['secret'] = $result['Wachtwoord'];
    }

    private function revert(): void
    {
        // Select all revertable zips from disk
        $files = array_diff(scandir($backup = self::path('install/backup')), ['.', '..']);
        $zips = array_filter($files, fn ($n) => str_ends_with($n, '.zip'));
        $sqls = array_filter($files, fn ($n) => str_ends_with($n, '.sql'));
        $removeExt = fn ($filename) => preg_replace('/\.\w+$/', '', $filename);
        $backups = array_intersect(array_map($removeExt, $zips), array_map($removeExt, $sqls));

        if (empty($backups)) {
            self::render('Backuptool', '<p>Geen backups beschikbaar om terug te zetten.</p>');
        }

        // Server configuration checks out, show update screen
        $data = self::form('Backuptool', '<p>Selecteer een backup om terug te zetten.</p>', [
            'version' => [
                'type' => 'radio',
                'options' => array_combine($backups, $backups),
            ],
        ]);

        // Lock the install process
        $log = fopen($logFile = self::path('update.log'), 'a+');
        if (!flock($log, LOCK_EX | LOCK_NB)) {
            self::render('Error!', 'Another instance is currently executing the updater');
        }

        try {
            // Start revert process
            $version = $data['version'];
            touch(self::path('public_html/kiwi/enable-maintenance.txt'));

            // Revert database
            require_once self::path('kiwi/vendor/autoload.php');
            $dumper = new MySQLImport($this->database($this->env('DATABASE_URL')));
            $dumper->load("$backup/$version.sql");

            // Remove remaining files from previous version (including this updater, it will still be in PHP memory)
            $this->rmdir(self::path('public_html'));
            $this->rmdir(self::path('kiwi'));

            // Install kiwi and public_html folders of the backup
            $this->extract("$backup/$version.zip", self::path());
        } finally {
            fclose($log);
            unlink($logFile);
        }

        self::render('Backuptool', "<p>Backup '$version' succesvol terug gezet!</p><a class=\"btn btn-success\">Herstart backuptool</a>");
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
            throw new Exception('The script is in the wrong location. Its location should end with "/public_html/kiwi/revert.php", but it\'s current location is '.__FILE__);
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
    Reverter::render('Probleem!', '<pre>'.$e->getMessage()."\n".$e->getTraceAsString().'</pre>');
});

$reverter = new Reverter();
$reverter->run();
