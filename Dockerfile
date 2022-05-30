FROM php:7.4-fpm

ARG user
ARG uuid

# install system dependecies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    default-mysql-client \
    zip \
    unzip

# clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# install PHP extentions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

RUN pecl install xdebug grpc \
    && docker-php-ext-enable xdebug \
    && docker-php-ext-enable grpc

# get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# create system user to run Composer and Artisan Commands
RUN useradd -G www-data,root -u $uuid -d /home/$user $user
RUN mkdir -p /home/$user/.composer && \
    chown -R $user:$user /home/$user

# set working directory
WORKDIR /var/www

USER $user