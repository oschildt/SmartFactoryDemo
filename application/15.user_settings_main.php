<?php
namespace MyApplication;

require "../vendor/autoload.php";

use SmartFactory\Interfaces\ILanguageManager;

use function SmartFactory\config_settings;
use function SmartFactory\messenger;
use function SmartFactory\singleton;
use function SmartFactory\session;
use function SmartFactory\text;
use function SmartFactory\user_settings;
use function SmartFactory\checkempty;
use function SmartFactory\input_text;
use function SmartFactory\select;

session()->startSession();
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>User Settings</title>

    <link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>User Settings: General Settings</h2>

<?php
$language_list = [];
$language_list[""] = "-";
singleton(ILanguageManager::class)->getLanguageList($language_list);

function process_form()
{
    if (empty($_REQUEST["act"])) {
        return true;
    }
    
    user_settings()->setParameter("LANGUAGE", checkempty($_REQUEST["user_settings"]["LANGUAGE"]));
    user_settings()->setParameter("TIME_ZONE", checkempty($_REQUEST["user_settings"]["TIME_ZONE"]));
    
    if (!user_settings()->validateSettings()) {
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
    user_settings()->setContext("general_settings");
    process_form();
    ?>
    
    <?php
    report_messages();
    ?>

    <form action="15.user_settings_main.php" method="post">

        <table>
            <tr>
                <td>Language*:</td>
                <td>
                    <?php select([
                        "name" => "user_settings[LANGUAGE]",
                        "options" => $language_list,
                        "value" => user_settings()->getParameter("LANGUAGE")
                    ]); ?>
                </td>
            </tr>
            <tr>
                <td>Time zone*:</td>
                <td>
                    <?php input_text([
                        "name" => "user_settings[TIME_ZONE]",
                        "autocomplete" => "off",
                        "value" => user_settings()->getParameter("TIME_ZONE")
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



