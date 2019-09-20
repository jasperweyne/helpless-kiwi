# Install

## Prerequisites
Make sure php 7 is installed on your machine. If you're deploying to another
machine, make sure this has a php server installed, for example Apache or Nginx.
Also, make sure that the machine you're deploying to has a database available,
MySQL is supported primairily.

After that, make sure [composer](https://getcomposer.org/), [node.js](https://nodejs.org/) 
and [yarn](https://yarnpkg.com/) are installed on your local system.

## Deployment
We're assuming you're deploying to either development or production. More
variations in between those too are possible, but to keep things brief, we're
assuming typical cases

### Development
First, create a ```/.env.local``` file in the root folder, and configure the 
database. For example, with a MySQL database:

```DATABASE_URL=mysql://username:password@127.0.0.1:3306/database```

Then, make sure dependencies are installed.

```bash
composer install
yarn install
```

After deploying the table structure (see the next chapter), you'll want to build
the assets. To do this continiously while editing them, use:

```bash
yarn watch
```

This should be enough to run the server locally, start it with:

```bash
php bin/console server:run
```

### Production
To ease the building process, you can run build_prod.sh to generate a production
environment. If this doesn't work, you can deploy manually using these
instructions.

For clarity purposes, we're assuming you're deploying to another location, for
example by moving the files over FTP. Locally or through SSH should work fine
as well though.

Again, make sure a database is installed and available. Configure this in the
```/.env.local``` file, the same way as with a development build. Additionally,
add to that file:

```APP_ENV=prod```

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

## Database
To insert the tables, make sure you're running the program with a database
configured. Then, run:

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