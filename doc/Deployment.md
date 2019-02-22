# Deploying the application in a production environment

1- ssh into the remote server

2- get the latest version from the remote repository

3- from the projet's root directory
```sh
$ php ./composer.phar install --no-dev --no-scripts --optimize-autoloader
$ bin/console assets:install --env=prod
$ bin/console assetic:dump --env=prod
$ bin/console cache:clear --env=prod
```

*Note*: if you installed composer [globally](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos) at system level, the command is:
```sh
$ composer install  # Instead of php ./composer.phar install
```