#!/bin/sh
set -e
COMPOSER_CACHE_DIR=/tmp/composer composer install --no-dev --no-interaction
