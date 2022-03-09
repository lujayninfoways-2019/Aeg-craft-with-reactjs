## Frontend gulp builder
FROM node:8.17.0-stretch AS builder

COPY frontend /frontend

WORKDIR /frontend

RUN npm install -g gulp -y
RUN npm install gulp -y
RUN npm install -g bower
RUN npm rebuild node-sass -f
RUN npm install
RUN gulp --gulpfile /frontend/gulpfile.babel.js build

## Craft 2 image
FROM php:7.4-apache
ARG APCU_VERSION=5.1.17

RUN apt update && \
    apt install -y --no-install-recommends \
    cron \
    curl \
    wget \
    vim \
    zip \
    rsync \
    ghostscript \
    imagemagick \
# Configure PHP
    libxml2-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libmcrypt-dev \
    libpng-dev \
    libpq-dev \
    libzip-dev \
    libwebp-dev \
    zlib1g-dev \
    libcurl3-openssl-dev \
    libssl-dev \
    libc-client-dev \
    libmcrypt-dev \
    libmagickwand-dev \
# Install required 3rd party tools
    graphicsmagick && \
# Configure extensions
    docker-php-ext-install exif && \
    docker-php-ext-enable exif && \
    docker-php-ext-install -j$(nproc) mysqli soap zip opcache intl pgsql pdo pdo_mysql && \
	docker-php-ext-configure gd --with-webp=/usr/include/ --with-freetype=/usr/include/ --with-jpeg=/usr/include/ && \
	docker-php-ext-install -j$(nproc) gd && \
    docker-php-ext-install ctype json dom && \
    echo 'always_populate_raw_post_data = -1\nmax_execution_time = 108000\nmax_input_vars = 1500\nupload_max_filesize = 128M\npost_max_size = 128M\nmemory_limit = 1024M' > /usr/local/etc/php/conf.d/typo3.ini && \
    docker-php-ext-configure opcache --enable-opcache

#INSTALL APCU
RUN pecl install apcu-${APCU_VERSION} && docker-php-ext-enable apcu
RUN echo "extension=apcu.so" >> /usr/local/etc/php/php.ini
RUN echo "apc.enable_cli=1" >> /usr/local/etc/php/php.ini
RUN echo "apc.enable=1" >> /usr/local/etc/php/php.ini
#APCU

RUN pecl install imagick
RUN docker-php-ext-enable imagick


RUN mkdir -p /etc/certbot-renewal-hook \
    && apt-get update \
    && apt-get install certbot python3-certbot-apache -y \
    && a2enmod rewrite ssl headers

COPY Docker/server.conf /etc/apache2/conf-available/
COPY Docker/server.conf /etc/apache2/conf-enabled/
COPY Docker/init.sh /usr/local/bin/
COPY Docker/certbot /etc/cron.d/certbot
COPY Docker/certbot.service /lib/systemd/system/certbot.service
COPY Docker/deploy-hook-script.sh /etc/certbot-renewal-hook/deploy-hook-script.sh
COPY Docker/sync_frontend.sh /var/www/sync_frontend.sh  

RUN chmod a+x /usr/local/bin/init.sh \
    && chmod a+x /etc/certbot-renewal-hook/deploy-hook-script.sh \
    && chmod 0644 /etc/cron.d/certbot \
	&& chmod a+x /var/www/sync_frontend.sh

COPY scripts /scripts
COPY site /var/www

COPY --from=builder /frontend /var/www/frontend

RUN chmod -R 774 /var/www/craft/storage
RUN chown -R www-data:www-data /var/www

WORKDIR /var/www/
RUN ./sync_frontend.sh

WORKDIR /var/www/html

CMD ["init.sh"]