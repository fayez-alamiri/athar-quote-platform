#!/usr/bin/env bash
set -e

# Map DATABASE_URL <-> DB_URL and convert Neon pooled (-pooler.) hostnames
# to direct Neon endpoints so PostgreSQL transactional DDL migrations succeed.
if [ -n "${DATABASE_URL:-}" ] && [ -z "${DB_URL:-}" ]; then
    export DB_URL="$DATABASE_URL"
fi

if [ -n "${DB_URL:-}" ]; then
    export DB_URL="$(echo "$DB_URL" | sed 's/-pooler\././g')"
    export DATABASE_URL="$DB_URL"
fi

if [ -n "${DB_HOST:-}" ]; then
    export DB_HOST="$(echo "$DB_HOST" | sed 's/-pooler\././g')"
fi

echo "Running production optimizations..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan storage:link --force || true

echo "Running database migrations..."
php artisan migrate --force

echo "Seeding database..."
php artisan db:seed --force

echo "Caching configuration and routes for maximum performance..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting Laravel HTTP server..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}