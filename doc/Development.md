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
