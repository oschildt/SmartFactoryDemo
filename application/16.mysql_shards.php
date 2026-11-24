<?php
namespace MyApplication;

require "../vendor/autoload.php";

use SmartFactory\Interfaces\IShardManager;

use function SmartFactory\messenger;

use function SmartFactory\singleton;
use function SmartFactory\randomDBShard;

?><!DOCTYPE html>
<html lang="en">

<head>
    <title>MySQL shards and load balancing</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="css/examples.css" type="text/css" />
    <script src="js/examples.js" defer></script>
</head>

<body>

    <div class="header"></div>
    <div class="content">

        <div class="left_links"></div>
        <div class="right_links">
            <p><a href="index.php">List of examples</a></p>
        </div>
        <div style="clear: both;"></div>

        <h1>MySQL shards and load balancing</h1>

        <p>This example demonstrates how to register database shards, select a connection from a load balancing group, and enforce read-only access using <i>ShardManager</i>.</p>

        <?php
        $shardmanager = singleton(IShardManager::class);


        $shardmanager->registerShard(
            "myshard1",
            [
                "db_type" => "MySQL",
                "db_server" => "localhost",
                "db_name" => "framework_demo",
                "db_user" => "root",
                "db_password" => "",
                "autoconnect" => true,
                "read_only" => true
            ],
            "load_balancing_group1"
        );

        $shardmanager->registerShard(
            "myshard2",
            [
                "db_type" => "MySQL",
                "db_server" => "localhost",
                "db_name" => "framework_demo",
                "db_user" => "root",
                "db_password" => "",
                "autoconnect" => true,
                "read_only" => true
            ],
            "load_balancing_group1"
        );

        $shardmanager->registerShard(
            "myshard3",
            [
                "db_type" => "MySQL",
                "db_server" => "localhost",
                "db_name" => "framework_demo",
                "db_user" => "root",
                "db_password" => "",
                "autoconnect" => true,
                "read_only" => true
            ],
            "load_balancing_group1"
        );

        $shardmanager->registerShard(
            "myshard4",
            [
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
                //$dbw = dbshard("myshard2");
                $dbw = randomDBShard("load_balancing_group1");
            } catch (\Exception $ex) {
                echo "<h4 style='color: maroon'>Please ensure that you have created the demo database with the script 'database/create_database_mysql.sql' and adjust the DB password and other connection data in the lines 31, 42, 53, 64 of this file!</h4>";
                return;
            }

            echo "<h2>Simple query</h2>";

            $dbw->execute_query("select first_name, last_name from users");

            while ($dbw->fetch_row()) {
                echo $dbw->field_by_name("first_name") . " " . $dbw->field_by_name("last_name") . "<br>";
            }

            $dbw->free_result();

            echo "<h2>Trying to update over the read olny connection</h2>";

            echo "<p>Update should fail because of the read only mode.</p>";

            $dbw->execute_query("update users set first_name = 'Alex'");

            echo "Update done.<br>";
        } // connect_mysql

        try {
            connect_mysql();
        } catch (\Exception $ex) {
            messenger()->addError($ex->getMessage());
            report_messages();
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
