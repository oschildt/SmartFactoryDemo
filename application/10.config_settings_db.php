<?php
namespace MyApplication;

require "../vendor/autoload.php";

use SmartFactory\SmartException;
use SmartFactory\SmartExceptionCollection;

use function SmartFactory\config_settings;
use function SmartFactory\text;
use function SmartFactory\input_text;
use function SmartFactory\input_password;
use function SmartFactory\messenger;
use function SmartFactory\escape_html;

config_settings()->setContext("database_settings");
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
            <p><a href="10.config_settings_main.php">Main settings</a> &middot; <a href="10.config_settings_summary.php">Summary</a></p>
        </div>
        <div class="right_links">
            <p><a href="index.php">List of examples</a></p>
        </div>
        <div style="clear: both;"></div>

        <h1>Config Settings: Database connection</h1>

        <p>These examples demonstrate how to edit, validate, save, and display server and database configuration settings using <i>ConfigSettingsManager</i>.</p>

        <?php
        function process_form()
        {
            if (empty($_REQUEST["act"])) {
                return;
            }

            config_settings()->setParameter("db_type", $_REQUEST["settings"]["db_type"] ?? "");
            config_settings()->setParameter("db_server", $_REQUEST["settings"]["db_server"] ?? "");
            config_settings()->setParameter("db_name", $_REQUEST["settings"]["db_name"] ?? "");
            config_settings()->setParameter("db_user", $_REQUEST["settings"]["db_user"] ?? "");
            if (!empty($_REQUEST["settings"]["db_password"])) {
                config_settings()->setParameter("db_password", $_REQUEST["settings"]["db_password"]);
            }
            config_settings()->setParameter("db_prefix", $_REQUEST["settings"]["db_prefix"] ?? "");

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

        <form action="10.config_settings_db.php" method="post" data-progress-message="Saving data ...">

            <table class="form_table">
                <tr>
                    <td><?php echo (escape_html(text('DatabaseType'))); ?>*:</td>
                    <td>
                        <?php input_text([
                            "name" => "settings[db_type]",
                            "autocomplete" => "off",
                            "value" => config_settings()->getParameter("db_type", "MySQL")
                        ]); ?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo (escape_html(text('DatabaseServer'))); ?>*:</td>
                    <td>
                        <?php input_text([
                            "name" => "settings[db_server]",
                            "autocomplete" => "off",
                            "value" => config_settings()->getParameter("db_server", "localhost")
                        ]); ?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo (escape_html(text('DatabaseName'))); ?>*:</td>
                    <td>
                        <?php input_text([
                            "name" => "settings[db_name]",
                            "autocomplete" => "off",
                            "value" => config_settings()->getParameter("db_name")
                        ]); ?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo (escape_html(text('DatabaseUser'))); ?>*:</td>
                    <td>
                        <?php input_text([
                            "name" => "settings[db_user]",
                            "autocomplete" => "off",
                            "value" => config_settings()->getParameter("db_user")
                        ]); ?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo (escape_html(text('DatabasePassword'))); ?>*:</td>
                    <td>
                        <?php input_password([
                            "name" => "settings[db_password]",
                            "autocomplete" => "off"
                        ]); ?>
                    </td>
                </tr>
                <tr>
                    <td><?php echo (escape_html(text('DatabaseTablePrefix'))); ?>*:</td>
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
