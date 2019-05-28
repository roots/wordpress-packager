FROM rootsdev/wordpress-packager

RUN apk --update --no-cache --virtual .xdebug-build add autoconf g++ make && \
    pecl install -f xdebug && \
    docker-php-ext-enable xdebug && \
    apk del .xdebug-build
