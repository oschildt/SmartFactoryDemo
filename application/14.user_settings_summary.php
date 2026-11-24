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
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="css/examples.css" type="text/css" />
    <script src="js/examples.js" defer></script>
</head>

<body>

    <div class="header"></div>
    <div class="content">

        <div class="left_links">
            <p><a href="14.user_settings_main.php">Main settings</a> &middot; <a href="14.user_settings_forum.php">Forum
                    settings</a></p>
        </div>
        <div class="right_links">
            <p><a href="index.php">List of examples</a></p>
        </div>
        <div style="clear: both;"></div>

        <h1>User Settings: Summary</h1>

        <p>These examples demonstrate how to edit, validate, save, and display settings for an individual user using
            <i>UserSettingsManager</i>.
        </p>

        <?php
        if (config_settings()->getParameter("db_password") == "") {
            echo "<h4 style='color: maroon'>Please ensure that you have created the demo database with the script 'database/create_database_mysql.sql' and adjust the DB password and other connection data in 'config/settings.json'!</h4>";
        } else {
            user_settings()->setUserID(1);
            ?>

            <?php
            report_messages();
            ?>

            <table class="example_table">
                <tr>
                    <th colspan="2"><?php echo (escape_html('General settings')); ?></th>
                </tr>
                <tr>
                    <td style="width: 200px;">Language*:</td>
                    <td><?php echo (escape_html(singleton(ILanguageManager::class)->getLanguageName(user_settings()->getParameter("language")))); ?>
                    </td>
                </tr>
                <tr>
                    <td>Time zone*:</td>
                    <td><?php echo (escape_html(user_settings()->getParameter("time_zone"))); ?></td>
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
                    <th colspan="2"><?php echo (escape_html('Forum settings')); ?></th>
                </tr>
                <tr>
                    <td style="width: 200px;">Status*:</td>
                    <td><?php echo (escape_html(user_settings()->getParameter("status"))); ?></td>
                </tr>
                <tr>
                    <td>Signature:</td>
                    <td><?php echo (escape_html(user_settings()->getParameter("signature"))); ?></td>
                </tr>
                <tr>
                    <td>Hide pictures:</td>
                    <td><?php echo (user_settings()->getParameter("hide_pictures") ? "1" : "0"); ?></td>
                </tr>
                <tr>
                    <td>Hide signatures:</td>
                    <td><?php echo (user_settings()->getParameter("hide_signatures") ? "1" : "0"); ?></td>
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
