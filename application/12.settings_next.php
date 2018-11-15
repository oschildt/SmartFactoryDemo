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
use function SmartFactory\messenger;

session()->startSession();

config_settings()->setContext("database_settings");
?><!DOCTYPE html>
<html lang="en">
<head>
<title>Config Settings</title>

<link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Config Settings: Database connection</h2>

<?php
function process_form()
{
  if(empty($_REQUEST["act"])) return true;
  
  config_settings()->setParameter("db_type", checkempty($_REQUEST["settings"]["db_type"]));
  config_settings()->setParameter("db_server", checkempty($_REQUEST["settings"]["db_server"]));
  config_settings()->setParameter("db_name", checkempty($_REQUEST["settings"]["db_name"]));
  config_settings()->setParameter("db_user", checkempty($_REQUEST["settings"]["db_user"]));
  if(!empty($_REQUEST["settings"]["db_password"])) config_settings()->setParameter("db_password", checkempty($_REQUEST["settings"]["db_password"]));
  config_settings()->setParameter("db_prefix", checkempty($_REQUEST["settings"]["db_prefix"]));
  
  if($_REQUEST["act"] == "Back") 
  {
    header("location: 12.settings.php");
    return true;
  }
  
  if(!config_settings()->validateSettings()) return false;
  
  if(!config_settings()->saveSettings()) return false;
  
  messenger()->setInfo(text("MsgSettingsSaved"));
  
  header("location: 12.settings_final.php");
  exit();  
} // process_form

process_form();
?>

<?php
report_messages();
?>

<p>Dirty (global): <?php echo(config_settings()->isDirty(true)); ?></p>
<p>Dirty (this mask - <?php echo(config_settings()->getContext()); ?>): <?php echo(config_settings()->isDirty()); ?></p>

<form action="12.settings_next.php" method="post">

<table>
<tr>
  <td><?php echo_html(text('DatabaseType')); ?>*:</td>
  <td>
    <?php input_text(["name" => "settings[db_type]", 
                      "autocomplete" => "off",
                      "value" => config_settings()->getParameter("db_type", true, "MySQL")
                     ]); ?>
  </td>
</tr>
<tr>
  <td><?php echo_html(text('DatabaseServer')); ?>*:</td>
  <td>
    <?php input_text(["name" => "settings[db_server]", 
                      "autocomplete" => "off",
                      "value" => config_settings()->getParameter("db_server", true, "localhost")
                     ]); ?>
  </td>
</tr>
<tr>
  <td><?php echo_html(text('DatabaseName')); ?>*:</td>
  <td>
    <?php input_text(["name" => "settings[db_name]", 
                      "autocomplete" => "off",
                      "value" => config_settings()->getParameter("db_name", true)
                     ]); ?>
  </td>
</tr>
<tr>
  <td><?php echo_html(text('DatabaseUser')); ?>*:</td>
  <td>
    <?php input_text(["name" => "settings[db_user]", 
                      "autocomplete" => "off",
                      "value" => config_settings()->getParameter("db_user", true)
                     ]); ?>
  </td>
</tr>
<tr>
  <td><?php echo_html(text('DatabasePassword')); ?>*:</td>
  <td>
    <?php input_password(["name" => "settings[db_password]", 
                      "autocomplete" => "off"
                     ]); ?>
  </td>
</tr>
<tr>
  <td><?php echo_html(text('DatabaseTablePrefix')); ?>*:</td>
  <td>
      <?php input_text(["name" => "settings[db_prefix]", 
                      "autocomplete" => "off",
                      "value" => config_settings()->getParameter("db_prefix", true, "V1")
                     ]); ?>
</td>
</tr>
</table>

<br>
<br>

<input type="submit" name="act" value="Next">

<input type="submit" name="act" value="Back">

</form>

</body>
</html>



