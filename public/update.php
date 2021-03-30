<?php

//Check if test
$unit_test = $_SESSION['unit_test'] ?? false;

if (!$unit_test) {
    session_start();
    set_time_limit(0);
}

class ScriptLocationException extends Exception
{
}

class DownloadException extends Exception
{
}

abstract class Step
{
    const INTRO = 'intro';
    const INTRO_INSTALL = 'intro-install';
    const INTRO_UPDATE = 'intro-update';
    const DATABASE_CHOICE = 'database-choice';
    const DATABASE = 'database';
    const EMAILER_CHOICE = 'emailer-choice';
    const BACKUP_OVERWRITE_WARNING = 'overwrite';
    const EMAILER = 'emailer';
    const SECURITY = 'security';
    const BUNNY = 'bunny';
    const ADMIN = 'admin';
    const UPDATE_PASSWORD = 'password';
    const CONFIRM_INSTALL = 'confirm-install';
    const SUCCESS_INSTALL = 'success-install';
    const FAILURE = 'failure';
    const UPDATE = 'update';
    const CONFIRM_UPDATE = 'confirm-update';
    const SUCCESS_UPDATE = 'success-update';
    const PROGRESS_UPDATE = 'progress-update';
    const PROGRESS_INSTALL = 'progress-install';
}

abstract class Progress
{
    const START = 'start';
    const FINISH = 'finish';
    const DATABASE = 'database';
    const DOCTRINE = 'doctrine';
    const ENV_FILE = 'env_file';
    const DOWNLOAD = 'download';
    const SQL_BACKUP = 'sql_backup';
    const KIWI_BACKUP = 'kiwi_backup';
    const RESTORE = 'restore';
}

abstract class Dir
{
    const ROOT_DIR = '';
    const KIWI_DIR = '/kiwi';
    const PUBLIC_DIR = '/public_html';
    const DOWNLOAD_ZIP = '/tempkiwi.zip';
    const BACKUP_DIR = '/back_up';
    const HT_ACCESS = '/public_html/kiwi/htaccess';
    const BACKUP_KIWI = '/back_up/kiwi_backup.zip';
    const BACKUP_SQL = '/back_up/sql_dump.sql';
}

class Log
{
    public static function msg(string $msg)
    {
        if (!isset($_SESSION['log'])) {
            $_SESSION['log'] = '';
        }

        $_SESSION['log'] .= str_replace("\n", '<br>', $msg).'<br>';
    }

    public static function console(string $msg)
    {
        self::msg('<pre>'.$msg.'</pre>');
    }
}

class Screen
{
    public $title;
    public $render;
    public $actions;
    public $exec;

    public function __construct($title, $render, $actions = [], $exec = null)
    {
        $this->title = $title;
        $this->render = $render;
        $this->actions = $actions;
        $this->exec = $exec;
    }
}

use App\Kernel;
use Doctrine\DBAL\ConnectionException;

class DirFilter extends RecursiveFilterIterator
{
    protected $exclude;

    public function __construct($iterator, array $exclude)
    {
        parent::__construct($iterator);
        $this->exclude = $exclude;
    }

    public function accept()
    {
        return !(is_dir($this->current()) && in_array($this->current()->getFilename(), $this->exclude));
    }

    public function getChildren()
    {
        $it = $this->getInnerIterator();
        if ($it instanceof RecursiveIterator) {
            return new DirFilter($it->getChildren(), $this->exclude);
        } else {
            // error here
        }
    }
}

//Delete session cookie and redirect at button click
if (isset($_SESSION['step']) && !$unit_test) {
    if ((Step::SUCCESS_INSTALL == $_SESSION['step'] || Step::SUCCESS_UPDATE == $_SESSION['step']) && isset($_POST['action']) && $_SESSION['step'] == $_POST['action']) {
        unset($_SESSION);
        session_destroy();
        header('Location: /');
        exit;
    }
}

//Autoload when installing to display progress.
if (isset($_SESSION['step']) && isset($_SESSION['install_progress']) && !$unit_test) {
    if (
        (Step::PROGRESS_INSTALL == $_SESSION['step'] || Step::PROGRESS_UPDATE == $_SESSION['step']) || (
        (Step::CONFIRM_INSTALL == $_SESSION['step'] || Step::CONFIRM_UPDATE == $_SESSION['step']) &&
        (Progress::START == $_SESSION['install_progress'] || Progress::FINISH == $_SESSION['install_progress'])
    )) {
        header('Refresh:1'); // refresh a second after previous action
    }
}

//region LOAD_SESSION_DATA
//Define variables, get the session data for each variable and set to a placeholder if empty in session.

$db_type = $_SESSION['db_type'] ?? '';
$db_name = $_SESSION['db_name'] ?? '';
$db_host = $_SESSION['db_host'] ?? '';
$db_username = $_SESSION['db_user'] ?? '';
$db_password = $_SESSION['db_pass'] ?? '';
$email_type = $_SESSION['email_type'] ?? '';
$mailer_url = $_SESSION['mailer_url'] ?? '';
$mailer_email = $_SESSION['mailer_email'] ?? '';
$updater_pass = $_SESSION['updater_pass'] ?? '';
$org_name = $_SESSION['org_name'] ?? '';
$sec_type = $_SESSION['sec_type'] ?? '';
$admin_name = $_SESSION['admin_name'] ?? '';
$admin_email = $_SESSION['admin_email'] ?? '';
$admin_pass = $_SESSION['admin_pass'] ?? '';
$app_id = $_SESSION['app_id'] ?? '';
$app_secret = $_SESSION['app_secret'] ?? '';
$bunny_url = $_SESSION['bunny_url'] ?? '';
$_SESSION['step'] ??= Step::INTRO;
$_SESSION['install_progress'] ??= Progress::START;
$_SESSION['log'] ??= '';
$_SESSION['install_error'] ??= false;
//endregion LOAD_SESSION_DATA

