<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\config_settings;
use function SmartFactory\checkempty;
use function SmartFactory\echo_html;
use function SmartFactory\session;
use function SmartFactory\text;
use function SmartFactory\textarea;
use function SmartFactory\input_text;
use function SmartFactory\checkbox;
use function SmartFactory\messenger;

session()->startSession();

config_settings()->setContext("server_settings");
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Config Settings</title>

    <link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Config Settings: Main Settings</h2>

<?php
function process_form()
{
    if (empty($_REQUEST["act"])) {
        return true;
    }
    
    config_settings()->setParameter("smtp_server", checkempty($_REQUEST["settings"]["smtp_server"]));
    config_settings()->setParameter("smtp_port", checkempty($_REQUEST["settings"]["smtp_port"]));
    config_settings()->setParameter("tracing_enabled", empty($_REQUEST["settings"]["tracing_enabled"]) ? 0 : 1);
    config_settings()->setParameter("show_message_details", empty($_REQUEST["settings"]["show_message_details"]) ? 0 : 1);
    config_settings()->setParameter("show_prog_warning", empty($_REQUEST["settings"]["show_prog_warning"]) ? 0 : 1);
    config_settings()->setParameter("domains", empty($_REQUEST["settings"]["domains"]) ? [] : preg_split("/[\n\r]+/", trim($_REQUEST["settings"]["domains"])));
    
    if (!config_settings()->validateSettings()) {
        return false;
    }
    
    if (!config_settings()->saveSettings()) {
        return false;
    }
    
    messenger()->setInfo(text("MsgSettingsSaved"));
    
    header("location: 12.config_settings_summary.php");
    exit();
} // process_form

process_form();
?>

<?php
report_messages();
?>

<form action="12.config_settings_main.php" method="post">

    <table>
        <tr>
            <td><?php echo_html(text('SmtpHost')); ?>*:</td>
            <td>
                <?php input_text([
                    "name" => "settings[smtp_server]",
                    "autocomplete" => "off",
                    "value" => config_settings()->getParameter("smtp_server", "localhost")
                ]); ?>
            </td>
        </tr>
        <tr>
            <td><?php echo_html(text('SmtpPort')); ?>:</td>
            <td>
                <?php input_text([
                    "name" => "settings[smtp_port]",
                    "autocomplete" => "off",
                    "value" => config_settings()->getParameter("smtp_port")
                ]); ?>
            </td>
        </tr>
        <tr>
            <td><?php echo_html(text('EnableTracing')); ?>:</td>
            <td>
                <?php checkbox([
                    "name" => "settings[tracing_enabled]",
                    "value" => "1",
                    "checked" => config_settings()->getParameter("tracing_enabled", 1)
                ]); ?>
            </td>
        </tr>
        <tr>
            <td><?php echo_html(text('ShowErrorDetails')); ?>:</td>
            <td>
                <?php checkbox([
                    "name" => "settings[show_message_details]",
                    "value" => "1",
                    "checked" => config_settings()->getParameter("show_message_details", 1)
                ]); ?>
            </td>
        </tr>
        <tr>
            <td><?php echo_html(text('ShowProgWarnings')); ?>:</td>
            <td>
                <?php checkbox([
                    "name" => "settings[show_prog_warning]",
                    "value" => "1",
                    "checked" => config_settings()->getParameter("show_prog_warning", 1)
                ]); ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?php echo_html(text('Domains')); ?>:<br>
                <?php
                textarea([
                    "name" => "settings[domains]",
                    "style" => "width: 300px;height: 150px",
                    "value" => implode("\n", config_settings()->getParameter("domains", []))
                ]);
                ?>
            </td>
        </tr>
    </table>

    <br>
    <br>

    <input type="submit" name="act" value="Save">

</form>

</body>
</html>



