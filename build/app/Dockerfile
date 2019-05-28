FROM roots/wordpress-packager-base

COPY composer.json composer.lock ./

RUN composer install --no-dev --no-ansi --no-interaction --no-progress --no-scripts --optimize-autoloader --prefer-dist

COPY src/ test/ phpcs.xml phpunit.xml phpunit-bootstrap.php ./
