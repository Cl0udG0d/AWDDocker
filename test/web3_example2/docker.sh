#!/bin/sh
cp run.sh tmp/
cp flag.py tmp/
docker run -p {out_port}:80  -p {ssh_port}:22 -v `pwd`/chinaz:/var/www/html -v `pwd`/tmp:/tmp -d  --name {team_name} -ti web_example2

#!/bin/sh
#cp run.sh tmp/
#docker run -p 8000:80  -p 8001:22 -v `pwd`/chinaz:/var/www/html -v `pwd`/tmp:/tmp -d  --name "example" -ti web_example2
docker run -p 8000:80 -p 8001:22 -v `pwd`/chinaz:/var/www/html -v `pwd`/tmp:/tmp -d --name "example" -it web_example2
docker run -p 8000:80 -p 8001:22  -d --name "example" -it web_example2
