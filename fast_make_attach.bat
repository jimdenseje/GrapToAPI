docker stop graptoapi
docker rm graptoapi
docker rmi jimdenseje/graptoapi
docker build -t jimdenseje/graptoapi .

docker run -d -p 8000:80 -v graptoapi-mysql:/var/lib/mysql:rw -v graptoapi-sides:/var/www/html/selenium/sides:rw --name graptoapi jimdenseje/graptoapi

docker exec -it graptoapi /bin/bash

pause