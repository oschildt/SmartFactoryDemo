<?php
require "../../vendor/autoload.php";
?><!DOCTYPE html>
<html lang="en">
<head>
<title>Class tester</title>
</head>
<body>

<h2>Class tester</h2>

<?php
function deep_include($dir)
{
  $files = scandir($dir);
  foreach($files as $file)
  {
    if($file == "." || $file == ".." || $file == ".htaccess") continue;
    
    if(is_dir($dir . $file))
    {
      deep_include($dir . $file . "/");
    }
  }  
  
  $files = scandir($dir);
  foreach($files as $file)
  {
    if($file == "." || $file == ".." || $file == ".htaccess") continue;
    
    if(!is_dir($dir . $file))
    {
      include_once $dir . $file;
    }
  }  
}

deep_include("../vendor/smartfactory/smartfactory/src/SmartFactory/");

deep_include("../src/MyApplication/");

echo "<p>Passed!</p>";
?>

<h2>Function tester</h2>

<?php
require_once "../../vendor/smartfactory/smartfactory/src/utility_functions_inc.php";

require_once "../../vendor/smartfactory/smartfactory/src/short_functions_inc.php";

require_once "../../vendor/smartfactory/smartfactory/src/html_utils_inc.php";

require_once "../src/application_root_inc.php";

require_once "../src/utf8_functions_inc.php";

echo "<p>Passed!</p>";

echo "<p>All is fine!</p>";
?>

</body>
</html>