#!/usr/bin/env bash
set -e

PORT="${PORT:-8080}"

# 1. Normalize DATABASE_URL and DB_URL while PRESERVING Neon's -pooler endpoint for HTTP runtime
if [ -n "${DATABASE_URL:-}" ] && [ -z "${DB_URL:-}" ]; then
    export DB_URL="$DATABASE_URL"
fi

# 2. Generate Nginx configuration dynamically bound to Render's $PORT
cat > /etc/nginx/sites-available/default <<EOF
server {
    listen ${PORT} default_server;
    listen [::]:${PORT} default_server;
    server_name _;
    root /var/www/html/public;
    index index.php;

    charset utf-8;
    client_max_body_size 10M;

    gzip on;
    gzip_vary on;
    gzip_proxied any;
    gzip_comp_level 5;
    gzip_types text/plain text/css text/javascript application/javascript application/json image/svg+xml font/woff font/woff2;

    # Serve Vite build assets & images directly from Nginx without touching PHP
    location ~* ^/(build|images)/.*\.(css|js|jpg|jpeg|jfif|png|gif|webp|svg|woff|woff2|ttf|eot|ico)$ {
        expires 1y;
        add_header Cache-Control "public, max-age=31536000, immutable";
        access_log off;
        try_files \$uri =404;
    }

    location = /favicon.ico { access_log off; log_not_found off; expires 30d; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_buffers 16 16k;
        fastcgi_buffer_size 32k;
        fastcgi_read_timeout 60s;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF

# 3. Ensure storage directories exist and are writable by www-data
mkdir -p storage/framework/sessions \
         storage/framework/views \
         storage/framework/cache/data \
         storage/framework/cache/locks \
         storage/logs \
         bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 4. Build production Laravel caches (config, routes, views)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 5. Run non-destructive migrations using direct (unpooled) URL in a subshell ONLY for migration CLI
(
    if [ -n "${DB_URL:-}" ]; then
        export DB_URL="$(echo "$DB_URL" | sed 's/-pooler\././g')"
        export DATABASE_URL="$DB_URL"
    fi
    if [ -n "${DB_HOST:-}" ]; then
        export DB_HOST="$(echo "$DB_HOST" | sed 's/-pooler\././g')"
    fi
    php artisan migrate --force
)

# 6. Seed ONLY if explicitly requested OR if the quotes table is completely empty (protects production data & startup speed)
if [ "${RUN_SEEDER:-false}" = "true" ]; then
    php artisan db:seed --force
else
    QUOTE_COUNT=$(php artisan tinker --execute="echo \App\Models\Quote::count();" 2>/dev/null | tr -d '[:space:]' || echo "1")
    if [ "$QUOTE_COUNT" = "0" ]; then
        php artisan db:seed --force
    fi
fi

# 7. Start PHP-FPM daemon and run Nginx in foreground
php-fpm -D
exec nginx -g "daemon off;"