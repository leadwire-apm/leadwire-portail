# Installing PHP and its dependencies

Note: This setup procedure assumes that we are working in a ***redhad*** based distro

```sh
$ wget http://rpms.remirepo.net/enterprise/remi-release-7.rpm

$ rpm -Uvh remi-release-7.rpm

$ yum install yum-utils -y

$ yum-config-manager --enable remi-php71

# Necessary PHP Extensions

$ yum --enablerepo=remi,remi-php71 install php-opcache php-pecl-apcu php-cli php-pear php-pecl-mongodb php-gd php-mbstring php-mcrypt php-xml
php-ldap php-json
```

# Installing Composer (The PHP Package manager)

The *get_composer.sh* script will automatically install composer to the latest version on the server (or in development environment)

```sh
# From the projets root folder

$ ./scripts/get_composer.sh
```
