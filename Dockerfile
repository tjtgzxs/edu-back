# 使用 PHP 7.4 作为基础镜像
FROM php:7.4-fpm

# 安装常用 PHP 扩展
RUN docker-php-ext-install pdo pdo_mysql
RUN apt-get update && apt-get install -y openssl git
RUN docker-php-ext-install bcmath
RUN docker-php-ext-enable bcmath

# 设置工作目录
WORKDIR /var/www

# 将 Laravel 项目文件复制到容器中
COPY . /var/www

# 安装 Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 安装 Laravel 项目依赖
RUN composer install
RUN composer require laravel/passport:~4.0 -W

# 设置容器对外开放的端口
EXPOSE 9000

# 启动 PHP-FPM 服务
CMD ["php-fpm"]
