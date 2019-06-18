FROM composer:1.8.6

RUN apk add --no-cache icu-dev \
 && docker-php-ext-configure intl \
 && docker-php-ext-install -j$(getconf _NPROCESSORS_ONLN) intl
