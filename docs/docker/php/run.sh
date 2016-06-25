#!/bin/bash

#
# This file runs on start of the docker container
# Prepare required files, then run php-fpm
#

# --prefer-source fixes github's limits
php /usr/local/bin/composer install --prefer-source

# using --no-bin-links to support installation with docker on windows
npm install --no-bin-links
npm install bower --no-bin-links
node node_modules/bower/bin/bower install --allow-root
node node_modules/gulp/bin/gulp.js

php-fpm
