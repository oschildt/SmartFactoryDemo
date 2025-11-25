<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\textarea;

if (empty($_REQUEST["jsondata"])) {
    $_REQUEST["jsondata"] = '{
    "login": "admin",
    "password": "qwerty"
}';
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>JSON API</title>

    <link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>JSON API</h2>

<p>Change password to see how the errors are displayed.</p>

<form action="../api/authenticate/" method="post" target="_blank">

    <?php textarea([
        "name" => "jsondata",
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



