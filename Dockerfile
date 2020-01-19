FROM php:7.4-apache
LABEL maintainer="Vincent Faliès <vincent@vfac.fr>"

RUN apt-get update && apt-get install -y \
    wget \
    git \
    zip
RUN apt-get clean -y && apt-get autoclean -y && apt-get autoremove -y \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod rewrite

# Composer installation
ADD scripts/composer.sh /tmp/composer.sh
RUN chmod +x /tmp/composer.sh \
    && sync \
    && /tmp/composer.sh \
    && mv composer.phar /usr/local/bin/composer

# Home installation
COPY ./home/envdev /var/www/html/envdev

WORKDIR /var/www/html/envdev
RUN composer update --lock

COPY ./conf/envdev_home.conf /etc/apache2/sites-enabled/envdev_home.conf
COPY ./conf/ssl /usr/local/apache2/conf/custom

RUN composer config --global repo.packagist composer https://packagist.org
