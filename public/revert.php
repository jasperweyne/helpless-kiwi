<?php

require_once 'update.php';

class Reverter extends Updater
{
    public function run(): void
    {
        // Validate that the reverter can run
        $this->checkReqs('revert.php');

        // Load the environment variables
        $this->loadEnv();

        // Check if logged in
        $this->login();

        // Run the reverter
        $this->revert();
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
            touch($disabler = self::path('public_html/kiwi/enable-maintenance.txt'));

            // Revert database
            require_once self::path('kiwi/vendor/autoload.php');
            $dumper = new MySQLImport($this->database($this->env['DATABASE_URL']));
            $dumper->load("$backup/$version.sql.gz");

            // Remove remaining files from previous version (including this updater, it will still be in PHP memory)
            $this->rmdir(self::path('public_html'));
            $this->rmdir(self::path('kiwi'));

            // Install kiwi and public_html folders of the backup
            $this->extract("$backup/$version.zip", self::path());
            unlink($disabler);
        } finally {
            fclose($log);
            unlink($logFile);
        }

        self::render('Backuptool', "<p>Backup '$version' succesvol terug gezet!</p><a class=\"btn btn-success\" href=\"/revert.php\">Herstart backuptool</a>");
    }
}

set_time_limit(0);
set_exception_handler(function (Throwable $e) {
    Reverter::render('Probleem!', '<pre>'.$e->getMessage()."\n".$e->getTraceAsString().'</pre>');
});

$reverter = new Reverter();
$reverter->run();
