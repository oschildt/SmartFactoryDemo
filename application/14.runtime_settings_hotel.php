<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\config_settings;
use function SmartFactory\runtime_settings;
use function SmartFactory\text;
use function SmartFactory\input_text;
use function SmartFactory\messenger;
use function SmartFactory\session;

session()->startSession();

runtime_settings()->setContext("data_exchange_settings");
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Booking Settings</title>

    <link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Booking Settings: Data Exchange Settings</h2>

<?php
function process_form()
{
    if (empty($_REQUEST["act"])) {
        return true;
    }
    
    runtime_settings()->setParameter("booking_url", $_REQUEST["booking_settings"]["booking_url"] ?? "");
    runtime_settings()->setParameter("hotel_id", $_REQUEST["booking_settings"]["hotel_id"] ?? "");
    runtime_settings()->setParameter("default_rate", $_REQUEST["booking_settings"]["default_rate"] ?? "");

    if (!runtime_settings()->validateSettings()) {
        return false;
    }
    
    runtime_settings()->saveSettings();
    
    messenger()->addInfoMessage(text("MsgSettingsSaved"));
    
    header("location: 14.runtime_settings_summary.php");
    exit();
} // process_form
?>

<?php
if (config_settings()->getParameter("db_password") == "") {
    echo "<h4 style='color: maroon'>Please ensure that you have created the demo database with the script 'database/create_database_mysql.sql' and adjust the DB password and other connection data in 'config/settings.json'!</h4>";
} else {
    try {
        process_form();
    } catch (\Exception $ex) {
        messenger()->addError($ex->getMessage());
    }
    ?>
    
    <?php
    report_messages();
    ?>

    <form action="14.runtime_settings_hotel.php" method="post">

        <table>
            <tr>
                <td>Booking Service URL*:</td>
                <td>
                    <?php input_text([
                        "name" => "booking_settings[booking_url]",
                        "autocomplete" => "off",
                        "value" => runtime_settings()->getParameter("booking_url")
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td>Hotel-ID*:</td>
                <td>
                    <?php input_text([
                        "name" => "booking_settings[hotel_id]",
                        "autocomplete" => "off",
                        "value" => runtime_settings()->getParameter("hotel_id")
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td>Default Rate*:</td>
                <td>
                    <?php input_text([
                        "name" => "booking_settings[default_rate]",
                        "autocomplete" => "off",
                        "value" => runtime_settings()->getParameter("default_rate")
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



