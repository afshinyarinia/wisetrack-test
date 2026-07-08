#!/bin/sh
set -e

wait_for_service() {
    host="$1"
    port="$2"
    name="$3"

    echo "Waiting for ${name} at ${host}:${port}..."

    until php -r "exit(@fsockopen('${host}', (int) ${port}) ? 0 : 1);"; do
        sleep 2
    done
}

if [ "${DB_CONNECTION}" = "mysql" ]; then
    wait_for_service "${DB_HOST:-mysql}" "${DB_PORT:-3306}" "MySQL"
fi

if [ -n "${REDIS_HOST}" ]; then
    wait_for_service "${REDIS_HOST}" "${REDIS_PORT:-6379}" "Redis"
fi

mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/testing \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

php artisan storage:link --force >/dev/null 2>&1 || true
php artisan config:clear >/dev/null 2>&1 || true

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force
fi

exec "$@"
