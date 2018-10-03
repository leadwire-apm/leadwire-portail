# Install Leadwire

## Requirements

### Download The repo
git clone {url du repo}

### Installation de Mongodb

https://docs.mongodb.com/v3.4/tutorial/install-mongodb-on-red-hat/


### Install Nginx

yum install epel-release -y

yum install nginx -y

systemctl start nginx

systemctl enable nginx

### Install PHP

wget http://rpms.remirepo.net/enterprise/remi-release-7.rpm

rpm -Uvh remi-release-7.rpm

yum install yum-utils -y

yum-config-manager --enable remi-php71

yum --enablerepo=remi,remi-php71 install php-opcache php-pecl-apcu php-cli php-pear php-pecl-mongodb php-gd php-mbstring php-mcrypt php-xml
php-ldap php-json


### Configure Nginx

Path of the projet is a free choice. conventionally is: /apps/leadwire-portail

Edit Config File /etc/nginx/nginx.conf

In section 80 redirect to 443

```
 server {
        listen 80 default_server;
        listen [::]:80 default_server;
        server_name _;
        return 301 https://$host$request_uri;
    }
```

In Https Section:

```
server {
        listen       443 ssl http2 default_server;
        listen       [::]:443 ssl http2 default_server;
        server_name  _;
        root         /apps/leadwire-portail/web;
        index app.php;

        ssl_certificate "/certs/leadwire.io_ssl_certificate.cer";
        ssl_certificate_key "/certs/_.leadwire.io_private_key.key";
        ssl_session_cache shared:SSL:1m;
        ssl_session_timeout  10m;
        ssl_ciphers HIGH:!aNULL:!MD5;
        ssl_prefer_server_ciphers on;

        # Load configuration files for the default server block.
        include /etc/nginx/default.d/*.conf;

        location / {
            index app.php;
            if (-f $request_filename) {
              break;
            }
          rewrite ^(.*)$ /app.php last;
        }
        location ~ (app).php {
              fastcgi_pass unix:/var/run/php-fpm/php-fpm.sock;
              fastcgi_index index.php;
              fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
              include fastcgi_params;
        }
        error_page 404 /404.html;
         }

        error_page 500 502 503 504 /50x.html;
            location = /50x.html {
        }
    }


```

root section is for project path. It should be 'Web' folder of the projet

Sections ssl_certificate & ssl_certificate_key are for certificats.

Check /var/run/php-fpm/php-fpm.sock


### Installation Composer

```
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"

```

### Installation Node

yum install nodejs

Pour la verification:

node --version

install npm

yum install npm

### Installation Grunt at Bower

npm install -g grunt-cli

npm install -g bower

npm install -g replace
 
yum install git
 



## Installation

* ```php composer.phar install```

if you installed composer globally, the command should be

```composer install```

The command should ask at the end for the parameters of the instances (Database, email, ldap...)

* ```cd src/UIBundle/Resources/public/dev```
* ```npm install```
* In case of regular user ```bower install``` in case of root add this parameter ```--allow-root```
* Uncomment the first and last line from `src/UIBundle/Resources/public/dev/app/index.html`
* Update parameters in `src/UIBundle/Resources/public/dev/app/scripts/app.js`
* Go root directory
* ```bin/console leadwire:install```

### notes

 In case of changing parameters like :
 
 * adding user/password to mongodb access
 * changing IPs of Ldap / elastic VPS
 
 
Just update the file app/config/parameters.yml and clean cache (rm -rf var/cache/app)

Stripe account should be unique for every instance. Do not share stripe account between test and prod.
Stripe account should have tel and email validated before going to test and prod.

# CLI cmd

## Import Stats

```bin/console leadwire:import:stats <file>```

This cmd should csv file. for help you can use 

```bin/console leadwire:import:stats --help```

## Sending mail

Sending mail is deferred task. It can be a cron (every minutes for example)


```bin/console swiftmailer:spool:send --env=prod```
