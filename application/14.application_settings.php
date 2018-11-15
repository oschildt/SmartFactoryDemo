<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\session;
use function SmartFactory\application_settings;
use function SmartFactory\checkempty;
use function SmartFactory\echo_html;
use function SmartFactory\text;
use function SmartFactory\input_text;
use function SmartFactory\checkbox;

session()->startSession();

application_settings()->setContext("general_settings");
?><!DOCTYPE html>
<html lang="en">
<head>
<title>Booking Settings</title>

<link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Booking Settings: General Settings</h2>

<?php
function process_form()
{
  if(empty($_REQUEST["act"])) return true;
  
  application_settings()->setParameter("hotel_name", checkempty($_REQUEST["booking_settings"]["hotel_name"]));
  application_settings()->setParameter("hotel_email", checkempty($_REQUEST["booking_settings"]["hotel_email"]));
  application_settings()->setParameter("show_free_rooms", checkempty($_REQUEST["booking_settings"]["show_free_rooms"]));
  
  if(!application_settings()->validateSettings()) return false;
  
  header("location: 14.application_settings_next.php");
  exit();  
} // process_form

process_form();
?>

<?php
report_messages();
?>

<p>Dirty (global): <?php echo(application_settings()->isDirty(true)); ?></p>
<p>Dirty (this mask - <?php echo(application_settings()->getContext()); ?>): <?php echo(application_settings()->isDirty()); ?></p>

<form action="14.application_settings.php" method="post">

<table>
<tr>
  <td>Hotel Name*:</td>
  <td>
    <?php input_text(["name" => "booking_settings[hotel_name]", 
                      "autocomplete" => "off",
                      "value" => application_settings()->getParameter("hotel_name", true)
                     ]); ?>
  </td>
</tr>
<tr>
  <td>Hotel Email*:</td>
  <td>
    <?php input_text(["name" => "booking_settings[hotel_email]", 
                      "autocomplete" => "off",
                      "value" => application_settings()->getParameter("hotel_email", true)
                     ]); ?>
  </td>
</tr>
<tr>
  <td>Show Free Rooms:</td>
  <td>
  <?php checkbox(["name" => "booking_settings[show_free_rooms]", 
                  "value" => "1", 
                  "checked" => application_settings()->getParameter("show_free_rooms", true, 1)
                  ]); ?>  
  </td>
</tr>
</table>

<br>
<br>

<input type="submit" name="act" value="Next">

</form>

</body>
</html>



