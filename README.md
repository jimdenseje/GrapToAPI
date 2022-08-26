# GrapToAPI
A Dockerfile for creating a image for GrapToAPI which takes a .side file from selenium
and runs it as a service with webpage as interface on localhost:8000 or what every you specify.

It generates a json of you stored text values, from you .side file.

Use By:
docker run -d --restart always -p 8000:80 -v graptoapi-mysql:/var/lib/mysql:rw -v graptoapi-sides:/var/www/html/selenium/sides:rw --name graptoapi jimdenseje/graptoapi