FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql mysqli \
    && a2enmod rewrite headers \
    && sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf \
    && sed -ri 's/Listen 80/Listen 8080/' /etc/apache2/ports.conf \
    && sed -ri 's/<VirtualHost \*:80>/<VirtualHost *:8080>/' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

COPY index.php .htaccess baotri.html import_db.php ./
COPY frontend ./frontend
COPY backend/public ./backend/public
COPY backend/src ./backend/src
COPY backend/storage ./backend/storage
COPY backend/uploads ./backend/uploads

RUN chown -R www-data:www-data /var/www/html/backend/storage /var/www/html/backend/uploads \
    && chmod -R 775 /var/www/html/backend/storage /var/www/html/backend/uploads \
    && chown -R www-data:www-data /var/run/apache2 /var/lock/apache2 /var/log/apache2

USER www-data

EXPOSE 8080

CMD ["apache2-foreground"]
