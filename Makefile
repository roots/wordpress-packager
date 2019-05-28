DRUN=docker run --rm -it -v $(shell pwd):/app -w /app
RUN=${DRUN} rootsdev/wordpress-packager-dev
COMPOSER=${RUN} composer
COMPOSER_FLAGS=--no-ansi --no-interaction --no-progress --no-scripts --optimize-autoloader --prefer-dist
PHPUNIT=vendor/bin/phpunit

## docker

.PHONY: docker docker-base docker-app docker-test

docker: docker-base docker-app docker-dev

docker-base:
	docker build -t rootsdev/wordpress-packager-base -f build/base/Dockerfile .

docker-app: docker-base
	docker build -t rootsdev/wordpress-packager -f build/app/Dockerfile .

docker-dev: docker-base
	docker build -t rootsdev/wordpress-packager-dev -f build/dev/Dockerfile .

## deps
composer.lock: composer.json
	${MAKE} update

vendor: composer.lock
	${MAKE} install

## test
.PHONY: test

## tests
.PHONY: test test-coverage

test: vendor
	${RUN} ${PHPUNIT}

.PHONY: install update

## util
.PHONY: clean install update phpcs phpcbf

clean:
	${RUN} rm -rf vendor composer.lock

install:
	${COMPOSER} install ${COMPOSER_FLAGS}

update:
	${COMPOSER} update ${COMPOSER_FLAGS}

phpcs: vendor
	${RUN} vendor/bin/phpcs src

phpcbf: vendor
	${RUN} vendor/bin/phpcbf src

shell:
	${RUN} sh