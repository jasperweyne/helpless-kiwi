<p align="center">
<img src="https://raw.githubusercontent.com/jasperweyne/helpless-kiwi/master/assets/image/readme-header.png" alt="helpless-kiwi" style="max-width:100%;">
</p>

# Helpless Kiwi
Helpless Kiwi is a project to manage members, create and manage activities, keep
track of inventory and more. It has been written using the Symfony/Doctrine framework.

## Built on...
 - Symfony
 - Doctrine
 - Twig
 - Webpack
 - Foundation for Sites
 - SCSS
 - Phpunit

## How to install
Make sure php 7, composer, node.js and yarn are installed on your system.

```
composer install
yarn install
```

## How to run in development
Make sure a database is installed. MySQL is supported.
Create a ```/.env.local``` file in the root folder, and configure the database.
For example, with a MySQL database:

```DATABASE_URL=mysql://username:password@127.0.0.1:3306/database```

Then, insert the tables:

```php bin/console doctrine:schema:update --force```

To add a user, first register a person with:

```php bin/console app:create-person [email] [name]```

Then, add a login to that user with:

```php bin/console app:set-auth [email]```

To run the server, use:

```php bin/console server:run```

## How to run in production
Don't! It's not ready yet!

## Other Commands

 - ```php bin/console cache:clear```: Always a good idea!
 - ```composer fix```: Fix PHP source code formatting
 - ```composer test```: Run unit tests in /tests/
 - ```yarn watch```: Automagically generate javascript/stylesheets from assets while editting
