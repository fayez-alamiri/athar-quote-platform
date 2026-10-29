#!/usr/bin/env bash
set -e

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

export PHP_CLI_SERVER_WORKERS="${PHP_CLI_SERVER_WORKERS:-4}"

echo "Clearing stale caches before migration..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

php artisan storage:link --force || true

echo "Running database migrations..."
php artisan migrate --force

# Only run the seeder if the quotes table has fewer than 4 records
QUOTE_COUNT=$(php artisan tinker --execute="echo \App\Models\Quote::count();" 2>/dev/null | tr -d '[:space:]' || echo "0")
if [ "${QUOTE_COUNT:-0}" -lt 4 ] 2>/dev/null; then
    echo "Seeding initial literary quotes..."
    php artisan db:seed --force
else
    echo "Quotes already seeded (${QUOTE_COUNT} records found). Skipping seeder."
fi

echo "Caching configuration, routes, and Blade views for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting Laravel multi-worker HTTP server on port ${PORT:-8080}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8080}