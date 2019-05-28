#!/usr/bin/env sh
set -x
set +e

cc-test-reporter before-build
composer test -- --coverage-clover clover.xml
cc-test-reporter after-build --coverage-input-type clover --exit-code $?
