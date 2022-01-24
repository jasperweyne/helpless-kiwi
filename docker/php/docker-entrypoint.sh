#!/bin/sh
set -e

if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'bin/console' ]; then
	symfony server:start --no-tls -d
    yarn dev

	until bin/console doctrine:query:sql "select 1" >/dev/null 2>&1; do
	    (>&2 echo "Waiting for database to be ready...")
		sleep 1
	done
fi

exec "$@"
