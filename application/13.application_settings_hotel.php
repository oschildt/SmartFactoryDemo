<?php
namespace MyApplication;

require "../vendor/autoload.php";

use SmartFactory\SmartException;
use SmartFactory\SmartExceptionCollection;
use function SmartFactory\config_settings;
use function SmartFactory\application_settings;
use function SmartFactory\text;
use function SmartFactory\input_text;
use function SmartFactory\messenger;
?><!DOCTYPE html>
<html lang="en">

<head>
    <title>Booking Settings</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="css/examples.css" type="text/css" />
    <script src="js/examples.js" defer></script>
</head>

<body>

    <div class="header"></div>
    <div class="content">

        <div class="left_links">
            <p><a href="13.application_settings_main.php">Main settings</a> &middot; <a href="13.application_settings_summary.php">Summary</a></p>
        </div>
        <div class="right_links">
            <p><a href="index.php">List of examples</a></p>
        </div>
        <div style="clear: both;"></div>

        <h1>Booking Settings: Data Exchange Settings</h1>

        <p>These examples demonstrate how to edit, validate, save, and display application settings stored in a database using <i>ApplicationSettingsManager</i>.</p>

        <?php
        function process_form()
        {
            if (empty($_REQUEST["act"])) {
                return;
            }

            application_settings()->setParameter("booking_url", $_REQUEST["booking_settings"]["booking_url"] ?? "");
            application_settings()->setParameter("hotel_id", $_REQUEST["booking_settings"]["hotel_id"] ?? "");
            application_settings()->setParameter("default_rate", $_REQUEST["booking_settings"]["default_rate"] ?? "");

            application_settings()->setContext("data_exchange_settings");

            application_settings()->validateSettings();

            application_settings()->saveSettings();

            messenger()->addInfoMessage(text("MsgSettingsSaved"));

            header("location: 13.application_settings_summary.php");
            exit();
        } // process_form
        ?>

        <?php
        if (config_settings()->getParameter("db_password") == "") {
            echo "<h4 style='color: maroon'>Please ensure that you have created the demo database with the script 'database/create_database_mysql.sql' and adjust the DB password and other connection data in 'config/settings.json'!</h4>";
        } else {
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

            <form action="13.application_settings_hotel.php" method="post" data-progress-message="Saving data ...">

                <table class="form_table">
                    <tr>
                        <td>Booking Service URL*:</td>
                        <td>
                            <?php input_text([
                                "name" => "booking_settings[booking_url]",
                                "autocomplete" => "off",
                                "value" => application_settings()->getParameter("booking_url")
                            ]); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Hotel-ID*:</td>
                        <td>
                            <?php input_text([
                                "name" => "booking_settings[hotel_id]",
                                "autocomplete" => "off",
                                "value" => application_settings()->getParameter("hotel_id")
                            ]); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Default Rate*:</td>
                        <td>
                            <?php input_text([
                                "name" => "booking_settings[default_rate]",
                                "autocomplete" => "off",
                                "value" => application_settings()->getParameter("default_rate")
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
