# Nginx Setup & v-host configuration
```sh
$ yum install epel-release -y

$ yum install nginx -y

$ systemctl start nginx

$ systemctl enable nginx
```

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