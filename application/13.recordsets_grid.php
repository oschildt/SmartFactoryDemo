<?php
namespace MyApplication;

require "../vendor/autoload.php";

use SmartFactory\Interfaces\IRecordsetManager;

use SmartFactory\DatabaseWorkers\DBWorker;

use function SmartFactory\config_settings;
use function SmartFactory\singleton;
use function SmartFactory\echo_html;
use function SmartFactory\input_text;
use function SmartFactory\format_number;
use function SmartFactory\text;
use function SmartFactory\messenger;
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Recordsets - Grid</title>

    <link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Recordsets - Grid</h2>

<?php
function load_data(&$cnt)
{
    try {
        $rsmanager = singleton(IRecordsetManager::class);
        
        $rsmanager->describeTableFields("room_prices",
            
            [
                "room" => DBWorker::DB_STRING,
                "dt" => DBWorker::DB_DATE,
                "price" => DBWorker::DB_NUMBER
            ],
            
            ["room", "dt"]);
        
        $now = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        
        $where = "where ";
        $where .= "dt between " . $rsmanager->format_date($now) . " ";
        $where .= "and " . $rsmanager->format_date($now + 7 * 24 * 3600);

        $cnt = $rsmanager->countRecords($where);
        
        $rsmanager->loadRecordSet($_REQUEST["room_prices"], $where, "order by room, dt");
    } catch (\Exception $ex) {
        messenger()->addError($ex->getMessage());
    }
}

function save_data()
{
    try {
        $rsmanager = singleton(IRecordsetManager::class);
    
        $rsmanager->describeTableFields("room_prices",
        
            [
                "room" => DBWorker::DB_STRING,
                "dt" => DBWorker::DB_DATE,
                "price" => DBWorker::DB_NUMBER
            ],
        
            ["room", "dt"]);
    
        $rsmanager->saveRecordSet($_REQUEST["room_prices"]);
    
        messenger()->addInfoMessage("Data saved successfully!");
    } catch (\Exception $ex) {
        messenger()->addError($ex->getMessage());
        return false;
    }
    
    return true;
}

$cnt = 0;

if (!empty($_REQUEST["act"])) {
    if (save_data()) {
        header("Location: 13.recordsets_grid.php");
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

    <form action="13.recordsets_grid.php" method="post">

        <h3>Rooms prices</h3>
        
        <p>Actual count: <?php echo($cnt); ?></p>
        
        <?php
        $now = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        $rooms = ["single_room", "double_room", "suite", "suite_delux"];
        ?>

        <table>
            <tr>
                <th>Room</th>
                <?php for ($i = 0; $i < 7; $i++): ?>
                    <th><?php echo_html(date(text("DateFormat"), $now + $i * 24 * 3600)); ?></th>
                <?php endfor; ?>
            </tr>
            
            <?php foreach ($rooms as $room): ?>
                <tr>
                    <td><?php echo_html($room); ?></td>
                    
                    <?php for ($i = 0; $i < 7; $i++): ?>
                        <td>
                            <?php input_text([
                                "name" => "room_prices[$room][" . ($now + $i * 24 * 3600) . "][price]",
                                "style" => "width: 70px",
                                "formatter" => function ($val) {
                                    return format_number($val, 2);
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

        <input type="submit" name="act" value="Save">

    </form>
    
    <?php
}
?>

</body>
</html>



