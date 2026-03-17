FROM php:8.2-apache

# Installer extensions PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Activer rewrite
RUN a2enmod rewrite

# Copier config apache
COPY apache/apache.conf /etc/apache2/sites-available/000-default.conf

# Copier code
COPY src/ /var/www/html/

WORKDIR /var/www/html

EXPOSE 80