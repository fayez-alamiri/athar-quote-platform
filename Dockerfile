FROM php:8.3-cli-bookworm

# Install system dependencies, PostgreSQL & SQLite dev libraries, GD image libraries, OPcache, and Node.js 22
RUN apt-get update && apt-get install -y \
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

# Configure PHP OPcache for fast production execution
RUN { \
    echo 'opcache.enable=1'; \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=16'; \
    echo 'opcache.max_accelerated_files=10000'; \
    echo 'opcache.validate_timestamps=0'; \
    echo 'opcache.save_comments=1'; \
} > /usr/local/etc/php/conf.d/opcache-prod.ini

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy dependency manifests first for faster Docker layer caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --prefer-dist --optimize-autoloader

COPY package.json package-lock.json* ./
RUN npm install

# Copy application source files
COPY . .

# Complete Composer autoload, build Vite assets, and normalize Windows CRLF line endings
RUN composer dump-autoload --optimize \
    && npm run build \
    && mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data storage/logs storage/app/public/book-covers storage/app/public/avatars bootstrap/cache \
    && sed -i 's/\r$//' docker/start.sh \
    && chmod -R 775 storage bootstrap/cache \
    && chmod +x docker/start.sh

# Enable 4 concurrent worker processes for PHP built-in server
ENV PHP_CLI_SERVER_WORKERS=4

EXPOSE 8080

CMD ["./docker/start.sh"]