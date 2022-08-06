FROM php:8.1-fpm

COPY composer.lock composer.json /var/www/

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    nano \
    unzip \
    git \
    curl

RUN docker-php-ext-install mysqli

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . /var/www

CMD ["/bin/sh", "/var/www/build.sh"]

EXPOSE 9000

