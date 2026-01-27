# Use PHP CLI image
FROM php:8.2-cli


WORKDIR /var/www/html


# Install system dependencies
RUN apt-get update && apt-get install -y --no-install-recommends \
git \
unzip \
curl \
libpng-dev \
libjpeg-dev \
libfreetype6-dev \
libonig-dev \
libxml2-dev \
libssl-dev \
pkg-config \
make \
gcc \
g++ \
zip \
&& docker-php-ext-configure gd --with-freetype --with-jpeg \
&& docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd \
&& rm -rf /var/lib/apt/lists/*


# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer


# Install Swoole via PECL
RUN pecl install openswoole \
&& docker-php-ext-enable openswoole


# Copy Laravel project
COPY . /var/www/html


# Set permissions
RUN chown -R www-data:www-data /var/www/html \
&& chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache


# Expose Swoole port
EXPOSE 1215


# Start Laravel Octane (Swoole)
CMD ["php", "artisan", "octane:start", "--server=swoole", "--host=0.0.0.0", "--port=1215", "--workers=auto"]