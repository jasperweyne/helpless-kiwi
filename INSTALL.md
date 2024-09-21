# Local installation manual

If you want to install Helpless Kiwi for day-to-day usage, please refer to the
[deployment manual](DEPLOY.md)! If you want to install it on your own machine
for development purposes, keep reading!

## Prerequisites
Make sure PHP >8.1 is installed on your machine. If you're deploying to another
machine, make sure this has a PHP server installed, for example Apache or Nginx.
Also, make sure that the machine you're deploying to has a database available,
MySQL or MariaDB is supported. Note that Windows isn't supported by PHP 8
anymore, it's recommended to use Windows Subsystem for Linux instead. For
dependencies, Kiwi uses the [composer](https://getcomposer.org/) package manager.  
It is furthermore recommended to have the Symfony CLI installed.

Throughout Kiwi, it is assumed that these tools are installed globally and are
accesible from your PATH variable. Please make sure of this by running in your
command line interface:

```
php -v
composer -V
symfony version
```

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
APP_ENV=dev
APP_DEBUG=1
SECURE_SCHEME=http
DATABASE_URL=mysql://username:password@127.0.0.1:3306/database
```

Then, make sure dependencies are installed. When doing this, composer will
install the git hooks from the .hooks folder.

```bash
composer install
```

Then, generate the assets by running:

```bash
php bin/console tailwind:build
php bin/console asset-map:compile
```

Now, you should deploy the database table structure. To insert the tables, make
sure you have your database running and connection configured correctly.
Then, run:

```bash
composer db-rebuild-dev
```
This will create the needed database tables, and create 2 users. One with normal privileges and one with admin privileges. 
| username | password | admin |
| --- | --- | --- |
| user@kiwi.nl | user | |
| admin@kiwi.nl | admin | x |

You can now start the server by running:

```bash
symfony server:start --no-tls
```
