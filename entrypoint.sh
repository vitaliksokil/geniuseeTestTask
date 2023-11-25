#!/usr/bin/env bash

set -uex

RUN() {
    su www-data -s /bin/bash -c "$1"
}

pushd /var/www/

    if [ ! -f ${PWD}/.env ]; then
        env | grep -E "^X_" | sed 's@"@@g;s@^X_@@g' | awk -F"=" '{print $1 "=" $2$3$4 }' | sort > ${PWD}/.env
        env -i bash
    fi
    chmod go+rw ${PWD}/.env
    chown -R www-data:www-data /var/www/

    RUN "composer install --no-cache"
    RUN "composer dump-autoload"

    RUN "php artisan key:generate --force"

    RUN "php artisan migrate"

    if [ ! -L /var/www/storage ]; then
        RUN "php artisan storage:link"
    fi
    RUN "rm -f storage/framework/sessions/*"
popd

apache2-foreground

