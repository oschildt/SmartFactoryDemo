<?php
namespace MyApplication;

require "../vendor/autoload.php";

use SmartFactory\Interfaces\IRecordsetManager;

use SmartFactory\DatabaseWorkers\DBWorker;

use function SmartFactory\config_settings;
use function SmartFactory\singleton;
use function SmartFactory\checkempty;
use function SmartFactory\echo_html;
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
    
        $rsmanager->describeTableFields("pages",
        
            [
                "id" => DBWorker::DB_NUMBER,
                "page_name" => DBWorker::DB_STRING,
                "page_type" => DBWorker::DB_STRING
            ],
        
            ["id"]);
    
        $rsmanager->loadRecordSet($page_list, "", "order by page_name");
    } catch (\Exception $ex) {
        messenger()->addError($ex->getMessage());
    }
}

function load_page_data()
{
    if (!empty($_REQUEST["page_id"])) {
        try {
            $rsmanager = singleton(IRecordsetManager::class);
    
            $rsmanager->describeTableFields("pages",
        
                [
                    "id" => DBWorker::DB_NUMBER,
                    "page_name" => DBWorker::DB_STRING,
                    "page_type" => DBWorker::DB_STRING,
                    "page_order" => DBWorker::DB_NUMBER,
                    "page_date" => DBWorker::DB_DATETIME
                ],
        
                ["id"]);
    
            $rsmanager->loadRecord($_REQUEST["page_data"], ["id" => $_REQUEST["page_id"]]);
    
            $rsmanager->describeTableFields("page_content",
        
                [
                    "page_id" => DBWorker::DB_NUMBER,
                    "language_key" => DBWorker::DB_STRING,
                    "title" => DBWorker::DB_STRING,
                    "content" => DBWorker::DB_STRING
                ],
        
                ["page_id", "language_key"]);
    
            $rsmanager->loadRecordSet($_REQUEST["page_content"], ["page_id" => $_REQUEST["page_id"]]);
        } catch (\Exception $ex) {
            messenger()->addError($ex->getMessage());
        }
    }
}

function save_data()
{
    $rsmanager = null;

    try {
        $rsmanager = singleton(IRecordsetManager::class);
    
        $rsmanager->start_transaction();
    
        $tm = timestamp($_REQUEST["page_data"]["page_date"], text("DateTimeFormat"));
        if ($tm == "error") {
            messenger()->addError(sprintf(text("ErrDateTimeFormat"), $_REQUEST["page_data"]["page_date"], date(text("DateTimeFormat"), mktime(20, 44, 30, 11, 27, 2018))));
            return false;
        }
    
        $_REQUEST["page_data"]["page_date"] = $tm;
    
        $rsmanager->describeTableFields("pages",
        
            [
                "id" => DBWorker::DB_NUMBER,
                "page_name" => DBWorker::DB_STRING,
                "page_type" => DBWorker::DB_STRING,
                "page_order" => DBWorker::DB_NUMBER,
                "page_date" => DBWorker::DB_DATETIME
            ],
        
            ["id"]);
    
        $rsmanager->saveRecord($_REQUEST["page_data"], ["id" => checkempty($_REQUEST["page_data"]["id"])], "id");
    
        $rsmanager->describeTableFields("page_content",
        
            [
                "page_id" => DBWorker::DB_STRING,
                "language_key" => DBWorker::DB_STRING,
                "title" => DBWorker::DB_STRING,
                "content" => DBWorker::DB_STRING
            ],
        
            ["page_id", "language_key"]);
    
        $rsmanager->saveRecordSet($_REQUEST["page_content"], ["page_id" => checkempty($_REQUEST["page_data"]["id"])]);
    
        $rsmanager->commit_transaction();
    } catch (\Exception $ex) {
        $rsmanager->rollback_transaction();
        messenger()->addError($ex->getMessage());
        return false;
    }
    
    messenger()->addInfoMessage("Data saved successfully!");
    
    return true;
} // process_form

$page_list = [];
load_page_list($page_list);

if (!empty($_REQUEST["act"])) {
    if (save_data()) {
        header("Location: 13.recordsets.php?page_id=" . $_REQUEST["page_data"]["id"]);
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
            <th>id</th>
            <th>page_name</th>
            <th>page_type</th>
            <th>&nbsp;</th>
        </tr>
        
        <?php foreach ($page_list as $page_id => $page_row): ?>
            <tr>
                <td><?php echo_html(checkempty($page_id)); ?></td>
                <td><?php echo_html(checkempty($page_row["page_name"])); ?></td>
                <td><?php echo_html(checkempty($page_row["page_type"])); ?></td>
                <td><a href="13.recordsets.php?page_id=<?php echo_html(checkempty($page_id)); ?>">Edit</a></td>
            </tr>
        <?php endforeach; ?>

    </table>

    <br><a href="13.recordsets.php">New page</a><br>

    <form action="13.recordsets.php" method="post">

        <h3>Basic properites</h3>

        id: <?php echo_html($id); ?>
        
        <?php input_hidden(["name" => "page_id", "value" => $id]); ?>
        <?php input_hidden(["name" => "page_data[id]", "value" => $id]); ?>

        <table>
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
                    <td><?php input_text(["name" => "page_content[$id][$lkey][title]", "style" => "width:300px", "autocomplete" => "off"]); ?></td>
                </tr>
                <tr>
                    <td>Page content:</td>
                    <td><?php textarea(["name" => "page_content[$id][$lkey][content]", "style" => "width:300px"]); ?></td>
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



