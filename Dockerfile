FROM php:7.2-fpm

RUN apt-get update && apt-get install -y cron

ADD cronjobs /etc/cron.d/cronjobs

RUN chmod 0644 /etc/cron.d/cronjobs && touch /var/log/cron.log

RUN crontab /etc/cron.d/cronjobs

RUN pecl install redis && docker-php-ext-enable redis

ADD entrypoint.sh /etc/entrypoint.sh

RUN chmod u+x /etc/entrypoint.sh

ENTRYPOINT /etc/entrypoint.sh
