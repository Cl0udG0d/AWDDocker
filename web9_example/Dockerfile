FROM phusion/baseimage

RUN apt-get update && \
    apt-get install python python-pip -y && \
    pip install flask flask_sqlalchemy flask_migrate -i https://pypi.tuna.tsinghua.edu.cn/simple && \
    rm -rf /var/lib/apt/lists/*

RUN groupadd ciscn && \
    useradd -g ciscn ciscn -m && \
    password=$(openssl passwd -1 -salt 'abcdefg' '123456') && \
    sed -i 's/^ciscn:!/ciscn:'$password'/g' /etc/shadow

WORKDIR /home/ciscn

RUN chown -R ciscn:ciscn . && \
    chmod -R 750 . && \
    rm -f /etc/service/sshd/down && \
    sed -ri 's/^#?PermitRootLogin\s+.*/PermitRootLogin yes/' /etc/ssh/sshd_config
ADD ./start.sh /etc/my_init.d/
RUN chmod u+x /etc/my_init.d/start.sh && \
    rm -rf /var/www/html/* && \
    /etc/my_init.d/00_regen_ssh_host_keys.sh
COPY ./html/index.php /var/www/html/
RUN chmod u+x /etc/my_init.d/start.sh
CMD ["/etc/init.d/ssh","start","-D"]