#!/bin/sh
service ssh start
a2enmod rewrite
service apache2 start
chown www-data:www-data /var/www/html/* -R
cd /var/www/html
useradd ctf
echo ctf:test123 | chpasswd
sleep 2
rm -rf /tmp
#service mysql start
#mysql -e "source /var/www/html/test.sql;"
supervisord -n
if [ -x "extra.sh" ]; then
./extra.sh
fi
/bin/bash