$screens = [
    Step::INTRO => new Screen('Updaten of installeren', function () { ?>
        <p>Welkom by de kiwi update of installatie optie. </p>
        <?php detect_kiwi_message(); ?>
        <form role="form" method="post">
            <input type="hidden" name="action" value="<?php echo $_SESSION['step']; ?>" />
            <input type="submit" class="button grow" value="intro" />
        </form>
        <?php }, [
            'action' => function ($value) {
                $_SESSION['step'] = detect_kiwi_step();
            },
        ]),
    Step::INTRO_INSTALL => new Screen('Installeren', function () { ?>
        <p>Welkom by de kiwi installatie optie. </p>
        <?php form_button('start instelling', 'action'); ?>
        <?php form_button(); ?>
        <?php }, [
            'action' => Step::DATABASE_CHOICE,
            'back' => Step::INTRO,
        ]),
    Step::INTRO_UPDATE => new Screen('Updaten', function () { ?>
        <p>Welkom by de kiwi update. Voor wachtwoord in aub.</p>
        <form role="form" method="post" enctype="multipart/form-data" id="step1-form">
            <input type="hidden" name="action" value="<?php echo $_SESSION['step']; ?>" />
            <div class="form-group">
                <label for="wachtwoord">Updater wachtwoord<sup>*</sup></label>
                <input type="text" class="form-control" id="password" name="password" placeholder=""
                required>
            </div>
            <input type="submit" class="button grow" value="Verifieer">
        </form>
        <?php }, [
            'action' => Step::BACKUP_OVERWRITE_WARNING,
            'back' => Step::INTRO,
            'password' => function ($value) use (&$updater_pass, &$error, &$error_type) {
                $updater_pass = trim($value);
                if (validate_updater_password($updater_pass)) {
                    $_SESSION['admin_email'] = $value;
                } else {
                    $error = 'The updater password is incorrect.';
                    $error_type = 'validation';

                    unset($_POST['action']);
                }
            },
        ]),
    Step::BACKUP_OVERWRITE_WARNING => new Screen('WAARSCHUWING', function () { ?>
        <p>PAS OP, de lokale back-up op de server wordt herschreven. Download en sla deze op als u hem wilt bewaren. </p>
        <?php form_button('Ik ben me bewust', 'action'); ?>
        <?php }, [
            'action' => Step::CONFIRM_UPDATE,
            'back' => Step::INTRO_UPDATE,
        ]),
    Step::DATABASE_CHOICE => new Screen('Database configuratie', function () use ($db_type) { ?>
        <p>Kies de database van de server. </p>
        <form role="form" method="post" enctype="multipart/form-data" id="step1-form">
            <input type="hidden" name="action" value="<?php echo $_SESSION['step']; ?>" />

            <div class="form-group">
                <label class="radio-inline"><input type="radio" name="db_type" value="mariadb" <?php if ('mariadb' == $db_type) { ?>checked<?php }?>>Maria DB</label>
                <label class="radio-inline"><input type="radio" name="db_type" value="sqldb" <?php if ('mariadb' != $db_type) { ?>checked<?php }?>>SQL DB</label>
            </div>

            <input type="submit" class="button grow" value="Configuur de database!">
        </form>
        <?php form_button(); ?>

        <?php }, [
            'action' => Step::DATABASE,
            'back' => Step::INTRO_INSTALL,
        ]),
    Step::DATABASE => new Screen('Database configuratie', function () use ($db_name, $db_host, $db_username, $db_password) { ?>
        <p>Configureer hier de database. </p>
        <form role="form" method="post" enctype="multipart/form-data" id="step1-form">
            <input type="hidden" name="action" value="<?php echo $_SESSION['step']; ?>" />
            <div class="form-group">
                <label for="db_name">Database name<sup>*</sup></label>
                <input type="text" class="form-control" id="db_name" name="db_name" placeholder=""
                <?php echo refill($db_name); ?>
                required>
            </div>

            <div class="form-group">
                    <label for="db_host">Database host<sup>*</sup></label>
                    <input id="db_host" name="db_host" type="text" class="form-control" placeholder="EXAMPEL MAIL URL"
                    <?php echo refill($db_host); ?>
                    required>
            </div>

            <div class="form-group">
                    <label for="db_user">Database username<sup>*</sup></label>
                    <input id="db_user" name="db_user" type="text" class="form-control" placeholder="EXAMPEL MAIL URL"
                    <?php echo refill($db_username); ?>
                    required>
            </div>

            <div class="form-group">
                    <label for="db_pass">Database password</label>
                    <input id="db_pass" name="db_pass" type="text" class="form-control" placeholder="EXAMPEL MAIL URL"
                    <?php echo refill($db_password); ?>
                    >
            </div>

            <p>Velden met een <sup>*</sup> zijn verplicht</p>
            <input type="submit" class="button grow" value="Bouw database!">
        </form>
        <?php form_button(); ?>

        <?php }, [
            'action' => Step::EMAILER_CHOICE,
            'back' => Step::DATABASE_CHOICE,
            'db_type' => function ($value) use (&$db_type) {
                $db_type = trim($value);
                $_SESSION['db_type'] = $value;
            },
            'db_name' => function ($value) use (&$db_name) {
                $db_name = trim($value);
                $_SESSION['db_name'] = $value;
            },
            'db_host' => function ($value) use (&$db_host) {
                $db_host = trim($value);
                $_SESSION['db_host'] = $value;
            },
            'db_user' => function ($value) use (&$db_username) {
                $db_username = trim($value);
                $_SESSION['db_user'] = $value;
            },
            'db_pass' => function ($value) use (&$db_password) {
                $db_password = trim($value);
                $_SESSION['db_pass'] = $value;
            },
        ]),
    Step::EMAILER_CHOICE => new Screen('Organisatie naam en email', function () use ($org_name, $email_type) { ?>
        <p>Bepaal de organisatienaam en bepaal de email service. </p>
        <form role="form" method="post" enctype="multipart/form-data" id="step1-form">
            <input type="hidden" name="action" value="<?php echo $_SESSION['step']; ?>" />

            <div class="form-group">
                <label for="org_name">Organisatie naam</label>
                <input type="text" class="form-control" id="org_name" name="org_name" placeholder=""
                <?php echo refill($org_name); ?>
                >
            </div>

            <div class="form-group">
                <label class="radio-inline"><input type="radio" name="email_type" value="stmp" <?php if ('stmp' == $email_type) { ?>checked<?php } ?>>STMP e-mail</label>
                <label class="radio-inline"><input type="radio" name="email_type" value="noemail" <?php if ('stmp' != $email_type) { ?>checked<?php } ?>>Geen e-mail</label>
            </div>

            <input type="submit" class="button grow" value="Zet keuze">
        </form>
        <?php form_button(); ?>

        <?php }, [
            'action' => function () {
                if ('stmp' == $_POST['email_type']) {
                    $_SESSION['step'] = Step::EMAILER;
                }
                if ('noemail' == $_POST['email_type']) {
                    $_SESSION['step'] = Step::SECURITY;
                }
            },
            'back' => Step::DATABASE,
            'org_name' => function ($value) use (&$org_name) {
                $org_name = trim($value);
                $_SESSION['org_name'] = $value;
            },
            'email_type' => function ($value) use (&$email_type) {
                $email_type = trim($value);
                $_SESSION['email_type'] = $value;
            },
        ]),
    Step::EMAILER => new Screen('Email configuratie', function () use ($mailer_url, $mailer_email) { ?>
        <p>Dit is de email configuratie. </p>
        <form role="form" method="post" enctype="multipart/form-data" id="step1-form">
            <input type="hidden" name="action" value="<?php echo $_SESSION['step']; ?>" />

            <div class="form-group">
                    <label for="mailer_url">Swift mailer URL<sup>*</sup></label>
                    <input id="mailer_url" name="mailer_url" type="text" class="form-control" placeholder="EXAMPEL MAIL URL"
                    <?php echo refill($mailer_url); ?>
                    required>
            </div>

            <div class="form-group">
                <label for="mailer_email">E-mailadres<sup>*</sup></label>
                <input type="mailer_email" class="form-control" id="mailer_email" name="mailer_email" placeholder="gigantischebaas@viakunst-utrecht.nl"
                <?php echo refill($mailer_email); ?>
                required>
            </div>

            <p>Velden met een <sup>*</sup> zijn verplicht</p>
            <input type="submit" class="button grow" value="Bam email">
        </form>
        <?php form_button(); ?>

        <?php }, [
            'action' => Step::SECURITY,
            'back' => Step::EMAILER_CHOICE,
            'mailer_url' => function ($value) use (&$mailer_url, &$error, &$error_type) {
                $mailer_url = trim($value);

                if (validate_url($mailer_url)) {
                    $_SESSION['mailer_url'] = $value;
                } else {
                    $error = 'The mailer url is incorrect.';
                    $error_type = 'validation';

                    unset($_POST['action']);
                }
            },
            'mailer_email' => function ($value) use (&$mailer_email, &$error, &$error_type) {
                $mailer_email = trim($value);
                if (validate_email($mailer_email)) {
                    $_SESSION['mailer_email'] = $value;
                } else {
                    $error = 'The mailer email is incorrect.';
                    $error_type = 'validation';

                    unset($_POST['action']);
                }
            },
        ]),
    Step::SECURITY => new Screen('Security', function () use ($sec_type, $updater_pass) { ?>
        <p>Kies de security modus van Kiwi en geef een updater wachtwoord op.</p>
        <form role="form" method="post" enctype="multipart/form-data" id="step1-form">
            <input type="hidden" name="action" value="<?php echo $_SESSION['step']; ?>" />

            <div class="form-group">
                <label class="radio-inline"><input type="radio" name="sec_type" value="admin" <?php if ('bunny' != $sec_type) { ?>checked<?php } ?>>Lokale userdata</label>
                <label class="radio-inline"><input type="radio" name="sec_type" value="bunny" <?php if ('bunny' == $sec_type) { ?>checked<?php } ?>>Bunny</label>
            </div>

            <div class="form-group">
                    <label for="updater_pass">Updater wachtwoord<sup>*</sup></label>
                    <input id="updater_pass" name="updater_pass" type="text" class="form-control" placeholder="wachtwoord123"
                    <?php echo refill($updater_pass); ?>
                    required>
            </div>

            <input type="submit" class="button grow" value="Security instellen">
        </form>
        <?php form_button(); ?>

        <?php }, [
            'action' => function () {
                if ('admin' == $_POST['sec_type']) {
                    $_SESSION['step'] = Step::ADMIN;
                }
                if ('bunny' == $_POST['sec_type']) {
                    $_SESSION['step'] = Step::BUNNY;
                }
            },
            'back' => function () use ($email_type) {
                if ('stmp' == $email_type) {
                    $_SESSION['step'] = Step::EMAILER;
                } else {
                    $_SESSION['step'] = Step::EMAILER_CHOICE;
                }
            },
            'sec_type' => function ($value) use (&$sec_type) {
                $sec_type = trim($value);
                $_SESSION['sec_type'] = $value;
            },
            'updater_pass' => function ($value) use (&$updater_pass) {
                $updater_pass = trim($value);
                $_SESSION['updater_pass'] = $value;
            },
        ]),
    Step::BUNNY => new Screen('Bunny', function () use ($app_id, $app_secret, $bunny_url) { ?>
        <p>Bunny is een openId connect identity en user-management system.</p>
        <form role="form" method="post" enctype="multipart/form-data" id="step1-form">
            <input type="hidden" name="action" value="<?php echo $_SESSION['step']; ?>" />
            <div class="form-group">
                <label for="app_id">App id<sup>*</sup></label>
                <input type="text" class="form-control" id="app_id" name="app_id" placeholder=""
                <?php echo refill($app_id); ?>
                required>
            </div>
            <div class="form-group">
                <label for="app_secret">App secret<sup>*</sup></label>
                <input type="text" class="form-control" id="app_secret" name="app_secret" placeholder=""
                <?php echo refill($app_secret); ?>
                required>
            </div>
            <div class="form-group">
                <label for="bunny_url">Bunny URL<sup>*</sup></label>
                <input type="text" class="form-control" id="bunny_url" name="bunny_url" placeholder=""
                <?php echo refill($bunny_url); ?>
                required>
            </div>

            <p>Velden met een <sup>*</sup> zijn verplicht</p>
            <input type="submit" class="button grow" value="Configueer bunny">
        </form>
        <?php form_button(); ?>

        <?php }, [
            'action' => Step::CONFIRM_INSTALL,
            'back' => Step::SECURITY,
            'bunny_url' => function ($value) use (&$bunny_url, &$error, &$error_type) {
                $bunny_url = trim($value);
                if (validate_url($bunny_url)) {
                    $_SESSION['bunny_url'] = $value;
                } else {
                    $error = 'The mailer url is incorrect.';
                    $error_type = 'validation';

                    unset($_POST['action']);
                }
            },
            'app_id' => function ($value) use (&$app_id) {
                $app_id = trim($value);
                $_SESSION['app_id'] = $value;
            },
            'app_secret' => function ($value) use (&$app_secret) {
                $app_secret = trim($value);
                $_SESSION['app_secret'] = $value;
            },
        ]),
    Step::ADMIN => new Screen('Admin instellingen', function () use ($admin_email, $admin_name, $admin_pass) { ?>
        <p>Dit is de user-data van het eerste kiwi account.</p>
        <form role="form" method="post" enctype="multipart/form-data" id="step1-form">
            <input type="hidden" name="action" value="<?php echo $_SESSION['step']; ?>" />
            <div class="form-group">
                <label for="admin_email">Admin email<sup>*</sup></label>
                <input type="text" class="form-control" id="admin_email" name="admin_email" placeholder=""
                <?php echo refill($admin_email); ?>
                required>
            </div>
            <div class="form-group">
                <label for="admin_name">Admin naam<sup>*</sup></label>
                <input type="text" class="form-control" id="admin_name" name="admin_name" placeholder=""
                <?php echo refill($admin_name); ?>
                required>
            </div>
            <div class="form-group">
                <label for="admin_pass">Admin wachtwoord<sup>*</sup></label>
                <input type="text" class="form-control" id="admin_pass" name="admin_pass" placeholder=""
                <?php echo refill($admin_pass); ?>
                required>
            </div>

            <p>Velden met een <sup>*</sup> zijn verplicht</p>
            <input type="submit" class="button grow" value="Construct account">
        </form>
        <?php form_button(); ?>

        <?php }, [
            'action' => Step::CONFIRM_INSTALL,
            'back' => Step::SECURITY,
            'admin_email' => function ($value) use (&$admin_email, &$error, &$error_type) {
                $admin_email = trim($value);
                if (validate_email($admin_email)) {
                    $_SESSION['admin_email'] = $value;
                } else {
                    $error = 'The admin email is incorrect.';
                    $error_type = 'validation';

                    unset($_POST['action']);
                }
            },
            'admin_name' => function ($value) use (&$admin_name) {
                $admin_name = trim($value);
                $_SESSION['admin_name'] = $value;
            },
            'admin_pass' => function ($value) use (&$admin_pass) {
                $admin_pass = trim($value);
                $_SESSION['admin_pass'] = $value;
            },
        ]),
    Step::CONFIRM_INSTALL => new Screen('Check alle data', function () { ?>
        <p>Kiwi is klaar om te installeren.</p>
        <?php form_button('Conformeer installatie', 'action'); ?>
        <?php form_button(); ?>

        <?php }, [
            'action' => function () {
                $_SESSION['step'] = Step::PROGRESS_INSTALL;
                $_SESSION['install_progress'] = Progress::START;
            },
            'back' => function () use ($sec_type) {
                if ('bunny' == $sec_type) {
                    $_SESSION['step'] = Step::BUNNY;
                } else {
                    $_SESSION['step'] = Step::ADMIN;
                }
            },
        ]),
    Step::SUCCESS_INSTALL => new Screen('Succesvolle installatie', function () { ?>
        <p>Kiwi is succesvol geinstalleerd </p>
        <h4>Log:</h4>
        <p> <?php echo $_SESSION['log']; ?></p>
        <?php form_button('Ga naar kiwi', 'action'); ?>

        <?php }, [
            'action' => 'go-to-kiwi',
            'back' => Step::SUCCESS_INSTALL,
        ]),
    Step::FAILURE => new Screen('Gefaalde installatie', function () { ?>
        <p>Kiwi is helaas niet correct geinstalleerd </p>
        <h4>Error log:</h4>
        <p> <?php echo $_SESSION['log']; ?></p>
        <?php form_button('Probeer het opnieuw', 'action'); ?>

        <?php }, [
            'action' => function () {
                unset($_SESSION);
                session_destroy();
                $_SESSION['step'] = Step::INTRO;
            },
        ]),
    Step::UPDATE => new Screen('Updaten', function () { ?>
        <p>Dit is de online updater van kiwi, dit programma update kiwi naar de meest recente stabiele versie.</p>
        <p>Dit is volledig automatisch, dus zonder handmatig verzetten van opties.</p>
        <?php form_button('Start update', 'action'); ?>

        <?php }, [
            'action' => Step::CONFIRM_UPDATE,
        ]),
    Step::CONFIRM_UPDATE => new Screen('Klaar om te gaan.', function () { ?>
        <p>De online updater van kiwi is klaar op kiwi naar de meest recente stabiele versie te updaten.</p>
        <p>Dit is volledig automatisch, dus zonder handmatig verzetten van opties.</p>
        <p>Controleer voor de laatste keer of je alles klaar hebt staan.</p>
        <?php form_button('Start update', 'action'); ?>

        <?php }, [
            'action' => function () {
                $_SESSION['step'] = Step::PROGRESS_UPDATE;
                $_SESSION['install_progress'] = Progress::START;
            },
            'back' => Step::UPDATE,
        ]),
    Step::SUCCESS_UPDATE => new Screen('Succesvolle update', function () { ?>
        <p>Kiwi is succesvol geupdated. </p>
        <h4>Log:</h4>
        <p> <?php echo $_SESSION['log']; ?></p>

        <?php form_button('Ga naar kiwi.', 'action'); ?>

        <?php }, [
            'action' => Step::SUCCESS_UPDATE,
        ]),
    Step::PROGRESS_UPDATE => new Screen('Aan het updaten', function () { ?>
        <p>Kiwi is aan het updaten, dit kan enkele minuten duren. </p>
        <p>Bezig met stap <?php echo $_SESSION['install_progress']; ?>  </p>

        <?php }, [], function () use ($sec_type, $admin_email, $admin_name, $admin_pass) {
        switch ($_SESSION['install_progress']) {
                case Progress::START:
                    extend_time_limit();
                    $_SESSION['install_progress'] = Progress::KIWI_BACKUP;
                    break;

                case Progress::KIWI_BACKUP:
                    create_backup();
                    $_SESSION['install_progress'] = Progress::SQL_BACKUP;
                    break;

                case Progress::SQL_BACKUP:
                    database_backup();
                    $_SESSION['install_progress'] = Progress::DOWNLOAD;
                    break;

                case Progress::DOWNLOAD:
                    download_kiwi();
                    $_SESSION['install_progress'] = Progress::DOCTRINE;
                    break;

                case Progress::DOCTRINE:
                    doctrine_commands($sec_type, $admin_email, $admin_name, $admin_pass);
                    $_SESSION['install_progress'] = Progress::FINISH;
                    break;

                case Progress::RESTORE:
                    restore_backup();
                    $_SESSION['install_progress'] = Progress::FINISH;
                    break;
                default:
                    break;
            }

        handleEndofInstall(Step::SUCCESS_UPDATE);
    }),
    Step::PROGRESS_INSTALL => new Screen('Aan het installeren', function () { ?>
        <p>Kiwi is aan het installeren, dit kan enkele minuten duren. </p>
        <p>Bezig met stap <?php echo $_SESSION['install_progress']; ?>  </p>

        <?php }, [], function () use ($app_id, $app_secret, $bunny_url, $db_host, $db_name, $db_password, $db_username, $db_type, $mailer_email, $mailer_url, $org_name, $sec_type, $updater_pass, $email_type, $admin_email, $admin_name, $admin_pass) {
        switch ($_SESSION['install_progress']) {
                case Progress::START:
                    $_SESSION['install_progress'] = Progress::DATABASE;
                    break;

                case Progress::DATABASE:
                    database_connect($db_host, $db_name, $db_password, $db_username);
                    $_SESSION['install_progress'] = Progress::DOWNLOAD;
                    break;

                case Progress::DOWNLOAD:
                    download_kiwi();
                    $_SESSION['install_progress'] = Progress::ENV_FILE;
                    break;

                case Progress::ENV_FILE:
                    generate_env($app_id, $app_secret, $bunny_url, $db_host, $db_name, $db_password, $db_username, $db_type, $mailer_email, $mailer_url, $org_name, $sec_type, $updater_pass, $email_type);
                    $_SESSION['install_progress'] = Progress::DOCTRINE;
                    break;

                case Progress::DOCTRINE:
                    doctrine_commands($sec_type, $admin_email, $admin_name, $admin_pass);
                    $_SESSION['install_progress'] = Progress::FINISH;
                    break;

                case Progress::RESTORE:
                    restore_backup();
                    $_SESSION['install_progress'] = Progress::FINISH;
                    break;

                default:
                    break;
            }
        handleEndofInstall(Step::SUCCESS_INSTALL);
    }),
];

