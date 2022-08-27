<?php

include("/var/www/html/functions/mysql_db.php");

function get_string_between($start, $end, $string){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

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
			
			//add last run to json out
			//$parse_end_values = "Test Suites:".@explode("Test Suites:", $data)[1];
			$values["GrapToApiProp"]["LastRun"] = date("Y-m-d H:i:s");
			$values["GrapToApiProp"]["PassedTest"] = trim(get_string_between("Test Suites: ", "passed", $data));
			$values["GrapToApiProp"]["ExecutionTime"] = trim(get_string_between("Time:", ",", $data));
			if ($values["GrapToApiProp"]["ExecutionTime"] == "") {
				$values["GrapToApiProp"]["ExecutionTime"] = trim(get_string_between("Time:", "\n", $data));
			}
			$values["GrapToApiProp"]["ExecutionTimeEstimated"] = trim(get_string_between("estimated", "\n", $data));
			
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