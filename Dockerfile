ARG PHP_VERSION
FROM php:${PHP_VERSION}-cli-alpine
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions pcov @composer xdebug
WORKDIR /app
ENTRYPOINT ["composer"]