set_exception_handler(function ($exception) use ($screens) {
    Log::console($exception);
    $_SESSION['step'] = Step::FAILURE;
    $_SESSION['install_error'] = true;
    render($screens[$_SESSION['step']], null, 0);
});

$error = null;
$error_type = 0;

$step = $screens[$_SESSION['step']];

//Handle the steps of the installation process.
if (!$unit_test && 'POST' == $_SERVER['REQUEST_METHOD'] && isset($_SESSION)) {
    if (isset($_POST['action'])) {
        $posting_step = $_POST['action'];
        handle_post($posting_step, $screens, 'action');
    }
    if (isset($_POST['back'])) {
        $posting_step = $_POST['back'];
        handle_post($posting_step, $screens, 'back');
    }
}

if (!$unit_test && !is_null($step->exec)) {
    call_user_func($step->exec);
}

$step = $screens[$_SESSION['step']];

if (!$unit_test) {
    check_updater_location();
}

if (!$unit_test) {
    render($step, $error, $error_type);
}

//region FUNCTIONS

use Doctrine\DBAL\Query\QueryException;
use Doctrine\ORM\UnexpectedResultException;
use Ifsnop\Mysqldump\Mysqldump;
use ProxyManager\Exception\FileNotWritableException;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

function form_button($label = 'Terug', $name = 'back')
{
    ?>
<form role="form" method="post" enctype="multipart/form-data" id="step1-backfrom">
    <input type="hidden" name="<?php echo $name; ?>" value="<?php echo $_SESSION['step']; ?>" />
    <input type="submit" class="button grow" value="<?php echo $label; ?>">
</form>
<?php
}

