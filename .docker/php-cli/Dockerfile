FROM php:8-cli-alpine

RUN apk update \
    && apk add --no-cache curl g++ make git autoconf bash

RUN docker-php-ext-install mysqli \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer\
    && export COMPOSER_PROCESS_TIMEOUT=600 \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY ./php-cli/configurations/* /usr/local/etc/php/conf.d/