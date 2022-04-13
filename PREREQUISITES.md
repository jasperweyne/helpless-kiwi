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
composer -V
yarn -v
```

We're assuming php version 7.4, composer at least version 2.0 and yarn 1.22.
