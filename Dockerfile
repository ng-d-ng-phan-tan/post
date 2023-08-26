# Sử dụng image có sẵn cho Laravel để build dự án
FROM composer:latest as build

# Sao chép mã nguồn vào thư mục làm việc
COPY . .

# Cài đặt các gói phụ thuộc và build ứng dụng Laravel
RUN composer install --ignore-platform-reqs

# Tạo khóa ứng dụng
RUN php artisan key:generate

# Sử dụng image của PHP để chạy ứng dụng
FROM php:8.2-fpm

RUN apt-get update && apt-get install -y libssl-dev

RUN pecl channel-update pecl.php.net && pecl install mongodb-1.15.0 && docker-php-ext-enable mongodb

# Cài đặt MongoDB Extension
# RUN pecl install mongodb && docker-php-ext-enable mongodb
# RUN docker-php-ext-install mongodb

# Sao chép tệp DLL vào thư mục ext
# COPY php_mongodb.dll /usr/local/lib/php/extensions/no-debug-non-zts-8.2/

# Sao chép tệp php.ini tùy chỉnh vào hình ảnh
# COPY php.ini /usr/local/etc/php/php.ini

# Đặt thư mục làm việc
WORKDIR /app

# Sao chép mã nguồn đã build từ image trước
COPY --from=build /app /app

# Expose port của service
EXPOSE 8003

# Chạy lệnh để khởi động ứng dụng
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8003"]

