FROM php:8.4-cli
RUN apt-get update && apt-get install -y --no-install-recommends libpq-dev && rm -rf /var/lib/apt/lists/*
RUN docker-php-ext-install pdo pdo_pgsql
WORKDIR /app
COPY . .
CMD ["php","-S","0.0.0.0:8080","-t","apps/api/public"]
