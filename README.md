# Install Leadwire

## Requirements
---

### Download The repo
```sh
$ git clone {url du repo}
```


### MongoDB Setup

https://docs.mongodb.com/v3.4/tutorial/install-mongodb-on-red-hat/


### Nginx Setup
```sh
$ yum install epel-release -y

$ yum install nginx -y

$ systemctl start nginx

$ systemctl enable nginx
```

### PHP & PHP-extensions Setup
#### RedHat based distros
```sh
$ wget http://rpms.remirepo.net/enterprise/remi-release-7.rpm

$ rpm -Uvh remi-release-7.rpm

$ yum install yum-utils -y

$ yum-config-manager --enable remi-php71

$ yum --enablerepo=remi,remi-php71 install php-opcache php-pecl-apcu php-cli php-pear php-pecl-mongodb php-gd php-mbstring php-mcrypt php-xml
php-ldap php-json
```

### Nginx vHost configuration

Path of the projet is a free choice. conventionally it is: /apps/leadwire-portail

Edit Config File `/etc/nginx/nginx.conf`

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

- `root`  should point to the web folder of the application. e.g: /path/to/leadwire-repo/`web`

- Sections ssl_certificate & ssl_certificate_key are for certificats.

Check /var/run/php-fpm/php-fpm.sock


### Composer Setup

```sh
# From the projets root folder

$ ./scripts/get_composer.sh

```
### git Setup
```sh
$ yum install git
```

## Install application's dependencies

```sh
$ php composer.phar install
```

*Note*: if you installed composer [globally](https://getcomposer.org/doc/00-intro.md#installation-linux-unix-macos), the command is:

```sh
$ composer install
```

The command should ask at the end for the parameters of the instances (Database, email, ldap...)

### Notes

- In case of changing parameters such as:
    * Adding user/password to mongodb access
    * Changing IPs of Ldap / elastic VPS

    Update the right parameter in app/config/parameters.yml then rebuild the cache:
    ```sh
    $ app/console cache:clear --env=ENV # Where ENV is dev on development environment and prod on production servers
    ```

- Stripe account should be unique for each instance. (i.e. Do not share stripe account between test and prod.)

- Stripe account should have a valid phone number and e-mail before going to test and prod.


# CLI cmd

## Import Stats

- To import applications' statistics from a CSV file
```sh
$ bin/console leadwire:import:stats /path/to/stats-file
```