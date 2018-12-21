<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\dbworker;
?><!DOCTYPE html>
<html lang="en">
<head>
<title>MySQL connection</title>

<link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>MySQL connection</h2>

<?php
function connect_mysql()
{
  try {
      $dbw = dbworker([
          "db_type" => "MySQL",
          "db_server" => "localhost",
          "db_name" => "framework_demo",
          "db_user" => "root",
          "db_password" => "",
          "autoconnect" => true
      ]);
  } catch(\SmartFactory\SmartException $ex) {
      return null;
  }
  
  echo "<h2>Simple query</h2>";
  
  if(!$dbw->execute_query("SELECT FIRST_NAME, LAST_NAME FROM USERS"))
  {
    return sql_error($dbw);
  }

  while($dbw->fetch_row())
  {
    echo $dbw->field_by_name("FIRST_NAME") . " " . $dbw->field_by_name("LAST_NAME") . "<br>";
  }

  $dbw->free_result();
  
  echo "<h2>Fetch array</h2>";

  if(!$dbw->execute_query("SELECT PAGE_ID, LANGUAGE_KEY, TITLE, CONTENT FROM PAGE_CONTENT"))
  {
    return sql_error($dbw);
  }

  $rows = [];
  if($dbw->fetch_array($rows))
  {
    echo "<pre>";
    print_r($rows);
    echo "</pre>";
  }

  $dbw->free_result();
  
  echo "<h2>Fetch array with dimensions [PAGE_ID, LANGUAGE_KEY]</h2>";

  if(!$dbw->execute_query("SELECT PAGE_ID, LANGUAGE_KEY, TITLE, CONTENT FROM PAGE_CONTENT"))
  {
    return sql_error($dbw);
  }

  $rows = [];
  if($dbw->fetch_array($rows, ["PAGE_ID", "LANGUAGE_KEY"]))
  {
    echo "<pre>";
    print_r($rows);
    echo "</pre>";
  }

  $dbw->free_result();

  echo "<h2>Prepared query (0, 1)</h2>";

  if(!$dbw->prepare_query("SELECT FIRST_NAME, LAST_NAME FROM USERS WHERE SALARY > ? AND DEPARTMENT_ID = ?"))
  {
    return sql_error($dbw);
  }
  
  if(!$dbw->execute_prepared_query(0, 1))
  {
    return sql_error($dbw);
  }

  while($dbw->fetch_row())
  {
    echo $dbw->field_by_name("FIRST_NAME") . " " . $dbw->field_by_name("LAST_NAME") . "<br>";
  }

  $dbw->free_result();

  echo "<h2>Prepared query (0, 2)</h2>";

  if(!$dbw->execute_prepared_query(0, 2))
  {
    return sql_error($dbw);
  }

  while($dbw->fetch_row())
  {
    echo $dbw->field_by_name("FIRST_NAME") . " " . $dbw->field_by_name("LAST_NAME") . "<br>";
  }

  $dbw->free_result();
  
  $dbw->free_prepared_query();
  
  echo "<h2>Prepared query to array</h2>";

  if(!$dbw->prepare_query("SELECT PAGE_ID, LANGUAGE_KEY, TITLE, CONTENT FROM PAGE_CONTENT WHERE PAGE_ID > ?"))
  {
    return sql_error($dbw);
  }
  
  if(!$dbw->execute_prepared_query(0))
  {
    return sql_error($dbw);
  }

  $rows = [];
  if($dbw->fetch_array($rows))
  {
    echo "<pre>";
    print_r($rows);
    echo "</pre>";
  }

  $dbw->free_result();
  
  echo "<h2>Prepared query to array with dimensions [PAGE_ID, LANGUAGE_KEY]</h2>";

  if(!$dbw->execute_prepared_query(0))
  {
    return sql_error($dbw);
  }

  $rows = [];
  if($dbw->fetch_array($rows, ["PAGE_ID", "LANGUAGE_KEY"]))
  {
    echo "<pre>";
    print_r($rows);
    echo "</pre>";
  }

  $dbw->free_result();

  $dbw->free_prepared_query();

  echo "<h2>Streaming large data</h2>";
  
  $stream = fopen(approot() . "resources/large_binary.jpg", "rb");

  if(!$dbw->stream_long_data("UPDATE LARGE_DATA SET BLOB_DATA = ? WHERE ID = 1", $stream))
  {
    return sql_error($dbw);
  }
  
  echo "Binary written.<br>";

  $stream = fopen(approot() . "resources/large_text.txt", "rt");

  if(!$dbw->stream_long_data("UPDATE LARGE_DATA SET TEXT_DATA = ? WHERE ID = 1", $stream))
  {
    return sql_error($dbw);
  }
  
  echo "Text written.<br>";

  if(!$dbw->execute_query("SELECT LENGTH(TEXT_DATA) LTD, LENGTH(BLOB_DATA) LBD FROM LARGE_DATA WHERE ID = 1"))
  {
    return sql_error($dbw);
  }

  while($dbw->fetch_row())
  {
    echo "TEXT_DATA: " . $dbw->field_by_name("LTD") . "<br>";
    echo "BLOB_DATA: " . $dbw->field_by_name("LBD") . "<br>";
  }

  $dbw->free_result();
  
  echo "<h2>Analizing of the meta data USERS</h2>";

  if(!$dbw->execute_query("SELECT * FROM USERS"))
  {
    return sql_error($dbw);
  }
  
  $fcnt = $dbw->field_count();
  echo "Number of fields: " . $fcnt . "<br><br>";

  for($i = 0; $i < $fcnt; $i++)
  {
    $finfo = $dbw->field_info_by_num($i);
    if(empty($finfo)) continue;

    echo "<pre>";
    print_r($finfo);
    echo "</pre>";
  }

  $dbw->free_result();

  echo "<h2>Analizing of the meta data LARGE_DATA</h2>";

  if(!$dbw->execute_query("SELECT * FROM LARGE_DATA"))
  {
    return sql_error($dbw);
  }
  
  $fcnt = $dbw->field_count();
  echo "Number of fields: " . $fcnt . "<br><br>";

  for($i = 0; $i < $fcnt; $i++)
  {
    $finfo = $dbw->field_info_by_num($i);
    if(empty($finfo)) continue;

    echo "<pre>";
    print_r($finfo);
    echo "</pre>";
  }

  $dbw->free_result();
  
  echo "<h2>Getting data from stored procedure</h2>";
  
  if(!$dbw->execute_procedure("GET_USERS", 100))
  {
    return sql_error($dbw);
  }

  while($dbw->fetch_row())
  {
    echo $dbw->field_by_name("FIRST_NAME") . " " . $dbw->field_by_name("LAST_NAME") . "<br>";
  }

  $dbw->free_result();
  
  return true;
} // connect_mysql

if(connect_mysql() === null)
{
  echo "<h4 style='color: maroon'>Please ensure that you have created the demo database with the script 'database/create_database_mysql.sql' and adjust the DB password and other connection data in line 21 of this file!</h4>";
}
else
{
  report_messages();
}
?>

</body>
</html>

