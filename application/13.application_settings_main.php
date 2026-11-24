<?php
namespace MyApplication;

require "../vendor/autoload.php";

use SmartFactory\SmartException;
use SmartFactory\SmartExceptionCollection;
use function SmartFactory\config_settings;
use function SmartFactory\application_settings;
use function SmartFactory\input_text;
use function SmartFactory\checkbox;
use function SmartFactory\select;
use function SmartFactory\messenger;
use function SmartFactory\text;
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
            <p><a href="13.application_settings_hotel.php">Data exchange settings</a> &middot; <a href="13.application_settings_summary.php">Summary</a></p>
        </div>
        <div class="right_links">
            <p><a href="index.php">List of examples</a></p>
        </div>
        <div style="clear: both;"></div>

        <h1>Booking Settings: General Settings</h1>

        <p>These examples demonstrate how to edit, validate, save, and display application settings stored in a database using <i>ApplicationSettingsManager</i>.</p>

        <?php
        function process_form()
        {
            if (empty($_REQUEST["act"])) {
                return;
            }

            application_settings()->setParameter("hotel_name", $_REQUEST["booking_settings"]["hotel_name"] ?? "");
            application_settings()->setParameter("hotel_email", $_REQUEST["booking_settings"]["hotel_email"] ?? "");
            application_settings()->setParameter("show_free_rooms", empty($_REQUEST["booking_settings"]["show_free_rooms"]) ? 0 : 1);
            application_settings()->setParameter("colors", empty($_REQUEST["booking_settings"]["colors"]) ? [] : $_REQUEST["booking_settings"]["colors"]);

            application_settings()->setContext("general_settings");

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

            <form action="13.application_settings_main.php" method="post" data-progress-message="Saving data ...">

                <table class="form_table">
                    <tr>
                        <td>Hotel Name*:</td>
                        <td>
                            <?php input_text([
                                "name" => "booking_settings[hotel_name]",
                                "autocomplete" => "off",
                                "value" => application_settings()->getParameter("hotel_name")
                            ]); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Hotel Email*:</td>
                        <td>
                            <?php input_text([
                                "name" => "booking_settings[hotel_email]",
                                "autocomplete" => "off",
                                "value" => application_settings()->getParameter("hotel_email")
                            ]); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Show Free Rooms:</td>
                        <td>
                            <?php checkbox([
                                "name" => "booking_settings[show_free_rooms]",
                                "value" => "1",
                                "checked" => application_settings()->getParameter("show_free_rooms", 1)
                            ]); ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="vertical-align: top;">Colors:</td>
                        <td>
                            <?php
                            $options = [
                                "yellow" => "Yellow",
                                "blue" => "Blue",
                                "red" => "Red",
                                "brown" => "Brown",
                                "black" => "Black",
                                "white" => "White",
                                "green" => "Green"
                            ];

                            select([
                                "name" => "booking_settings[colors][]",
                                "multiple" => "multiple",
                                "value" => application_settings()->getParameter("colors", []),
                                "style" => "height: 200px",
                                "options" => $options
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
