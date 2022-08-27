@echo off

echo ######### Last Worked On Version #########################
docker images ls -a --filter reference=jimdenseje/graptoapi*

echo.

echo ######### Last Realise Version On HUB.DOCKER.COM ##########
FOR /F "tokens=*" %%g IN ('C:\php\php.exe "current_relised_version.php"') do (SET current=%%g)
echo %current%

echo.

set /p version="Add Version tag for this image (>%current%): " 

docker stop graptoapi
docker rm graptoapi

rem list all images starting with ?
FOR /F "tokens=*" %%g IN ('docker images ls -aq --filter reference^=jimdenseje/graptoapi*') do (SET VAR=%%g)
docker rmi --force %VAR%

docker build -t jimdenseje/graptoapi:%version% .
docker image tag jimdenseje/graptoapi:%version% jimdenseje/graptoapi

docker run -d -p 8000:80 -v graptoapi-mysql:/var/lib/mysql:rw -v graptoapi-sides:/var/www/html/selenium/sides:rw --name graptoapi jimdenseje/graptoapi:%version%

docker exec -it graptoapi /bin/bash

pause