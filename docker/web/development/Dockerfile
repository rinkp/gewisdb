ARG BASE="alpine:latest"
ARG PHPVERSION="php83"
ARG BUILDTYPE="development"

# Compile the language files (shared between dev and prod)
# uses the php image to reduce number of images that need to be pulled
FROM ${BASE} AS gewisdb_translations
WORKDIR /translations

RUN --mount=type=cache,target=/var/cache/apk,sharing=locked \
    apk add --virtual .runtime-deps \
        gettext

COPY ./module/Application/language/*.po ./

RUN msgfmt en.po -o en -c --strict -v \
    && msgfmt nl.po -o nl -c --strict -v

# Install dependencies
FROM ${BASE} AS gewisdb_web_development_base
ARG PHPVERSION

WORKDIR /code

RUN --mount=type=cache,target=/var/cache/apk,sharing=locked \
    --mount=type=cache,target=/tmp/pear,sharing=locked \
    apk add --virtual .build-deps \
        tzdata \
    && apk add --virtual .runtime-programs \
        git \
        nano \
        ${PHPVERSION} \
        ${PHPVERSION}-calendar \
        ${PHPVERSION}-curl \
        ${PHPVERSION}-dom \
        ${PHPVERSION}-fpm \
        ${PHPVERSION}-iconv \
        ${PHPVERSION}-intl \
        ${PHPVERSION}-ldap \
        ${PHPVERSION}-mbstring \
        ${PHPVERSION}-opcache \
        ${PHPVERSION}-openssl \
        ${PHPVERSION}-pdo_pgsql \
        ${PHPVERSION}-pecl-xdebug \
        ${PHPVERSION}-phar \
        ${PHPVERSION}-session \
        ${PHPVERSION}-simplexml \
        ${PHPVERSION}-tokenizer \
        ${PHPVERSION}-xml \
        ${PHPVERSION}-xmlwriter \
        ${PHPVERSION}-zip \
    && runtimeDeps="$( \
            scanelf --needed --nobanner --format '%n#p' --recursive /usr/local/lib/php/extensions \
            | tr ',' '\n' \
            | sort -u \
            | awk 'system("[ -e /usr/local/lib/" $1 " ]") == 0 { next } { print "so:" $1 }' \
        )" \
    && apk add --virtual .gewisdb-runtime-deps $runtimeDeps \
    && cp /usr/share/zoneinfo/Europe/Amsterdam /etc/localtime \
    && echo 'Europe/Amsterdam' > /etc/timezone \
    && apk del .build-deps \
    && ln -s /usr/sbin/php-fpm83 /usr/sbin/php-fpm

RUN addgroup -S php-fpm && \
    adduser -S -D -H php-fpm -G php-fpm

# Copy composer over (needed for replenish)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN --mount=type=cache,target=/root/.composer,sharing=locked \
    --mount=from=composer:latest,source=/usr/bin/composer,target=/usr/bin/composer \
    --mount=source=./composer.json,target=/code/composer.json \
    --mount=source=./composer.lock,target=/code/composer.lock \
    composer install

# Create the final image.
FROM gewisdb_web_development_base AS gewisdb_web_development
ARG BUILDTYPE

COPY --chown=php-fpm:php-fpm ./docker/web/${BUILDTYPE}/php.ini /usr/local/etc/php/conf.d/default.ini
COPY --chown=php-fpm:php-fpm ./docker/web/${BUILDTYPE}/php-fpm.conf /etc/${PHPVERSION}/php-fpm.d/zz-gewisdb.conf
COPY --chown=php-fpm:php-fpm ./config/autoload/local.${BUILDTYPE}.php.dist ./config/autoload/local.php
COPY --chown=php-fpm:php-fpm ./config/autoload/doctrine.local.${BUILDTYPE}.php.dist ./config/autoload/doctrine.local.php
COPY --chown=php-fpm:php-fpm ./config/autoload/laminas-developer-tools.local.php.dist ./config/autoload/laminas-developer-tools.local.php

COPY --chown=php-fpm:php-fpm ./docker/web/${BUILDTYPE}/crontab /etc/cron.d/crontab
RUN chmod 0644 /etc/cron.d/crontab && crontab /etc/cron.d/crontab

COPY --chown=php-fpm:php-fpm ./docker/web/${BUILDTYPE}/docker-entrypoint.sh ./docker-entrypoint.sh
RUN chmod 0775 ./docker-entrypoint.sh

COPY --link --chown=php-fpm:php-fpm . /code
COPY --chown=php-fpm:php-fpm --from=gewisdb_translations /translations/*.mo /code/module/Application/language/

RUN chmod 0775 ./orm ./web

ENV PHP_IDE_CONFIG="serverName=database.gewis.nl"

RUN --mount=from=composer:latest,source=/usr/bin/composer,target=/usr/bin/composer \
    composer dump-autoload

ARG GIT_COMMIT
ENV GIT_COMMIT=${GIT_COMMIT}

VOLUME ["/code/data", "/code/public"]

ENTRYPOINT ["/bin/sh", "/code/docker-entrypoint.sh"]
