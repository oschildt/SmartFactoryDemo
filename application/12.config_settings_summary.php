<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\config_settings;
use function SmartFactory\echo_html;
use function SmartFactory\text;
use function SmartFactory\session;

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

<h3>Server settings</h3>

<table>
    <tr>
        <td><?php echo_html(text('SmtpHost')); ?>*:</td>
        <td><?php echo_html(config_settings()->getParameter("smtp_server")); ?></td>
    </tr>
    <tr>
        <td><?php echo_html(text('SmtpPort')); ?>*:</td>
        <td><?php echo_html(config_settings()->getParameter("smtp_port")); ?></td>
    </tr>
    <tr>
        <td><?php echo_html(text('EnableTracing')); ?>:</td>
        <td><?php echo(config_settings()->getParameter("trace_programming_warnings") ? "1" : "0"); ?></td>
    </tr>
    <tr>
        <td><?php echo_html(text('DebugMode')); ?>:</td>
        <td><?php echo(config_settings()->getParameter("debug_mode") ? "1" : "0"); ?></td>
    </tr>
    <tr>
        <td colspan="2"><?php echo_html(text('Domains')); ?>:<br>
            <?php echo(implode("<br>", config_settings()->getParameter("domains", []))); ?></td>
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

<a href="12.config_settings_main.php">Main Settings</a><br>
<br>
<a href="12.config_settings_db.php">DB Settings</a>

</body>
</html>



