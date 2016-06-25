FROM php:5.6-fpm

# add repository for openjdk-8-jre-headless
RUN echo 'deb http://httpredir.debian.org/debian jessie-backports main' > /etc/apt/sources.list.d/jessie-backports.list

# prepare nodejs; implicit apt-get update
RUN curl -sL https://deb.nodesource.com/setup_4.x | bash -

# install dependencies, cleanup
RUN apt-get install -y git unzip build-essential nodejs imagemagick tesseract-ocr tesseract-ocr-deu poppler-utils openjdk-8-jre-headless ca-certificates-java && \
    rm -rf /var/lib/apt/lists/*

ENV JAVA_HOME /usr/lib/jvm/java-8-openjdk-amd64/jre

# download pdfbox
RUN curl -sgo /opt/pdfbox-app-1.8.12.jar 'https://pdfbox.apache.org/[preferred]pdfbox/1.8.12/pdfbox-app-1.8.12.jar'

# configure php: short_open_tag is required
ADD ./docs/docker/php/short_open_tag_on.ini /usr/local/etc/php/conf.d/

# install & enable php extensions - for more extensions look at https://hub.docker.com/_/php
RUN docker-php-ext-install -j$(nproc) pdo pdo_mysql

# add composer
RUN curl -o /tmp/composer-setup.php https://getcomposer.org/installer \
  && curl -o /tmp/composer-setup.sig https://composer.github.io/installer.sig \
  && php -r "if (hash('SHA384', file_get_contents('/tmp/composer-setup.php')) !== trim(file_get_contents('/tmp/composer-setup.sig'))) { unlink('/tmp/composer-setup.php'); echo 'Invalid installer' . PHP_EOL; exit(1); }"

RUN php /tmp/composer-setup.php --no-ansi --install-dir=/usr/local/bin --filename=composer && rm -rf /tmp/composer-setup.php

# add unprivileged user, required for npm
RUN groupadd -r muctp && useradd -r -g muctp muctp && mkdir /home/muctp && chown muctp:muctp /home/muctp
RUN mkdir /var/www/ris3
RUN chown muctp:muctp /var/www/ris3

# the app lives in this folder, share it as volume
WORKDIR /var/www/ris3
VOLUME /var/www/ris3

# drop down to unprivileged user
USER muctp

# and this command runs on start
CMD ["bash", "/var/www/ris3/docs/docker/php/run.sh"]
