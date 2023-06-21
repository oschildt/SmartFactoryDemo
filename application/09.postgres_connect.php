<?php
namespace MyApplication;

require "../vendor/autoload.php";

use SmartFactory\DatabaseWorkers\DBWorker;

use function SmartFactory\dbworker;
use function SmartFactory\messenger;

?><!DOCTYPE html>
<html lang="en">
<head>
    <title>PostgreSQL connection</title>

    <link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>PostgreSQL connection</h2>

<?php
function test_database()
{
    $connection_parameters = [
        "db_type" => "PostgreSQL",
        "db_server" => "localhost",
        "db_name" => "framework_demo",
        "db_user" => "postgres",
        "db_password" => "",
        "autoconnect" => true
    ];

    if (empty($connection_parameters["db_password"])) {
        throw new \Exception("DB password is not set!", 100);
    }

    $dbw = dbworker($connection_parameters);

    echo "<h2>Simple query</h2>";

    $dbw->execute_query("select first_name, last_name, salary from users");

    while ($dbw->fetch_row()) {
        echo $dbw->field_by_name("first_name") . " " . $dbw->field_by_name("last_name") . " " . $dbw->field_by_name("salary") . "<br>";
    }

    $dbw->free_result();

    echo "<h2>Fetch array</h2>";

    $dbw->execute_query("select page_id, language_key, title, content from page_content");

    $rows = [];
    if ($dbw->fetch_array($rows)) {
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

    echo "<h2>Prepared query (0, 1)</h2>";

    // PostgreSQL's syntax is: select first_name, last_name, salary from users where salary > $1 and department_id = $2.
    // Classic syntax is also supported.
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

    echo "<h2>Prepared DML query</h2>";

    $dbw->prepare_query("update users set salary = salary + $1 where department_id = $2");

    $dbw->execute_prepared_query(100, 2);

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

    $lid = $dbw->insert_id();

    echo "Done. New id: " . $lid . "<br>";

    $dbw->execute_query("delete from users where id = " . $dbw->escape($lid));

    echo "<h2>Inserting new row prepared</h2>";

    $dbw->prepare_query("insert into users (first_name, last_name, salary, department_id) values (?, ?, ?, 2)");

    $dbw->execute_prepared_query('John', 'Smith', 1000);

    $lid = $dbw->insert_id();

    $dbw->free_prepared_query();

    echo "Done. New id from prepared: " . $lid . "<br>";

    $dbw->execute_query("delete from users where id = " . $dbw->escape($lid));

    echo "<h2>Prepared query to array</h2>";

    $dbw->prepare_query("select page_id, language_key, title, content from page_content where page_id > $1");

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

    echo "<h2>Streaming large data</h2>";

    // large object operations must be done in a transaction
    //
    $dbw->start_transaction();

    $stream = fopen(approot() . "resources/large_binary.jpg", "rb");

    $dbw->stream_long_data("update large_data set blob_data = $1 where id = 1", $stream);

    echo "Binary written.<br>";

    $stream = fopen(approot() . "resources/large_text.txt", "rt");

    $dbw->stream_long_data("update large_data set text_data = $1 where id = 1", $stream);

    echo "Text written.<br>";

    $dbw->execute_query("select length(lo_get(text_data)) tds, length(lo_get(blob_data)) bds from large_data where id = 1");

    while ($dbw->fetch_row()) {
        echo "text_data size: " . $dbw->field_by_name("tds") . "<br>";
        echo "blob_data size: " . $dbw->field_by_name("bds") . "<br>";
    }

    $dbw->free_result();

    echo "<h2>Reading large data from DB</h2>";

    $dbw->execute_query("select text_data, blob_data from large_data where id = 1");

    if ($dbw->fetch_row()) {
        file_put_contents(approot() . "resources/large_binary_out.jpg", $dbw->field_by_name("blob_data", DBWorker::DB_LARGE_OBJECT_STREAM));
        file_put_contents(approot() . "resources/large_text_out.txt", $dbw->field_by_name("text_data", DBWorker::DB_LARGE_OBJECT_STREAM));
    }

    $dbw->free_result();

    $dbw->commit_transaction();

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

    $dbw->execute_query("create temporary table temp (email varchar(255) default null, last_name varchar(500) not null, first_name varchar(500) default null)");

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
        echo "<h4 style='color: maroon'>Please ensure that you have created the demo database with the script 'database/create_database_postgres.sql' and adjust the DB password and other connection data in the line 29 of this file!</h4>";
    } else {
        messenger()->addError($ex->getMessage());
        report_messages();
    }
}
?>

</body>
</html>

