<?php

use App\Kernel;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Query\QueryException;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

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
    public static function read(bool $clear = true)
    {
        $log = $_SESSION['log'];
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
    public function getPublicPath()
    {
        return $this->getCommonPath().DIRECTORY_SEPARATOR.'public_html'.DIRECTORY_SEPARATOR.'kiwi';
    }

    /**
     * Get the root path where the Kiwi source is stored.
     *
     * @return string The root path for the Kiwi source
     */
    public function getRootPath()
    {
        return $this->getCommonPath().DIRECTORY_SEPARATOR.'kiwi';
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
                throw new FileNotFoundException('Symfony is not installed.');
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
    public function load()
    {
        if ($this->exists()) {
            $this->buffer = require $this->file();
        } else {
            $this->buffer = [];
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

        $accessfile = fopen($this->file(), 'w');
        fwrite($accessfile, $this->export());
        fclose($accessfile);
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
            $random_val = '';
            for ($i = 0; $i < 32; ++$i) {
                $random_val .= chr(random_int(65, 90));
            }
            $this->setVar('APP_SECRET', $random_val);
        }

        // Userprovider Key
        if (!$this->hasVar('USERPROVIDER_KEY')) {
            $random_val = '';
            for ($i = 0; $i < 32; ++$i) {
                $random_val .= chr(random_int(65, 90));
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

class ArchiveTool
{
    public function createFileBackup()
    {
        // Make backup dir if not existing.
        if (!file_exists(kiwidir(Dir::BACKUP_DIR))) {
            mkdir(kiwidir(Dir::BACKUP_DIR));
        }

        // Remove old backup
        if (file_exists(kiwidir(Dir::BACKUP_KIWI))) {
            unlink(kiwidir(Dir::BACKUP_KIWI));
        }

        // Start backup
        $backup = new ZipArchive();
        $backup->open(kiwidir(Dir::BACKUP_KIWI), ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $dirit = new RecursiveDirectoryIterator(kiwidir(Dir::KIWI_DIR));
        $filterit = new DirFilter($dirit, get_dir_exceptions(Dir::KIWI_DIR));
        $files = new RecursiveIteratorIterator($filterit,
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen(kiwidir(Dir::ROOT_DIR)) + 1);

                // Add current file to archive
                $backup->addFile($filePath, $relativePath);
            }
        }

        $dirit = new RecursiveDirectoryIterator(kiwidir(Dir::PUBLIC_DIR));
        $filterit = new DirFilter($dirit, get_dir_exceptions(Dir::PUBLIC_DIR));
        $files = new RecursiveIteratorIterator($filterit,
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen(kiwidir(Dir::ROOT_DIR)) + 1);

                // Add current file to archive
                $backup->addFile($filePath, $relativePath);
            }
        }

        // Zip archive will be created only after closing object
        $backup->close();
        unset($dirit);
        unset($filterit);
        unset($files);
        // Give the garbage collecter time to close all files.
        sleep(2);

        Log::msg('Legacy kiwi backup created.');
    }

    public function restoreFileBackup()
    {
        // If the kiwi backup exists, restore
        if (!file_exists(kiwidir(Dir::BACKUP_KIWI))) {
            echo 'Restoring the previous kiwi files... <br>';

            $di = new RecursiveDirectoryIterator(kiwidir(Dir::KIWI_DIR), FilesystemIterator::SKIP_DOTS);
            $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($ri as $file) {
                $file->isDir() ? rmdir($file) : unlink($file);
            }

            $di = new RecursiveDirectoryIterator(kiwidir(Dir::PUBLIC_DIR), FilesystemIterator::SKIP_DOTS);
            $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($ri as $file) {
                $file->isDir() ? rmdir($file) : unlink($file);
            }

            $zip = new ZipArchive();
            $zip->open(kiwidir(Dir::KIWI_DIR));
            $zip->extractTo(kiwidir(Dir::ROOT_DIR));
            $zip->close();

            Log::msg('Restored kiwi from the backup.');
        } else {
            Log::msg('No backup found.');
        }
    }

    /**
     * Extract the content.
     *
     * @param string $path archive path
     *
     * @return string name (not path!) of the subdirectory where files where extracted
     *                should look like <user>-<repository>-<lastCommitHash>
     */
    public function extractArchive($path, $dest)
    {
        // $archive = basename($path);
        $directory = '';

        $zip = new ZipArchive();
        if (true === $zip->open($path)) {
            $stat = $zip->statIndex(0);
            $directory = substr($stat['name'], 0, strlen($stat['name']) - 1);
            $zip->extractTo(dirname($path));
            $zip->close();
        } else {
            throw new \Exception('Archive extraction failed. The file might be corrupted and you should download it again.');
        }

        return $directory;
    }

    public function compressArchive($path)
    {
        $backup = new ZipArchive();
        $backup->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $dirit = new RecursiveDirectoryIterator(kiwidir(Dir::KIWI_DIR));
        $filterit = new DirFilter($dirit, get_dir_exceptions(Dir::KIWI_DIR));
        $files = new RecursiveIteratorIterator($filterit,
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen(kiwidir(Dir::ROOT_DIR)) + 1);

                // Add current file to archive
                $backup->addFile($filePath, $relativePath);
            }
        }

        $dirit = new RecursiveDirectoryIterator(kiwidir(Dir::PUBLIC_DIR));
        $filterit = new DirFilter($dirit, get_dir_exceptions(Dir::PUBLIC_DIR));
        $files = new RecursiveIteratorIterator($filterit,
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen(kiwidir(Dir::ROOT_DIR)) + 1);

                // Add current file to archive
                $backup->addFile($filePath, $relativePath);
            }
        }

        // Zip archive will be created only after closing object
        $backup->close();
        unset($dirit);
        unset($filterit);
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
    public function moveFilesRecursive($source, $destination)
    {
        $result = true;

        if (file_exists($source) && is_dir($source)) {
            if (!file_exists($destination)) {
                mkdir($destination);
            }

            $files = scandir($source);
            foreach ($files as $file) {
                if (in_array($file, ['.', '..'])) {
                    continue;
                }

                if (is_dir($source.DIRECTORY_SEPARATOR.$file)) {
                    $result = $this->moveFilesRecursive(
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
        }

        rmdir($source);

        return $result;
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
        $connection = mysqli_connect($this->host, $this->user, $this->pass);
        if (!$connection) {
            throw new ConnectionException(mysqli_connect_error());
        }
        Log::msg('Succesfully connected to the database server.');

        // Check whether database exists
        $res = mysqli_query($connection, 'SHOW DATABASES');
        $found = false;
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
            Log::msg('No matching data base found.');

            $sql = 'CREATE DATABASE '.$this->name;
            if (!$connection->query($sql)) {
                throw new QueryException('Error creating database: '.$connection->error);
            }

            Log::msg('Database created successfully.');
            $found = true;
        }

        $connection->close();

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
     * Create a backup of the database.
     */
    public function createBackup()
    {
        // Make backup dir if it doesn't exist yet
        $backupPath = $this->integration->getCommonPath().DIRECTORY_SEPARATOR.'backup';
        $backup = $backupPath.DIRECTORY_SEPARATOR.'database.sql';
        if (!file_exists($backupPath)) {
            mkdir($backupPath);
        }

        // Remove old backup
        if (file_exists($backup)) {
            unlink($backup);
        }

        $db = new mysqli($this->host, $this->user, $this->pass, $this->name);
        $dump = new MySQLDump($db);
        $dump->save($backup);
    }

    /**
     * Restore a backup of the database.
     */
    protected function restoreDatabaseBackup()
    {
        $backupPath = $this->integration->getCommonPath().DIRECTORY_SEPARATOR.'backup';
        $backup = $backupPath.DIRECTORY_SEPARATOR.'database.sql';

        $db = new mysqli($this->host, $this->user, $this->pass, $this->name);
        $dump = new MySQLImport($db);
        $dump->load($backup);
    }
}

class DownloadTool
{
    protected $server;
    protected $user;
    protected $repository;
    protected $releases;

    /**
     * Init the updater with remote repository information.
     *
     * @param string $user       user name
     * @param string $repository repository name
     * @param string $server     (optional) server name. Default: Github
     *                           useful for Github Enterprise using Github API v3
     */
    public function __construct($user, $repository, $server = 'https://api.github.com/')
    {
        $this->user = $user;
        $this->repository = $repository;
        $this->server = $server;
        $this->releases = false;
    }

    /**
     * Download archive for the given version directly from Github.
     *
     * @param string $archive path to the downloaded archive
     *
     * @return misc FALSE on failure, path to archive on success
     */
    public function downloadVersion($version, $archive)
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
    protected function getReleases($forceFetch = false)
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
    protected function getAssetUrl($version, $name)
    {
        $this->getReleases();

        // Find the asset by name
        foreach ($this->releases[$version]['assets'] ?? [] as $asset) {
            if ($asset['name'] == $name) {
                return $asset['url'];
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
    public function isUpToDate($version)
    {
        // Retrieve latest release
        $this->getReleases();
        reset($this->releases);
        $latest = current($this->releases);

        // Convert version strings to dates
        $currentVersion = date_create_from_format('d/m/Y', $version);
        $latestVersion = date_create_from_format('d/m/Y', $latest['tag_name']);

        // If conversion failed and raw strings are not equal, assume out of date
        if ((!$currentVersion || !$latestVersion) && $latest !== $version) {
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
    public function isCompatible($version)
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
    protected function getServerRequirements($version)
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
    protected function downloadAndVerifyAsset($version, $name, $path = false)
    {
        $url = $this->getAssetUrl($version, $name);
        $contents = $this->downloadContent($url, $path);

        $checksums = $this->getChecksums($version);
        if ($checksums[$name] !== hash('sha512', $contents)) {
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
    public function getChecksums($version)
    {
        $url = $this->getAssetUrl($version, 'hashes.txt');
        $checksumData = $this->downloadContent($url);

        // Read data
        $checksums = [];
        foreach (explode('\n', $checksumData) as $rule) {
            list($asset, $checksum) = preg_split('/\s+/', $rule);
            $checksums[$asset] = $checksum;
        }

        return $checksums;
    }

    /**
     * Perform a request to Github API.
     *
     * @param string $url URL to get
     *
     * @return string Github's response
     */
    protected function downloadContent($url, $path = false)
    {
        //use curl if possible
        if (function_exists('curl_version')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_USERAGENT, 'helpless-kiwi');
            if (false !== $path) {
                if (!file_exists(dirname($path))) {
                    mkdir(dirname($path));
                }
                touch($path);
                $file = fopen($path, 'w+');
                curl_setopt($ch, CURLOPT_FILE, $file);
                curl_setopt($ch, CURLOPT_HEADER, 0);
            }
            $content = curl_exec($ch);
            curl_close($ch);
            if (false !== $path) {
                fclose($file);
            }

            //fallback - might raise a warning with proxies
        } else {
            $content = file_get_contents($url);
        }

        if (empty($content)) {
            throw new \Exception('Fetch data from Github failed. You might be behind a proxy.');
        }

        return $content;
    }
}

class Updater
{
    protected $integration;
    protected $env;
    protected $interface;
    protected $download;
    protected $archive;
    protected $database;

    public function __construct()
    {
        // Check if script location is correct
        $this->integration = new IntegrationTool();
        $this->integration->envIsValid();

        // Setup base dependencies
        $this->env = new EnvFileTool($this->integration->getRootPath());
        $this->interface = new UserInterface($this->env);
        $this->download = new DownloadTool('jasperweyne', 'helpless-kiwi');
    }

    public function run()
    {
        // Check if login is possible
        $this->env->hasVar('UPDATER_PASSWORD') || $this->interface->registerPassword();

        // Check if logged in
        $this->interface->isLoggedIn() || $this->interface->login();

        // Check if database is configured and exists
        $this->archive = new ArchiveTool();
        $this->database = new DatabaseTool($this->env->getVar('DATABASE_URL'), $this->integration);
        $this->database->exists(true) || $this->interface->registerDatabase();

        // Check if backup should be reverted
        $this->revert();

        // Only one session may run an update (step), lock the install process
        $this->update();

        // Check if default environment parameters are setVar
        $this->env->defaults();

        // Check if application name is confirm_update
        $this->env->hasVar('ORG_NAME') || $this->interface->registerName();

        // Check if mailer is configured
        $this->env->hasVar('MAILER_URL') || $this->interface->registerMailer();

        // Check if bunny is configured or admin account has been added
        $this->env->hasVar('BUNNY_ENABLED') || $this->interface->registerUser();

        // Check if application is up to date
        if (!$this->download->isUpToDate($this->env->getVar('INSTALLED_VERSION'))) {
            $compatible = $this->download->isCompatible($this->download->getLatestVersion());
            $this->interface->update($compatible);
        }

        // Everything checks out, let the user know
        $this->interface->render('Up-to-date!', '<p>Je draait de laatste versie van Kiwi.</p>');
    }

    protected function update()
    {
        if ($this->isInstalling()) {
            try {
                // Lock the install process
                $this->lock();

                // Download variables
                $latest = $this->download->getLatestVersion();
                $installpath = $this->integration->getCommonPath().DIRECTORY_SEPARATOR.'install';
                $downloadfile = $installpath.DIRECTORY_SEPARATOR.$latest.'.zip';

                // Check if backup can be made
                if ($this->integration->hasApplication() && !file_exists($downloadfile)) {
                    // Check if database backup has been made
                    if (!$this->database->hasBackup()) {
                        $this->database->createBackup();
                        $this->break();
                    }

                    // Check if file backup can be/has been made
                    if (!$this->archive->hasBackup()) {
                        $this->archive->createBackup();
                        $this->break();
                    }
                }

                // Check if version is downloaded
                if (!file_exists($downloadfile)) {
                    $this->download->downloadVersion($latest, $downloadfile);
                    $this->break();
                }

                // Check if download is unpacked
                $extractdir = $installpath.DIRECTORY_SEPARATOR.'extract';
                if (!file_exists($extractdir)) {
                    $this->archive->extractArchive($downloadfile, $extractdir);
                    $this->break();
                }

                // Check if unpacked version has been moved
                if (!$this->download->isUpToDate($this->env->getVar('INSTALLED_VERSION'))) {
                    $result = $this->archive->moveFilesRecursive(
                        $extractdir,
                        $this->integration->getCommonPath()
                    );
                    $this->break();
                }

                // Run migrations if presentry
                if (!$this->database->canMigrate()) {
                    $this->database->migrateDb();
                    $this->break();
                }

                // Installation finished succesfully, remove backups
                // todo
            } finally {
                // Always unlock the installation on exceptions
                $this->endInstallation();
            }
        }
    }

    protected function revert()
    {
        if ($this->isInstalling()) {
            try {
                // Lock the install process
                $this->lock();

                // Check if database backup has been made
                if (!$this->database->hasBackup()) {
                    $this->database->revertBackup();
                    $this->break();
                }

                // Check if file backup can be/has been made
                if (!$this->archive->hasBackup()) {
                    $this->archive->revertBackup();
                    $this->break();
                }
            } finally {
                // Always unlock the installation on exceptions
                $this->endInstallation();
            }
        }
    }

    protected function break()
    {
        $this->unlock();
        header('Refresh: 1');
        $this->interface->displayLog();
    }

    protected function isLocked()
    {
        return file_exists($this->lockfile());
    }

    protected function lock()
    {
        if ($this->isLocked()) {
            throw new \Exception('Installer already locked.');
        }

        return touch($this->lockfile());
    }

    protected function unlock()
    {
        if (!$this->isLocked()) {
            return true;
        }

        return unlink($this->lockfile());
    }

    protected function isInstalling()
    {
        return file_exists($this->installationfile());
    }

    protected function beginInstallation()
    {
        // set maintanance mode, indicating installation
        touch($this->installationfile());

        // extend timelimit
        $accessfile = fopen($this->integration->getPublicPath().DIRECTORY_SEPARATOR.'.htaccess', 'w');
        $access = '#Extend execution time
<IfModule mod_php5.c>
    php_value max_execution_time 0
</IfModule>';
        fwrite($accessfile, $access);
        fclose($accessfile);
    }

    protected function endInstallation()
    {
        // unset maintanance mode
        unlink($this->installationfile());

        $this->unlock();
    }

    private function installationfile()
    {
        return $this->integration->getPublicPath().DIRECTORY_SEPARATOR.'enable-maintenance.txt';
    }

    private function lockfile()
    {
        return $this->integration->getCommonPath().DIRECTORY_SEPARATOR.'installer.lock';
    }
}

class UserInterface
{
    protected $env;
    protected $error;
    protected $error_type;

    public function __construct(EnvFileTool $env)
    {
        $this->env = $env;
    }

    /**
     * Check whether the current user (session) is authenticated.
     */
    public function isLoggedIn(): bool
    {
        if (!$this->env->hasVar('UPDATER_PASSWORD')) {
            return false;
        }

        session_start();

        return password_verify($_SESSION['secret'] ?? '', $this->env->getVar('UPDATER_PASSWORD'));
    }

    public function displayLog($clear = false)
    {
        $log = $_SESSION['log'];
        if ($clear) {
            unset($_SESSION['log']);
        }

        $this->render('Probleem', "
<p>Kiwi is helaas niet correct geinstalleerd </p>
<h4>Error log:</h4>
<p>$log</p>
        ");
    }

    public function registerPassword()
    {
        // Check POST data
        if ('POST' == $_SERVER['REQUEST_METHOD'] && 'register' == $_POST['action']) {
            $this->env->setVar('UPDATER_PASSWORD', password_hash($_POST['password'], PASSWORD_BCRYPT));
            $this->env->save();
            $_SESSION['secret'] = $_POST['password'];

            return;
        }

        $this->render('Welkom bij de Kiwi installer', '
<p>Welkom by de Kiwi installatie. Registreer een wachtwoord voor de installer.</p>
<form role="form" method="post">
    <input type="hidden" name="action" value="register" />
    <input type="password" name="password"'.$this->fill($_POST['password']).' />
    <input type="submit" class="button grow" value="intro" />
</form>
        ');
    }

    public function login()
    {
        // Check POST data
        if ('POST' == $_SERVER['REQUEST_METHOD'] && 'login' == $_POST['action']) {
            if (password_verify($_POST['password'], $this->env->getVar('UPDATER_PASSWORD'))) {
                $_SESSION['secret'] = $_POST['password'];

                return;
            } else {
                // todo: Show invalid password error
            }
        }

        $this->render('Welkom bij de Kiwi installer', '
<p>Welkom by de Kiwi installatie. Log in om door te gaan.</p>
<form role="form" method="post">
    <input type="hidden" name="action" value="login" />
    <input type="password" name="password"'.$this->fill($_POST['password']).' />
    <input type="submit" class="button grow" value="intro" />
</form>
        ');
    }

    public function registerDatabase()
    {
        // Check POST data
        if ('POST' == $_SERVER['REQUEST_METHOD'] && 'register' == $_POST['action']) {
            // Extract POST data
            $db_username = trim($_POST['db_user']);
            $db_password = trim($_POST['db_pass']);
            $db_host = trim($_POST['db_host']);
            $db_name = trim($_POST['db_name']);
            $db_type = $_POST['db_type'];

            // Test configuration
            $url = "mysql://$db_username:$db_password@$db_host:3306/$db_name?serverVersion=$db_type";
            $database = new DatabaseTool($url);
            if ($database->exists()) {
                $this->env->setVar('DATABASE_URL', $url);
                $this->env->save();
                $_SESSION['secret'] = $_POST['password'];

                return;
            } else {
                // todo: show error
            }
        }

        $this->render('Database Configuratie', '
<p>Welkom by de Kiwi installatie. Registreer een wachtwoord voor de installer.</p>
<form role="form" method="post">
    <input type="hidden" name="action" value="database" />
    <div class="form-group">
        <label class="radio-inline"><input type="radio" name="db_type"'.$this->checked('mariadb-10.5.8', $db_type).'>Maria DB</label>
        <label class="radio-inline"><input type="radio" name="db_type"'.$this->checked('5.7', $db_type).'>MySQL DB</label>
    </div>
    <div class="form-group">
        <label for="db_name">Database name<sup>*</sup></label>
        <input type="text" class="form-control" id="db_name" name="db_name" placeholder="" '.$this->fill($db_name).'required />
    </div>
    <div class="form-group">
        <label for="db_host">Database host<sup>*</sup></label>
        <input id="db_host" name="db_host" type="text" class="form-control" placeholder="EXAMPLE MAIL URL"'.$this->fill($db_host).'required />
    </div>
    <div class="form-group">
        <label for="db_user">Database username<sup>*</sup></label>
        <input id="db_user" name="db_user" type="text" class="form-control" placeholder="EXAMPLE MAIL URL"'.$this->fill($db_username).'required />
    </div>
    <div class="form-group">
        <label for="db_pass">Database password</label>
        <input id="db_pass" name="db_pass" type="text" class="form-control" placeholder="EXAMPLE MAIL URL"'.$this->fill($db_password).'/>
    </div>
    <p>Velden met een <sup>*</sup> zijn verplicht</p>
    <input type="submit" class="button grow" value="Opslaan">
</form>
        ');
    }

    public function registerMailer()
    {
        if ('POST' == $_SERVER['REQUEST_METHOD'] && 'email' == $_POST['action']) {
            // Extract POST data
            $mailer_url = trim($_POST['mailer_url']);
            $mailer_email = trim($_POST['mailer_email']);
            $email_type = $_POST['email_type'];

            // Test configuration
            if ($this->validate_url($mailer_url) && $this->validate_email($mailer_email)) {
                if ('smtp' === $email_type) {
                    $this->env->setVar('MAILER_URL', $mailer_url);
                    $this->env->setVar('DEFAULT_FROM', $mailer_email);
                } else {
                    $this->env->setVar('MAILER_URL', 'null://localhost');
                }
                $this->env->save();

                return;
            } else {
                // todo: show error
            }
        }

        $this->render('E-mail configuratie', '
<form role="form" method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="email" />
    <div class="form-group">
        <label class="radio-inline"><input type="radio" name="email_type"'.$this->checked('smtp', $email_type).'>SMTP e-mail</label>
        <label class="radio-inline"><input type="radio" name="email_type"'.$this->checked('noemail', $email_type).'>Geen e-mail</label>
    </div>
    <div class="form-group">
        <label for="mailer_url">Swift mailer URL</label>
        <input id="mailer_url" name="mailer_url" type="text" class="form-control" placeholder="EXAMPLE MAIL URL"'.$this->fill($mailer_url).'>
    </div>
    <div class="form-group">
        <label for="mailer_email">E-mailadres</label>
        <input type="mailer_email" class="form-control" id="mailer_email" name="mailer_email" placeholder="gigantischebaas@viakunst-utrecht.nl"'.$this->fill($mailer_email).'>
    </div>
    <input type="submit" class="button grow" value="Opslaan">
</form>
        ');
    }

    protected function validate_email($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    protected function validate_url($url)
    {
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    protected function fill($posted): string
    {
        return $posted ? ' value="'.$posted.'" ' : '';
    }

    protected function checked($value, $posted = false): string
    {
        return ' value="'.$value.'" '.($value === $posted ? 'checked ' : '');
    }

    public static function render($title, $step, $error = null)
    {
        //region HTML_HEADER ?>
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
            echo '<p>'.$error.' </p> <br>';
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
set_exception_handler(function (\Exception $e) {
    Log::console($e->getMessage());
    UserInterface::render('Probleem!', Log::read(true));
});

$application = new Updater();
$application->run();
