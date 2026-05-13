FROM php:8.2-apache-bookworm

RUN docker-php-ext-install mysqli

COPY index.php /var/www/html/index.php
COPY style.css /var/www/html/style.css
COPY app.js    /var/www/html/app.js

EXPOSE 80