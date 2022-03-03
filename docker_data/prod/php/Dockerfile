FROM php:7.1-fpm

# install mysql extentions
RUN docker-php-ext-install pdo pdo_mysql

# install zip extention
RUN apt-get install -y zlib1g-dev libzip-dev unzip
RUN docker-php-ext-install zip

# install gd extension
RUN apt-get install -y libpng-dev
RUN docker-php-ext-install gd

WORKDIR /var/www

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('SHA384', 'composer-setup.php') === '55d6ead61b29c7bdee5cccfb50076874187bd9f21f65d8991d46ec5cc90518f447387fb9f76ebae1fbbacf329e583e30') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && php composer.phar install --no-dev --no-scripts \
    && rm composer.phar

COPY . /var/www

RUN chown -R www-data:www-data \
        /var/www/storage \
        /var/www/bootstrap/cache