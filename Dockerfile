FROM php:7.4.2-fpm-alpine

RUN apk update && \
    apk add imap-dev && \
    apk add libressl-dev

RUN PHP_OPENSSL=yes docker-php-ext-configure imap --with-imap --with-imap-ssl && \
    docker-php-ext-install imap

WORKDIR /var/www

#COPY . .

#CMD [ "php", "sync.php" ]