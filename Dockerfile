FROM php:cli-alpine
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions xdebug @composer && \
    apk add --update --no-cache make
WORKDIR /app
ENTRYPOINT ["make"]
