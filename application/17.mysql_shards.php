<?php
namespace MyApplication;

require "../vendor/autoload.php";

use SmartFactory\Interfaces\IShardManager;

use function SmartFactory\singleton;
use function SmartFactory\dbshard;

?><!DOCTYPE html>
<html lang="en">
<head>
    <title>MySQL connection</title>

    <link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>MySQL connection</h2>

<?php
$shardmanager = singleton(IShardManager::class);
$shardmanager->registerShard("myshard", [
        "db_type" => "MySQL",
        "db_server" => "localhost",
        "db_name" => "framework_demo",
        "db_user" => "root",
        "db_password" => "",
        "autoconnect" => true,
        "read_only" => true
    ]
);

function connect_mysql()
{
    try {
        $dbw = dbshard("myshard");
    } catch (\Exception $ex) {
        echo "<h4 style='color: maroon'>Please ensure that you have created the demo database with the script 'database/create_database_mysql.sql' and adjust the DB password and other connection data in line 21 of this file!</h4>";
        return false;
    }
    
    echo "<h2>Simple query</h2>";
    
    if (!$dbw->execute_query("SELECT FIRST_NAME, LAST_NAME FROM USERS")) {
        return sql_error($dbw);
    }
    
    while ($dbw->fetch_row()) {
        echo $dbw->field_by_name("FIRST_NAME") . " " . $dbw->field_by_name("LAST_NAME") . "<br>";
    }
    
    $dbw->free_result();
    
    echo "<h2>Trying to update over the read olny connection</h2>";
    
    echo "<p>Update should fail because of the read only mode.</p>";
    
    if (!$dbw->execute_query("UPDATE USERS SET FIRST_NAME = 'Alex'")) {
        return sql_error($dbw);
    }
    
    echo "Update done.<br>";
    
    return true;
} // connect_mysql

connect_mysql();

report_messages();
?>

</body>
</html>

