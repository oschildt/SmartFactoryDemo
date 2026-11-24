<?php
namespace MyApplication;

require "../vendor/autoload.php";

use SmartFactory\SmartException;
use SmartFactory\SmartExceptionCollection;

use function SmartFactory\config_settings;
use function SmartFactory\text;
use function SmartFactory\textarea;
use function SmartFactory\input_text;
use function SmartFactory\checkbox;
use function SmartFactory\messenger;
use function SmartFactory\escape_html;

config_settings()->setContext("server_settings");
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
            <p><a href="10.config_settings_db.php">Database settings</a> &middot; <a href="10.config_settings_summary.php">Summary</a></p>
        </div>
        <div class="right_links">
            <p><a href="index.php">List of examples</a></p>
        </div>
        <div style="clear: both;"></div>

        <h1>Config Settings: Main Settings</h1>

        <p>These examples demonstrate how to edit, validate, save, and display server and database configuration settings using <i>ConfigSettingsManager</i>.</p>

        <?php
        function process_form()
        {
            if (empty($_REQUEST["act"])) {
                return;
            }

            config_settings()->setParameter("smtp_server", $_REQUEST["settings"]["smtp_server"] ?? "");
            config_settings()->setParameter("smtp_port", $_REQUEST["settings"]["smtp_port"] ?? "");
            config_settings()->setParameter("trace_programming_warnings", empty($_REQUEST["settings"]["trace_programming_warnings"]) ? 0 : 1);
            config_settings()->setParameter("debug_mode", empty($_REQUEST["settings"]["debug_mode"]) ? 0 : 1);
            config_settings()->setParameter("domains", empty($_REQUEST["settings"]["domains"]) ? [] : preg_split("/[\n\r]+/", trim($_REQUEST["settings"]["domains"])));

            config_settings()->validateSettings();

            config_settings()->saveSettings();

            messenger()->addInfoMessage(text("MsgSettingsSaved"));

            header("location: 10.config_settings_summary.php");
            exit();
        } // process_form

        try {
            process_form();
        } catch (SmartExceptionCollection $ex) {
            messenger()->addErrorFromSmartException($ex);
        } catch (SmartException $ex) {
            messenger()->addErrorFromSmartException($ex);
        } catch (\Exception $ex) {
            messenger()->addError($ex->getMessage());
        }
        ?>

        <?php
        report_messages();
        ?>

        <form action="10.config_settings_main.php" method="post" data-progress-message="Saving data ...">

            <table class="form_table">
                <tr>
                    <td><?php echo (escape_html(text('SmtpHost'))); ?>*:</td>
                    <td>
                        <?php input_text([
                            "name" => "settings[smtp_server]",
                            "autocomplete" => "off",
                            "value" => config_settings()->getParameter("smtp_server", "localhost")
                        ]); ?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo (escape_html(text('SmtpPort'))); ?>:</td>
                    <td>
                        <?php input_text([
                            "name" => "settings[smtp_port]",
                            "autocomplete" => "off",
                            "value" => config_settings()->getParameter("smtp_port")
                        ]); ?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo (escape_html(text('EnableTracing'))); ?>:</td>
                    <td>
                        <?php checkbox([
                            "name" => "settings[trace_programming_warnings]",
                            "value" => "1",
                            "checked" => config_settings()->getParameter("trace_programming_warnings", 0)
                        ]); ?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo (escape_html(text('DebugMode'))); ?>:</td>
                    <td>
                        <?php checkbox([
                            "name" => "settings[debug_mode]",
                            "value" => "1",
                            "checked" => config_settings()->getParameter("debug_mode", 0)
                        ]); ?>
                    </td>
                </tr>
                <tr>
                    <td style="vertical-align: top;"><?php echo (escape_html(text('Domains'))); ?>:</td>
                    <td>
                        <?php
                        textarea([
                            "name" => "settings[domains]",
                            "value" => implode(PHP_EOL, config_settings()->getParameter("domains", []))
                        ]);
                        ?>
                    </td>
                </tr>
            </table>

            <br>
            <br>

            <div class="button_area">
                <input type="submit" name="act" value="Save">
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
