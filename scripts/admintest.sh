#!/bin/bash
#let's configure environment
run-parts /etc/my_init.d
chown app:app /tmp/behat

cd /app
/sbin/setuser app mkdir -p /tmp/behat
apt-get update > /dev/null 2>&1
export PGHOST=${API_DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${API_DATABASE_HOSTNAME:=api}
export PGDATABASE=${API_DATABASE_HOSTNAME:=postgres}
export PGUSER=${API_DATABASE_HOSTNAME:=postgres}
rm -rf app/cache/*
/sbin/setuser app bin/behat --config=tests/behat/behat.yml.dist --suite=admin --profile=${PROFILE:=headless} --stop-on-failure
