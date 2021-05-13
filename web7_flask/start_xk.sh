#!/bin/bash
su ciscn -c "nohup python /home/ciscn/run.py >/dev/null 2>&1 &"
/etc/init.d/ssh start -D
