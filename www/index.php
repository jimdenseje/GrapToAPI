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

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js">
</script>

<script>
$(document).ready(function(){
  $("#flip").click(function(){
    $("#panel").slideToggle("fast");
  });
});
</script>

<script>
$(document).ready(function(){
  $("#flip2").click(function(){
    $("#panel2").slideToggle("fast");
  });
});
</script>

<style>
.alert {
  padding: 10px;
  margin: 4px;
  background-color:  #2196F3;
  color: white;
}

.closebtn {
  margin-left: 15px;
  color: white;
  font-weight: bold;
  float: right;
  font-size: 16px;
  line-height: 20px;
  cursor: pointer;
  transition: 0.3s;
}

.closebtn:hover {
  color: black;
}
</style>

<style>
* {
  box-sizing: border-box;
  margin: 0px;
  padding: 4px;
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
  background-color: #f2f2f2;
  padding: 20px;
  width: 100%;
  position: relative;
  margin-top: 0px;
  margin-bottom: 8px;
}

.title {
  padding: 10px;
  padding-left: 20px;
  padding-bottom: 10px;
  text-align: left;
  background-color: #04AA6D;
  color: white;
  font-size: 16px;
  border-bottom: 10px solid green;
  width: calc(100vw - 8px - 12px);
  position: relative;
  margin-bottom:0px;
  cursor:pointer;
  
  margin-top:8px;
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
  width: calc(100vw - 8px - 8px - 4px);
  float: left;
  position: relative;
  margin:4px;
}

#jobs td, #jobs th {
  padding: 8px;
}

#jobs tr {
  background-color: #f2f2f2;
}

#jobs tr:nth-child(even){
  background-color: #d9d9d9;
}

#jobs tr:hover {
  background-color: #2196F3
}

#jobs th {
  padding-top: 12px;
  padding-bottom: 12px;
  text-align: left;
  background-color: #04AA6D;
  color: white;
}


#panel
{
display:none;
}


#panel2
{
display:none;
}

@media (min-width:600px) { 
    
  #jobs {
    min-width: 500px;
    width: auto;
  }

  .container {
    max-width: 480px;
  }

  .title {
    max-width: 480px;
  }

}

</style>
<title>Jobs</title>
</head>
<body>

';

if (@$_GET["msg"] != "") {
	echo '
		<div class="alert">
		  <span class="closebtn" onclick="this.parentElement.style.display=\'none\';">&times;</span> 
		  <span>'.urldecode($_GET["msg"]).'</span>
		</div>
	';
}

if (@$_POST["Name"] != "" && isset($_FILES["PageFile"]["name"])) {
	
	$next_file_name = @mysql_DB_SQL_array("SELECT Id FROM jobs ORDER BY Id DESC LIMIT 1")[0]["Id"] + 1;
	$FilePath = "selenium/sides/".$next_file_name.".side";
	
	if (move_uploaded_file($_FILES["PageFile"]["tmp_name"], $FilePath)) {
		header('Location: /?msg='."The file ". urlencode(htmlspecialchars( basename( $_FILES["PageFile"]["name"]))). " has been uploaded");
	} else {
		header('Location: /?msg='."Sorry, there was an error uploading your file.");
		exit;
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

if (@$_GET["remove"] == "true" && @$_GET["id"] != "") {
	
	$item = mysql_DB_SQL_array(
	"SELECT Name FROM jobs WHERE Id=".$_GET["id"].";"
	);
	
	mysql_DB_SQL_array(
	"DELETE FROM jobs WHERE Id=".$_GET["id"].";"
	);

	unlink("selenium/sides/".$_GET["Id"].".side");
	unlink("selenium/data/".$_GET["Id"].".json");
	unlink("selenium/last_run/".$_GET["Id"].".txt");
	
	header('Location: /?msg='."Removed side: ".urlencode($item[0]["Name"]));

} else if (@$_GET["edit"] == "true" && @$_GET["id"] != "") {
	
	//TODO
	header('Location: /?msg='."Might be implemented later");
	
} else {

	echo '
  <span style=" float: left; margin-top:-8px;">
  <div class="title" id="flip">Create Selenium IDE Job</div>
	<div class="container" id="panel">
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
		  <label for="subject">.side file</label>
		</div>
		<div class="col-75">
		  <input type="file" name="PageFile" accept=".side">
		</div>
	  </div>
	  <br>
	  <div class="row">
		<input type="submit" value="Create Job" >
	  </div>
	  </form>
	</div>
  
  <div class="title" id="flip2">Create PHP Hack Job</div>
	<div class="container" id="panel2">
    
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
		  <label for="subject">.side file</label>
		</div>
		<div class="col-75">
		  <input type="file" name="PageFile" accept=".side">
		</div>
	  </div>
	  <br>
	  <div class="row">
		<input type="submit" value="Create Job" >
	  </div>
	  </form>
	</div>
	</span>
	';

	$jobs = mysql_DB_SQL_array(
	"SELECT Id, Name, Description, UpdateInterval, LastRun, FilePath FROM jobs"
	);

	echo '
	<table id="jobs">
	  <tr>
		<th>Name</th>
		<th>UpdateInterval</th>
		<th>LastRun</th>
		<th></th>
	  </tr>
	  ';

	foreach($jobs as $job) {
		$job["FilePath"] = str_replace(array("selenium/sides/", ".side"), "", $job["FilePath"]);
		echo '<tr>';
		echo '<td title="'.$job["Description"].'">'.$job["Name"]."</td>\n";
		echo "<td>".$job["UpdateInterval"]."</td>\n";
		echo "<td>".$job["LastRun"]."</td>\n";
		echo "
		<td>
			<a href=\"selenium/last_run/".$job["FilePath"].".txt\">Last Run Log</a>
			<a href=\"selenium/data/".$job["FilePath"].".json\">Json Endpoint</a>
			<a href=\"selenium/sides/".$job["FilePath"].".side\" download>DW .side file</a>
			<a href=\"?edit=true&id=".$job["Id"]."\">Edit</a>
			<a href=\"?remove=true&id=".$job["Id"]."\">Remove</a>
		</td>";
		echo '</tr>';
	}

	echo "</table>
	
	</div>";

}

?>