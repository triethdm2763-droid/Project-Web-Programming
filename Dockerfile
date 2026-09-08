FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install pdo_mysql mysqli mbstring \
    && a2enmod rewrite headers \
    && sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY .htaccess index.php baotri.html ./
COPY frontend/ ./frontend/
COPY backend/public/ ./backend/public/
COPY backend/realtime/ ./backend/realtime/
COPY backend/src/ ./backend/src/
COPY backend/uploads/ ./backend/uploads/
COPY docker/app.conf /etc/apache2/conf-enabled/marketplace.conf

# The custom autoloader lowercases namespace directories, so normalize the
# source tree for Linux's case-sensitive filesystem.
RUN mv backend/src/Config backend/src/config \
    && mv backend/src/Controllers backend/src/controllers \
    && mv backend/src/Core backend/src/core \
    && mv backend/src/Repositories backend/src/repositories \
    && mkdir -p backend/storage/sessions backend/storage/logs backend/storage/cache \
       backend/uploads/avatars backend/uploads/products \
    && chown -R www-data:www-data backend/storage backend/uploads \
    && chmod -R 775 backend/storage backend/uploads

EXPOSE 80

HEALTHCHECK --interval=10s --timeout=3s --start-period=10s --retries=5 \
    CMD php -r '$$s=@fsockopen("127.0.0.1",(int)(getenv("PORT")?:80)); exit($$s?0:1);'

CMD ["sh", "-c", "sed -i \"s/Listen 80/Listen ${PORT:-80}/\" /etc/apache2/ports.conf && sed -i \"s/<VirtualHost \\*:80>/<VirtualHost \\*:${PORT:-80}>/\" /etc/apache2/sites-available/000-default.conf && apache2-foreground"]
