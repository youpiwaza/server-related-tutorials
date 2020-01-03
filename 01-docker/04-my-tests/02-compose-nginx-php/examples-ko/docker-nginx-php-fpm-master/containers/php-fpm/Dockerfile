FROM alpine:3.10

LABEL maintainer="Pisarev Andrey <pisarev_aa@mail.ru>"

RUN apk --update add \
  nano \
  composer \
  php7 \
  php7-bcmath \
  php7-dom \
  php7-ctype \
  php7-curl \
  php7-fpm \
  php7-gd \
  php7-iconv \
  php7-intl \
  php7-json \
  php7-mbstring \
  php7-mcrypt \
  php7-opcache \
  php7-openssl \
  php7-pdo \
  php7-pdo_pgsql \
  php7-phar \
  php7-posix \
  php7-session \
  php7-soap \
  php7-xml \
  php7-zip \
  && rm -rf /var/cache/apk/*

COPY php.ini /etc/php7/php.ini
COPY opcache.ini /etc/php7/conf.d/00_opcache.ini
COPY www.conf /etc/php7/php-fpm.d/www.conf

EXPOSE 9000

CMD ["php-fpm7", "-F"]