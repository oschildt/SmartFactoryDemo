<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\config_settings;
use function SmartFactory\runtime_settings;
use function SmartFactory\checkempty;
use function SmartFactory\input_text;
use function SmartFactory\checkbox;
use function SmartFactory\messenger;
use function SmartFactory\session;
use function SmartFactory\text;

session()->startSession();
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Booking Settings</title>

    <link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Booking Settings: General Settings</h2>

<?php
function process_form()
{
    if (empty($_REQUEST["act"])) {
        return true;
    }
    
    runtime_settings()->setParameter("hotel_name", checkempty($_REQUEST["booking_settings"]["hotel_name"]));
    runtime_settings()->setParameter("hotel_email", checkempty($_REQUEST["booking_settings"]["hotel_email"]));
    runtime_settings()->setParameter("show_free_rooms", empty($_REQUEST["booking_settings"]["show_free_rooms"]) ? 0 : 1);
    
    if (!runtime_settings()->validateSettings()) {
        return false;
    }
    
    if (!runtime_settings()->saveSettings()) {
        return false;
    }
    
    messenger()->setInfo(text("MsgSettingsSaved"));
    
    header("location: 14.runtime_settings_summary.php");
    exit();
} // process_form
?>

<?php
if (config_settings()->getParameter("db_password") == "") {
    echo "<h4 style='color: maroon'>Please ensure that you have created the demo database with the script 'database/create_database_mysql.sql' and adjust the DB password and other connection data in 'config/settings.cfg'!</h4>";
} else {
    runtime_settings()->setContext("general_settings");
    process_form();
    ?>
    
    <?php
    report_messages();
    ?>

    <form action="14.runtime_settings_main.php" method="post">

        <table>
            <tr>
                <td>Hotel Name*:</td>
                <td>
                    <?php input_text([
                        "name" => "booking_settings[hotel_name]",
                        "autocomplete" => "off",
                        "value" => runtime_settings()->getParameter("hotel_name")
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td>Hotel Email*:</td>
                <td>
                    <?php input_text([
                        "name" => "booking_settings[hotel_email]",
                        "autocomplete" => "off",
                        "value" => runtime_settings()->getParameter("hotel_email")
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td>Show Free Rooms:</td>
                <td>
                    <?php checkbox([
                        "name" => "booking_settings[show_free_rooms]",
                        "value" => "1",
                        "checked" => runtime_settings()->getParameter("show_free_rooms", 1)
                    ]); ?>
                </td>
            </tr>
        </table>

        <br>
        <br>

        <input type="submit" name="act" value="Save">

    </form>
    
    <?php
}
?>

</body>
</html>



