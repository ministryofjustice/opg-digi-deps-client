FROM registry.service.dsd.io/opguk/php-fpm:0.1.128

# adds nodejs pkg repository
RUN  curl --silent --location https://deb.nodesource.com/setup_4.x | bash -

RUN  apt-get update && apt-get install -y \
     php-pear php5-curl php5-memcached php5-redis \
     dos2unix postgresql-client \
     nodejs ruby && \
     apt-get clean && apt-get autoremove && \
     rm -rf /var/lib/cache/* /var/lib/log/* /tmp/* /var/tmp/*


#we need npm3 for "npm run build" to work
RUN  curl -L https://www.npmjs.com/install.sh | sh
RUN  cd /tmp && curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

RUN  npm install --global gulp
RUN  gem install sass scss_lint

# build app dependencies
COPY composer.json /app/
COPY composer.lock /app/
WORKDIR /app
USER app
ENV  HOME /app
RUN  composer install --prefer-source --no-interaction --no-scripts
COPY package.json /app/
RUN  npm install

# install remaining parts of app
ADD  . /app
USER root
RUN find . -not -user app -exec chown app:app {} \;
USER app
ENV  HOME /app
#do we still need the post-install-cmd
RUN  composer run-script post-install-cmd --no-interaction
RUN  npm run build

# cleanup
RUN  rm /app/app/config/parameters.yml
USER root
ENV  HOME /root

# app configuration
ADD docker/confd /etc/confd

# let's make sure they always work
RUN dos2unix /app/scripts/*

# copy init scripts
ADD  docker/my_init.d /etc/my_init.d
RUN  chmod a+x /etc/my_init.d/*

ENV  OPG_SERVICE client
