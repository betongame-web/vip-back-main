FROM php:8.2-cli

WORKDIR /app

RUN apt-get update && apt-get install -y     git     unzip     libzip-dev     libicu-dev     libxml2-dev     libpng-dev     libjpeg62-turbo-dev     libfreetype6-dev     libcurl4-openssl-dev     libpq-dev  && docker-php-ext-configure gd --with-freetype --with-jpeg  && docker-php-ext-install     pdo     pdo_mysql     pdo_pgsql     intl     curl     zip     simplexml     gd     bcmath  && apt-get clean  && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

RUN chmod +x start.sh

CMD ["bash", "start.sh"]
