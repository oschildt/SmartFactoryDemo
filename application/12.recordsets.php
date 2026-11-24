<?php
namespace MyApplication;

require "../vendor/autoload.php";

use SmartFactory\DatabaseWorkers\DBWorker;

use function SmartFactory\config_settings;
use function SmartFactory\escape_html;
use function SmartFactory\input_hidden;
use function SmartFactory\input_text;
use function SmartFactory\textarea;
use function SmartFactory\text;
use function SmartFactory\timestamp;
use function SmartFactory\messenger;
?><!DOCTYPE html>
<html lang="en">

<head>
    <title>Recordsets</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="css/examples.css" type="text/css" />
    <script src="js/examples.js" defer></script>
</head>

<body>

    <div class="header"></div>
    <div class="content">

        <div class="left_links">
            <p><a href="12.recordsets_grid.php">Grid</a></p>
        </div>
        <div class="right_links">
            <p><a href="index.php">List of examples</a></p>
        </div>
        <div style="clear: both;"></div>

        <h1>Recordsets</h1>

        <p>These examples demonstrate how to load and save database records using <i>Recordset</i>, including
            multilingual page content and a grid of room prices.</p>

        <?php
        $id = $_REQUEST["page_id"] ?? "-1";

        /**
         * Loads the page list indexed by page identity and reports database errors to the messenger.
         *
         * @param array<int|string, array<string, mixed>> $page_list
         * Receives page names and types indexed by their database identities.
         *
         * @return void
         * Replaces the page list when the query succeeds.
         *
         * @author Oleg Schildt
         */
        function load_page_list(array &$page_list): void
        {
            try {
                $dbw = app_dbworker();

                $dbw->execute_query("select id, page_name, page_type from pages order by page_name");

                try {
                    $dbw->fetch_array($page_list, ["id"]);
                } finally {
                    $dbw->free_result();
                }
            } catch (\Exception $ex) {
                messenger()->addError($ex->getMessage());
            }
        } // load_page_list

        /**
         * Loads the requested page and its translations into the form data.
         *
         * Database errors are reported to the messenger. Requests without a page ID leave form data unchanged.
         *
         * @return void
         * Populates page_data and page_content in $_REQUEST; dates are loaded as Unix timestamps.
         *
         * @author Oleg Schildt
         */
        function load_page_data(): void
        {
            if (!empty($_REQUEST["page_id"])) {
                try {
                    $dbw = app_dbworker();

                    $dbw->prepare_query(
                        "select id, page_name, page_type, page_order, page_date from pages where id = :page_id"
                    );

                    try {
                        $dbw->execute_prepared_query(page_id: $_REQUEST["page_id"]);

                        $rows = [];

                        $dbw->fetch_array($rows);

                        $_REQUEST["page_data"] = $rows[0] ?? [];
                    } finally {
                        $dbw->free_prepared_query();
                    }

                    $dbw->prepare_query(
                        "select page_id, language_key, title, content from page_content where page_id = :page_id"
                    );

                    try {
                        $dbw->execute_prepared_query(page_id: $_REQUEST["page_id"]);

                        $_REQUEST["page_content"] = [];

                        $dbw->fetch_array($_REQUEST["page_content"], ["page_id", "language_key"]);
                    } finally {
                        $dbw->free_prepared_query();
                    }
                } catch (\Exception $ex) {
                    messenger()->addError($ex->getMessage());
                }
            }
        } // load_page_data

        /**
         * Validates and saves the submitted page and translations in one database transaction.
         *
         * New page identities replace temporary page-content keys after a successful commit.
         * The form's -1 marker is removed before saving so the database allocates a new identity.
         * Validation, database and rollback errors are reported to the messenger.
         *
         * @return bool
         * Returns true after committing both tables, or false if date validation or database operations fail.
         *
         * @author Oleg Schildt
         */
        function save_data(): bool
        {
            $dbw = null;
            $transaction_started = false;

            try {
                $tm = timestamp($_REQUEST["page_data"]["page_date"], text("DateTimeFormat"));

                if ($tm === "error") {
                    messenger()->addError(sprintf(
                        text("ErrDateTimeFormat"),
                        $_REQUEST["page_data"]["page_date"],
                        date(text("DateTimeFormat"), mktime(20, 44, 30, 11, 27, 2018))
                    ));

                    return false;
                }

                $dbw = app_dbworker();
                $page_data = $_REQUEST["page_data"];
                $page_data["page_date"] = $tm;
                $old_page_id = $_REQUEST["page_id"] ?? ($page_data["id"] ?? -1);

                // The form uses -1 for a new page; save_records() now preserves every supplied integer ID.
                if ((string) ($page_data["id"] ?? "") === "-1") {
                    unset($page_data["id"]);
                }

                $table_config = [
                    "table" => "pages",
                    "fields" => [
                        "id" => DBWorker::DB_NUMBER,
                        "page_name" => DBWorker::DB_STRING,
                        "page_type" => DBWorker::DB_STRING,
                        "page_order" => DBWorker::DB_NUMBER,
                        "page_date" => DBWorker::DB_DATETIME
                    ],
                    "array_dimension_keys" => [],
                    "record_key_fields" => ["id"],
                    "identity_field" => "id"
                ];

                $dbw->start_transaction();

                $transaction_started = true;

                $dbw->save_records($page_data, $table_config);

                // Translations must use the actual page identity rather than the temporary form key.
                $page_content = [$page_data["id"] => $_REQUEST["page_content"][$old_page_id] ?? []];
                $table_config = [
                    "table" => "page_content",
                    "fields" => [
                        "page_id" => DBWorker::DB_NUMBER,
                        "language_key" => DBWorker::DB_STRING,
                        "title" => DBWorker::DB_STRING,
                        "content" => DBWorker::DB_STRING
                    ],
                    "array_dimension_keys" => ["page_id", "language_key"],
                    "record_key_fields" => ["page_id", "language_key"]
                ];

                $dbw->save_records($page_content, $table_config);

                $dbw->commit_transaction();

                $_REQUEST["page_data"] = $page_data;
                $_REQUEST["page_content"] = $page_content;
            } catch (\Exception $ex) {
                if ($transaction_started) {
                    try {
                        $dbw->rollback_transaction();
                    } catch (\Exception $rollback_ex) {
                        messenger()->addError($rollback_ex->getMessage());
                    }
                }

                messenger()->addError($ex->getMessage());

                return false;
            }

            messenger()->addInfoMessage("Data saved successfully!");

            return true;
        } // save_data

        $page_list = [];
        load_page_list($page_list);

        if (!empty($_REQUEST["act"])) {
            if (save_data()) {
                header("Location: 12.recordsets.php?page_id=" . $_REQUEST["page_data"]["id"]);
                exit;
            }
        } else {
            load_page_data();
        }
        ?>

        <?php
        if (config_settings()->getParameter("db_password") == "") {
            echo "<h4 style='color: maroon'>Please ensure that you have created the demo database with the script 'database/create_database_mysql.sql' and adjust the DB password and other connection data in 'config/settings.json'!</h3>";
        } else {
            ?>

            <?php
            report_messages();
            ?>

            <h2>Pages</h2>

            <table class="example_table">
                <tr>
                    <th>id</th>
                    <th>page_name</th>
                    <th>page_type</th>
                    <th>&nbsp;</th>
                </tr>

                <?php foreach ($page_list as $page_id => $page_row): ?>
                    <tr>
                        <td><?php echo (escape_html($page_id ?? "")); ?></td>
                        <td><?php echo (escape_html($page_row["page_name"] ?? "")); ?></td>
                        <td><?php echo (escape_html($page_row["page_type"] ?? "")); ?></td>
                        <td><a href="12.recordsets.php?page_id=<?php echo (escape_html($page_id ?? "")); ?>">Edit</a></td>
                    </tr>
                <?php endforeach; ?>

            </table>

            <br><a href="12.recordsets.php">New page</a><br>

            <form action="12.recordsets.php" method="post" data-progress-message="Saving data ...">

                <h2>Basic properites</h2>

                id: <?php echo (escape_html($id)); ?>

                <?php input_hidden(["name" => "page_id", "value" => $id]); ?>
                <?php input_hidden(["name" => "page_data[id]", "value" => $id]); ?>

                <table class="form_table">
                    <tr>
                        <td>Page name*:</td>
                        <td><?php input_text(["name" => "page_data[page_name]", "autocomplete" => "off"]); ?></td>
                    </tr>
                    <tr>
                        <td>Page type*:</td>
                        <td><?php input_text(["name" => "page_data[page_type]", "autocomplete" => "off"]); ?></td>
                    </tr>
                    <tr>
                        <td>Page order:</td>
                        <td><?php input_text(["name" => "page_data[page_order]", "autocomplete" => "off"]); ?></td>
                    </tr>
                    <tr>
                        <td>Page time:</td>
                        <td><?php input_text([
                            "name" => "page_data[page_date]",
                            "autocomplete" => "off",
                            "formatter" => function ($val) {
                                            if (is_int($val)) {
                                                return date(text("DateTimeFormat"), $val);
                                            }

                                            return $val;
                                        }
                        ]);
                        ?>
                        </td>
                    </tr>
                </table>

                <h2>Language properites</h2>

                <?php foreach (["en", "de", "ru"] as $lkey): ?>

                    <h3>Language: <?php echo ($lkey); ?></h3>

                    <table class="form_table">
                        <tr>
                            <td>Page title:</td>
                            <td><?php input_text(["name" => "page_content[$id][$lkey][title]", "autocomplete" => "off"]); ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Page content:</td>
                            <td><?php textarea(["name" => "page_content[$id][$lkey][content]"]); ?>
                            </td>
                        </tr>
                    </table>

                <?php endforeach; ?>

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
