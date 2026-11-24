<?php
namespace MyApplication;

require "../vendor/autoload.php";

use SmartFactory\Interfaces\ILanguageManager;

use SmartFactory\SmartException;
use SmartFactory\SmartExceptionCollection;

use function SmartFactory\config_settings;
use function SmartFactory\messenger;
use function SmartFactory\singleton;
use function SmartFactory\text;
use function SmartFactory\user_settings;
use function SmartFactory\input_text;
use function SmartFactory\select;
?><!DOCTYPE html>
<html lang="en">

<head>
    <title>User Settings</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="css/examples.css" type="text/css" />
    <script src="js/examples.js" defer></script>
</head>

<body>

    <div class="header"></div>
    <div class="content">

        <div class="left_links">
            <p><a href="14.user_settings_forum.php">Forum settings</a> &middot; <a href="14.user_settings_summary.php">Summary</a></p>
        </div>
        <div class="right_links">
            <p><a href="index.php">List of examples</a></p>
        </div>
        <div style="clear: both;"></div>

        <h1>User Settings: General Settings</h1>

        <p>These examples demonstrate how to edit, validate, save, and display settings for an individual user using <i>UserSettingsManager</i>.</p>

        <?php
        $language_list = [];
        $language_list[""] = "-";
        singleton(ILanguageManager::class)->getLanguageList($language_list);

        function process_form()
        {
            if (empty($_REQUEST["act"])) {
                return;
            }

            user_settings()->setParameter("language", $_REQUEST["user_settings"]["language"] ?? "");
            user_settings()->setParameter("time_zone", $_REQUEST["user_settings"]["time_zone"] ?? "");
            user_settings()->setParameter("user_colors", empty($_REQUEST["user_settings"]["user_colors"]) ? [] : $_REQUEST["user_settings"]["user_colors"]);

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
                user_settings()->setContext("general_settings");
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

            <form action="14.user_settings_main.php" method="post" data-progress-message="Saving data ...">

                <table class="form_table">
                    <tr>
                        <td>Language*:</td>
                        <td>
                            <?php select([
                                "name" => "user_settings[language]",
                                "options" => $language_list,
                                "value" => user_settings()->getParameter("language")
                            ]); ?>
                        </td>
                    </tr>
                    <tr>
                        <td>Time zone*:</td>
                        <td>
                            <?php input_text([
                                "name" => "user_settings[time_zone]",
                                "autocomplete" => "off",
                                "value" => user_settings()->getParameter("time_zone")
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
                                "name" => "user_settings[user_colors][]",
                                "multiple" => "multiple",
                                "value" => user_settings()->getParameter("user_colors", []),
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
