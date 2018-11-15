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
use function SmartFactory\messenger;

session()->startSession();
?><!DOCTYPE html>
<html lang="en">
<head>
<title>Booking Settings</title>

<link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Booking Settings: Summary</h2>

<?php
report_messages();
?>

<p>Dirty (global): <?php echo(application_settings()->isDirty(true)); ?></p>

<h3>General Settings</h3>

<table>
<tr>
  <td>Hotel Name*:</td>
  <td><?php echo_html(application_settings()->getParameter("hotel_name")); ?></td>
</tr>
<tr>
  <td>Hotel Email*:</td>
  <td><?php echo_html(application_settings()->getParameter("hotel_email")); ?></td>
</tr>
<tr>
  <td>Show Free Rooms:</td>
  <td><?php echo(application_settings()->getParameter("show_free_rooms") ? "1" : "0"); ?></td>
</tr>
</table>

<h3>Data Exchange Settings</h3>

<table>
<tr>
  <td>Booking Service URL*:</td>
  <td><?php echo_html(application_settings()->getParameter("booking_url")); ?></td>
</tr>
<tr>
  <td>Hotel-ID*:</td>
  <td><?php echo_html(application_settings()->getParameter("hotel_id")); ?></td>
</tr>
<tr>
  <td>Default Rate*:</td>
  <td><?php echo_html(application_settings()->getParameter("default_rate")); ?></td>
</tr>
</table>

<br>
<br>

<button onclick="document.location.href = '14.application_settings_next.php'">Back</button>

</body>
</html>



