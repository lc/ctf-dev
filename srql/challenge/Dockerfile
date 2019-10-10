FROM ubuntu:latest
MAINTAINER Corben Leo "docker@threat.dev"
RUN apt-get update -y
RUN apt-get update && apt-get -y upgrade && DEBIAN_FRONTEND=noninteractive apt-get install -y apache2 php libapache2-mod-php php-mysql php-curl
RUN a2enmod rewrite
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid
EXPOSE 80
ADD site.conf /etc/apache2/sites-enabled/000-default.conf
ADD www /var/www/html/
CMD /usr/sbin/apache2ctl -D FOREGROUND

