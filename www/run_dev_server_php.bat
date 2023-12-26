SET mypath=%~dp0
start firefox localhost:8000
C:\php\php.exe -S localhost:8000 -t %mypath%
pause