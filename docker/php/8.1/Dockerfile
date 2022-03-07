#FROM ghcr.io/roadrunner-server/roadrunner:2.X.X AS roadrunner
FROM php:8.1.3-cli

WORKDIR /home/app

RUN apt-get update && apt-get install -y libfcgi-bin vim unzip p7zip autoconf zlib1g-dev libonig-dev procps

RUN docker-php-ext-install sockets mbstring bcmath

RUN pecl channel-update pecl.php.net
RUN pecl install grpc
RUN docker-php-ext-enable grpc

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

#COPY --from=roadrunner /usr/bin/rr /usr/local/bin/rr
COPY --from=composer /usr/bin/composer /usr/bin/composer

#COPY --chown=www-data:www-data . /home/app
RUN chown -R www-data:www-data /home/app

#RUN composer install --no-interaction --no-dev --optimize-autoloader
#RUN ./vendor/bin/rr get-binary
RUN #./rr serve

#CMD ["php-fpm"]
