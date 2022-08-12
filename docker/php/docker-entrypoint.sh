#!/bin/sh
set -e

if [ "${1#-}" != "$1" ]; then
        set -- php "$@"
fi

if [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
        composer install && symfony server:start --no-tls -d
        yarn install && yarn watch

        until bin/console doctrine:query:sql "select 1" >/dev/null 2>&1; do
            (>&2 echo "Still waiting for db to be ready... Or maybe the db is not reachable.")
                sleep 1
        done
fi

exec "$@"