docker stop graptoapi
docker rm graptoapi
docker rmi graptoapi
docker build -t graptoapi .

docker run -d -p 8000:80 --network=opg1-bridge --name graptoapi graptoapi

docker exec -it graptoapi /bin/bash
