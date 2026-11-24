<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\textarea;

if (empty($_REQUEST["xmldata"])) {
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
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="css/examples.css" type="text/css" />
    <script src="js/examples.js" defer></script>
</head>

<body>

    <div class="header"></div>
    <div class="content">

        <div class="left_links"></div>
        <div class="right_links">
            <p><a href="index.php">List of examples</a></p>
        </div>
        <div style="clear: both;"></div>

        <h1>XML API</h1>

        <p>This example demonstrates how to send an XML request for hotel rooms and view the response, including validation errors.</p>

        <p>Remove the tag City to see how the errors are displayed.</p>

        <form action="xmlapi/get_rooms/" method="post" target="_blank">

            <?php textarea([
                "name" => "xmldata",
                "cols" => "60",
                "style" => "height: 250px; padding: 4px"
            ]); ?>
            <br>
            <br>

            <div class="button_area">
                <input type="submit" name="act" value="Submit">
            </div>

        </form>

    </div>

    <div class="_sys_load_lightbox" id="sys_progress_indicator" role="status" aria-live="polite" aria-hidden="true">
        <div>
            <div>
                <div class="progress_caption">Loading data ...</div>
                <div id="loading_indicator"><img src="images/loading.gif" width="220" height="19" alt=""></div>
            </div>
        </div>
    </div>

</body>

</html>
