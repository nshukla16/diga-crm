#sudo docker build -t registry.gitlab.com/diga.pt/erp/laravel-erp .
#sudo docker push registry.gitlab.com/diga.pt/erp/laravel-erp
# Set the base image for subsequent instructions
FROM roadiz/php72-runner

# Update packages
RUN apt-get update

# Install PHP and composer dependencies
RUN apt-get install -qq git curl libmcrypt-dev libjpeg-dev libpng-dev libfreetype6-dev libbz2-dev sudo zip unzip nodejs

# Clear out the local repository of retrieved package files
RUN apt-get clean

# Install needed extensions

# Install Composer
RUN curl --silent --show-error "https://getcomposer.org/installer" | php -- --install-dir=/usr/local/bin --filename=composer