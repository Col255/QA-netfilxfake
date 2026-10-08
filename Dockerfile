FROM php:8.2-apache

# Cài đặt extension MySQLi cho PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy toàn bộ mã nguồn netfix2 vào thư mục máy chủ Apache trong container
COPY ./netfix2 /var/www/html/

# Mở cổng 80
EXPOSE 80