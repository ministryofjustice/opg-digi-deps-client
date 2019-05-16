FROM node:8-alpine AS gulp

RUN apk add --no-cache python g++ make

WORKDIR /app
COPY package.json .
COPY package-lock.json .
COPY Gulpfile.js .
COPY src src

# Install NPM dependencies
RUN npm install

# Build assets with Gulp
RUN NODE_ENV=production npm run build


FROM php:5.5-fpm-alpine

# Install postgresql drivers
RUN apk add --no-cache postgresql postgresql-client zlib-dev unzip \
  && docker-php-ext-install zip

# Enable Redis driver
RUN apk add --no-cache autoconf g++ make \
  && pecl install redis \
  && docker-php-ext-enable redis

# Add NGINX
RUN apk add --no-cache nginx

# Install openssl for wget and certificate generation
RUN apk add --update openssl

# Add Confd to configure parameters on start
ENV CONFD_VERSION="0.16.0"
RUN wget -q -O /usr/local/bin/confd "https://github.com/kelseyhightower/confd/releases/download/v${CONFD_VERSION}/confd-${CONFD_VERSION}-linux-amd64" \
  && chmod +x /usr/local/bin/confd

# Add Waitforit to wait on db starting
ENV WAITFORIT_VERSION="v2.4.1"
RUN wget -q -O /usr/local/bin/waitforit https://github.com/maxcnunes/waitforit/releases/download/$WAITFORIT_VERSION/waitforit-linux_amd64 \
  && chmod +x /usr/local/bin/waitforit

# Add Composer
RUN cd /tmp && curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer
RUN composer self-update

WORKDIR /var/www

# Install composer dependencies
COPY composer.json .
COPY composer.lock .
RUN composer install --prefer-dist --no-interaction --no-scripts

# Generate certificate
RUN mkdir -p /etc/nginx/certs
RUN openssl req -newkey rsa:4096 -x509 -nodes -keyout /etc/nginx/certs/app.key -new -out /etc/nginx/certs/app.crt -subj "/C=GB/ST=GB/L=London/O=OPG/OU=Digital/CN=default" -sha256 -days "3650"

EXPOSE 80
EXPOSE 443

WORKDIR /var/www
# See this page for directories required
# https://symfony.com/doc/3.4/quick_tour/the_architecture.html
COPY --from=gulp /app/web/assets web/assets
COPY --from=gulp /app/web/images web/images
COPY web/app_dev.php web/app_dev.php
COPY web/config.php web/config.php
COPY app app
COPY bin bin
COPY docker/confd /etc/confd
ENV TIMEOUT=20
CMD confd -onetime -backend env \
  && mkdir -p var/cache \
  && mkdir -p var/logs \
  && chown -R www-data var \
  && php-fpm -D \
  && nginx
