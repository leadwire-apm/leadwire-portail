# Working (developing) on the application

1- get the latest version from the remote repository

2- from the projet's root directory
```sh
$ php ./composer.phar install
$ bin/console assets:install --symlink
$ bin/console assetic:dump
$ bin/console cache:clear
```

***Note***: if you installed composer [globally](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos) at system level, the command is:
```sh
$ composer install  # Instead of php ./composer.phar install
```


***Note 2***: When working on frontend features (Javascript, CSS), a special Symfony command provided by the AsseticBundle eases the rebuild of the assets. Thus, from the root folder of the project:
```sh
$ bin/console assetic:watch
```
At this point every change made to the assets (i.e. any JS or CSS file) will trigger a rebuild of the assets

# Configuration
The application can be configured either by providing a `.env` file or by providing the necessary configuration keys as envvars (as per the 12-factor app paradigm)

In development environment, it's easier to use a `.env` file. A template file is provided (`.env.dist`) which contains all the variables that need to be defined for the application to work properly.

# Quality assurance

Software quality is enforced by:
- PHPCS: A Coding Standard checker for PHP
- PHPSTAN: A static code analysis tool for PHP
- PHPUnit: Unit testing framework for PHP

For usage reference on how to issue those commands, check the `.gitlab-ci.yml` file

# File Permissions
One important Symfony requirement is that the `var` directory must be writable both by the web server and the command line user. Check the official documentation for more details on how to achieve that [Symfony Permissions](https://symfony.com/doc/3.4/setup/file_permissions.html)

# Seeding Data
Leadwire portal application needs some date to be setup in the database to operate properly. This can be achieved via a spacific command
```sh
$ bin/console leadwire:install
```

***Note***: The previous command will:
    - Delete any Stripe plan (in Stripe Platform)
    - ***Commpletely delete*** any previous data in the data base
    - Create Demo Applications entries in MongoDB
    - Create Demo Applications entries in LDAP
    - Create Pricing plans in MongoDB
    - Create Pricing plans in Stripe