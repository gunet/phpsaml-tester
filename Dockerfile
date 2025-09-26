# syntax=docker/dockerfile:1
FROM php:8.4-cli-bookworm AS builder
RUN apt-get update && \
    apt-get install -y libmcrypt-dev libpng-dev libjpeg-dev libfreetype6-dev \
        libzip-dev unzip git pkg-config libssl-dev libcurl4-openssl-dev libxml2-dev

RUN pecl install mcrypt && \
    docker-php-ext-enable mcrypt

COPY --from=composer /usr/bin/composer /usr/bin/composer
ARG VERSION=4.3.0
ARG REPO="https://github.com/SAML-Toolkits/php-saml.git"
ADD ${REPO}#${VERSION} /var/www/html/
WORKDIR /var/www/html/
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install

RUN echo "Options +Indexes" >> /var/www/html/.htaccess

FROM php:8.4-apache-bookworm
RUN apt-get update && \
    apt-get install -y \
        libmcrypt4 \
        libpng16-16 \
        libjpeg62-turbo \
        libfreetype6 \
        libzip4 \
        libssl3 \
        libcurl4 \
        libxml2 \
        openssl \
        ca-certificates \
        curl \
    && rm -rf /var/lib/apt/lists/*

COPY php/php.ini $PHP_INI_DIR/php.ini
COPY php/log.conf $PHP_INI_DIR/conf.d/zz-log.conf

COPY --from=builder /var/www/html/ /var/www/html/
COPY --chmod=0755 docker-php-entrypoint /usr/local/bin/

COPY settings/ /var/www/html/
COPY --chown=www-data certs/ /var/www/html/certs/
COPY code/ /var/www/html/
WORKDIR /var/www/html/

HEALTHCHECK --interval=5s --timeout=2s --start-period=5s --retries=5 CMD /usr/bin/pgrep -c -u www-data apache2 || exit 1

ENV SP_ENTITYID=http://localhost
ENV DEBUG_MODE=true
ENV IDP_METADATA=https://sso/idp/metadata
ENV SP_SIGN_AUTHNREQUEST=false
ENV SP_WANT_MESSAGE_SIGNED=false
ENV SP_WANT_ASSERTIONS_ENCRYPTED=false
ENV SP_WANT_ASSERTIONS_SIGNED=false
ENV SP_SIGN_LOGOUTREQUEST=false
ENV SP_SIGN_LOGOUTRESPONSE=false
ENV TZ=Europe/Athens

EXPOSE 80/tcp