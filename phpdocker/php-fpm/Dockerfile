FROM phpdockerio/php72-fpm:latest

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libmemcached-dev \
    libcurl3-openssl-dev \
    php7.2-gd \
    php-xdebug \
    curl \
    python

COPY ./xdebug.ini /etc/php/7.2/fpm/conf.d

WORKDIR "/var/wwww/html"

# Fix debconf warnings upon build
ARG DEBIAN_FRONTEND=noninteractive

# Install selected extensions and other stuff
RUN apt-get update && apt -y upgrade \
    && apt-get -y --no-install-recommends install php7.2-mysql php-redis php7.2-bcmath php7.2-intl \
    && libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    #&& telnet iputils-ping mysql-client vim htop php-curl \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) iconv \
    && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* \