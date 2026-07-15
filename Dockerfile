FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY . /var/www/html/

RUN git clone --depth 1 https://github.com/PHPMailer/PHPMailer.git vendor/phpmailer/phpmailer \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80
