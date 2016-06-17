#!/bin/bash

export DEBIAN_FRONTEND=noninteractive
apt-get update -y
apt-get upgrade -y
apt-get install -y \
        apache2 \
        dos2unix \
        mysql-client-core-5.5 \
        mysql-server \
        php5 \
        php5-cli \
        php5-curl \
        php5-mcrypt \
        php5-mysql \
        libapache2-mod-php5

curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

mysql -u root --password='' -e "GRANT ALL PRIVILEGES ON *.* TO 'root'@'192.168.33.%'; FLUSH PRIVILEGES;"
mysql -u root --password='' -e "CREATE USER 'homestead'@'localhost' IDENTIFIED BY 'secret';"
mysql -u root --password='' -e "GRANT ALL PRIVILEGES ON *.* TO 'homestead'@'localhost'; FLUSH PRIVILEGES;"
mysql -uhomestead -psecret -e "create database homestead"

mv /tmp/custom-envvars /etc/apache2/envvars
dos2unix /etc/apache2/envvars
a2enmod php5
a2enmod rewrite
php5enmod mcrypt
a2dissite 000-default
a2ensite 001-laravel
service apache2 restart