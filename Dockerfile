FROM php:7.2-fpm

RUN apt-get update && apt-get install -y cron && apt-get install -y libzip-dev

ADD cronjobs /etc/cron.d/cronjobs

RUN chmod 0644 /etc/cron.d/cronjobs && touch /var/log/cron.log

RUN crontab /etc/cron.d/cronjobs

RUN pecl install redis && docker-php-ext-enable redis

RUN pecl install zip && docker-php-ext-enable zip

RUN pecl install mbstring && docker-php-ext-enable mbstring

RUN pecl install curl && docker-php-ext-enable curl

ADD entrypoint.sh /etc/entrypoint.sh

RUN chmod u+x /etc/entrypoint.sh

ENTRYPOINT /etc/entrypoint.sh
