### Variables

# Applications
COMPOSER ?= /usr/bin/env composer
DEPENDENCIES ?= latest
PHP ?= /usr/bin/env php
# Behat scenarios tagged @php85 use a PHP 8.5 only syntax, they are filtered on older versions
BEHAT_TAGS = $(shell ${PHP} -r 'echo PHP_VERSION_ID < 80500 ? "--tags=~@php85" : "";')

### Helpers
all: clean depend

.PHONY: all

### Dependencies
depend:
ifeq ($(DEPENDENCIES), lowest)
	${COMPOSER} update --prefer-lowest --prefer-dist --no-interaction;
else
	${COMPOSER} update --prefer-dist --no-interaction;
endif

.PHONY: depend

### QA
qa: lint phpstan phpcs audit
qa-offline: lint phpstan phpcs

lint:
	find ./src -name "*.php" -exec ${PHP} -l {} \; | grep "Parse error" > /dev/null && exit 1 || exit 0

phpstan:
	${PHP} -d memory_limit=256M vendor/bin/phpstan analyse

phpcs:
	${PHP} vendor/bin/phpcs --standard=PSR12 --extensions=php src/

audit:
	${COMPOSER} audit

.PHONY: qa qa-offline lint phpstan phpcs audit

### Testing
test:
	XDEBUG_MODE=coverage ${PHP} -dzend_extension=xdebug.so -dxdebug.mode=coverage vendor/bin/phpunit -c phpunit.xml --colors --coverage-text
	${PHP} vendor/bin/behat ${BEHAT_TAGS}

.PHONY: test

### Cleaning
clean:
	rm -rf vendor

.PHONY: clean
