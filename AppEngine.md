Version
* PHP 5.5 is required
* Current app require greater than PHP 5.6

Local PHP preview
* https://gae-php-tips.appspot.com/

```sudo apt-get install gcc libmysqlclient-dev libxml2-dev libcurl4-openssl-dev libpng-dev libjpeg-dev
   wget --trust-server-names http://us2.php.net/get/php-5.5.18.tar.bz2/from/us1.php.net/mirror
   tar -xvf php-5.5.18.tar.bz2
   cd php-5.5.18
   DEST=$HOME/app_engine/5.5
   ./configure --prefix=$DEST/installdir \
     --enable-bcmath \
     --enable-calendar \
     --enable-ftp \
     --enable-mbstring \
     --enable-opcache \
     --enable-soap \
     --enable-sockets \
     --enable-zip \
     --disable-fileinfo \
     --disable-flatfile \
     --disable-posix \
     --with-curl \
     --with-gd \
     --with-openssl \
     --without-sqlite3 \
     --without-pdo-sqlite \
     --without-imap \
     --without-kerberos \
     --without-imap-ssl\
     --without-interbase \
     --without-ldap \
     --without-mssql \
     --without-oci8 \
     --without-pgsql \
     --without-pear \
     --disable-phar \
     --without-snmp \
     --enable-mysqlnd \
     --with-pdo-mysql=mysqlnd \
     --with-mysqli=mysqlnd \
     --with-mysql=mysqlnd
   make install -j
   cd -```
   Execute script
`./dev_appserver.py /var/www/vhosts/tools/toggl-laravel/app.yaml --php_executable_path=/home/zain/app_engine/5.5/installdir/bin/php-cgi`

