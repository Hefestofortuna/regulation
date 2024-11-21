FROM alpine:3.15.0
RUN apk add --no-cache php8  \
    php8-pgsql \
    php8-pdo_pgsql \
    php8-cli \
    php8-common \
    php8-fpm \
    php8-dom \
    php8-xml \
    php8-zip \
    php8-xmlwriter \
    php8-tokenizer \
    php8-opcache \
    php8-pecl-xdebug \
    perl \
    nginx \
    composer \
    dos2unix \
    unzip
RUN sed -i 's:#.*$::g' /etc/nginx/http.d/default.conf &&\
    sed -i '/^$/d' /etc/nginx/http.d/default.conf &&\
    sed -i "s|;clear_env = no|clear_env = no |g" /etc/php8/php-fpm.d/www.conf
RUN echo 'zend_extension=xdebug' >> /etc/php8/php.ini &&\
    echo 'xdebug.mode=debug' >> /etc/php8/php.ini &&\
    echo 'xdebug.start_with_request=yes' >> /etc/php8/php.ini &&\
    echo 'xdebug.discover_client_host=true' >> /etc/php8/php.ini &&\
    echo 'xdebug.client_host=host.docker.internal' >> /etc/php8/php.ini &&\
    echo 'xdebug.log_level=0' >> /etc/php8/php.ini &&\
    echo 'date.timezone=Europe/Moscow' >> /etc/php8/php.ini
COPY entrypoint.sh /
RUN dos2unix entrypoint.sh
RUN chmod +x entrypoint.sh
WORKDIR /app/
ENTRYPOINT [ "/entrypoint.sh"]
