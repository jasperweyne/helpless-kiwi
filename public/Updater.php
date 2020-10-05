<?php

$ini = parse_ini_file('install.ini');

// MySQL host
$mysql_host = $ini['db_host'];
// MySQL username
$mysql_username = $ini['db_user'];
// MySQL password
$mysql_password = $ini['db_pass'];
// Database name
$mysql_database = $ini['db_name'];

$download = true;
$backup = false;

$backupPath = dirname(__FILE__,3).'/backup_kiwi.zip';

//Used in checks, do not change these vars.
$contine = true;
$revert = true;





if ($download) {
    echo "Starting download... \t\t\t";
    //Delete previous temp file and make a new one.
    $tempPath = dirname(__FILE__,3).'/tempkiwi.zip';
    if (file_exists($tempPath)) {
        unlink($tempPath);
    }
    $tempFile = fopen($tempPath, 'w+');

    $release_url = 'https://api.github.com/repos/jasperweyne/helpless-kiwi/releases/latest';

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $release_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent:jasperweyne']);

    $release_info = curl_exec($ch);
    if (empty(false == curl_error($ch))) {
        echo "Curl error found \n";
        $contine = false;
        var_dump(curl_error($ch));
    }

    if ($contine) {
        //$content2 = file_get_contents($url);
        $decoded_release_info = json_decode($release_info, true);
        $download_url = $decoded_release_info['assets']['0']['browser_download_url'];

        curl_setopt($ch, CURLOPT_URL, $download_url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 50);
        curl_setopt($ch, CURLOPT_FILE, $tempFile);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_exec($ch);

        if (empty(false == curl_error($ch))) {
            echo "Curl error found \n";
            $contine = false;
            var_dump(curl_error($ch));
        }

        curl_close($ch);

        fclose($tempFile);
        echo "Finished downloading.\n";
    }

    //check if there are files to backup.
    if ($contine) {
        if (file_exists(dirname(__FILE__,3).'/kiwi') && file_exists(dirname(__FILE__,3).'/public_html')) {
            echo "Legacy kiwi folders found. \n";
        } else {
            $backup = false;
            echo "No previous kiwi files found. \n";
        }
    }

    if ($contine && $backup) {
        echo "Creating backup of kiwi files...\t";
        //backup before overwriting the main file.

        $backup = new ZipArchive();
        $backup->open($backupPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $rootPath = dirname(__FILE__,3).'/kiwi';
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        $rootPath = dirname(__FILE__,3);
        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                // Add current file to archive
                $backup->addFile($filePath, $relativePath);
            }
        }

        $rootPath = dirname(__FILE__,3).'/public_html';
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        $rootPath = dirname(__FILE__,3);
        foreach ($files as $name => $file) {
            // Skip directories (they would be added automatically)
            if (!$file->isDir()) {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                // Add current file to archive
                $backup->addFile($filePath, $relativePath);
            }
        }
        // Zip archive will be created only after closing object
        $backup->close();
        echo "Backup created. \n";
    }

    if ($contine) {
        echo "Unzipping the kiwi files... \t\t";
        $zip = new ZipArchive();
        $zip->open($tempPath);
        $zip->extractTo(dirname(__FILE__,3));
        $zip->close();

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
        echo "Finished unzipping kiwi. \n";
    }
}

//Generate env files.
if ($contine) {
    $envpath = dirname(__FILE__,3).'\kiwi\.env';
    $generate = false;
    if (file_exists($envpath)) {
        echo ".env file found.\n";
    } else {
        echo "WARNING no .env file found.\n";
        echo "\n";
        $contine = true;
        $generate = true;
    }

    //Not used, as we have a env sample 
    if ($generate) {
        echo "Generating .env file... \t\t";
        $envfile = fopen($envpath, 'w+');
        $line = "APP_DEBUG=0\n";
        fwrite($envfile, $line);
        $line = "APP_ENV=prod\n";
        fwrite($envfile, $line);
        $line = "APP_SECRET=6badc0fca270ab84a00a67226f9e2554\n";
        fwrite($envfile, $line);
        $line = "MAILER_URL=null://localhost=prod\n";
        fwrite($envfile, $line);
        $line = "DEFAULT_FROM=foo@bar.com\n";
        fwrite($envfile, $line);
        $line = 'DATABASE_URL=mysql://'.$mysql_username.':'.$mysql_password.'@'.$mysql_host.':3306/'.$mysql_database."\n";
        fwrite($envfile, $line);

        echo "Finished generating .env file.\n";
    }
}


