# FROM php:7.4-fpm
FROM php:8.1-fpm

# Make sure apt is up to date
RUN apt-get update --fix-missing
RUN apt-get install -y curl
RUN apt-get install -y build-essential libssl-dev zlib1g-dev libpng-dev libjpeg-dev libfreetype6-dev

# Only needed for international (intl) extension
RUN apt-get update && apt-get install -y libicu-dev \
    && docker-php-ext-configure intl \
    && docker-php-ext-install intl

# Add PDO, MySQL and gd
RUN docker-php-ext-install pdo pdo_mysql mysqli gd \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd

# Clean up temporary files after installation is complete
RUN rm -rf /tmp/*

# php.ini
COPY config/php.ini /usr/local/etc/php/

# ioncube loader
COPY config/ioncube_loader_lin_8.1.so /usr/local/lib/php/extensions/no-debug-non-zts-20210902/
