#!/bin/bash
set -e
#let's configure environment
run-parts /etc/my_init.d
mkdir -p /tmp/behat

# create log dir locally failing sometimes)
mkdir -p /var/log/app

cd /var/www
export PGHOST=${API_DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${API_DATABASE_PASSWORD:=api}
export PGDATABASE=${API_DATABASE_NAME:=api}
export PGUSER=${API_DATABASE_USERNAME:=api}
rm -rf var/cache/*

# phpunit
php vendor/phpunit/phpunit/phpunit -c tests/phpunit/

# behat
bin/behat --config=tests/behat/behat.yml --profile=${PROFILE:=headless} --stop-on-failure
