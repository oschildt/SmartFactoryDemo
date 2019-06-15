<?php

namespace MyApplication;

require "../../vendor/autoload.php";

use function SmartFactory\singleton;

$rmanager = singleton(HotelXmlApiRequestManager::class);

//-----------------------------------------------------------------
$rmanager->registerApiRequestHandler("GetRooms", "MyApplication\\Hotel\\RoomHandler");
//-----------------------------------------------------------------

$rmanager->handleApiRequest();
?>