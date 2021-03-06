<?php
namespace MyApplication;

require "../vendor/autoload.php";

use SmartFactory\Interfaces\IRecordsetManager;

use SmartFactory\DatabaseWorkers\DBWorker;

use function SmartFactory\config_settings;
use function SmartFactory\singleton;
use function SmartFactory\session;
use function SmartFactory\checkempty;
use function SmartFactory\echo_html;
use function SmartFactory\input_hidden;
use function SmartFactory\input_text;
use function SmartFactory\textarea;
use function SmartFactory\text;
use function SmartFactory\timestamp;
use function SmartFactory\messenger;

session()->startSession();
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Recordsets</title>

    <link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Recordsets</h2>

<?php
$id = checkempty($_REQUEST["page_id"]);
if (empty($id)) {
    $id = "-1";
}

function load_page_list(&$page_list)
{
    try {
        $rsmanager = singleton(IRecordsetManager::class);
    
        $rsmanager->describeTableFields("PAGES",
        
            [
                "ID" => DBWorker::DB_NUMBER,
                "PAGE_NAME" => DBWorker::DB_STRING,
                "PAGE_TYPE" => DBWorker::DB_STRING
            ],
        
            ["ID"]);
    
        $rsmanager->loadRecordSet($page_list, "", "ORDER BY PAGE_NAME");
    } catch (\Exception $ex) {
        messenger()->setError($ex->getMessage());
    }
}

function load_page_data()
{
    if (!empty($_REQUEST["page_id"])) {
        try {
            $rsmanager = singleton(IRecordsetManager::class);
    
            $rsmanager->describeTableFields("PAGES",
        
                [
                    "ID" => DBWorker::DB_NUMBER,
                    "PAGE_NAME" => DBWorker::DB_STRING,
                    "PAGE_TYPE" => DBWorker::DB_STRING,
                    "PAGE_ORDER" => DBWorker::DB_NUMBER,
                    "PAGE_DATE" => DBWorker::DB_DATETIME
                ],
        
                ["ID"]);
    
            //$rsmanager->loadRecord($_REQUEST["page_data"], "WHERE ID = " . $dbw->escape($_REQUEST["page_id"]));
            $rsmanager->loadRecord($_REQUEST["page_data"], ["ID" => $_REQUEST["page_id"]]);
    
            $rsmanager->describeTableFields("PAGE_CONTENT",
        
                [
                    "PAGE_ID" => DBWorker::DB_NUMBER,
                    "LANGUAGE_KEY" => DBWorker::DB_STRING,
                    "TITLE" => DBWorker::DB_STRING,
                    "CONTENT" => DBWorker::DB_STRING
                ],
        
                ["PAGE_ID", "LANGUAGE_KEY"]);
    
            //$rsmanager->loadRecordSet($_REQUEST["page_content"], "WHERE PAGE_ID = " . $dbw->escape($_REQUEST["page_id"]));
            $rsmanager->loadRecordSet($_REQUEST["page_content"], ["PAGE_ID" => $_REQUEST["page_id"]]);
        } catch (\Exception $ex) {
            messenger()->setError($ex->getMessage());
        }
    }
}

function save_data()
{
    try {
        $rsmanager = singleton(IRecordsetManager::class);
    
        $dbw = $rsmanager->getDBWorker();
        if (!$dbw) {
            return false;
        }
    
        $dbw->start_transaction();
    
        $tm = timestamp($_REQUEST["page_data"]["PAGE_DATE"], text("DateTimeFormat"));
        if ($tm == "error") {
            messenger()->setError(sprintf(text("ErrDateTimeFormat"), $_REQUEST["page_data"]["PAGE_DATE"], date(text("DateTimeFormat"), mktime(20, 44, 30, 11, 27, 2018))));
            return false;
        }
    
        $_REQUEST["page_data"]["PAGE_DATE"] = $tm;
    
        $rsmanager->describeTableFields("PAGES",
        
            [
                "ID" => DBWorker::DB_NUMBER,
                "PAGE_NAME" => DBWorker::DB_STRING,
                "PAGE_TYPE" => DBWorker::DB_STRING,
                "PAGE_ORDER" => DBWorker::DB_NUMBER,
                "PAGE_DATE" => DBWorker::DB_DATETIME
            ],
        
            ["ID"]);
    
        $rsmanager->saveRecord($_REQUEST["page_data"], "ID");
    
        $rsmanager->describeTableFields("PAGE_CONTENT",
        
            [
                "PAGE_ID" => DBWorker::DB_STRING,
                "LANGUAGE_KEY" => DBWorker::DB_STRING,
                "TITLE" => DBWorker::DB_STRING,
                "CONTENT" => DBWorker::DB_STRING
            ],
        
            ["PAGE_ID", "LANGUAGE_KEY"]);
    
        $rsmanager->saveRecordSet($_REQUEST["page_content"], ["PAGE_ID" => checkempty($_REQUEST["page_data"]["ID"])]);
    
        $dbw->commit_transaction();
    } catch (\Exception $ex) {
        $dbw->rollback_transaction();
        messenger()->setError($ex->getMessage());
        return false;
    }
    
    messenger()->setInfo("Data saved successfully!");
    
    return true;
} // process_form

