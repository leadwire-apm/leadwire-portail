# Install Leadwire

## Requirements

mongo 3.4

nginx

Nginx should be installed and ssl configured.

PHP 7.* (php-fpm)

ext-json / ext-ldap / ext-mongodb


composer

```
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"

```

node / npm

npm install -g grunt-cli

npm install -g bower


## Installation

* ```composer install```

The command should ask at the end for the parameters of the instances (Database, email, ldap...)

* ```cd src/UIBundle/Resources/public/dev```
* ```npm install```
* ```bower install```
* Uncomment the first and last line from `src/UIBundle/Resources/public/dev/app/index.html`
* Update parameters in `src/UIBundle/Resources/public/dev/app/scripts/app.js`
* ```grunt build```
* Go root directory
* ```bin/console leadwire:install```