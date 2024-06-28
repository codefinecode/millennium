FROM php:8.1-fpm

COPY composer.lock composer.json /var/www/
COPY .env.example /var/www/.env

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    locales \
    zip \
    unzip \
    libonig-dev \
    libzip-dev

#    build-essential \
#    vim \
#    git \
#    curl \
#    libgd-dev
#    libpng-dev \
#    libjpeg62-turbo-dev \
#    libfreetype6-dev \
#    jpegoptim optipng pngquant gifsicle \

RUN apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo_mysql mbstring zip

#RUN #docker-php-ext-install exif pcntl
#RUN docker-php-ext-configure gd --with-external-gd
#RUN docker-php-ext-install gd

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN groupadd -g 1000 www
RUN useradd -u 1000 -ms /bin/bash -g www www

COPY . /var/www

COPY --chown=www:www . /var/www

USER www

EXPOSE 9000
CMD ["php-fpm"]