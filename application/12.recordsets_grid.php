<?php
namespace MyApplication;

require "../vendor/autoload.php";

use SmartFactory\DatabaseWorkers\DBWorker;

use function SmartFactory\config_settings;
use function SmartFactory\escape_html;
use function SmartFactory\input_text;
use function SmartFactory\format_number;
use function SmartFactory\string_to_number;
use function SmartFactory\text;
use function SmartFactory\messenger;
?><!DOCTYPE html>
<html lang="en">

<head>
    <title>Recordsets - Grid</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="css/examples.css" type="text/css" />
    <script src="js/examples.js" defer></script>
</head>

<body>

    <div class="header"></div>
    <div class="content">

        <div class="left_links">
            <p><a href="12.recordsets.php">Recordsets</a></p>
        </div>
        <div class="right_links">
            <p><a href="index.php">List of examples</a></p>
        </div>
        <div style="clear: both;"></div>

        <h1>Recordsets - Grid</h1>

        <p>These examples demonstrate how to load and save database records using <i>Recordset</i>, including multilingual page content and a grid of room prices.</p>

        <?php
        /**
         * Loads room prices for the current date range into the grid and reports database errors.
         *
         * @param int $cnt
         * Receives the number of fetched rows, including zero when the query has no results.
         *
         * @return void
         * Populates $_REQUEST["room_prices"] indexed by room and date timestamp.
         *
         * @author Oleg Schildt
         */
        function load_data(int &$cnt): void
        {
            try {
                $dbw = app_dbworker();
                $now = mktime(0, 0, 0, date("m"), date("d"), date("Y"));

                $dbw->prepare_query(
                    "select room, dt, price from room_prices where dt between :date_from and :date_to order by room, dt"
                );

                try {
                    $dbw->execute_prepared_query(
                        date_from: $dbw->format_date_bind($now),
                        date_to: $dbw->format_date_bind($now + 7 * 24 * 3600)
                    );

                    $_REQUEST["room_prices"] = [];
                    $cnt = $dbw->fetch_array($_REQUEST["room_prices"], ["room", "dt"]);
                } finally {
                    $dbw->free_prepared_query();
                }
            } catch (\Exception $ex) {
                messenger()->addError($ex->getMessage());
            }
        } // load_data

        /**
         * Saves submitted room prices using the room and date dimensions as record keys.
         *
         * Database errors are reported to the messenger. Dates in the request array are Unix timestamps.
         *
         * @return bool
         * Returns true when all submitted prices were saved, or false if a database operation fails.
         *
         * @author Oleg Schildt
         */
        function save_data(): bool
        {
            try {
                $dbw = app_dbworker();
                $table_config = [
                    "table" => "room_prices",
                    "fields" => [
                        "room" => DBWorker::DB_STRING,
                        "dt" => DBWorker::DB_DATE,
                        "price" => DBWorker::DB_NUMBER
                    ],
                    "array_dimension_keys" => ["room", "dt"],
                    "record_key_fields" => ["room", "dt"]
                ];

                $dbw->save_records($_REQUEST["room_prices"], $table_config);

                messenger()->addInfoMessage("Data saved successfully!");
            } catch (\Exception $ex) {
                messenger()->addError($ex->getMessage());

                return false;
            }

            return true;
        } // save_data

        $cnt = 0;

        if (!empty($_REQUEST["act"])) {
            if (save_data()) {
                header("Location: 12.recordsets_grid.php");
                exit();
            }
        } else {
            load_data($cnt);
        }
        ?>

        <?php
        if (config_settings()->getParameter("db_password") == "") {
            echo "<h4 style='color: maroon'>Please ensure that you have created the demo database with the script 'database/create_database_mysql.sql' and adjust the DB password and other connection data in 'config/settings.json'!</h4>";
        } else {
            ?>

            <?php
            report_messages();
            ?>

            <form action="12.recordsets_grid.php" method="post" data-progress-message="Saving data ...">

                <h2>Rooms prices</h2>

                <p>Actual count: <?php echo ($cnt); ?></p>

                <?php
                $now = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
                $rooms = ["single_room", "double_room", "suite", "suite_delux"];
                ?>

                <table class="form_table">
                    <tr>
                        <th>Room</th>
                        <?php for ($i = 0; $i < 7; $i++): ?>
                            <th><?php echo (escape_html(date(text("DateFormat"), $now + $i * 24 * 3600))); ?></th>
                        <?php endfor; ?>
                    </tr>

                    <?php foreach ($rooms as $room): ?>
                        <tr>
                            <td style="white-space: nowrap;"><?php echo (escape_html($room)); ?></td>

                            <?php for ($i = 0; $i < 7; $i++): ?>
                                <td>
                                    <?php input_text([
                                        "name" => "room_prices[$room][" . ($now + $i * 24 * 3600) . "][price]",
                                        "style" => "width: 100%; text-align: right",
                                        "formatter" => function ($val) {
                                                                return format_number(string_to_number($val), 2);
                                                            }
                                    ]); ?>
                                </td>
                            <?php endfor; ?>
                        </tr>

                        </tr>
                    <?php endforeach; ?>

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
