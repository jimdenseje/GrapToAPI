#!/bin/bash
mysql -e "CREATE DATABASE test"
mysql -e "CREATE USER 'HjfOIHf'@'localhost' IDENTIFIED BY 'uygsdf55445'"
mysql -e "GRANT ALL PRIVILEGES ON *.* TO 'HjfOIHf'@'localhost'"
php build_tabels.php