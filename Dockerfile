FROM dore.devim.team/crm_php:latest

RUN curl http://get.sensiolabs.org/php-cs-fixer.phar -o php-cs-fixer
RUN chmod a+x php-cs-fixer
RUN mv php-cs-fixer /usr/local/bin/php-cs-fixer

RUN curl http://get.sensiolabs.org/sami.phar -o sami
RUN chmod a+x sami
RUN mv sami /usr/local/bin/sami

RUN export APP_ENV=local

# install composer according https://getcomposer.org/download/
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer
RUN php -r "unlink('composer-setup.php');"

WORKDIR /var/www
