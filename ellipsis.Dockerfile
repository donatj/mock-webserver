FROM php:8.2-cli

ENV DEBIAN_FRONTEND noninteractive
RUN apt-get -y clean \
	&& apt-get -y dist-upgrade \
	&& apt-get -y update --fix-missing \
	&& apt-get -y install locales

RUN apt-get -y install zip libzip-dev git

RUN docker-php-ext-install zip sockets pcntl

ENV COMPOSER_ALLOW_SUPERUSER 1
RUN curl -sS https://getcomposer.org/installer | php \
	&& mv composer.phar /usr/local/bin/composer

WORKDIR /src

COPY . .

RUN composer install
