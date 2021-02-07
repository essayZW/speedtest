FROM php:7.4-apache

# Install extensions
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
    && docker-php-ext-install -j$(nproc) iconv \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install mysqli

# Prepare files and folders

RUN mkdir -p /speedtest/

# Copy sources
COPY web/ /speedtest
COPY docker/servers.json /servers.json

COPY docker/entrypoint.sh /

# Prepare environment variabiles defaults

ENV TITLE=LibreSpeed
ENV MODE=standalone
ENV PASSWORD=password
ENV TELEMETRY=false
ENV ENABLE_ID_OBFUSCATION=false
ENV REDACT_IP_ADDRESSES=false
ENV WEBPORT=80
ENV MYSQL_USER=root
ENV MYSQL_PASSWORD=speedroot
ENV MYSQL_HOST=127.0.0.1
ENV MYSQL_PORT=3306
ENV MYSQL_DATABASE=speedtest

# Final touches

EXPOSE 80
CMD ["bash", "/entrypoint.sh"]