$page_list = [];
load_page_list($page_list);

if (!empty($_REQUEST["act"])) {
    if (save_data()) {
        header("Location: 13.recordsets.php?page_id=" . $_REQUEST["page_data"]["ID"]);
        exit;
    }
} else {
    load_page_data();
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

    <h3>Pages</h3>

    <table>
        <tr>
            <th>ID</th>
            <th>PAGE_NAME</th>
            <th>PAGE_TYPE</th>
            <th>&nbsp;</th>
        </tr>
        
        <?php foreach ($page_list as $page_id => $page_row): ?>
            <tr>
                <td><?php echo_html(checkempty($page_id)); ?></td>
                <td><?php echo_html(checkempty($page_row["PAGE_NAME"])); ?></td>
                <td><?php echo_html(checkempty($page_row["PAGE_TYPE"])); ?></td>
                <td><a href="13.recordsets.php?page_id=<?php echo_html(checkempty($page_id)); ?>">Edit</a></td>
            </tr>
        <?php endforeach; ?>

    </table>

    <br><a href="13.recordsets.php">New page</a><br>

    <form action="13.recordsets.php" method="post">

        <h3>Basic properites</h3>

        ID: <?php echo_html($id); ?>
        
        <?php input_hidden(["name" => "page_id", "value" => $id]); ?>
        <?php input_hidden(["name" => "page_data[ID]", "value" => $id]); ?>

        <table>
            <tr>
                <td>Page name*:</td>
                <td><?php input_text(["name" => "page_data[PAGE_NAME]", "autocomplete" => "off"]); ?></td>
            </tr>
            <tr>
                <td>Page type*:</td>
                <td><?php input_text(["name" => "page_data[PAGE_TYPE]", "autocomplete" => "off"]); ?></td>
            </tr>
            <tr>
                <td>Page order:</td>
                <td><?php input_text(["name" => "page_data[PAGE_ORDER]", "autocomplete" => "off"]); ?></td>
            </tr>
            <tr>
                <td>Page time:</td>
                <td><?php input_text([
                        "name" => "page_data[PAGE_DATE]",
                        "autocomplete" => "off",
                        "formatter" => function ($val) {
                            if (!empty($val)) {
                                return date(text("DateTimeFormat"), $val);
                            } else {
                                return $val;
                            }
                        }
                    ]);
                    ?>
                </td>
            </tr>
        </table>

        <h3>Language properites</h3>
        
        <?php foreach (["en", "de", "ru"] as $lkey): ?>

            <h4>Language: <?php echo($lkey); ?></h4>

            <table>
                <tr>
                    <td>Page title:</td>
                    <td><?php input_text(["name" => "page_content[$id][$lkey][TITLE]", "style" => "width:300px", "autocomplete" => "off"]); ?></td>
                </tr>
                <tr>
                    <td>Page content:</td>
                    <td><?php textarea(["name" => "page_content[$id][$lkey][CONTENT]", "style" => "width:300px"]); ?></td>
                </tr>
            </table>
        
        <?php endforeach; ?>

        <br>
        <br>

        <input type="submit" name="act" value="Save">

    </form>
    
    <?php
}
?>

</body>
</html>



