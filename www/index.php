<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include("functions/mysql_db.php");

function gen_uid($l=10) {
	return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, $l);
}

function make_pagej($file) {
	$content = json_decode(file_get_contents($file), true);
	//print_r($content);
	$content["suites"][0]["tests"] = array($content["tests"][0]["id"]);
	foreach ($content["tests"][0]["commands"] as $key => $command) {
		if ($command["command"] == "storeText") {
			$command["value"] = str_replace(" ", "_", $command["value"]);
			$content["tests"][0]["commands"][$key]["value"] = $command["value"];
			$content["tests"][0]["commands"][] = array(
				"id" => gen_uid(32),
				"comment" => "",
				"command" => "echo",
				"target" => 'jdata{#&%'.$command["value"].'#&%${'.$command["value"].'}#&%}',
				"targets" => array()
			);
		}
	}
	file_put_contents($file.'j', json_encode($content, JSON_PRETTY_PRINT));
}

echo '
<!DOCTYPE html>
<html>
<head>
<style>
* {
  box-sizing: border-box;
}

input[type=text], input[type=file], select, textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  resize: vertical;
}

label {
  padding: 12px 12px 12px 0;
  display: inline-block;
}

input[type=submit] {
  background-color: #04AA6D;
  color: white;
  padding: 12px 20px;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  float: right;
}

input[type=submit]:hover {
  background-color: #45a049;
}

.container {
  border-radius: 5px;
  background-color: #f2f2f2;
  padding: 20px;
}

.col-25 {
  float: left;
  width: 25%;
  margin-top: 6px;
}

.col-75 {
  float: left;
  width: 75%;
  margin-top: 6px;
}

/* Clear floats after the columns */
.row:after {
  content: "";
  display: table;
  clear: both;
}

/* Responsive layout - when the screen is less than 600px wide, make the two columns stack on top of each other instead of next to each other */
@media screen and (max-width: 600px) {
  .col-25, .col-75, input[type=submit] {
    width: 100%;
    margin-top: 0;
  }
}

#jobs {
  font-family: Arial, Helvetica, sans-serif;
  border-collapse: collapse;
  min-width: 500px;
}

#jobs td, #jobs th {
  padding: 8px;
}

#jobs tr:nth-child(even){background-color: #f2f2f2;}

#jobs tr:hover {background-color: #ddd;}

#jobs th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #04AA6D;
  color: white;
}

</style>
<title>jobs</title>
</head>
<body>

';

if (@$_POST["Name"] != "" && isset($_FILES["PageFile"]["name"])) {
	
	$next_file_name = @mysql_DB_SQL_array("SELECT Id FROM jobs ORDER BY Id DESC LIMIT 1")[0]["Id"] + 1;
	$FilePath = "selenium/sides/".$next_file_name.".side";
	
	if (move_uploaded_file($_FILES["PageFile"]["tmp_name"], $FilePath)) {
		echo "The file ". htmlspecialchars( basename( $_FILES["PageFile"]["name"])). " has been uploaded.";
	} else {
		echo "Sorry, there was an error uploading your file.";
	}

	make_pagej($FilePath);

	if ($_POST["UpdateInterval"] < 10) {
		$_POST["UpdateInterval"] = 10;
	}

	mysql_DB_SQL_array(
	"INSERT INTO jobs (Name, Description, UpdateInterval, FilePath)
		VALUES ('".$_POST["Name"]."', '".$_POST["Description"]."', '".$_POST["UpdateInterval"]."', '".$FilePath."'); "
	);
	
}

if (@$_GET["id"] == "") {

	echo '
	<div class="container" style="max-width: 500px;">
	  <form action="" method="post" enctype="multipart/form-data">
	  <div class="row">
		<div class="col-25">
		  <label>Name</label>
		</div>
		<div class="col-75">
		  <input type="text" name="Name" placeholder="Name of you job">
		</div>
	  </div>
	  <div class="row">
		<div class="col-25">
		  <label>Description</label>
		</div>
		<div class="col-75">
		  <textarea name="Description" placeholder="Write something.." style="height:200px"></textarea>
		</div>
	  </div>
	  <div class="row">
		<div class="col-25">
		  <label>UpdateInterval</label>
		</div>
		<div class="col-75">
		  <input type="text" name="UpdateInterval" placeholder="In Minutes, Minimum 10">
		</div>
	  </div>
	  <div class="row">
		<div class="col-25">
		  <label for="subject">.page file</label>
		</div>
		<div class="col-75">
		  <input type="file" name="PageFile">
		</div>
	  </div>
	  <br>
	  <div class="row">
		<input type="submit" value="Create Job" >
	  </div>
	  </form>
	</div>
	
	';

	$jobs = mysql_DB_SQL_array(
	"SELECT Id, Name, Description, UpdateInterval, LastRun FROM jobs"
	);

	echo '
	<br>
	<table id="jobs">
	  <tr>
		<th>Name</th>
		<th>Description</th>
		<th>UpdateInterval</th>
		<th>LastRun</th>
		<th></th>
	  </tr>
	  ';

	foreach($jobs as $job) {
		echo '<tr>';
		echo "<td>".$job["Name"]."</td>\n";
		echo "<td>".$job["Description"]."</td>\n";
		echo "<td>".$job["UpdateInterval"]."</td>\n";
		echo "<td>".$job["LastRun"]."</td>\n";
		echo "
		<td>
			<a href=\"?edit=true&id=".$job["Id"]."\">Edit</a>
			<a href=\"?remove=true&id=".$job["Id"]."\">Remove</a>
		</td>";
		echo '</tr>';
	}

	echo "</table>";

}

?>