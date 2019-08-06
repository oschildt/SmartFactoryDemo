<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\config_settings;
use function SmartFactory\messenger;
use function SmartFactory\session;
use function SmartFactory\user_settings;
use function SmartFactory\checkempty;
use function SmartFactory\text;
use function SmartFactory\input_text;
use function SmartFactory\checkbox;

session()->startSession();
user_settings()->setUserID(1);
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>User Settings</title>

    <link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>User Settings: Forum Settings</h2>

<?php
function process_form()
{
    if (empty($_REQUEST["act"])) {
        return true;
    }
    
    user_settings()->setParameter("STATUS", checkempty($_REQUEST["user_settings"]["STATUS"]));
    user_settings()->setParameter("SIGNATURE", checkempty($_REQUEST["user_settings"]["SIGNATURE"]));
    
    user_settings()->setParameter("HIDE_PICTURES", empty($_REQUEST["user_settings"]["HIDE_PICTURES"]) ? 0 : 1);
    user_settings()->setParameter("HIDE_SIGNATURES", empty($_REQUEST["user_settings"]["HIDE_SIGNATURES"]) ? 0 : 1);
    
    if (!user_settings()->validateSettings("forum_settings")) {
        return false;
    }
    
    if (!user_settings()->saveSettings()) {
        return false;
    }
    
    messenger()->setInfo(text("MsgSettingsSaved"));
    
    header("location: 15.user_settings_summary.php");
    exit();
} // process_form
?>

<?php
if (config_settings()->getParameter("db_password") == "") {
    echo "<h4 style='color: maroon'>Please ensure that you have created the demo database with the script 'database/create_database_mysql.sql' and adjust the DB password and other connection data in 'config/settings.cfg'!</h4>";
} else {
    user_settings()->setContext("forum_settings");
    process_form();
    ?>
    
    <?php
    report_messages();
    ?>

    <form action="15.user_settings_forum.php" method="post">

        <table>
            <tr>
                <td>Status*:</td>
                <td>
                    <?php input_text([
                        "name" => "user_settings[STATUS]",
                        "autocomplete" => "off",
                        "value" => user_settings()->getParameter("STATUS", true)
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td>Signature:</td>
                <td>
                    <?php input_text([
                        "name" => "user_settings[SIGNATURE]",
                        "autocomplete" => "off",
                        "value" => user_settings()->getParameter("SIGNATURE", true)
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td>Hide pictures:</td>
                <td>
                    <?php checkbox([
                        "name" => "user_settings[HIDE_PICTURES]",
                        "value" => "1",
                        "checked" => user_settings()->getParameter("HIDE_PICTURES", true, 0)
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td>Hide signatures:</td>
                <td>
                    <?php checkbox([
                        "name" => "user_settings[HIDE_SIGNATURES]",
                        "value" => "1",
                        "checked" => user_settings()->getParameter("HIDE_SIGNATURES", true, 0)
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



