FROM php:8.2-apache

# Cài đặt extension MySQLi và PDO cho PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy toàn bộ mã nguồn netfix2 vào thư mục máy chủ Apache trong container
COPY ./netfix2 /var/www/html/

# Tạo symlink /netfix2 để tương thích đồng thời cả http://localhost/ và http://localhost/netfix2/ (dùng cho CodeceptJS & Postman)
RUN ln -s /var/www/html /var/www/html/netfix2 \
    && chown -R www-data:www-data /var/www/html

# Mở cổng 80
EXPOSE 80