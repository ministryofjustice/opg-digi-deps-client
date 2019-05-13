FROM php:5.5-fpm-alpine

# Install postgresql drivers
RUN apk add --no-cache postgresql-dev \
  && docker-php-ext-install pdo pdo_pgsql

# Enable Redis driver
RUN apk add --no-cache autoconf g++ make \
  && pecl install redis \
  && docker-php-ext-enable redis

# Install openssl for wget
RUN apk add --update openssl

# Add Confd to configure parameters on start
ENV CONFD_VERSION="0.16.0"
RUN wget -q -O /usr/local/bin/confd "https://github.com/kelseyhightower/confd/releases/download/v${CONFD_VERSION}/confd-${CONFD_VERSION}-linux-amd64" \
  && chmod +x /usr/local/bin/confd

# Add Waitforit to wait on db starting
ENV WAITFORIT_VERSION="v2.4.1"
RUN wget -q -O /usr/local/bin/waitforit https://github.com/maxcnunes/waitforit/releases/download/$WAITFORIT_VERSION/waitforit-linux_amd64 \
  && chmod +x /usr/local/bin/waitforit

WORKDIR /var/www
# See this page for directories required
# https://symfony.com/doc/3.4/quick_tour/the_architecture.html
COPY web/app_dev.php web/app_dev.php
RUN mkdir -p web/assets
COPY web/config.php web/config.php
COPY vendor vendor
COPY src src
COPY app app
COPY docker/confd /etc/confd
ENV TIMEOUT=20
CMD confd -onetime -backend env \
  && mkdir var \
  && chown -R www-data var \
  && php-fpm
