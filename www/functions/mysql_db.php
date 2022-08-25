<?php

function mysql_DB_SQL_array($sql) {

	$array_store = array();

	$db_servername = "localhost";
	$db_username = "HjfOIHf";
	$db_password = "uygsdf55445";
	$db_name = "test";

	// Create connection
	$conn = new mysqli($db_servername, $db_username, $db_password, $db_name);
	// Check connection
	if ($conn->connect_error) {
	    die("Connection failed: " . $conn->connect_error);
	}

	/* change character set to utf8 */
	if (!$conn->set_charset("utf8")) {
		//printf("Error loading character set utf8: %s\n", $mysqli->error);
	}

	$result = $conn->query($sql);

	if (@$result->num_rows > 0) {
   		// output data of each row
    	while($row = $result->fetch_assoc()) {
			$array_store[] = $row;
    	}
		return $array_store;
	}

	$conn->close();

	return array();

}

?>
