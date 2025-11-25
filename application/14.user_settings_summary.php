<?php
namespace MyApplication;

require "../vendor/autoload.php";

use SmartFactory\Interfaces\ILanguageManager;

use function SmartFactory\config_settings;
use function SmartFactory\singleton;
use function SmartFactory\user_settings;
use function SmartFactory\escape_html;
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>User Settings</title>

    <link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>User Settings: Summary</h2>

<?php
if (config_settings()->getParameter("db_password") == "") {
    echo "<h4 style='color: maroon'>Please ensure that you have created the demo database with the script 'database/create_database_mysql.sql' and adjust the DB password and other connection data in 'config/settings.json'!</h4>";
} else {
    user_settings()->setUserID(1);
    ?>
    
    <?php
    report_messages();
    ?>

    <h3>General Settings</h3>

    <table>
        <tr>
            <td>Language*:</td>
            <td><?php echo(escape_html(singleton(ILanguageManager::class)->getLanguageName(user_settings()->getParameter("language")))); ?></td>
        </tr>
        <tr>
            <td>Time zone*:</td>
            <td><?php echo(escape_html(user_settings()->getParameter("time_zone"))); ?></td>
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

            $colors = user_settings()->getParameter("user_colors", []);
            $txt = "";
            foreach($colors as $color)
            {
              $txt .= $options[$color] . ", ";
            }
            
            $txt = trim($txt, ", ");
            echo(escape_html($txt));
            ?>
            </td>
        </tr>
    </table>

    <h3>Forum Settings</h3>

    <table>
        <tr>
            <td>Status*:</td>
            <td><?php echo(escape_html(user_settings()->getParameter("status"))); ?></td>
        </tr>
        <tr>
            <td>Signature:</td>
            <td><?php echo(escape_html(user_settings()->getParameter("signature"))); ?></td>
        </tr>
        <tr>
            <td>Hide pictures:</td>
            <td><?php echo(user_settings()->getParameter("hide_pictures") ? "1" : "0"); ?></td>
        </tr>
        <tr>
            <td>Hide signatures:</td>
            <td><?php echo(user_settings()->getParameter("hide_signatures") ? "1" : "0"); ?></td>
        </tr>
    </table>

    <br>
    <br>

    <a href="14.user_settings_main.php">Main Settings</a><br>
    <br>
    <a href="14.user_settings_forum.php">Forum Settings</a>
    
    <?php
}
?>

</body>
</html>



