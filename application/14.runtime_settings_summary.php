<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\config_settings;
use function SmartFactory\runtime_settings;
use function SmartFactory\echo_html;
use function SmartFactory\session;

session()->startSession();
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Booking Settings</title>

    <link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Booking Settings: Summary</h2>

<?php
if (config_settings()->getParameter("db_password") == "") {
    echo "<h4 style='color: maroon'>Please ensure that you have created the demo database with the script 'database/create_database_mysql.sql' and adjust the DB password and other connection data in 'config/settings.cfg'!</h4>";
} else {
    ?>
    
    <?php
    report_messages();
    ?>

    <h3>General Settings</h3>

    <table>
        <tr>
            <td>Hotel Name*:</td>
            <td><?php echo_html(runtime_settings()->getParameter("hotel_name")); ?></td>
        </tr>
        <tr>
            <td>Hotel Email*:</td>
            <td><?php echo_html(runtime_settings()->getParameter("hotel_email")); ?></td>
        </tr>
        <tr>
            <td>Show Free Rooms:</td>
            <td><?php echo(runtime_settings()->getParameter("show_free_rooms") ? "1" : "0"); ?></td>
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

            $colors = runtime_settings()->getParameter("colors", []);
            $txt = "";
            foreach($colors as $color)
            {
              $txt .= $options[$color] . ", ";
            }
            
            $txt = trim($txt, ", ");
            echo_html($txt);
            ?>
            </td>
        </tr>
    </table>

    <h3>Data Exchange Settings</h3>

    <table>
        <tr>
            <td>Booking Service URL*:</td>
            <td><?php echo_html(runtime_settings()->getParameter("booking_url")); ?></td>
        </tr>
        <tr>
            <td>Hotel-ID*:</td>
            <td><?php echo_html(runtime_settings()->getParameter("hotel_id")); ?></td>
        </tr>
        <tr>
            <td>Default Rate*:</td>
            <td><?php echo_html(runtime_settings()->getParameter("default_rate")); ?></td>
        </tr>
    </table>

    <br>
    <br>

    <a href="14.runtime_settings_main.php">Main Settings</a><br>
    <br>
    <a href="14.runtime_settings_hotel.php">Hotel Settings</a>
    
    <?php
}
?>

</body>
</html>



