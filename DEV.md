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
php bin/console server:run
```
