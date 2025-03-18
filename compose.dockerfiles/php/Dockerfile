#FROM php:7.4-fpm

#ENV TZ=Asia/Shanghai
#RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

#RUN echo "deb http://mirrors.aliyun.com/debian/ buster main non-free contrib \n \
		#deb-src http://mirrors.aliyun.com/debian/ buster main non-free contrib \n \
		#deb http://mirrors.aliyun.com/debian-security buster/updates main \n \
		#deb-src http://mirrors.aliyun.com/debian-security buster/updates main \n \
		#deb http://mirrors.aliyun.com/debian/ buster-updates main non-free contrib \n \
		#deb-src http://mirrors.aliyun.com/debian/ buster-updates main non-free contrib \n \
		#deb http://mirrors.aliyun.com/debian/ buster-backports main non-free contrib \n \
		#deb-src http://mirrors.aliyun.com/debian/ buster-backports main non-free contrib" > /etc/apt/sources.list \
		#&& apt-get update \
		#&& apt-get install -y \
		#build-essential \
		#libmagickcore-dev \
		#libmagickwand-dev \
		#imagemagick \
		#libfreetype6-dev \
		#libjpeg62-turbo-dev \
		#libmcrypt-dev \
		#libpng-dev \
		#libmemcached-dev \
		#zlib1g-dev \
		#curl \
		#libcurl4-gnutls-dev \
		#libzip-dev \
		#&& docker-php-ext-configure gd --with-freetype --with-jpeg \
		#&& docker-php-ext-install -j$(nproc) soap gd zip iconv pdo_mysql opcache mysqli bcmath\
		#&& pecl install redis-5.1.1 && docker-php-ext-enable redis \
		#&& pecl install imagick && docker-php-ext-enable imagick \
		#&& pecl install mcrypt && docker-php-ext-enable mcrypt \
		#&& pecl install memcached && docker-php-ext-enable memcached \
		#&& pecl install msgpack && docker-php-ext-enable msgpack \
		#&& pecl install yar && docker-php-ext-enable yar \
		#&& pecl install xdebug && docker-php-ext-enable xdebug

FROM php:7.3.8-fpm

RUN mv /etc/apt/sources.list /etc/apt/sources.list.bak \
    && echo 'deb http://mirrors.aliyun.com/debian/ buster main non-free contrib' > /etc/apt/sources.list \
    && echo 'deb http://mirrors.aliyun.com/debian-security buster/updates main' >> /etc/apt/sources.list \
    && echo 'deb http://mirrors.aliyun.com/debian/ buster-updates main non-free contrib' >> /etc/apt/sources.list \
    && echo 'deb http://mirrors.aliyun.com/debian/ buster-backports main non-free contrib' >> /etc/apt/sources.list \
    && apt-get update \
    && export CFLAGS="$PHP_CFLAGS" CPPFLAGS="$PHP_CPPFLAGS" LDFLAGS="$PHP_LDFLAGS" \
    && apt-get install -y --no-install-recommends libfreetype6-dev libjpeg62-turbo-dev libpng-dev libmagickwand-dev libmcrypt-dev libmemcached-dev zlib1g-dev curl libcurl4-gnutls-dev libzip-dev \
    && rm -rf /var/lib/apt/lists/* \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) bcmath calendar exif gettext sockets dba mysqli pcntl pdo_mysql shmop sysvmsg sysvsem sysvshm iconv gd soap zip \
    && pecl install imagick-3.4.4 mcrypt-1.0.2 memcached-3.1.3 redis-5.0.2 xdebug-2.7.2 swoole-4.4.3 msgpack yar \
    && docker-php-ext-enable imagick mcrypt memcached redis xdebug swoole msgpack yar zip \
    && docker-php-ext-configure opcache --enable-opcache && docker-php-ext-install opcache

## 从官方基础版本构建
#FROM php:7.3.7-fpm
## 官方版本默认安装扩展: 
## Core, ctype, curl
## date, dom
## fileinfo, filter, ftp
## hash
## iconv
## json
## libxml
## mbstring, mysqlnd
## openssl
## pcre, PDO, pdo_sqlite, Phar, posix
## readline, Reflection, session, SimpleXML, sodium, SPL, sqlite3, standard
## tokenizer
## xml, xmlreader, xmlwriter
## zlib
#
## 更新为国内镜像
#RUN mv /etc/apt/sources.list /etc/apt/sources.list.bak \
#    && echo 'deb http://mirrors.163.com/debian/ stretch main non-free contrib' > /etc/apt/sources.list \
#    && echo 'deb http://mirrors.163.com/debian/ stretch-updates main non-free contrib' >> /etc/apt/sources.list \
#    && echo 'deb http://mirrors.163.com/debian-security/ stretch/updates main non-free contrib' >> /etc/apt/sources.list \
#    && apt-get update
#
## bcmath, calendar, exif, gettext, sockets, dba, 
## mysqli, pcntl, pdo_mysql, shmop, sysvmsg, sysvsem, sysvshm 扩展
#RUN docker-php-ext-install -j$(nproc) bcmath calendar exif gettext sockets dba mysqli pcntl pdo_mysql shmop sysvmsg sysvsem sysvshm iconv
#
## GD 扩展
#RUN apt-get install -y --no-install-recommends libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
#    && rm -r /var/lib/apt/lists/* \
#    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
#    && docker-php-ext-install -j$(nproc) gd
#
## imagick 扩展
#RUN export CFLAGS="$PHP_CFLAGS" CPPFLAGS="$PHP_CPPFLAGS" LDFLAGS="$PHP_LDFLAGS" \
#    && apt-get install -y --no-install-recommends libmagickwand-dev \
#    && rm -r /var/lib/apt/lists/* \
#    && pecl install imagick-3.4.4 \
#    && docker-php-ext-enable imagick
#
## mcrypt 扩展 
#RUN apt-get install -y --no-install-recommends libmcrypt-dev \
#    && rm -r /var/lib/apt/lists/* \
#    && pecl install mcrypt-1.0.2 \
#    && docker-php-ext-enable mcrypt
#
## Memcached 扩展 
#RUN apt-get install -y --no-install-recommends libmemcached-dev zlib1g-dev \
#    && rm -r /var/lib/apt/lists/* \
#    && pecl install memcached-3.1.3 \
#    && docker-php-ext-enable memcached
#
## redis 扩展
#RUN pecl install redis-5.0.0 && docker-php-ext-enable redis
#
## opcache 扩展 
#RUN docker-php-ext-configure opcache --enable-opcache && docker-php-ext-install opcache
#
## xdebug 扩展
#RUN pecl install xdebug-2.7.2 && docker-php-ext-enable xdebug
#
## swoole 扩展
#RUN pecl install swoole-4.4.0 && docker-php-ext-enable swoole
#
## 镜像信息
#LABEL Author="Stone"
#LABEL Version="2019.7"
#LABEL Description="PHP 7.3.7 开发环境镜像. 
