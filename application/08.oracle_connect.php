<?php
namespace MyApplication;

require "../vendor/autoload.php";

use SmartFactory\DatabaseWorkers\DBWorker;

use function SmartFactory\dbworker;
use function SmartFactory\escape_html;
use function SmartFactory\messenger;

?><!DOCTYPE html>
<html lang="en">

<head>
    <title>Oracle connection</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="css/examples.css" type="text/css" />
    <script src="js/examples.js" defer></script>
</head>

<body>

    <div class="header"></div>
    <div class="content">

        <div class="left_links">
            <p><a href="08.mysql_connect.php">MySQL</a> &middot; <a href="08.mssql_connect.php">MSSQL</a> &middot; <a href="08.postgres_connect.php">PostgreSQL</a></p>
        </div>
        <div class="right_links">
            <p><a href="index.php">List of examples</a></p>
        </div>
        <div style="clear: both;"></div>

        <h1>Oracle connection</h1>

        <p>These examples demonstrate database connections, queries, prepared statements, transactions, stored procedures, and large data handling with MSSQL, MySQL, Oracle, and PostgreSQL.</p>

        <?php
        function test_database(): void
        {
            $connection_parameters = [
                "db_type" => "Oracle",
                "db_server" => "localhost:1521/orcl",
                "db_name" => "framework_demo",
                "db_user" => "framework_demo",
                "db_password" => "",
                "autoconnect" => true
            ];

            if (empty($connection_parameters["db_password"])) {
                throw new \Exception("DB password is not set!", 100);
            }

            $dbw = dbworker($connection_parameters);

            echo "<h2>Simple query</h2>";

            $dbw->execute_query("select first_name, last_name, salary, birth_date, last_login from users");

            echo "<table class=\"example_table\">";

            echo "<tr>";
            echo "<th>First name</th>";
            echo "<th>Last name</th>";
            echo "<th>Salary</th>";
            echo "<th>Birth date</th>";
            echo "<th>Last login</th>";
            echo "<th>Birth date (timestamp)</th>";
            echo "<th>Last login (timestamp)</th>";
            echo "</tr>";

            while ($dbw->fetch_row()) {
                echo "<tr>";
                echo "<td>" . escape_html($dbw->field_by_name("first_name")) . "</td>";
                echo "<td>" . escape_html($dbw->field_by_name("last_name")) . "</td>";
                echo "<td>" . escape_html($dbw->field_by_name("salary")) . "</td>";
                echo "<td>" . escape_html($dbw->field_by_name("birth_date")) . "</td>";
                echo "<td>" . escape_html($dbw->field_by_name("last_login")) . "</td>";
                echo "<td>" . escape_html($dbw->field_by_name("birth_date", DBWorker::DB_DATE)) . "</td>";
                echo "<td>" . escape_html($dbw->field_by_name("last_login", DBWorker::DB_DATETIME)) . "</td>";
                echo "</tr>";
            }

            echo "</table>";

            $dbw->free_result();

            echo "<h2>Fetch array</h2>";

            $dbw->execute_query("select first_name, last_name, salary, birth_date, last_login from users");

            $rows = [];

            if ($dbw->fetch_array($rows)) {
                echo "<pre>";

                print_r($rows);

                echo "</pre>";
            }

            $dbw->free_result();

            echo "<h2>Fetch array (as is)</h2>";

            $dbw->execute_query("select first_name, last_name, salary, birth_date, last_login from users");

            $rows = [];

            if ($dbw->fetch_array($rows, [], true)) {
                echo "<pre>";

                print_r($rows);

                echo "</pre>";
            }

            $dbw->free_result();

            echo "<h2>Fetch array with dimensions [page_id, language_key]</h2>";

            $dbw->execute_query("select page_id, language_key, title, content from page_content");

            $rows = [];

            if ($dbw->fetch_array($rows, ["page_id", "language_key"])) {
                echo "<pre>";

                print_r($rows);

                echo "</pre>";
            }

            $dbw->free_result();

            echo "<h2>Fetch array with dimensions [page_id, language_key] with helper function</h2>";

            $rows = [];

            if ($dbw->query_to_array($rows, ["page_id", "language_key"], false, "select page_id, language_key, title, content from page_content")) {
                echo "<pre>";

                print_r($rows);

                echo "</pre>";
            }

            echo "<h2>Prepared query (0, 1)</h2>";

            $dbw->prepare_query("select first_name, last_name, salary from users where salary > ? and department_id = ?");

            $dbw->execute_prepared_query(0, 1);

            while ($dbw->fetch_row()) {
                echo $dbw->field_by_name("first_name") . " " . $dbw->field_by_name("last_name") . " " . $dbw->field_by_name("salary") . "<br>";
            }

            $dbw->free_result();

            echo "<h2>Prepared query (0, 2)</h2>";

            $dbw->execute_prepared_query(0, 2);

            while ($dbw->fetch_row()) {
                echo $dbw->field_by_name("first_name") . " " . $dbw->field_by_name("last_name") . " " . $dbw->field_by_name("salary") . "<br>";
            }

            $dbw->free_result();

            $dbw->free_prepared_query();

            echo "<h2>Prepared query, named parameter used in two positions</h2>";

            $dbw->prepare_query("select first_name, last_name, salary from users where first_name = :name or last_name = :name");

            echo "<h2>Called for John</h2>";

            $dbw->execute_prepared_query(name: 'John');

            while ($dbw->fetch_row()) {
                echo $dbw->field_by_name("first_name") . " " . $dbw->field_by_name("last_name") . "<br>";
            }

            $dbw->free_result();

            echo "<h2>Called for Smith</h2>";

            $dbw->execute_prepared_query(["name" => 'Smith']);

            while ($dbw->fetch_row()) {
                echo $dbw->field_by_name("first_name") . " " . $dbw->field_by_name("last_name") . "<br>";
            }

            $dbw->free_result();

            $dbw->free_prepared_query();

            echo "<h2>Prepared query (0, 1) with helper function</h2>";

            $dbw->prepare_query("select first_name, last_name, salary from users where salary > ? and department_id = ?");

            $rows = [];

            if ($dbw->prepared_query_to_array($rows, [], false, 0, 1)) {
                echo "<pre>";

                print_r($rows);

                echo "</pre>";
            }

            echo "<h2>Prepared DML query</h2>";

            echo "<p>Increase salary by 100</p>";

            $dbw->prepare_query("update users set salary = salary + ? where department_id = ?");

            $dbw->execute_prepared_query(100, 2);

            $affected_count = $dbw->affected_count();

            echo "<p>Affected count: $affected_count</p>";

            $dbw->free_prepared_query();

            echo "Done.";

            echo "<h2>Checking DML results</h2>";

            $dbw->execute_query("select first_name, last_name, salary from users where department_id = 2");

            while ($dbw->fetch_row()) {
                echo $dbw->field_by_name("first_name") . " " . $dbw->field_by_name("last_name") . " " . $dbw->field_by_name("salary") . "<br>";
            }

            $dbw->free_result();

            echo "<h2>Inserting new row</h2>";

            $dbw->execute_query("insert into users (first_name, last_name, salary, department_id) values ('John', 'Smith', 1000, 2)");

            $affected_count = $dbw->affected_count();

            echo "<p>Affected count: $affected_count</p>";

            $lid = $dbw->insert_id();

            echo "Done. New id: " . $lid . "<br>";

            echo "<h2>Deleting inserted row</h2>";

            $dbw->execute_query("delete from users where id = " . $dbw->escape($lid));

            $affected_count = $dbw->affected_count();

            echo "<p>Affected count: $affected_count</p>";

            echo "<h2>Inserting new row prepared</h2>";

            $dbw->prepare_query("insert into users (first_name, last_name, salary, department_id) values (?, ?, ?, 2)");

            $dbw->execute_prepared_query('John', 'Smith', 1000);

            $affected_count = $dbw->affected_count();

            echo "<p>Affected count: $affected_count</p>";

            $lid = $dbw->insert_id();

            $dbw->free_prepared_query();

            echo "Done. New id from prepared: " . $lid . "<br>";

            echo "<h2>Deleting inserted row</h2>";

            $dbw->execute_query("delete from users where id = " . $dbw->escape($lid));

            $affected_count = $dbw->affected_count();

            echo "<p>Affected count: $affected_count</p>";

            echo "<h2>Prepared query to array</h2>";

            $dbw->prepare_query("select page_id, language_key, title, content from page_content where page_id > ?");

            $dbw->execute_prepared_query(0);

            $rows = [];

            if ($dbw->fetch_array($rows)) {
                echo "<pre>";

                print_r($rows);

                echo "</pre>";
            }

            $dbw->free_result();

            echo "<h2>Prepared query to array with dimensions [page_id, language_key]</h2>";

            $dbw->execute_prepared_query(0);

            $rows = [];

            if ($dbw->fetch_array($rows, ["page_id", "language_key"])) {
                echo "<pre>";

                print_r($rows);

                echo "</pre>";
            }

            $dbw->free_result();

            $dbw->free_prepared_query();

            echo "<h2>Streaming large data to DB</h2>";

            $stream = fopen(approot() . "resources/large_binary.jpg", "rb");

            $dbw->stream_long_data("update large_data set blob_data = ? where id = 1", $stream);
            fclose($stream);

            echo "Binary written.<br>";

            $stream = fopen(approot() . "resources/large_text.txt", "rb");

            $dbw->stream_long_data("update large_data set text_data = ? where id = 1", $stream, DBWorker::DB_STRING);
            fclose($stream);

            echo "Text written.<br>";

            $dbw->execute_query("select length(text_data) tds, length(blob_data) bds from large_data where id = 1");

            while ($dbw->fetch_row()) {
                echo "text_data size: " . $dbw->field_by_name("tds") . "<br>";
                echo "blob_data size: " . $dbw->field_by_name("bds") . "<br>";
            }

            $dbw->free_result();

            echo "<h2>Reading large data from DB</h2>";

            $dbw->execute_query("select text_data, blob_data from large_data where id = 1");

            if ($dbw->fetch_row()) {
                $output = fopen(approot() . "resources/large_binary_out.jpg", "wb");
                $dbw->copy_field_to_stream("blob_data", $output);
                fclose($output);
                $output = fopen(approot() . "resources/large_text_out.txt", "wb");
                $dbw->copy_field_to_stream("text_data", $output);
                fclose($output);
            }

            $dbw->free_result();

            echo "Date read and placed to files.<br>";
            echo "Text file size: " . filesize(approot() . "resources/large_text_out.txt") . "<br>";
            echo "Binary file size: " . filesize(approot() . "resources/large_binary_out.jpg") . "<br>";

            echo "<h2>Analizing of the meta data users</h2>";

            $dbw->execute_query("select * from users");

            $fcnt = $dbw->field_count();
            echo "Number of fields: " . $fcnt . "<br><br>";

            for ($i = 0; $i < $fcnt; $i++) {
                $finfo = $dbw->field_info_by_num($i);
                if (empty($finfo)) {
                    continue;
                }

                echo "<pre>";
                print_r($finfo);
                echo "</pre>";
            }

            $dbw->free_result();

            echo "<h2>Analizing of the meta data large_data</h2>";

            $dbw->execute_query("select * from large_data");

            $fcnt = $dbw->field_count();
            echo "Number of fields: " . $fcnt . "<br><br>";

            for ($i = 0; $i < $fcnt; $i++) {
                $finfo = $dbw->field_info_by_num($i);
                if (empty($finfo)) {
                    continue;
                }

                echo "<pre>";
                print_r($finfo);
                echo "</pre>";
            }

            $dbw->free_result();

            echo "<h2>Testing transactions</h2>";

            $dbw->start_transaction();

            try {
                $dbw->execute_query("update users set salary = salary + 1");
            } catch (\Exception $ex) {
                $dbw->rollback_transaction();

                throw $ex;
            }

            $dbw->commit_transaction();

            echo "Done.";

            echo "<h2>Executing stored procedure</h2>";

            // The setup script creates TEMP with session-private rows preserved across commits.

            $dbw->execute_procedure("collect_users", 100);

            echo "Done.";

            echo "<h2>Selecting from temporary table</h2>";

            $dbw->execute_query("select first_name, last_name, email from temp");

            while ($dbw->fetch_row()) {
                echo $dbw->field_by_name("first_name") . " " . $dbw->field_by_name("last_name") . "<br>";
            }

            $dbw->free_result();
        } // test_database


        try {
            test_database();
        } catch (\Exception $ex) {
            if ($ex->getCode() == 100) {
                echo "<h4 style='color: maroon'>Please ensure that you have created the demo schema with 'database/oracle/create_database.cmd' and adjusted the DB password and other connection data in the line 29 of this file!</h4>";
            } else {
                messenger()->addError($ex->getMessage());
                report_messages();
            }
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
