<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\config_settings;
use function SmartFactory\application_settings;
use function SmartFactory\escape_html;

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
            <p><a href="13.application_settings_main.php">Main settings</a> &middot; <a href="13.application_settings_hotel.php">Data exchange settings</a></p>
        </div>
        <div class="right_links">
            <p><a href="index.php">List of examples</a></p>
        </div>
        <div style="clear: both;"></div>

        <h1>Booking Settings: Summary</h1>

        <p>These examples demonstrate how to edit, validate, save, and display application settings stored in a database using <i>ApplicationSettingsManager</i>.</p>

        <?php
        if (config_settings()->getParameter("db_password") == "") {
            echo "<h4 style='color: maroon'>Please ensure that you have created the demo database with the script 'database/create_database_mysql.sql' and adjust the DB password and other connection data in 'config/settings.json'!</h4>";
        } else {
            ?>

            <?php
            report_messages();
            ?>

            <table class="example_table">
                <tr>
                    <th colspan="2"><?php echo (escape_html('General settings')); ?></th>
                </tr>
                <tr>
                    <td style="width: 200px;">Hotel Name*:</td>
                    <td><?php echo (escape_html(application_settings()->getParameter("hotel_name"))); ?></td>
                </tr>
                <tr>
                    <td>Hotel Email*:</td>
                    <td><?php echo (escape_html(application_settings()->getParameter("hotel_email"))); ?></td>
                </tr>
                <tr>
                    <td>Show Free Rooms:</td>
                    <td><?php echo (application_settings()->getParameter("show_free_rooms") ? "1" : "0"); ?></td>
                </tr>
                <tr>
                    <td>Colors:</td>
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

                        $colors = application_settings()->getParameter("colors", []);
                        $txt = "";
                        foreach ($colors as $color) {
                            $txt .= $options[$color] . ", ";
                        }

                        $txt = trim($txt, ", ");
                        echo (escape_html($txt));
                        ?>
                    </td>
                </tr>
            </table>

            <br><br>

            <table class="example_table">
                <tr>
                    <th colspan="2"><?php echo (escape_html('Data exchange settings')); ?></th>
                </tr>
                <tr>
                    <td style="width: 200px;">Booking Service URL*:</td>
                    <td><?php echo (escape_html(application_settings()->getParameter("booking_url"))); ?></td>
                </tr>
                <tr>
                    <td>Hotel-ID*:</td>
                    <td><?php echo (escape_html(application_settings()->getParameter("hotel_id"))); ?></td>
                </tr>
                <tr>
                    <td>Default Rate*:</td>
                    <td><?php echo (escape_html(application_settings()->getParameter("default_rate"))); ?></td>
                </tr>
            </table>


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
