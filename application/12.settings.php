<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\session;
use function SmartFactory\config_settings;
use function SmartFactory\checkempty;
use function SmartFactory\echo_html;
use function SmartFactory\text;
use function SmartFactory\textarea;
use function SmartFactory\input_text;
use function SmartFactory\input_password;
use function SmartFactory\checkbox;

session()->startSession();

config_settings()->setContext("server_settings");
?><!DOCTYPE html>
<html lang="en">
<head>
<title>Config Settings</title>

<link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Config Settings: Server Settings</h2>

<?php
function process_form()
{
  if(empty($_REQUEST["act"])) return true;

  config_settings()->setParameter("smtp_server", checkempty($_REQUEST["settings"]["smtp_server"]));
  config_settings()->setParameter("tracing_enabled", checkempty($_REQUEST["settings"]["tracing_enabled"]));
  config_settings()->setParameter("show_message_details", checkempty($_REQUEST["settings"]["show_message_details"]));
  config_settings()->setParameter("show_prog_warning", checkempty($_REQUEST["settings"]["show_prog_warning"]));
  config_settings()->setParameter("domains", empty($_REQUEST["settings"]["domains"]) ? [] : preg_split("/[\n\r]+/", trim($_REQUEST["settings"]["domains"])));

  if(!config_settings()->validateSettings()) return false;

  header("location: 12.settings_next.php");
  exit();
} // process_form

process_form();
?>

<?php
report_messages();
?>

<p>Dirty (global): <?php echo(config_settings()->isDirty(true)); ?></p>
<p>Dirty (this mask - <?php echo(config_settings()->getContext()); ?>): <?php echo(config_settings()->isDirty()); ?></p>

<form action="12.settings.php" method="post">

<table>
<tr>
  <td><?php echo_html(text('SmtpHost')); ?>*:</td>
  <td>
    <?php input_text(["name" => "settings[smtp_server]",
                      "autocomplete" => "off",
                      "value" => config_settings()->getParameter("smtp_server", true, "localhost")
                     ]); ?>
  </td>
</tr>
<tr>
  <td><?php echo_html(text('EnableTracing')); ?>:</td>
  <td>
  <?php checkbox(["name" => "settings[tracing_enabled]",
                  "value" => "1",
                  "checked" => config_settings()->getParameter("tracing_enabled", true, 1)
                  ]); ?>
  </td>
</tr>
<tr>
  <td><?php echo_html(text('ShowErrorDetails')); ?>:</td>
  <td>
  <?php checkbox(["name" => "settings[show_message_details]",
                  "value" => "1",
                  "checked" => config_settings()->getParameter("show_message_details", true, 1)
                  ]); ?>
  </td>
</tr>
<tr>
  <td><?php echo_html(text('ShowProgWarnings')); ?>:</td>
  <td>
  <?php checkbox(["name" => "settings[show_prog_warning]",
                  "value" => "1",
                  "checked" => config_settings()->getParameter("show_prog_warning", true, 1)
                  ]); ?>
  </td>
</tr>
<tr>
  <td colspan="2">
  <?php echo_html(text('Domains')); ?>:<br>
  <?php 
  textarea(["name" => "settings[domains]",
                  "style" => "width: 300px;height: 150px",
                  "value" => implode("\n", config_settings()->getParameter("domains", true, []))
                  ]); 
                  ?>
  </td>
</tr>
</table>

<br>
<br>

<input type="submit" name="act" value="Next">

</form>

</body>
</html>



