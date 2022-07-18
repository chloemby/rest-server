#!/bin/sh

composer install
bin/console lexik:jwt:generate-keypair --skip-if-exists
bin/console --no-interaction doctrine:migrations:migrate
php-fpm