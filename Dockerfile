FROM alpine:3.15.0
COPY ./app/ /app/
COPY entrypoint.sh /
RUN apk add php8  \
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
    perl \
    nginx \
    composer \
    dos2unix \
    unzip \
    curl \
    tzdata
RUN cp /usr/share/zoneinfo/Europe/Moscow /etc/localtime
RUN echo 'opcache.memory_consumption=128' >> /etc/php8/php.ini &&\
    echo 'pcache.interned_strings_buffer=8' >> /etc/php8/php.ini &&\
    echo 'opcache.max_accelerated_files=4000' >> /etc/php8/php.ini &&\
    echo 'opcache.revalidate_freq=60' >> /etc/php8/php.ini &&\
    echo 'opcache.fast_shutdown=1' >> /etc/php8/php.ini &&\
    echo 'opcache.enable_cli=1' >> /etc/php8/php.ini &&\
    echo 'date.timezone=Europe/Moscow' >> /etc/php8/php.ini
RUN sed -i 's:#.*$::g' /etc/nginx/http.d/default.conf &&\
    sed -i '/^$/d' /etc/nginx/http.d/default.conf &&\
    sed -i "s|;clear_env = no|clear_env = no |g" /etc/php8/php-fpm.d/www.conf
COPY ./container/nginx/default.conf /etc/nginx/http.d/default.conf
RUN dos2unix /app/*
RUN dos2unix entrypoint.sh
RUN chmod +x entrypoint.sh
RUN chmod -R 777 /app/storage/
ENTRYPOINT [ "/entrypoint.sh"]
