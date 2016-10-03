FROM dore.devim.team/crm_php:latest

RUN apt-get update

RUN curl http://get.sensiolabs.org/php-cs-fixer.phar -o php-cs-fixer
RUN chmod a+x php-cs-fixer
RUN mv php-cs-fixer /usr/local/bin/php-cs-fixer

RUN curl https://xdebug.org/files/xdebug-2.4.0.tgz -o xdebug.tgz -s
RUN tar -xzvf xdebug.tgz

RUN cd xdebug-2.4.0/ && phpize && ./configure --enable-xdebug && make && make install

COPY ./docker/config/local/xdebug-setup /usr/bin/

RUN chmod +x /usr/bin/xdebug-setup

RUN curl http://get.sensiolabs.org/sami.phar -o sami
RUN chmod a+x sami
RUN mv sami /usr/local/bin/sami

# install bcmath for accuracy of calculation
RUN apt-get install -y php7.1-bcmath

RUN export APP_ENV=local

CMD xdebug-setup && fpm-start

WORKDIR /var/www
