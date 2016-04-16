FROM php:5.6-apache
RUN apt-get update
RUN apt-get install -y libicu-dev xz-utils git zlib1g-dev python npm nodejs-legacy
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install intl
RUN docker-php-ext-install zip
RUN npm install -g bower grunt-cli
RUN a2enmod rewrite
COPY php.ini /usr/local/etc/php/
COPY . /var/www/html/
RUN curl -sS https://getcomposer.org/installer | php
RUN php composer.phar install
RUN npm install
RUN bower --allow-root install
RUN grunt
