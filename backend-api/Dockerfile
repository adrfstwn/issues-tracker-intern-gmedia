FROM dunglas/frankenphp

# Install supervisord
RUN apt-get update && apt-get install -y supervisor

RUN install-php-extensions \
    pcntl \
    zip \
    bcmath \
    pdo_pgsql \
    pgsql

# Install Composer secara manual
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

COPY . /app

# Install semua dependensi tanpa dev dependencies
RUN composer install --no-dev --optimize-autoloader

# Set Environment Production
ENV APP_ENV=production
ENV APP_DEBUG=false

# Install Octane dan set servernya ke FrankenPHP
RUN echo "yes" | php artisan octane:install --server=frankenphp

# ENTRYPOINT ["php", "artisan", "octane:frankenphp"]

# Copy konfigurasi Supervisor
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
