FROM php:8.3-fpm-bookworm

# Install system dependencies, Nginx, PHP extensions (including OPcache & PostgreSQL), and Node.js 22
RUN apt-get update && apt-get install -y \
    nginx \
    git \
    curl \
    unzip \
    libpq-dev \
    libsqlite3-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libfreetype6-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql pgsql pdo_sqlite gd zip bcmath opcache \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Configure PHP OPcache and production PHP settings
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.enable_cli=1'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.save_comments=1'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'realpath_cache_size=4096K'; \
    echo 'realpath_cache_ttl=600'; \
    echo 'upload_max_filesize=8M'; \
    echo 'post_max_size=10M'; \
    echo 'memory_limit=256M'; \
} > /usr/local/etc/php/conf.d/99-production-performance.ini

# Configure PHP-FPM worker pool for concurrent requests on Render
RUN { \
    echo '[www]'; \
    echo 'pm = dynamic'; \
    echo 'pm.max_children = 10'; \
    echo 'pm.start_servers = 3'; \
    echo 'pm.min_spare_servers = 2'; \
    echo 'pm.max_spare_servers = 5'; \
    echo 'pm.max_requests = 500'; \
    echo 'clear_env = no'; \
    echo 'catch_workers_output = yes'; \
} > /usr/local/etc/php-fpm.d/zz-render.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

COPY package.json package-lock.json* ./
RUN npm ci || npm install

COPY . .

# Ensure misplaced migration is in database/migrations if still in database/
RUN if [ -f database/2026_10_29_000005_change_image_columns_to_text.php ]; then \
        mv database/2026_10_29_000005_change_image_columns_to_text.php database/migrations/2026_10_29_000005_change_image_columns_to_text.php; \
    fi

# Optimize Composer autoloader, build Vite assets, and prepare storage directories
RUN composer dump-autoload --optimize --classmap-authoritative \
    && npm run build \
    && rm -rf node_modules \
    && mkdir -p storage/framework/sessions \
                storage/framework/views \
                storage/framework/cache/data \
                storage/framework/cache/locks \
                storage/logs \
                storage/app/public/book-covers \
                storage/app/public/avatars \
                bootstrap/cache \
                /run/nginx \
    && sed -i 's/\r$//' docker/start.sh \
    && chown -R www-data:www-data storage bootstrap/cache public \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x docker/start.sh

EXPOSE 8080

CMD ["./docker/start.sh"]