docker stop graptoapi
docker rm graptoapi
docker rmi graptoapi
docker build -t graptoapi .

docker run -d -p 8000:80 -v graptoapi-mysql:/var/lib/mysql:rw -v graptoapi-sides:/var/www/selenium/sides:rw --name graptoapi graptoapi

docker exec -it graptoapi /bin/bash
