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

    <link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>User Settings: Forum Settings</h2>

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

    <form action="14.user_settings_forum.php" method="post">

        <table>
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

        <input type="submit" name="act" value="Save">

    </form>
    
    <?php
}
?>

</body>
</html>



