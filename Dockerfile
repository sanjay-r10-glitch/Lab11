# Lab 11 — PHP 8.4 on Apache, for Render (or any Docker host).
# The official php image already includes pdo_sqlite.
FROM php:8.4-apache

# Render passes the port to listen on in $PORT (default 10000); fall back to 80 locally.
ENV PORT=80
RUN sed -ri 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf \
 && sed -ri 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/' /etc/apache2/sites-available/000-default.conf \
 && echo "ServerName localhost" >> /etc/apache2/apache2.conf \
 && echo "RedirectMatch 404 ^/(data|\.git)(/|$)" >> /etc/apache2/apache2.conf \
 && echo "date.timezone=Asia/Kolkata" > /usr/local/etc/php/conf.d/timezone.ini

COPY . /var/www/html/
RUN rm -f /var/www/html/Dockerfile /var/www/html/render.yaml /var/www/html/start.ps1 \
 && mkdir -p /var/www/html/data \
 && chown -R www-data:www-data /var/www/html
