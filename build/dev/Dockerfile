FROM roots/wordpress-packager

RUN apk --update --no-cache --virtual .xdebug-build add autoconf g++ make && \
    pecl install -f xdebug && \
    docker-php-ext-enable xdebug && \
    apk del .xdebug-build

RUN curl -L https://codeclimate.com/downloads/test-reporter/test-reporter-latest-linux-amd64 > /usr/local/bin/cc-test-reporter \
    && chmod +x /usr/local/bin/cc-test-reporter