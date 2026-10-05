FROM php:8.2-apache

# Install required system dependencies and PHP extensions
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    curl \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        mbstring \
        bcmath \
        gd \
        zip \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Force exactly ONE Apache MPM (prefork — required by mod_php).
# Fixes "AH00534: More than one MPM loaded" on Railway.
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load /etc/apache2/mods-enabled/mpm_*.conf \
    && a2enmod mpm_prefork

# Enable Apache mod_rewrite, mod_headers, and mod_remoteip (for Railway proxy)
RUN a2enmod rewrite headers remoteip \
    && echo 'ServerName localhost' > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername

# Configure recommended PHP settings for production
RUN { \
    echo 'opcache.memory_consumption=128'; \
    echo 'opcache.interned_strings_buffer=8'; \
    echo 'opcache.max_accelerated_files=4000'; \
    echo 'opcache.revalidate_freq=2'; \
    echo 'opcache.fast_shutdown=1'; \
    echo 'opcache.enable=1'; \
    echo 'upload_max_filesize=64M'; \
    echo 'post_max_size=64M'; \
    echo 'memory_limit=256M'; \
    echo 'session.cookie_secure=On'; \
    echo 'session.cookie_httponly=On'; \
} > /usr/local/etc/php/conf.d/weboflix.ini

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY . /var/www/html/

# Copy entrypoint script and make executable
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html \
    && find /var/www/html -type f -name "*.php" -exec chmod 644 {} \;

# Expose standard port (Railway dynamically overrides via $PORT)
EXPOSE 80

# Health check — Railway uses this to verify the app is running
HEALTHCHECK --interval=30s --timeout=10s --start-period=30s --retries=3 \
    CMD curl -f http://localhost:${PORT:-80}/health.php || exit 1

LABEL maintainer="DWO Weboflix" \
      description="Weboflix — DWO Branded Streaming Platform for Tech Courses" \
      version="2.0"

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
CMD ["apache2-foreground"]
