<?php

include("/var/www/html/functions/mysql_db.php");

while (true) {

	$jobs = mysql_DB_SQL_array(
		"SELECT Id, UpdateInterval, LastRun, FilePath FROM jobs"
	);

	foreach($jobs as $job) {
		if ((strtotime($job["LastRun"]) + ($job["UpdateInterval"] * 60)) <= time()) {
				
			$file = $job["FilePath"]."j";
			
			$filename = str_replace(array("selenium/sides/", ".sidej"), "", $file);
			$data = shell_exec('selenium-side-runner ../'.$file.' 2>&1');

			echo $data."\n";

			$values = array();
			$count = 0;
			foreach (explode("jdata{#&%", $data) as $xdata) {
				if ($count > 0) {
					$values[trim(explode("#&%", $xdata)[0])] = trim(explode("#&%", $xdata)[1]);
				}
				$count++;
			}
			file_put_contents("data/".$filename.".json", json_encode($values, JSON_PRETTY_PRINT));

			file_put_contents("last_run/".$filename.".txt", $data);
			
			mysql_DB_SQL_array(
				"UPDATE jobs
				SET LastRun = '".date("Y-m-d H:i:s")."'
				WHERE Id = ".$job["Id"].";"
			);
			
		}
	}
	
	sleep(30);

}