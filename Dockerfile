FROM php:7.0-apache
RUN apt-get update && apt-get install -my gnupg
RUN curl -sL https://deb.nodesource.com/setup_6.x | bash -
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add -
RUN echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list
RUN apt-get update
RUN apt-get install -y libicu-dev xz-utils git zlib1g-dev python nodejs libgmp-dev gettext yarn
RUN docker-php-ext-install mbstring
RUN docker-php-ext-install intl
RUN docker-php-ext-install zip
RUN docker-php-ext-install gmp
RUN docker-php-ext-install gettext
RUN a2enmod rewrite
RUN curl -sS https://getcomposer.org/installer | php
COPY resources/php.ini /usr/local/etc/php/
COPY . /var/www/html/
RUN php composer.phar install --prefer-dist
RUN yarn install --prod
RUN yarn grunt
ENV CONVERT=1
