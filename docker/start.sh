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

php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan storage:link --force || true

# Run additive migrations safely without wiping existing data
php artisan migrate --force

# Seed initial literary quotes safely using updateOrCreate
php artisan db:seed --force

exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}