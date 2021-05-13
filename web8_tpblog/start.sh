#!/bin/bash
service ssh start
service mysql start
apache2ctl start
while test "1" = "1"
do
sleep 1000
done
