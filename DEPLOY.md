# Deploying Helpless Kiwi on a server

When you're planning on using Helpless Kiwi, you'll want to install it on a
server. This allows your installation of Helpless Kiwi to be accessed by anyone
on the internet. Helpless Kiwi is designed to run on common, cheap webhosting,
which makes running it as easy and as cheap as possible. To make this possible,
Helpless Kiwi is written in the PHP 8.1 programming language. If you're running
your website with software like WordPress, Joomla or Drupal, your webhosting
should be compatible!

## Using the installer
For easy installation, the install & update script is now provided. Right click
[this link to the script](https://raw.githubusercontent.com/jasperweyne/helpless-kiwi/master/public/update.php)
and download it. Place it on your server, with the filename 'update.php'. The
instructions will differ from webhosting to webhosting.

> [!IMPORTANT]
> The update.php script MUST be placed in a directory named `kiwi`, which itself
> MUST be placed in a directory named `public_html`
> (eg. `.../public_html/kiwi/update.php`). Moreover, the `kiwi` directory MUST
> be served as the root directory of the (sub)domain! If your webhosting doesn't
> allow this, you'll need to manually build kiwi from source using the
> instructions below.

If everything is configured correctly, start the script in your browser by
navigating to https://__your.server.com__/update.php. Follow the instructions to
install Kiwi.

## Manually installing
The installer downloads the latest release. If the installer doesn't work, you
can download the latest release from the
[releases page](https://github.com/jasperweyne/helpless-kiwi/releases), and
upload the folders directly to your server. Note that the same directory
structure MUST be used as with the installation script.

## From source
If you want to deploy a custom version of Helpless Kiwi (eg. with your own
modifications, or with a different directory structure), you can instead create
your own version from the source code in this repository.

For clarity purposes, we're assuming you're deploying to another location, for
example by moving the files over FTP. Locally or through SSH should work fine
as well though.

Again, make sure a database is installed and available. Configure this in the
`/.env.local` file, the same way as with a development build. Additionally,
remove the HTTPS disabling flag and set your environment to production. A basic
configuration could look like:

```bash
DATABASE_URL=mysql://username:password@127.0.0.1:3306/database
```

In the releases, the public folder is placed in a different location. To do this,
you need to start by replacing 'public/' to the new folder, in all files in the
repository. Notable files are:

* config/packages/assets.yaml
* config/packages/vich_uploader.yaml

Additionally, add this line to `composer.json`:

```
{
    ...
    "extra": {
        "public-dir": "new/folder/name",
        ...
    }
}
```

Now, you can move the 'public' folder to the new location. To finish, edit the 
`require dirname(__DIR__).'/vendor/autoload_runtime.php';`` line in the
index.php file if necessary.

Then, install the dependencies. Not all dependencies are required. To signal
this, begin by exporting the corresponding environment variables before running
the installation, as seen here.

```bash
composer install --no-dev --optimize-autoloader
```

> Note: when deploying to production, make sure you keep working in the same
> shell environment, or re-export these variables again.

When moving the public folder, `composer install` will give a warning on the
out-of-date lock file. You can safely ignore this message.

After that, you can build the assets

```bash
php bin/console tailwind:build --minify
php bin/console asset-mapper:compile
```

Now, you should deploy your database table structure. The means to do this
differs wildly from environment to environment. For hints, look at the development
guide.

At this time, you should be good to go. Move the following folders and files to
your deployment server:

* config
* public (this might be renamed)
* src
* templates
* vendor
* .env(*)

If you deploy on the local machine or over SSH, it is recommended to run
`php bin/console cache:clear` and deploy the var folder as well, otherwise
skip this step. If everything went right, your server should now be running
correctly!

