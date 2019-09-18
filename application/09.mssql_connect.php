<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\dbworker;
use function SmartFactory\messenger;

?><!DOCTYPE html>
<html lang="en">
<head>
    <title>MSSQL connection</title>

    <link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>MSSQL connection</h2>

<?php
function connect_mssql()
{
    try {
        $dbw = dbworker([
            "db_type" => "MSSQL",
            "db_server" => "localhost",
            "db_name" => "framework_demo",
            "db_user" => "sa",
            "db_password" => "",
            "autoconnect" => true
        ]);
    } catch (\Exception $ex) {
        messenger()->setError($ex->getMessage());
        return null;
    }
    
    echo "<h2>Simple query</h2>";
    
    try {
        $dbw->execute_query("SELECT FIRST_NAME, LAST_NAME, SALARY FROM USERS");
    } catch (\Exception $ex) {
        messenger()->setError($ex->getMessage());
        return false;
    }
    
    while ($dbw->fetch_row()) {
        echo $dbw->field_by_name("FIRST_NAME") . " " . $dbw->field_by_name("LAST_NAME") . " " . $dbw->field_by_name("SALARY") . "<br>";
    }
    
    $dbw->free_result();
    
    echo "<h2>Fetch array</h2>";
    
    try {
        $dbw->execute_query("SELECT PAGE_ID, LANGUAGE_KEY, TITLE, CONTENT FROM PAGE_CONTENT");
    } catch (\Exception $ex) {
        messenger()->setError($ex->getMessage());
        return false;
    }

    $rows = [];
    if ($dbw->fetch_array($rows)) {
        echo "<pre>";
        print_r($rows);
        echo "</pre>";
    }
    
    $dbw->free_result();
    
    echo "<h2>Fetch array with dimensions [PAGE_ID, LANGUAGE_KEY]</h2>";
    
    try {
        $dbw->execute_query("SELECT PAGE_ID, LANGUAGE_KEY, TITLE, CONTENT FROM PAGE_CONTENT");
    } catch (\Exception $ex) {
        messenger()->setError($ex->getMessage());
        return false;
    }
    
    $rows = [];
    if ($dbw->fetch_array($rows, ["PAGE_ID", "LANGUAGE_KEY"])) {
        echo "<pre>";
        print_r($rows);
        echo "</pre>";
    }
    
    $dbw->free_result();
    
    echo "<h2>Prepared query (0, 1)</h2>";
    
    try {
        $dbw->prepare_query("SELECT FIRST_NAME, LAST_NAME, SALARY FROM USERS WHERE SALARY > ? AND DEPARTMENT_ID = ?");
    
        $dbw->execute_prepared_query(0, 1);
    } catch (\Exception $ex) {
        messenger()->setError($ex->getMessage());
        return false;
    }

    while ($dbw->fetch_row()) {
        echo $dbw->field_by_name("FIRST_NAME") . " " . $dbw->field_by_name("LAST_NAME") . " " . $dbw->field_by_name("SALARY") . "<br>";
    }
    
    $dbw->free_result();
    
    echo "<h2>Prepared query (0, 2)</h2>";
    
    try {
        $dbw->execute_prepared_query(0, 2);
    } catch (\Exception $ex) {
        messenger()->setError($ex->getMessage());
        return false;
    }
    
    while ($dbw->fetch_row()) {
        echo $dbw->field_by_name("FIRST_NAME") . " " . $dbw->field_by_name("LAST_NAME") . " " . $dbw->field_by_name("SALARY") . "<br>";
    }
    
    $dbw->free_result();
    
    try {
        $dbw->free_prepared_query();
    } catch (\Exception $ex) {
        messenger()->setError($ex->getMessage());
        return false;
    }
    
    echo "<h2>Prepared DML query</h2>";
    
    try {
        $dbw->prepare_query("UPDATE USERS SET SALARY = SALARY + ? WHERE DEPARTMENT_ID = ?");
        
        $dbw->execute_prepared_query(100, 2);
    } catch (\Exception $ex) {
        messenger()->setError($ex->getMessage());
        return false;
    }
    
    try {
        $dbw->free_prepared_query();
    } catch (\Exception $ex) {
        messenger()->setError($ex->getMessage());
        return false;
    }
    
    echo "Done.";
    
    echo "<h2>Checking DML results</h2>";
    
    try {
        $dbw->execute_query("SELECT FIRST_NAME, LAST_NAME, SALARY FROM USERS WHERE DEPARTMENT_ID = 2");
    } catch (\Exception $ex) {
        messenger()->setError($ex->getMessage());
        return false;
    }
    
    while ($dbw->fetch_row()) {
        echo $dbw->field_by_name("FIRST_NAME") . " " . $dbw->field_by_name("LAST_NAME") . " " . $dbw->field_by_name("SALARY") . "<br>";
    }
    
    $dbw->free_result();
    
    echo "<h2>Prepared query to array</h2>";
    
    try {
        $dbw->prepare_query("SELECT PAGE_ID, LANGUAGE_KEY, TITLE, CONTENT FROM PAGE_CONTENT WHERE PAGE_ID > ?");
    
        $dbw->execute_prepared_query(0);
    } catch (\Exception $ex) {
        messenger()->setError($ex->getMessage());
        return false;
    }
    
    $rows = [];
    if ($dbw->fetch_array($rows)) {
        echo "<pre>";
        print_r($rows);
        echo "</pre>";
    }
    
    $dbw->free_result();
    
    echo "<h2>Prepared query to array with dimensions [PAGE_ID, LANGUAGE_KEY]</h2>";
    
    try {
        $dbw->execute_prepared_query(0);
    } catch (\Exception $ex) {
        messenger()->setError($ex->getMessage());
        return false;
    }
    
    $rows = [];
    if ($dbw->fetch_array($rows, ["PAGE_ID", "LANGUAGE_KEY"])) {
        echo "<pre>";
        print_r($rows);
        echo "</pre>";
    }
    
    $dbw->free_result();
    
    $dbw->free_prepared_query();
    try {
        $dbw->free_prepared_query();
    } catch (\Exception $ex) {
        messenger()->setError($ex->getMessage());
        return false;
    }
    
    echo "<h2>Streaming large data</h2>";
    
    $stream = fopen(approot() . "resources/large_binary.jpg", "rb");
    
    try {
        $dbw->stream_long_data("UPDATE LARGE_DATA SET BLOB_DATA = ? WHERE ID = 1", $stream);
    } catch (\Exception $ex) {
        messenger()->setError($ex->getMessage());
        return false;
    }
    
    echo "Binary written.<br>";
    
    $stream = fopen(approot() . "resources/large_text.txt", "rt");
    
    try {
        $dbw->stream_long_data("UPDATE LARGE_DATA SET TEXT_DATA = ? WHERE ID = 1", $stream);
    } catch (\Exception $ex) {
        messenger()->setError($ex->getMessage());
        return false;
    }

    echo "Text written.<br>";
    
    try {
        $dbw->execute_query("SELECT DATALENGTH(TEXT_DATA) LTD, DATALENGTH(BLOB_DATA) LBD FROM LARGE_DATA WHERE ID = 1");
    } catch (\Exception $ex) {
        messenger()->setError($ex->getMessage());
        return false;
    }
    
    while ($dbw->fetch_row()) {
        echo "TEXT_DATA: " . $dbw->field_by_name("LTD") . "<br>";
        echo "BLOB_DATA: " . $dbw->field_by_name("LBD") . "<br>";
    }
    
    $dbw->free_result();
    
    echo "<h2>Analizing of the meta data USERS</h2>";
    
    try {
        $dbw->execute_query("SELECT * FROM USERS");
    
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
    } catch (\Exception $ex) {
        messenger()->setError($ex->getMessage());
        return false;
    }
    
    $dbw->free_result();
    
    echo "<h2>Analizing of the meta data LARGE_DATA</h2>";
    
    try {
        $dbw->execute_query("SELECT * FROM LARGE_DATA");
    
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
    } catch (\Exception $ex) {
        messenger()->setError($ex->getMessage());
        return false;
    }
    
    $dbw->free_result();
    
    echo "<h2>Getting data from stored procedure</h2>";
    
    try {
        $dbw->execute_procedure("GET_USERS", 100);
    } catch (\Exception $ex) {
        messenger()->setError($ex->getMessage());
        return false;
    }
    
    while ($dbw->fetch_row()) {
        echo $dbw->field_by_name("FIRST_NAME") . " " . $dbw->field_by_name("LAST_NAME") . "<br>";
    }
    
    $dbw->free_result();
    
    return true;
} // connect_mssql

if (connect_mssql() === null) {
    echo "<h4 style='color: maroon'>Please ensure that you have created the demo database with the script 'database/create_database_mssql.sql' and adjust the DB password and other connection data in the line 26 of this file!</h4>";
} else {
    report_messages();
}
?>

</body>
</html>

