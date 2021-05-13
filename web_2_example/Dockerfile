FROM phusion/baseimage:focal-1.0.0

EXPOSE 80
EXPOSE 22

RUN sed -i "s/http:\/\/archive.ubuntu.com/http:\/\/mirrors.aliyun.com/g" /etc/apt/sources.list && \
    add-apt-repository ppa:ondrej/php && \
    apt-get update && \
    apt-get install -y php5.6 && \
    rm -f /etc/service/sshd/down && \
    sed -ri 's/^#?PermitRootLogin\s+.*/PermitRootLogin yes/' /etc/ssh/sshd_config && \
    groupadd ciscn && \
    useradd -g ciscn ciscn -m && \
    password=$(openssl passwd -1 -salt 'abcdefg' '123456') && \
    sed -i 's/^ciscn:!/ciscn:'$password'/g' /etc/shadow && \
    mkdir -p /var/www/html/ && \
    chown -R ciscn:ciscn /var/www/html/ && \
    /etc/my_init.d/00_regen_ssh_host_keys.sh && \
    service ssh start

COPY ./html/index.php /var/www/html/

WORKDIR /var/www/html

CMD ["php","-S","0.0.0.0:80"]