function handle_post($posting_step, $screens, $last_act_key)
{
    // If the last_act is not in the post, dont do anything.
    if (!isset($_POST[$last_act_key])) {
        // error.
        return;
    }

    //Get the action associated with the post.
    $post_acts = $screens[$posting_step]->actions;

    // We always want to do the "back" and "action" acts last, so we unset and do it manually after loop.
    unset($post_acts[$last_act_key]);

    // Run all functions that are not back or action.
    // These functions can change action or back, unset, etc
    foreach ($post_acts as $key => $act) {
        if (isset($_POST[$key])) {
            if (is_string($act)) {
                // This should not do anything.
            } else {
                call_user_func($act, $_POST[$key]);
            }
        }
    }

    $last_act = $screens[$posting_step]->actions[$last_act_key];
    if (isset($_POST[$last_act_key])) {
        if (is_string($last_act)) {
            $_SESSION['step'] = $last_act;
        } else {
            call_user_func($last_act, $_POST[$last_act_key]);
        }
    }
}

function handleEndofInstall($step)
{
    if (Progress::FINISH == $_SESSION['install_progress']) {
        $_SESSION['step'] = $step;
    } else {
        $_SESSION['step'] = Step::PROGRESS_INSTALL;
    }

    if ($_SESSION['install_error']) {
        $_SESSION['step'] = Step::FAILURE;
    }
}

