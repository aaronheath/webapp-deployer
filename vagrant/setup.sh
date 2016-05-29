#!/usr/bin/env bash

echo '----------------------'
echo '------ SETUP.SH ------'
echo '----------------------'

#######################################
## DIST SETUP
#######################################

# Update packages
sudo apt-get update

# Install Packages
sudo apt-get -y install apache2 curl libfontconfig run-one vim git sqlite3

# Add Sources
curl -sL https://deb.nodesource.com/setup_6.x | sudo -E bash -

# Install Node & Grunt
sudo apt-get -y install nodejs
sudo npm set progress=false
sudo npm install -g grunt-cli

# Install PHP
sudo apt-get -y install php5 libapache2-mod-php5
sudo apt-get -y install php5-cli
sudo apt-get -y install php5-mcrypt
sudo apt-get -y install php-sqlite
sudo php5enmod mcrypt

# Install Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php -r "unlink('composer-setup.php');"
sudo mv composer.phar /usr/local/bin/composer

#######################################
## SETUP
#######################################

# Apache
sudo sed -i -e 's/APACHE_RUN_USER=www-data/APACHE_RUN_USER=vagrant/g' /etc/apache2/envvars
sudo sed -i -e 's/APACHE_RUN_GROUP=www-data/APACHE_RUN_GROUP=vagrant/g' /etc/apache2/envvars
export APACHE_RUN_USER=vagrant
export APACHE_RUN_GROUP=vagrant
sudo su -
cp /vagrant/vagrant/apache-site-config.conf /etc/apache2/sites-available/000-default.conf
sudo a2enmod rewrite
sudo service apache2 restart

# Update Composer Packages
cd /vagrant/
composer install
composer update

# Update NPM Packages
npm install
npm update

# Database Setup
php artisan migrate:refresh

# Build
##

# Setup Queues
(crontab -l 2>/dev/null; echo "* * * * * run-one php /vagrant/artisan queue:work --daemon --sleep=5 --delay=60 --tries=60") | crontab -

echo '----------------------------'
echo '------ SETUP.SH (end) ------'
echo '----------------------------'

exit
