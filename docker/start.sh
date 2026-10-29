#!/usr/bin/env bash
set -euo pipefail

# Accept either common URL variable name, without changing the runtime endpoint.
if [[ -z "${DB_URL:-}" && -n "${DATABASE_URL:-}" ]]; then
    export DB_URL="$DATABASE_URL"
fi
if [[ -z "${DATABASE_URL:-}" && -n "${DB_URL:-}" ]]; then
    export DATABASE_URL="$DB_URL"
fi

APP_DATABASE_URL="${DB_URL:-${DATABASE_URL:-}}"

# Neon pooled endpoints are ideal for ordinary application requests. Some schema
# migration operations are better run through Neon’s direct endpoint. Use an
# explicitly provided MIGRATION_DB_URL when available; otherwise derive the
# direct hostname only for the migration command. The application's DB_URL is
# not modified, so config:cache will retain the pooled runtime URL.
if [[ -n "${MIGRATION_DB_URL:-}" ]]; then
    echo "Running database migrations using the configured migration endpoint..."
    DB_URL="$MIGRATION_DB_URL" DATABASE_URL="$MIGRATION_DB_URL" php artisan migrate --force
elif [[ "$APP_DATABASE_URL" == *"-pooler."* ]]; then
    MIGRATION_DATABASE_URL="$(printf '%s' "$APP_DATABASE_URL" | sed 's/-pooler\././g')"
    echo "Running database migrations using the direct Neon endpoint..."
    DB_URL="$MIGRATION_DATABASE_URL" DATABASE_URL="$MIGRATION_DATABASE_URL" php artisan migrate --force
else
    echo "Running database migrations..."
    php artisan migrate --force
fi

# Create the public storage symlink if needed. Do not seed demo data on startup:
# DatabaseSeeder includes known sample-account credentials and would reset those
# accounts' passwords on every container restart.
php artisan storage:link --force || true

echo "Building production configuration, route, and view caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Starting Laravel HTTP server..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