function check_updater_location()
{
    $name = dirname(__FILE__);

    if ('public_html\kiwi' != substr($name, -16)) {
        throw new ScriptLocationException('The script is in the wrong location. It should like '.Dir::PUBLIC_DIR.'\kiwi\updater.php'.', but it currently looks like '.dirname(__FILE__));
    }
}

function detect_kiwi()
{
    // detect kiwi in some meaningful way.
    // check .env.local.php
    //.env.* wildcard.
    $envpath = kiwidir(Dir::KIWI_DIR).'/.env*';

    $list = glob($envpath);
    if (count($list) < 2) {
        return false;
    }

    $env_vars = get_env_vars();

    if (isset($env_vars['UPDATER_PASSWORD'])) {
        return true;
    }

    return false;
}

function get_env_vars()
{
    if (isset($_SESSION['unit_test']) && $_SESSION['unit_test'] && isset($_SESSION['unit_test_dir'])) {
        return $_SESSION['unit_test_env'];
    } else {
        return include_once kiwidir(Dir::KIWI_DIR).'/.env.local.php';
    }
}

function kiwidir($name)
{
    if (isset($_SESSION['unit_test']) && $_SESSION['unit_test'] && isset($_SESSION['unit_test_dir'])) {
        return $_SESSION['unit_test_dir'].$name;
    } else {
        return dirname(__FILE__, 3).$name;
    }
}