$filldatabase = false;
if ($contine) {
    echo "Checking the sql database... \t\t";
    $connection = new mysqli($mysql_host, $mysql_username, $mysql_password);
    if (empty(mysqli_fetch_array(mysqli_query($connection, "SHOW DATABASES LIKE '$mysql_database'")))) {
        echo "No data base found.\n";
        echo "\n";
        $input = readline("\nDo you want to create a database with the name '$mysql_database'? (y/n) \n");
        echo "\n";
        if ('y' == $input) {
            echo "Creating database... \t\t";
            $sql = "CREATE DATABASE $mysql_database";
            if (true === $connection->query($sql)) {
                echo "Database created successfully.\n";
            } else {
                echo "\nError creating database: ".$connection->error;
                $contine = $false;
            }

        } else {
            echo "No further steps will be taken. Without a database kiwi will not function. \n";
            echo "Manual installation of the database is not recommended. \n";
            $contine = false;
        }
    } else {
        echo "Database already exists.\n";
        mysqli_select_db($connection, $mysql_database);
        echo "Checking database compatibility... \t";

        if (0 == mysqli_fetch_array(mysqli_query($connection, "SELECT COUNT(DISTINCT `table_name`) FROM `information_schema`.`columns` WHERE `table_schema` = '$mysql_database'
        "))[0]) {
            echo "\n";
            $input = readline("Database is empty, use this database '$mysql_database' for kiwi? (y/n) \n");
            if ('y' == $input) {
                
            } else {
                echo "No further steps will be taken. Without a database kiwi will not function. \n";
                echo "Manual installation of the database is not recommended. \n";
                $contine = false;
            }
        } else {
            
            if ($contine) {
                echo "Database compatible.\n";
            }
        }
    }
}


//Doctrine commands
use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;

use Symfony\Component\Console\Output\BufferedOutput;

set_time_limit(0);

require_once dirname(__FILE__,3).'/kiwi/vendor/autoload.php';

// Initialize symfony, to run symfony and doctine commands.
$input = new ArgvInput();
if (null !== $env = $input->getParameterOption(['--env', '-e'], null, true)) {
    putenv('APP_ENV='.$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = $env);
}

if ($input->hasParameterOption('--no-debug', true)) {
    putenv('APP_DEBUG='.$_SERVER['APP_DEBUG'] = $_ENV['APP_DEBUG'] = '0');
}

require_once dirname(__FILE__,3).'\kiwi\config\bootstrap.php';

// Production enviroment, because dev bundles are not included in the release.
$env = $input->getParameterOption(['--env', '-e'], getenv('SYMFONY_ENV') ?: 'prod');
$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG'], dirname(__DIR__,3));
$application = new Application($kernel);
$application->setAutoExit(false);

$output = new BufferedOutput();

// Warm up the cache
if ($contine) {
    echo "Warming up the symfony cache... \t\t";
    
    $input = new ArrayInput([
        'command' => 'cache:warmup',
        // (optional) define the value of command arguments
        '--env' => 'prod',
    ]);

    $output = new BufferedOutput();
    // Run the command in the symfony framework.
    $succes = $application->run($input,$output);
    echo $output->fetch();
    if ($succes==0){
        echo "\nCache warm-up finished. \n";
    } else {
        echo "\nCache warm-up failed. \n";
        $contine = false;
        $revert = true;
    }
    
}

// Run migrations, hopefully succesfull.
if ($contine) {
    
    echo "Running doctrine migrations... \t\t";

    $input = new ArrayInput([
        'command' => 'doctrine:migrations:migrate',
        // (optional) define the value of command arguments
        '-n' => '-n',
        '-v' => '--allow-no-migration'
    ]);

    $output = new BufferedOutput();
    // Run the command in the symfony framework.
    $succes = $application->run($input,$output);
        
    if ($succes==0){
        echo "\nDoctrine migration finished. \n";
    } else {
        echo "\nDoctrine migration failed. \n";
        $contine = false;
        $revert = true;
    }
}

//Backup and stuff.
if ($revert && $backup) {
    echo "Restoring the previous kiwi files... \t";

    if (!file_exists($backupPath)) {
        echo 'error.';
    }

    $dir = dirname(__FILE__,3).'/kiwi';
    $di = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
    $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($ri as $file) {
        $file->isDir() ? rmdir($file) : unlink($file);
    }

    $dir = dirname(__FILE__,3).'/public_html';
    $di = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
    $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($ri as $file) {
        $file->isDir() ? rmdir($file) : unlink($file);
    }

    $zip = new ZipArchive();
    $zip->open($backupPath);
    $zip->extractTo(dirname(__FILE__,3));
    $zip->close();

    echo "Finished restoring kiwi. \n";
}
