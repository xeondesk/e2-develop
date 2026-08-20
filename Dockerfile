FROM php:8.4-apache

RUN docker-php-ext-install mysqli \
    && a2enmod rewrite

# Config
COPY docker/php.ini /usr/local/etc/php/conf.d/e2.ini

WORKDIR /var/www/html
COPY . .

RUN chown -R www-data:www-data /var/www/html \
    && touch /var/www/html/lastpost.id \
    && chown www-data:www-data /var/www/html/lastpost.id

EXPOSE 80
CMD ["apache2-foreground"]