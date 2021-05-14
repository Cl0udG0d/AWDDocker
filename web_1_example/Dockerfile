FROM nickistre/ubuntu-lamp


ADD apache2.conf /etc/apache2/apache2.conf

EXPOSE 80
EXPOSE 22

RUN sed -i "s/http:\/\/archive.ubuntu.com/http:\/\/mirrors.aliyun.com/g" /etc/apt/sources.list && \
    apt-get update && \
    service ssh start && \
    a2enmod rewrite && \
    service apache2 start && \
    rm -f /var/www/html/* && \
    chown www-data:www-data /var/www/html/ -R && \
    useradd ctf && \
    echo ctf:test123 | chpasswd && \
    sleep 2

COPY ./html/index.php /var/www/html/