#!/bin/bash
set -e

php artisan migrate --no-interaction --force
php artisan db:seed --no-interaction --force 2>/dev/null || true

exec "$@"
