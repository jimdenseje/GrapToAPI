# FROM debian:sid-slim
FROM bitnami/minideb:latest
MAINTAINER jimdenseje@gmail.com

# Set correct environment variables.
ENV HOME /root

# Install apache2 and php and curl and npm
RUN apt-get update -y
RUN apt-get upgrade -y --no-install-recommends
RUN apt-get install -y --no-install-recommends apache2 php php-curl curl php-mysqli
RUN apt-get install -y --no-install-recommends wget unzip
RUN apt-get install -y --no-install-recommends mariadb-server
RUN apt-get install -y --no-install-recommends ca-certificates

# Install google chrome
RUN wget --no-verbose -O /tmp/chrome.deb https://dl.google.com/linux/chrome/deb/pool/main/g/google-chrome-stable/google-chrome-stable_112.0.5615.165-1_amd64.deb
RUN apt install -y --no-install-recommends /tmp/chrome.deb
RUN rm /tmp/chrome.deb

# Clean up APT when done.
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

ENV CONTAINER_TIMEZONE="Europe/Copenhagen"
RUN ln -snf /usr/share/zoneinfo/$CONTAINER_TIMEZONE /etc/localtime && echo $CONTAINER_TIMEZONE > /etc/timezone

ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_RUN_DIR /var/www/html
ENV MARIADB_ROOT_PASSWORD gndjk&tguy47tasg6&%^45

RUN mkdir -p ${APACHE_LOG_DIR}
RUN mkdir -p ${APACHE_RUN_DIR}

# REMOVING STANDARD INDEX FILE
RUN rm /var/www/html/index.html

# COPY MY FILES
COPY www /var/www/html
RUN chmod -R 777 /var/www/html

# Install latest NPM
RUN curl -fsSL https://deb.nodesource.com/setup_16.x | bash -
RUN apt-get install -y --no-install-recommends nodejs
RUN npm install -g npm@latest

# Install selenium-side-runner
RUN npm install -g selenium-side-runner

# install chrome driver
RUN wget https://chromedriver.storage.googleapis.com/112.0.5615.49/chromedriver_linux64.zip
RUN unzip chromedriver_linux64.zip
RUN rm chromedriver_linux64.zip
RUN mv chromedriver /usr/bin/chromedriver
RUN chown root:root /usr/bin/chromedriver
RUN chmod +x /usr/bin/chromedriver

# starting services on boot
# If a dedicated script seems like too much overhead, you can spawn separate processes explicitly with sh -c. For example:
# CMD sh -c 'mini_httpd -C /my/config -D &' \
#  && ./content_computing_loop

RUN mkdir /var/lib/mysql/test
RUN chmod 777 /var/lib/mysql/test

CMD sh -c '/etc/init.d/mariadb start' \
 && sh -c 'cd /var/www/html/ && ./init.sh' \
 && sh -c 'cd /var/www/html/selenium/ && php run_sides.php &' \
 && /usr/sbin/apache2 -D FOREGROUND
