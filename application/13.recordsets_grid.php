<?php
namespace MyApplication;

require "../vendor/autoload.php";

use SmartFactory\Interfaces\IRecordsetManager;

use function SmartFactory\singleton;
use function SmartFactory\session;
use function SmartFactory\echo_html;
use function SmartFactory\input_text;
use function SmartFactory\format_number;
use function SmartFactory\text;
use function SmartFactory\messenger;

session()->startSession();
?><!DOCTYPE html>
<html lang="en">
<head>
<title>Recordsets - Grid</title>

<link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Recordsets - Grid</h2>

<?php
function load_data()
{
  $rsmanager = singleton(IRecordsetManager::class);

  $dbw = $rsmanager->getDBWorker();
  
  $rsmanager->defineTableMapping("ROOM_PRICES", 
  
                                 ["ROOM" => $dbw::db_string, 
                                  "DT" => $dbw::db_date,
                                  "PRICE" => $dbw::db_number
                                 ],
                                 
                                 ["ROOM", "DT"]);

  $now = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
  
  $where = "WHERE ";                               
  $where .= "DT BETWEEN '" . $dbw->format_date($now) . "'";                               
  $where .= " AND '" . $dbw->format_date($now + 7*24*3600) . "'";  
                                 
  $rsmanager->loadRecordSet($_REQUEST["room_prices"], $where);
}

function save_data()
{
  $rsmanager = singleton(IRecordsetManager::class);
  
  $dbw = $rsmanager->getDBWorker();

  $rsmanager->defineTableMapping("ROOM_PRICES", 
  
                                 ["ROOM" => $dbw::db_string, 
                                  "DT" => $dbw::db_date,
                                  "PRICE" => $dbw::db_number
                                 ],
                                 
                                 ["ROOM", "DT"]);

  if(!$rsmanager->saveRecordSet($_REQUEST["room_prices"])) 
  {
    return false;
  }

  messenger()->setInfo("Data saved successfully!");
  
  return true;
}

if(!empty($_REQUEST["act"])) 
{
  if(save_data())
  {
    header("Location: 13.recordsets_grid.php");
    exit;
  }
}
else
{
  load_data();
}
?>

<?php
report_messages();
?>

<form action="13.recordsets_grid.php" method="post">

<h3>Rooms prices</h3>

<?php
$now = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
$rooms = ["single_room", "double_room", "suite", "suite_delux"];
?>

<table>
<tr>
<th>Room</th>
<?php for($i = 0; $i < 7; $i++): ?>
<th><?php echo_html(date(text("DateFormat"), $now + $i*24*3600)); ?></th>
<?php endfor; ?>
</tr>

<?php foreach($rooms as $room): ?>
<tr>
<td><?php echo_html($room); ?></td>

  <?php for($i = 0; $i < 7; $i++): ?>
  <td>
  <?php input_text([
                  "name" => "room_prices[$room][" . ($now + $i*24*3600) . "][PRICE]", 
                  "style" => "width: 70px",
                  "formatter" => function ($val) { return format_number($val, 2); }
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

</body>
</html>



