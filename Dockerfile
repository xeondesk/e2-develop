FROM php:8.4-cli
RUN docker-php-ext-install pdo pdo_pgsql
WORKDIR /app
COPY . .
CMD ["php","-S","0.0.0.0:8080","-t","apps/api/public"]
