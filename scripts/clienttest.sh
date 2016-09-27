#!/bin/bash
set -e
#let's configure environment
run-parts /etc/my_init.d
chown app:app /tmp/behat

# create log dir locally failing sometimes)
mkdir -p /var/log/app

cd /app
/sbin/setuser app mkdir -p /tmp/behat
export PGHOST=${API_DATABASE_HOSTNAME:=postgres}
export PGPASSWORD=${API_DATABASE_HOSTNAME:=api}
export PGDATABASE=${API_DATABASE_HOSTNAME:=postgres}
export PGUSER=${API_DATABASE_HOSTNAME:=postgres}
rm -rf app/cache/*
# deprecated
suitename=${1:-deputy}

/sbin/setuser app php vendor/phpunit/phpunit/phpunit -c tests/phpunit/

# tests with coverage (install php5-xdebug if needed)
#/sbin/setuser app php -d zend_extension=xdebug.so bin/phpunit -c tests/phpunit/phpunit.xml --coverage-html=web/coverage-html

# deprecated
if [ -f tests/behat/behat.yml ]; then
    behatConfigFile=tests/behat/behat.yml
else
    behatConfigFile=tests/behat/behat.yml.dist
fi
# end deprecated

export BEHAT_PARAMS="{\"extensions\" : {\"Behat\\\\MinkExtension\\\\ServiceContainer\\\\MinkExtension\" : {\"base_url\" : \"${FRONTEND_NONADMIN_HOST}\",\"selenium2\" : { \"wd_host\" : \"$WD_HOST\" }, \"browser_stack\" : { \"username\": \"$BROWSERSTACK_USER\", \"access_key\": \"$BROWSERSTACK_KEY\"}}}}"
echo BEHAT_PARAMS
/sbin/setuser app bin/behat --config=${behatConfigFile} --suite=deputy --profile=${PROFILE:=headless} --stop-on-failure
/sbin/setuser app bin/behat --config=${behatConfigFile} --suite=deputyodr --profile=${PROFILE:=headless} --stop-on-failure
