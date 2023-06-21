<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\config_settings;
use function SmartFactory\checkempty;
use function SmartFactory\echo_html;
use function SmartFactory\session;
use function SmartFactory\text;
use function SmartFactory\input_text;
use function SmartFactory\input_password;
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
    if (empty($_REQUEST["act"])) {
        return;
    }
    
    config_settings()->setParameter("db_type", checkempty($_REQUEST["settings"]["db_type"]));
    config_settings()->setParameter("db_server", checkempty($_REQUEST["settings"]["db_server"]));
    config_settings()->setParameter("db_name", checkempty($_REQUEST["settings"]["db_name"]));
    config_settings()->setParameter("db_user", checkempty($_REQUEST["settings"]["db_user"]));
    if (!empty($_REQUEST["settings"]["db_password"])) {
        config_settings()->setParameter("db_password", checkempty($_REQUEST["settings"]["db_password"]));
    }
    config_settings()->setParameter("db_prefix", checkempty($_REQUEST["settings"]["db_prefix"]));
    
    if (!config_settings()->validateSettings()) {
        return;
    }
    
    config_settings()->saveSettings();
    
    messenger()->addInfoMessage(text("MsgSettingsSaved"));
    
    header("location: 12.config_settings_summary.php");
    exit();
} // process_form

try {
    process_form();
} catch (\Exception $ex) {
    messenger()->addError($ex->getMessage());
}
?>

<?php
report_messages();
?>

<form action="12.config_settings_db.php" method="post">

    <table>
        <tr>
            <td><?php echo_html(text('DatabaseType')); ?>*:</td>
            <td>
                <?php input_text([
                    "name" => "settings[db_type]",
                    "autocomplete" => "off",
                    "value" => config_settings()->getParameter("db_type", "MySQL")
                ]); ?>
            </td>
        </tr>
        <tr>
            <td><?php echo_html(text('DatabaseServer')); ?>*:</td>
            <td>
                <?php input_text([
                    "name" => "settings[db_server]",
                    "autocomplete" => "off",
                    "value" => config_settings()->getParameter("db_server", "localhost")
                ]); ?>
            </td>
        </tr>
        <tr>
            <td><?php echo_html(text('DatabaseName')); ?>*:</td>
            <td>
                <?php input_text([
                    "name" => "settings[db_name]",
                    "autocomplete" => "off",
                    "value" => config_settings()->getParameter("db_name")
                ]); ?>
            </td>
        </tr>
        <tr>
            <td><?php echo_html(text('DatabaseUser')); ?>*:</td>
            <td>
                <?php input_text([
                    "name" => "settings[db_user]",
                    "autocomplete" => "off",
                    "value" => config_settings()->getParameter("db_user")
                ]); ?>
            </td>
        </tr>
        <tr>
            <td><?php echo_html(text('DatabasePassword')); ?>*:</td>
            <td>
                <?php input_password([
                    "name" => "settings[db_password]",
                    "autocomplete" => "off"
                ]); ?>
            </td>
        </tr>
        <tr>
            <td><?php echo_html(text('DatabaseTablePrefix')); ?>*:</td>
            <td>
                <?php input_text([
                    "name" => "settings[db_prefix]",
                    "autocomplete" => "off",
                    "value" => config_settings()->getParameter("db_prefix", "V1")
                ]); ?>
            </td>
        </tr>
    </table>

    <br>
    <br>

    <input type="submit" name="act" value="Save">

</form>

</body>
</html>



