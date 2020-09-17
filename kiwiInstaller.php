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

$backupPath = dirname(__FILE__).'/backup_kiwi.zip';

//Used in checks, do not change these vars.
$contine = true;
$revert = true;

if ($download) {
    echo "Starting download... \t\t\t";
    //Delete previous temp file and make a new one.
    $tempPath = dirname(__FILE__).'/tempkiwi.zip';
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
        if (file_exists(dirname(__FILE__).'/kiwi') && file_exists(dirname(__FILE__).'/public_html')) {
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

        $rootPath = dirname(__FILE__).'/kiwi';
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        $rootPath = dirname(__FILE__);
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

        $rootPath = dirname(__FILE__).'/public_html';
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($rootPath),
            RecursiveIteratorIterator::LEAVES_ONLY
        );

        $rootPath = dirname(__FILE__);
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
        $zip->extractTo(dirname(__FILE__));
        $zip->close();

        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
        echo "Finished unzipping kiwi. \n";
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

            $filldatabase = true;
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
                $filldatabase = true;
            } else {
                echo "No further steps will be taken. Without a database kiwi will not function. \n";
                echo "Manual installation of the database is not recommended. \n";
                $contine = false;
            }
        } else {
            if (empty(mysqli_fetch_row(mysqli_query($connection, "SHOW TABLES LIKE 'doctrine_migration_versions'")))) {
                echo "Doctrine migration versions is missing from the tables. \n";
                echo "You might have a legacy version of kiwi, please contact the developers on github. \n";
                $contine = false;
            }

            if ($contine) {
                echo "Database compatible.\n";
            }
        }
    }
}

if ($filldatabase && $contine) {
    echo "Attempting to import the kiwi tables...\t";
    mysqli_select_db($connection, $mysql_database);

    $templine = '';
    // Read in entire file
    $lines = file(dirname(__FILE__).'/kiwi/sql/sql_initial.sql');
    // Loop through each line
    foreach ($lines as $line) {
        // Skip it if it's a comment
        if ('--' == substr($line, 0, 2) || '' == $line) {
            continue;
        }

        // Add this line to the current segment
        $templine .= $line;
        // If it has a semicolon at the end, it's the end of the query
        if (';' == substr(trim($line), -1, 1)) {
            // Perform the query
            mysqli_query($connection, $templine) or print 'Error performing query \'<strong>'.$templine.'\': '.$connection->error.'<br /><br />';
            // Reset temp variable to empty
            $templine = '';
        }
    }

    echo "Import succesfull.\n";
}

//Generate env files.
if ($contine) {
    $envpath = dirname(__FILE__).'\kiwi\.env';
    $generate = false;
    if (file_exists($envpath)) {
        echo ".env file found.\n";
    } else {
        echo "no .env file found.\n";
        $generate = true;
    }

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

//Doctrine commands
// ???
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use App\Kernel;

set_time_limit(0);

// What?
require_once dirname(__FILE__).'\kiwi\config\bootstrap.php';
require_once dirname(__FILE__).'\kiwi\vendor\symfony\framework-bundle\Tests\Functional\app\AppKernel.php';
require_once dirname(__FILE__).'\kiwi\vendor\symfony\http-kernel\Kernel.php';
require_once dirname(__FILE__).'\kiwi\vendor\symfony\http-kernel\Kernel.php';

// Run migrations, hopefully succesfull.
if ($contine) {
    echo "Running doctrine migrations... \t\t";

    //Add extra flags in the 3,4,5 etc argv array.
    $input = new ArgvInput([1 => 'not important appearently', 2 => 'doctrine:migrations:migrate', 3 => '-n', 4 => '--allow-no-migration']);
    // production enviroment, because dev bundles are not included in the release.
    $env = $input->getParameterOption(['--env', '-e'], getenv('SYMFONY_ENV') ?: 'prod');

    // Initiliaze symfony, just to run the doctrine commands.
    $kernel = new Kernel($env, false);
    $application = new Application($kernel);
    $application->setAutoExit(false);

    $output = new BufferedOutput();
    // Run the command in the symfony framework.
    $application->run($input);

    echo "\nDoctrine migration finished. \n";
}

if ($revert && $backup) {
    echo "Restoring the previous kiwi files... \t";

    if (!file_exists($backupPath)) {
        echo 'error.';
    }

    $dir = dirname(__FILE__).'/kiwi';
    $di = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
    $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($ri as $file) {
        $file->isDir() ? rmdir($file) : unlink($file);
    }

    $dir = dirname(__FILE__).'/public_html';
    $di = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
    $ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($ri as $file) {
        $file->isDir() ? rmdir($file) : unlink($file);
    }

    $zip = new ZipArchive();
    $zip->open($backupPath);
    $zip->extractTo(dirname(__FILE__));
    $zip->close();

    echo "Finished restoring kiwi. \n";
}
