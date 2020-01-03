FROM nginx:1.17.6-alpine

LABEL maintainer="Pisarev Andrey <pisarev_aa@mail.ru>"

COPY nginx.conf /etc/nginx/
COPY default.conf /etc/nginx/conf.d

RUN apk update \
    && apk upgrade \
    && apk add --no-cache nano

# RUN set -x ; \
#     addgroup -g 82 -S www-data ; \
#     adduser -u 82 -D -S -G www-data www-data && exit 0 ; exit 1

# RUN echo "upstream php-upstream { server php-fpm:9000; }" > /etc/nginx/conf.d/upstream.conf

EXPOSE 80 443