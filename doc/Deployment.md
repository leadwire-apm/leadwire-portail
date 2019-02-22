# Deploying the application in a production environment

1- ssh into the remote server

2- get the latest version from the remote repository

3- Provide the necessary configuration for the application (See `Configuration` section below )

4- from the projet's root directory
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

# Configuration
The application can be configured either by providing a `.env` file or by providing the necessary configuration keys as envvars (as per the 12-factor app paradigm)

 A template file is provided (`.env.dist`) which contains all the variables that need to be defined for the application to work properly.