FROM composer

RUN apk --update --no-cache add sqlite sqlite-dev
RUN docker-php-ext-install pdo_sqlite