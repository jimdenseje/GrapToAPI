<?php

include("functions/mysql_db.php");

mysql_DB_SQL_array(
"CREATE TABLE jobs (
    Id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    Name VARCHAR(255) NOT NULL DEFAULT '',
    Description TEXT NOT NULL DEFAULT '',
    UpdateInterval INT NOT NULL DEFAULT 10,
    FilePath TEXT DEFAULT '',
	LastRun DATETIME DEFAULT '0000-00-00 00:00:00'
);"
);

?>