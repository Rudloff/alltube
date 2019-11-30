FROM php:7.3-apache
RUN apt-get update
RUN apt-get install -y libicu-dev xz-utils git zlib1g-dev python libgmp-dev gettext libzip-dev
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install intl
RUN docker-php-ext-install zip
RUN docker-php-ext-install gmp
RUN docker-php-ext-install gettext
RUN a2enmod rewrite
RUN curl -sS https://getcomposer.org/installer | php
COPY resources/php.ini /usr/local/etc/php/
COPY . /var/www/html/
RUN php composer.phar install --prefer-dist --no-progress
ENV CONVERT=1
