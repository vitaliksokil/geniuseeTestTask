FROM php:8.1-apache

# Arguments defined in docker-compose.yml

# Install system dependencies
RUN apt-get update \
    && apt install -y \
        g++ \
        libicu-dev \
        libpq-dev \
        libzip-dev \
        libpng-dev \
        libwebp-dev \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        zip \
        zlib1g-dev \
        imagemagick \
        libmagickwand-dev \
    && a2enmod ssl \
    && a2enmod rewrite \
    && docker-php-ext-install \
        zip \
        intl \
        opcache \
        pdo \
        pdo_mysql \
        bcmath \
        exif \
        gd \
    && pecl install imagick \
    && docker-php-ext-enable exif imagick \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd \
    && apt-get clean && rm -rf /var/lib/apt/lists/*


# Get latest Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Set working directory
WORKDIR /var/www
VOLUME /data

COPY ./src/ /var/www/
COPY ./entrypoint.sh /

RUN chmod +x /entrypoint.sh

ENTRYPOINT [ "/entrypoint.sh" ]

COPY apache_host.conf /etc/apache2/sites-available/000-default.conf