function symfony_autoload()
{
    if (isset($_SESSION['unit_test']) && $_SESSION['unit_test'] && isset($_SESSION['unit_autoload'])) {
        return $_SESSION['unit_autoload'];
    } else {
        return kiwidir(Dir::KIWI_DIR).'/vendor/autoload.php';
    }
}

function symfony_bootstrapper()
{
    if (isset($_SESSION['unit_test']) && $_SESSION['unit_test'] && isset($_SESSION['unit_bootstrapper'])) {
        return $_SESSION['unit_bootstrapper'];
    } else {
        return kiwidir(Dir::KIWI_DIR).'/config/bootstrap.php';
    }
}

function get_dir_exceptions($dir)
{
    // TO-DO: find a way to get non-overwritable dir to this installer.
    if (Dir::KIWI_DIR == $dir) {
        return ['uploads'];
    }
    if (Dir::PUBLIC_DIR == $dir) {
        return [];
    }
}

function detect_kiwi_message()
{
    $detected = detect_kiwi();
    if ($detected) {
        echo '<p> Dit script heeft gedetecteerd dat je al een kiwi versie hebt of deze server.</p>';

        return true;
    } else {
        echo '<p>  Dit script heeft gedetecteerd dit een volledig nieuwe installatie van kiwi. </p>';

        return false;
    }
}

function detect_kiwi_step()
{
    $detected = detect_kiwi();

    if ($detected) {
        return Step::INTRO_UPDATE;
    } else {
        return Step::INTRO_INSTALL;
    }
}

function detect_kiwi_value()
{
    $detected = detect_kiwi();

    if ($detected) {
        echo Step::INTRO_UPDATE;

        return true;
    } else {
        echo Step::INTRO_INSTALL;

        return $detected;
    }
}

function validate_updater_password($pass)
{
    //TO-DO: real password validator
    $env_vars = get_env_vars();
    $env_pass = $env_vars['UPDATER_PASSWORD'];

    if ($env_pass == trim($pass)) {
        return true;
    }

    return false;
}

function validate_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_url($url)
{
    return filter_var($url, FILTER_VALIDATE_URL);
}

function print_error_message($error, $error_type)
{
    if (isset($error) && 'validation' == $error_type) {
        return '<p>'.$error.' </p> <br>';
    }

    return '';
}

function refill($var)
{
    return 'value = "'.$var.'"';
}

