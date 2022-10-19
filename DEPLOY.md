# Deploying

## Using the installer
For easy installation, the install & update script is now provided. Right click
[this link to the script](https://raw.githubusercontent.com/jasperweyne/helpless-kiwi/master/public/update.php)
and download it. Place it on your server, with the filename 'update.php'. Make
sure its parent directories are /public_html/kiwi. Locate the script in your
browser by navigating to https://__your.server.com__/update.php. Follow the
instructions to install Kiwi.

## Manually installing
To ease the building process, you can download the latest release from the
[releases page](https://github.com/jasperweyne/helpless-kiwi/releases), and
upload the folders directly to your server. Here, it is assumed your server's
root directory is 'public_html' and Kiwi is served from a sub-directory within,
called 'kiwi', usually representing a subdomain.

If these conditions don't apply to you, you can instead download, modify and run
the build_prod.sh script from this repository to generate a production
environment. This script is intended to produce the same results as a release.
If this doesn't work properly for you can deploy manually using these
instructions.

For clarity purposes, we're assuming you're deploying to another location, for
example by moving the files over FTP. Locally or through SSH should work fine
as well though.

Again, make sure a database is installed and available. Configure this in the
`/.env.local` file, the same way as with a development build. Additionally,
remove the HTTPS disabling flag and set your environment to production. A basic
configuration could look like:

```bash
APP_ENV=prod
DATABASE_URL=mysql://username:password@127.0.0.1:3306/database
```

Typically, the public folder will need to be moved or renamed. To do this,
you need to start by replacing 'public/' to the new folder, in all files
in the repository. Notable files are:

* webpack.config.js (this file will most likely require additional configuration)
* config/packages/assets.yaml
* config/packages/vich_uploader.yaml
* config/packages/webpack_encore.yaml

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
export APP_ENV=prod APP_DEBUG=0
composer install --no-dev --optimize-autoloader
yarn install
```

> Note: when deploying to production, make sure you keep working in the same
> shell environment, or re-export these variables again.

When moving the public folder, `composer install` will give a warning on the
out-of-date lock file. You can safely ignore this message.

After that, you can build the assets

```bash
yarn build
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

