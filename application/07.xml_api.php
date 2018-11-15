<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\textarea;

if(empty($_REQUEST["xmldata"]))
{
  $_REQUEST["xmldata"] = '<?xml version="1.0" encoding="UTF-8"?> 
<Request>
<User>
  <Login>alex</Login>
  <Password>123</Password>
</User>
<Action>GetRooms</Action>
<City>Munich</City>
</Request>';
}
?><!DOCTYPE html>
<html lang="en">
<head>
<title>XML API</title>

<link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>XML API</h2>

<form action="../xmlapi/" method="post" target="_blank">

<?php textarea([  "name" => "xmldata", 
                  "style" => "border:1px solid gray",
                  "cols" => "60",
                  "rows" => "12"
                  ]); ?>
<br>
<br>

<input type="submit" name="act" value="Submit">

</form>

</body>
</html>



