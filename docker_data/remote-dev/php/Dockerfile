FROM php:7.1.27-fpm

RUN apt-get update

# install nano
RUN apt-get install nano

# install mysql extentions
RUN docker-php-ext-install pdo pdo_mysql

# install zip extention
RUN apt-get install -y zlib1g-dev libzip-dev unzip
RUN docker-php-ext-install zip

# install gd extension
RUN apt-get install -y libpng-dev
RUN docker-php-ext-install gd

# install composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "if (hash_file('sha384', 'composer-setup.php') === '48e3236262b34d30969dca3c37281b3b4bbe3221bda826ac6a9a62d6444cdb0dcd0615698a5cbe587c3f0fe57a54d8f5') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php \
    && php -r "unlink('composer-setup.php');" \
    && mv composer.phar /usr/local/bin/composer

# install git, mc
RUN apt-get -y install mc git

# install ssh-server
RUN apt-get install -y openssh-server
RUN mkdir /var/run/sshd
RUN echo 'root:zSJk84af9k' | chpasswd
COPY sshd_config /etc/ssh/sshd_config
# SSH login fix. Otherwise user is kicked off after login
RUN sed 's@session\s*required\s*pam_loginuid.so@session optional pam_loginuid.so@g' -i /etc/pam.d/sshd
ENV NOTVISIBLE "in users profile"
RUN echo "export VISIBLE=now" >> /etc/profile

# install supervisor
RUN apt-get install -y supervisor
RUN mkdir -p /var/log/supervisor
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# install nginx
RUN apt-get install -y nginx
COPY default /etc/nginx/sites-enabled/default

# configure php-fpm
COPY php-fpm.conf /usr/local/etc/php/php-fpm.conf

# install node 8
RUN apt-get install curl
RUN curl -sL https://deb.nodesource.com/setup_8.x | bash -
RUN apt-get install -y nodejs npm

# permissions
RUN chown -R www-data:www-data /var/www/html

# install application
#RUN npm install -g bower
#RUN npm run dev
#RUN php artisan key:generate
#RUN php artisan migrate
#RUN php artisan seed:initial_with_language ru
#RUN php artisan passport:install

EXPOSE 22

CMD ["/usr/bin/supervisord"]