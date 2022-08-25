<?php

include("functions/mysql_db.php");

echo "<pre style=\"padding:8px; margin:20px; border-left: 4px solid black; padding-left: 10px; margin-left: 20px;\">\n";

$dbs = mysql_DB_SQL_array("SHOW TABLES");
$all_db_data = array();
$scheam_test = array();
$missing = array();
$found = array();

foreach ($dbs as $db_arr) {
  $db = reset($db_arr);
  //echo $db."\n\n";
  $fields = mysql_DB_SQL_array("show columns from ".$db);

  foreach ($fields as $field) {
    //echo $field["Field"]."\n";
    if (strpos($field["Field"], '_id') !== false) {
      $scheam_test[0][1][str_replace("_id", "", $field["Field"])][] = $db;
      $found[$db] = 0;
    }
    $all_db_data[$db][] = $field["Field"];
  }

  //echo "\n";
}

foreach ($dbs as $db_arr) {
  $db = reset($db_arr);
  if (!isset($found[$db]) && !isset($scheam_test[0][1][$db])) {
    $missing[] = $db;
  }
}

$x = 0;
while (isset($scheam_test[$x][1])) {
  $y = $x + 1;
  foreach ($scheam_test[$x][1] as $table_main => $tables) {
    foreach ($scheam_test[$x][1] as $main_tables => $crap) {
      if (in_array($table_main, $crap)) {
        $scheam_test[$y][1][$table_main] = $tables;
      }
    }
    if (!isset($scheam_test[$y][1][$table_main])) {
      $scheam_test[$y][0][$table_main] = $tables;
    }
  }
  $x++;
}

unset($scheam_test[0]);

echo "<b>Tables Without Relations</b>\n";
foreach ($missing as $val) {
  echo "\n -> ".$val."\n";
}

echo "\n\n<b>Tables With Relations</b>\n";
foreach ($scheam_test as $key1 => $val1) {
  echo "\nLvl ".$key1."\n";
  foreach ($val1[0] as $key2 => $val2) {
    echo "\n -> ".$key2."\n";
    foreach ($val2 as $key3 => $val3) {
      echo "   -| ".$val3."\n";
    }
  }
}

//print_r($missing);

//print_r($all_db_data);

//print_r($scheam_test);

?>
