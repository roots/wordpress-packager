FROM php:8.0.3-alpine

WORKDIR /app

RUN apk add --no-cache --virtual .composer-rundeps git make zip unzip coreutils \
 && apk add --no-cache --virtual .build-deps zlib-dev libzip-dev icu-dev \
 && docker-php-ext-configure zip --with-libzip \
 && docker-php-ext-configure intl \
 && docker-php-ext-install -j$(getconf _NPROCESSORS_ONLN) zip opcache intl \
 && runDeps="$( \
    scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
      | tr ',' '\n' \
      | sort -u \
      | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
    )" \
 && apk add --no-cache --virtual .composer-phpext-rundeps $runDeps \
 && apk del .build-deps \
 && printf "# composer php cli ini settings\n\
date.timezone=UTC\n\
memory_limit=-1\n\
opcache.enable_cli=1\n\
" > $PHP_INI_DIR/php-cli.ini

COPY build/install-composer.sh /tmp/

# cheeky
ENV COMPOSER_ALLOW_SUPERUSER 1

ENV COMPOSER_VERSION 1.8.5

RUN /tmp/install-composer.sh && rm /tmp/install-composer.sh
