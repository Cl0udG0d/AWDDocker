# Pull base image 
FROM ubuntu:trusty
  
MAINTAINER image "malingtao1019@163.com"  
# update source  
RUN echo "deb http://archive.ubuntu.com/ubuntu trusty main universe"> /etc/apt/sources.list  
RUN apt-get update \
	&& apt-get install -y mysql-server apache2 php5 php5-mysql
COPY database.sql /root/
RUN /etc/init.d/mysql start &&\
    mysql -e "grant all privileges on *.* to 'root'@'%' identified by 'toor';"&&\
    mysql -e "grant all privileges on *.* to 'root'@'localhost' identified by 'toor';"&&\
    mysql -u root -ptoor -e "show databases;" &&\
    mysql -u root -ptoor </root/database.sql &&\
	mysql -u root -ptoor -e "show databases;"
RUN sed -Ei 's/^(bind-address|log)/#&/' /etc/mysql/my.cnf \
	&& echo 'skip-host-cache\nskip-name-resolve' | awk '{ print } $1 == "[mysqld]" && c == 0 { c = 1; system("cat") }' /etc/mysql/my.cnf > /tmp/my.cnf \
	&& mv /tmp/my.cnf /etc/mysql/my.cnf 
COPY tpblog /var/www/html 
RUN rm /var/www/html/index.html &&\
 chown www-data:www-data /var/www/html -R
RUN apt install -y ssh

RUN rm -f /etc/service/sshd/down
RUN sed -ri 's/^#?PermitRootLogin\s+.*/PermitRootLogin yes/' /etc/ssh/sshd_config

RUN groupadd ciscn && \
        useradd -g ciscn ciscn -m && \
        password=$(openssl passwd -1 -salt 'abcdefg' '123456') && \
        sed -i 's/^ciscn:!/ciscn:'$password'/g' /etc/shadow && \
	chmod 777 /var/www/html -R

COPY httpd-foreground /usr/bin
EXPOSE 80
CMD ["httpd-foreground"]