function generate_env($app_id, $app_secret, $bunny_url, $db_host, $db_name, $db_password, $db_username, $db_type, $mailer_email, $mailer_url, $org_name, $sec_type, $updater_pass, $email_type)
{
    if (detect_kiwi()) {
        Log::msg('Environment file found.');

        return true;
    }

    Log::msg('No environment file found.');
    $vars = [];

    //production env
    $vars['APP_DEBUG'] = 0;
    $vars['APP_ENV'] = 'prod';

    $random_val = '';
    for ($i = 0; $i < 32; ++$i) {
        $random_val = $random_val.chr(random_int(65, 90));
    }

    $vars['APP_SECRET'] = $random_val;

    $random_val2 = '';
    for ($i = 0; $i < 16; ++$i) {
        $random_val2 = $random_val2.chr(random_int(65, 90));
    }

    $vars['USERPROVIDER_KEY'] = $random_val2;
    $vars['UPDATER_PASSWORD'] = $updater_pass;
    //bunny
    if ('bunny' == $sec_type) {
        $vars['BUNNY_SECRET'] = $app_secret;
        $vars['BUNNY_ID'] = $app_id;
        $vars['BUNNY_URL'] = $bunny_url;
    }

    //mailer
    if ('stmp' == $email_type) {
        $vars['MAILER_URL'] = $mailer_url;
        $vars['DEFAULT_FROM'] = $mailer_email;
    } else {
        $vars['MAILER_URL'] = 'null://localhost';
    }

    if ('mariadb' == $db_type) {
        //database
        $vars['DATABASE_URL'] = 'mysql://'.$db_username.':'.$db_password.'@'.$db_host.':3306/'.$db_name.'?serverVersion=mariadb-10.5.8';
    } else {
        //database
        $vars['DATABASE_URL'] = 'mysql://'.$db_username.':'.$db_password.'@'.$db_host.':3306/'.$db_name.'?serverVersion=5.7';
    }

    //org name
    if ('' != $org_name) {
        $vars['ORG_NAME'] = $org_name;
    }

    //php
    $envdir = kiwidir(Dir::KIWI_DIR);
    $envpath = $envdir.'/.env.local.php';
    if (!file_exists($envdir) && !mkdir($envdir)) {
        Log::msg("Could not create folder {$envdir}, make sure the parent directory is writable.");

        return false;
    }

    $envfile = fopen($envpath, 'w');
    if (!$envfile) {
        Log::msg("Could not write to {$envpath}, make sure the directory is writable.");

        return false;
    }

    //php start
    $line = "<?php\n";
    $line .= "return [\n";
    foreach ($vars as $key => $value) {
        $line .= "\t'".addslashes($key)."' => '".addslashes($value)."',\n";
    }
    $line .= "];\n";
    fwrite($envfile, $line);

    Log::msg('Environment file created.');
    fclose($envfile);

    return true;
}

function download_kiwi()
{
    //Delete previous temp file and make a new one.
    $tempPath = kiwidir(Dir::DOWNLOAD_ZIP);
    if (file_exists($tempPath)) {
        unlink($tempPath);
    }
    $tempFile = fopen($tempPath, 'w+');

    if (!$tempFile) {
        Log::msg("Could not write to {$tempPath}, make sure the directory is writable.");

        throw new DownloadException("Could not write to {$tempPath}, make sure the directory is writable.");
    }

    $release_url = 'https://api.github.com/repos/jasperweyne/helpless-kiwi/releases/latest';

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $release_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['User-Agent:jasperweyne']);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $release_info = curl_exec($ch);
    if (empty(!curl_error($ch))) {
        //Fatal error, stop immediatily
        Log::msg('Curl error found');
        Log::msg(curl_error($ch));

        throw new DownloadException('Curl error found\n'.curl_error($ch));
    }

    $decoded_release_info = json_decode($release_info, true);
    $download_url = $decoded_release_info['assets']['0']['browser_download_url'];

    curl_setopt($ch, CURLOPT_URL, $download_url);
    curl_setopt($ch, CURLOPT_TIMEOUT, 50);
    curl_setopt($ch, CURLOPT_FILE, $tempFile);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    curl_exec($ch);

    if (empty(!curl_error($ch))) {
        //Fatal error, stop immediatily
        Log::msg('Curl error found');
        Log::msg(curl_error($ch));

        throw new DownloadException('Curl error found\n'.curl_error($ch));
    }

    curl_close($ch);
    fclose($tempFile);
    Log::msg('Kiwi download succesfull.');

    check_php();

    //check if there are files to backup.
    if (!isset($_SESSION['unit_test']) && file_exists(kiwidir(Dir::KIWI_DIR)) && file_exists(kiwidir(Dir::PUBLIC_DIR))) {
        Log::msg('Legacy kiwi folders found.');
        create_backup();
    } else {
        Log::msg('No previous kiwi files found.');
    }

    // Unzip the fresh kiwi release.
    $zip = new ZipArchive();
    $zip->open($tempPath);
    $zip->extractTo(kiwidir(Dir::ROOT_DIR));
    $zip->close();

    if (file_exists($tempPath)) {
        unlink($tempPath);
    }
    Log::msg('Unzipped the new kiwi files.');
}

function check_php()
{
    $tempPath = kiwidir(Dir::DOWNLOAD_ZIP);

    // Unzip the fresh kiwi release.
    $zip = new ZipArchive();
    $zip->open($tempPath);
    $composer_lock = $zip->getFromName('kiwi/composer.lock');
    $zip->close();

    if (false == $composer_lock) {
        Log::msg('WARNING: php check skipped due to no lock file');
    } else {
        $composer_json = json_decode($composer_lock);
        $req_version = $composer_json->platform->php;

        if (version_compare(PHP_VERSION, $req_version, '<')) {
            Log::msg('PHP is outdated. Kiwi requires '.$req_version.', currently installed '.PHP_VERSION);

            if (file_exists($tempPath)) {
                unlink($tempPath);
            }

            throw new DownloadException();
        } else {
            Log::msg('PHP version sufficient.');
        }
    }
}

function extend_time_limit()
{
    $accessfile = fopen(__DIR__.'/.htaccess', 'w');
    if (!$accessfile) {
        Log::msg("Could not write to {$accessfile}, make sure the directory is writable.");

        throw new FileNotWritableException('Could not write accessfile');
    }

    //php start
    $access = '#Extend execution time
<IfModule mod_php5.c>
    php_value max_execution_time 0
</IfModule>';
    fwrite($accessfile, $access);

    fclose($accessfile);
}

