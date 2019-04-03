#!/bin/bash

php /app/bin/console update:harbors && php /app/bin/console update:weather
cron -f &
docker-php-entrypoint php-fpm
