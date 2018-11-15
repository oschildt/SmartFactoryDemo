<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\table;
use function SmartFactory\format_number;
use function SmartFactory\text;
?><!DOCTYPE html>
<html lang="en">
<head>
<title>Tables from array</title>

<link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Tables from array</h2>

<?php
$rows = [
  ["name" => "DB design", "employee" => "Alex", "time_estimation" => "5", "deadline" => time() + 3*24*3600, "comments" => "No comments"],
  ["name" => "Mask implementation", "employee" => "Boris", "time_estimation" => "3", "deadline" => time() + 6*24*3600, "comments" => "No comments"],
  ["name" => "Validation", "employee" => "Alex", "time_estimation" => "2", "deadline" => time() + 3*24*3600, "comments" => "No comments"],
  ["name" => "Settings", "employee" => "Boris", "time_estimation" => "8", "deadline" => time() + 6*24*3600, "comments" => "No comments"],
  ["name" => "About", "employee" => "Robert", "time_estimation" => "3", "deadline" => time() + 5*24*3600, "comments" => "No comments"],
  ["name" => "Initialization", "employee" => "Alon", "time_estimation" => "4", "deadline" => time() + 4*24*3600, "comments" => "No comments"]
];

$captions = [
  "name" => "Task name",
  "employee" => "Employee",
  "time_estimation" => "Time estimation",
  "deadline" => "Deadline",
  "comments" => "Conmments"
];

$formatter = function ($rownum, $colnum, $colname, $val) { 
  
  if($colname == "time_estimation") return format_number($val, 2);
  if($colname == "deadline") return date(text("DateTimeFormat"), $val);
  
  return $val;
};
?>

<?php table($rows, 
            ["captions" => $captions,
             "class" => "my_table",
             "style" => "background-color: #dddddd",
             "col_class_from_keys" => true,
             "no_escape_html" => false,
             "formatter" => $formatter
            ]); ?>

</body>
</html>
