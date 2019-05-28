DRUN=docker run --rm -it -v $(shell pwd):/app -w /app -e CC_TEST_REPORTER_ID -e GIT_COMMIT_SHA -e GIT_BRANCH -e CIRCLE_BRANCH -e CIRCLE_SHA1
RUN=${DRUN} roots/wordpress-packager-dev
COMPOSER=${RUN} composer
COMPOSER_FLAGS=--no-ansi --no-interaction --no-progress --no-scripts --optimize-autoloader --prefer-dist
PHPUNIT=vendor/bin/phpunit

## docker

.PHONY: docker docker-base docker-app docker-test

docker: docker-base docker-app docker-dev

docker-base:
	docker build -t roots/wordpress-packager-base -f build/base/Dockerfile .

docker-app: docker-base
	docker build -t roots/wordpress-packager -f build/app/Dockerfile .

docker-dev: docker-base
	docker build -t roots/wordpress-packager-dev -f build/dev/Dockerfile .

## deps
composer.lock: composer.json
	${MAKE} update

vendor: composer.lock
	${MAKE} install

## test
.PHONY: test

## tests
.PHONY: test test-coverage test-coverage-remote

test: vendor
	${RUN} ${PHPUNIT}

COVERAGE_SCRIPT =  cc-test-reporter before-build
COVERAGE_SCRIPT += && ${PHPUNIT}
COVERAGE_SCRIPT += && cc-test-reporter format-coverage -t clover coverage/clover.xml
COVERAGE_SCRIPT += && cc-test-reporter upload-coverage
test-coverage-remote: vendor
	${RUN} sh -c '${COVERAGE_SCRIPT}'

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