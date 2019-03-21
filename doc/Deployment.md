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

# File Permissions
One important Symfony requirement is that the `var` directory must be writable both by the web server and the command line user. Check the official documentation for more details on how to achieve that [Symfony Permissions](https://symfony.com/doc/3.4/setup/file_permissions.html)

# Seeding Data
Leadwire portal application needs some date to be setup in the database to operate properly. This can be achieved via a spacific command
```sh
$ bin/console leadwire:install --env=prod # add --purge flag to clear the entire database before creating new data
```

***Note***: The previous command will:
    - Delete any Stripe plan (in Stripe Platform)
    - ***Commpletely delete*** any previous data in the data base
    - Create Demo Applications entries in MongoDB
    - Create Demo Applications entries in LDAP
    - Create Pricing plans in MongoDB
    - Create Pricing plans in Stripe
