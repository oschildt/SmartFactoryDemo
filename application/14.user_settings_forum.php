<?php
namespace MyApplication;

require "../vendor/autoload.php";

use SmartFactory\SmartException;
use SmartFactory\SmartExceptionCollection;

use function SmartFactory\config_settings;
use function SmartFactory\messenger;
use function SmartFactory\user_settings;
use function SmartFactory\text;
use function SmartFactory\input_text;
use function SmartFactory\checkbox;
?><!DOCTYPE html>
<html lang="en">

<head>
    <title>User Settings</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="css/examples.css" type="text/css" />
    <script src="js/examples.js" defer></script>
</head>

<body>

    <div class="header"></div>
    <div class="content">

        <div class="left_links">
            <p><a href="14.user_settings_main.php">Main settings</a> &middot; <a href="14.user_settings_summary.php">Summary</a></p>
        </div>
        <div class="right_links">
            <p><a href="index.php">List of examples</a></p>
        </div>
        <div style="clear: both;"></div>

        <h1>User Settings: Forum Settings</h1>

        <p>These examples demonstrate how to edit, validate, save, and display settings for an individual user using <i>UserSettingsManager</i>.</p>

        <?php
        function process_form()
        {
            if (empty($_REQUEST["act"])) {
                return;
            }

            user_settings()->setParameter("status", $_REQUEST["user_settings"]["status"] ?? "");
            user_settings()->setParameter("signature", $_REQUEST["user_settings"]["signature"] ?? "");

            user_settings()->setParameter("hide_pictures", empty($_REQUEST["user_settings"]["hide_pictures"]) ? 0 : 1);
            user_settings()->setParameter("hide_signatures", empty($_REQUEST["user_settings"]["hide_signatures"]) ? 0 : 1);

            user_settings()->validateSettings();

            user_settings()->saveSettings();

            messenger()->addInfoMessage(text("MsgSettingsSaved"));

            header("location: 14.user_settings_summary.php");
            exit();
        } // process_form
        ?>

        <?php
        if (config_settings()->getParameter("db_password") == "") {
            echo "<h4 style='color: maroon'>Please ensure that you have created the demo database with the script 'database/create_database_mysql.sql' and adjust the DB password and other connection data in 'config/settings.json'!</h4>";
        } else {
            try {
                user_settings()->setContext("forum_settings");
                user_settings()->setUserID(1);

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

            <form action="14.user_settings_forum.php" method="post" data-progress-message="Saving data ...">

                <table class="form_table">
                    <tr>
                        <td>Status*:</td>
                        <td>
                            <?php input_text([
                                "name" => "user_settings[status]",
                                "autocomplete" => "off",
                                "value" => user_settings()->getParameter("status", true)
                            ]); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Signature:</td>
                        <td>
                            <?php input_text([
                                "name" => "user_settings[signature]",
                                "autocomplete" => "off",
                                "value" => user_settings()->getParameter("signature", true)
                            ]); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Hide pictures:</td>
                        <td>
                            <?php checkbox([
                                "name" => "user_settings[hide_pictures]",
                                "value" => "1",
                                "checked" => user_settings()->getParameter("hide_pictures", true)
                            ]); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Hide signatures:</td>
                        <td>
                            <?php checkbox([
                                "name" => "user_settings[hide_signatures]",
                                "value" => "1",
                                "checked" => user_settings()->getParameter("hide_signatures", true)
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

            <?php
        }
        ?>

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
