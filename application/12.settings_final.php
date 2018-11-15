<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\session;
use function SmartFactory\config_settings;
use function SmartFactory\checkempty;
use function SmartFactory\echo_html;
use function SmartFactory\text;
use function SmartFactory\input_text;
use function SmartFactory\input_password;
use function SmartFactory\checkbox;

session()->startSession();
?><!DOCTYPE html>
<html lang="en">
<head>
<title>Config Settings</title>

<link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Config Settings: Summary</h2>

<?php
report_messages();
?>

<p>Dirty (global): <?php echo(config_settings()->isDirty(true)); ?></p>

<h3>Server settings</h3>

<table>
<tr>
  <td><?php echo_html(text('SmtpHost')); ?>*:</td>
  <td><?php echo_html(config_settings()->getParameter("smtp_server")); ?></td>
</tr>
<tr>
  <td><?php echo_html(text('EnableTracing')); ?>:</td>
  <td><?php echo(config_settings()->getParameter("tracing_enabled") ? "1" : "0"); ?></td>
</tr>
<tr>
  <td><?php echo_html(text('ShowErrorDetails')); ?>:</td>
  <td><?php echo(config_settings()->getParameter("show_message_details") ? "1" : "0"); ?></td>
</tr>
<tr>
  <td><?php echo_html(text('ShowProgWarnings')); ?>:</td>
  <td><?php echo(config_settings()->getParameter("show_prog_warning") ? "1" : "0"); ?></td>
</tr>
</table>

<h3>Database settings</h3>

<table>
<tr>
  <td><?php echo_html(text('DatabaseType')); ?>*:</td>
  <td><?php echo_html(config_settings()->getParameter("db_type")); ?></td>
</tr>
<tr>
  <td><?php echo_html(text('DatabaseServer')); ?>*:</td>
  <td><?php echo_html(config_settings()->getParameter("db_server")); ?></td>
</tr>
<tr>
  <td><?php echo_html(text('DatabaseName')); ?>*:</td>
  <td><?php echo_html(config_settings()->getParameter("db_name")); ?></td>
</tr>
<tr>
  <td><?php echo_html(text('DatabaseUser')); ?>*:</td>
  <td><?php echo_html(config_settings()->getParameter("db_user")); ?></td>
</tr>
<tr>
  <td><?php echo_html(text('DatabasePassword')); ?>*:</td>
  <td><?php echo_html(config_settings()->getParameter("db_password")); ?></td>
</tr>
<tr>
  <td><?php echo_html(text('DatabaseTablePrefix')); ?>*:</td>
  <td><?php echo_html(config_settings()->getParameter("db_prefix")); ?></td>
</tr>
</table>

<br>
<br>

<button onclick="document.location.href = '12.settings_next.php'">Back</button>

</body>
</html>



