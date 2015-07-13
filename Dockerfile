FROM registry.service.dsd.io/opguk/php-fpm:0.1.38

RUN  apt-get update && apt-get install -y \
     php-pear php5-curl php5-memcached php5-redis \
     nodejs && \
     apt-get clean && apt-get autoremove && \
     rm -rf /var/lib/cache/* /var/lib/log/* /tmp/* /var/tmp/*

RUN  cd /tmp && curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

#npm, nodejs, sass libs
#TODO: zac freeze gem versions
RUN  ln -s /usr/bin/nodejs /usr/bin/node
RUN  curl -L https://www.npmjs.com/install.sh | sh
RUN  npm install -g grunt
RUN  npm install -g grunt-cli
RUN  apt-get install -y ruby
RUN  gem install sass

# build app dependencies
COPY composer.json /app/
COPY composer.lock /app/
RUN  chown -R app /app
WORKDIR /app
USER app
ENV  HOME /app
RUN  composer install --prefer-source --no-interaction --no-scripts

# install remaining parts of app
ADD  . /app
USER root
RUN  chown -R app /app
USER app
ENV  HOME /app
RUN  composer run-script post-install-cmd --no-interaction
RUN  npm install
RUN  npm run all

# cleanup
RUN  rm /app/app/config/parameters.yml
USER root
ENV  HOME /root

# app configuration
ADD docker/confd /etc/confd
