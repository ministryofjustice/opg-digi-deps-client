#!/bin/bash
#let's configure environment
confd -onetime -backend env

cd /var/www
mkdir -p /tmp/behat
apt-get update > /dev/null 2>&1
export PGHOST=${API_DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${API_DATABASE_PASSWORD:=api}
export PGDATABASE=${API_DATABASE_NAME:=api}
export PGUSER=${API_DATABASE_USERNAME:=api}
rm -rf var/cache/*
# remove behat cache as it's mounted in a persistent container
rm -rf /tmp/behat/*

bin/behat --config=tests/behat/behat.yml --suite=admin --profile=${PROFILE:=headless} --stop-on-failure