function database_connect($db_host, $db_name, $db_password, $db_username)
{
    $connection = mysqli_connect($db_host, $db_username, $db_password);
    if (!$connection) {
        throw new ConnectionException(mysqli_connect_error());
    }

    Log::msg('Succesfully connected to the database server.');

    $res = mysqli_query($connection, 'SHOW DATABASES');

    $found = false;
    while ($row = mysqli_fetch_assoc($res)) {
        if ($row['Database'] == $db_name) {
            $found = true;
            break;
        }
    }

    $res->close();
    $connection->next_result();

    if (!$found) {
        Log::msg('No matching data base found.');

        // Can't use prepared statements here.
        $sql = "CREATE DATABASE $db_name";
        if ($connection->query($sql)) {
            Log::msg('Database created successfully.');
        } else {
            throw new QueryException('Error creating database: '.$connection->error);
        }
    } else {
        Log::msg('The kiwi database found.');

        mysqli_select_db($connection, $db_name);
        $queryTwo = $connection->prepare('SELECT COUNT(DISTINCT `table_name`) FROM `information_schema`.`columns` WHERE `table_schema` = ?');
        $queryTwo->bind_param('s', $db_name);

        $queryTwo->execute();
        $queryTwo->store_result();
        $queryTwo->bind_result($column_count);
        $queryTwo->fetch();

        // check if database is empty.
        if (0 == $column_count) {
            echo "\n";
            Log::msg('Database was empty, and usable by kiwi.');
        } else {
            Log::msg('Database is being backup.');
        }

        $queryTwo->free_result();
        $connection->next_result();
    }

    $connection->close();
}

use Symfony\Component\Filesystem\Exception\FileNotFoundException;

function database_backup()
{
    $env_vars = get_env_vars();

    if (!include_once symfony_autoload()) {
        throw new FileNotFoundException('Dependency autoloader was not found');
    }

    $sql_url = $env_vars['DATABASE_URL'];

    $firstpart = stristr($sql_url, '//', true).'host='.substr(stristr($sql_url, '@', false), 1);
    $secondpart = stristr($firstpart, '/', false);
    $host = stristr($firstpart, '/', true).';dbname='.substr($secondpart, 1);

    $firstpart = stristr($sql_url, '//', false);
    $secondpart = stristr($firstpart, ':', false);
    $db_username = substr(stristr($firstpart, ':', true), 2);
    $db_password = substr(stristr($secondpart, '@', true), 1);

    // Make backup dir if it doesn't exist yet
    if (!file_exists(kiwidir(Dir::BACKUP_DIR))) {
        mkdir(kiwidir(Dir::BACKUP_DIR));
    }

    // Remove old backup
    if (file_exists(kiwidir(Dir::BACKUP_SQL))) {
        unlink(kiwidir(Dir::BACKUP_SQL));
    }

    try {
        $dump = new  Mysqldump($host, $db_username, $db_password);
        $dump->start(kiwidir(Dir::BACKUP_SQL));
    } catch (Exception $e) {
        throw new ConnectionException($e);
    }
}

function get_application()
{
    if (!include_once symfony_autoload()) {
        throw new FileNotFoundException('Dependency autoloader was not found');
    }

    // Initialize symfony, to run symfony and doctine commands.
    if (!include_once symfony_bootstrapper()) {
        throw new FileNotFoundException('Symfony bootstrap was not found');
    }

    // Production enviroment, because dev bundles are not included in the release
    $kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
    $application = new Application($kernel);
    $application->setAutoExit(false);

    Log::msg('Symfony initialized.');

    return $application;
}

function doctrine_commands($sec_type, $admin_email, $admin_name, $admin_pass)
{
    //Doctrine commands
    $application = get_application();
    $input = new ArrayInput([
        'command' => 'doctrine:migrations:migrate',
        // (optional) define the value of command arguments
        '-n' => true,
        '--allow-no-migration' => true,
    ]);

    $output = new BufferedOutput();
    // Run the command in the symfony framework.

    $result = $application->run($input, $output);
    Log::msg($output->fetch());

    if (0 != $result) {
        throw new UnexpectedResultException('Doctrine migration failed.');
    }
    Log::msg('Doctrine migration succes.');

    if ('admin' == $sec_type) {
        create_user($application, $admin_email, $admin_name, $admin_pass);
    }
}

function create_user($application, $admin_email, $admin_name, $admin_pass)
{
    $input = new ArrayInput([
        'command' => 'app:create-account',
        // (optional) define the value of command arguments
        'email' => $admin_email,
        'name' => $admin_name,
        'pass' => $admin_pass,
        '--admin' => true,
    ]);

    $output = new BufferedOutput();
    // Run the command in the symfony framework.
    $result = $application->run($input, $output);
    Log::msg($output->fetch());
    if (0 != $result) {
        throw new UnexpectedResultException('User creation failed.');
    }
    Log::msg('User creation succes.');
}

function create_backup()
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

function restore_backup()
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

function render($step, $error, $error_type)
{
    //region HTML_HEADER
?>
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
    <title>PHP Test</title>
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-lg-6 col-lg-offset-3 col-md-8 col-md-offset-2 col-sm-10 col-sm-offset-1">
                <div id="digidecs" class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Helpless Kiwi &mdash; <?php echo $step->title; ?></h3>
                    </div>
                    <div class="panel-body">
                        <?php echo print_error_message($error, $error_type); ?>
<?php
//endregion HTML_HEADER
//region HTML_FORMS
call_user_func($step->render);
    //endregion HTML_FORMS
//region HTML_FOOTER
?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
<?php

//endregion HTML_FOOTER
}

//endregion FUNCTIONS
