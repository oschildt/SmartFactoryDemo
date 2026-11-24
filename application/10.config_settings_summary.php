<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\config_settings;
use function SmartFactory\text;
use function SmartFactory\escape_html;

?><!DOCTYPE html>
<html lang="en">

<head>
    <title>Config Settings</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="css/examples.css" type="text/css" />
    <script src="js/examples.js" defer></script>
</head>

<body>

    <div class="header"></div>
    <div class="content">

        <div class="left_links">
            <p><a href="10.config_settings_main.php">Main settings</a> &middot; <a
                    href="10.config_settings_db.php">Database settings</a></p>
        </div>
        <div class="right_links">
            <p><a href="index.php">List of examples</a></p>
        </div>
        <div style="clear: both;"></div>

        <h1>Config Settings: Summary</h1>

        <p>These examples demonstrate how to edit, validate, save, and display server and database configuration
            settings using <i>ConfigSettingsManager</i>.</p>

        <?php
        report_messages();
        ?>

        <table class="example_table">
            <tr>
                <th colspan="2"><?php echo (escape_html('Server settings')); ?></th>
            </tr>
            <tr>
                <td style="width: 200px"><?php echo (escape_html(text('SmtpHost'))); ?>*:</td>
                <td><?php echo (escape_html(config_settings()->getParameter("smtp_server"))); ?></td>
            </tr>
            <tr>
                <td><?php echo (escape_html(text('SmtpPort'))); ?>*:</td>
                <td><?php echo (escape_html(config_settings()->getParameter("smtp_port"))); ?></td>
            </tr>
            <tr>
                <td><?php echo (escape_html(text('EnableTracing'))); ?>:</td>
                <td><?php echo (escape_html(config_settings()->getParameter("trace_programming_warnings") ? "1" : "0")); ?>
                </td>
            </tr>
            <tr>
                <td><?php echo (escape_html(text('DebugMode'))); ?>:</td>
                <td><?php echo (escape_html(config_settings()->getParameter("debug_mode") ? "1" : "0")); ?></td>
            </tr>
            <tr>
                <td><?php echo (escape_html(text('Domains'))); ?>:</td>
                <td><?php echo (escape_html(implode(",  ", config_settings()->getParameter("domains", [])))); ?></td>
            </tr>
        </table>

        <br><br>

        <table class="example_table">
            <tr>
                <th colspan="2"><?php echo (escape_html('Database settings')); ?></th>
            </tr>
            <tr>
                <td style="width: 200px"><?php echo (escape_html(text('DatabaseType'))); ?>*:</td>
                <td><?php echo (escape_html(config_settings()->getParameter("db_type"))); ?></td>
            </tr>
            <tr>
                <td><?php echo (escape_html(text('DatabaseServer'))); ?>*:</td>
                <td><?php echo (escape_html(config_settings()->getParameter("db_server"))); ?></td>
            </tr>
            <tr>
                <td><?php echo (escape_html(text('DatabaseName'))); ?>*:</td>
                <td><?php echo (escape_html(config_settings()->getParameter("db_name"))); ?></td>
            </tr>
            <tr>
                <td><?php echo (escape_html(text('DatabaseUser'))); ?>*:</td>
                <td><?php echo (escape_html(config_settings()->getParameter("db_user"))); ?></td>
            </tr>
            <tr>
                <td><?php echo (escape_html(text('DatabasePassword'))); ?>*:</td>
                <td><?php echo (escape_html(config_settings()->getParameter("db_password"))); ?></td>
            </tr>
            <tr>
                <td><?php echo (escape_html(text('DatabaseTablePrefix'))); ?>*:</td>
                <td><?php echo (escape_html(config_settings()->getParameter("db_prefix"))); ?></td>
            </tr>
        </table>


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
