# Install

## Prerequisites
Make sure php 7 is installed on your machine. If you're deploying to another
machine, make sure this has a php server installed, for example Apache or Nginx.
Also, make sure that the machine you're deploying to has a database available,
MySQL is supported primairily. When developing, it is recommended to download
both PHP and MySQL as part of a stack, such as WAMP, XAMPP or LAMP for Windows,
Mac and Linux respectively.

For dependencies, Kiwi uses two package managers, [composer](https://getcomposer.org/)
and [yarn](https://yarnpkg.com/). Composer is dependent on php, which you've
already installed, and yarn is dependent on [node.js](https://nodejs.org/). All
these tools need to be installed. For installing these tools, it is recommended
to use a package manager, such as [Chocolatey](https://chocolatey.org/) when
using Windows, [Homebrew](https://brew.sh/) when using Mac or the package
manager with your distro when using Linux. 

Throughout Kiwi, it is assumed that these tools are installed globally and are
accesible from your PATH variable. Please make sure of this by running in your
command line interface:

```
php -v
composer -v
yarn -v
```

## Deployment
We're assuming you're deploying to either development or production. More
variations in between those too are possible, but to keep things brief, we're
assuming typical cases

### Development
Start by cloning the develop branch of the repository. For first time git users,
it is recommended to use a GUI for git. Instructions for those vary, please
check the documentation for your program. When using the git in the command line,
simply clone the develop branch:

```bash
git clone https://github.com/jasperweyne/helpless-kiwi.git -b develop
cd helpless-kiwi
```

Now, create a ```.env.local``` file in the root folder. Here, disable HTTPS, and
configure your database connection. For example (modify this according to your
local environment:

```bash
SECURE_SCHEME=http
DATABASE_URL=mysql://username:password@127.0.0.1:3306/database
```

Then, make sure dependencies are installed. When doing this, composer will
install the git hooks from the .hooks folder.

```bash
composer install
yarn install
```

Now, you should deploy the database table structure. To insert the tables, make
sure you have your database connection configured correctly. Then, run:

```bash
php bin/console doctrine:schema:update --force
```

To add a user, first register a person with:

```bash
php bin/console app:create-person [email] [name]
```

Then, add a login to that user with:

```bash
php bin/console app:set-auth --admin [email]
```

Now, you'll want to build the assets. To do this continuously while editing them,
use:

```bash
yarn watch
```

This should be enough to run the server locally, start it with:

```bash
php bin/console server:run
```

### Production
To ease the building process, you can download, modify and run the build_prod.sh
script from this repository to generate a production environment. If this
doesn't work properly for you can deploy manually using these instructions.

For clarity purposes, we're assuming you're deploying to another location, for
example by moving the files over FTP. Locally or through SSH should work fine
as well though.

Again, make sure a database is installed and available. Configure this in the
```/.env.local``` file, the same way as with a development build. Additionally,
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

Additionally, add this line to ```composer.json```:

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
```require dirname(__DIR__).'/config/bootstrap.php';``` line in the index.php
file if necessary.

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

When moving the public folder, ```composer install``` will give a warning on the
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
```php bin/console cache:clear``` and deploy the var folder as well, otherwise
skip this step. If everything went right, your server should now be running
correctly!
