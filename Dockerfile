FROM php:5.6-apache
RUN apt-get update
RUN curl -sL https://deb.nodesource.com/setup_6.x | bash -
RUN apt-get install -y libicu-dev xz-utils git zlib1g-dev python nodejs
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install intl
RUN docker-php-ext-install zip
RUN npm install -g bower grunt-cli
RUN a2enmod rewrite
RUN curl -sS https://getcomposer.org/installer | php
COPY php.ini /usr/local/etc/php/
COPY . /var/www/html/
RUN php composer.phar install --prefer-dist
RUN npm install
RUN bower --allow-root install
RUN grunt
