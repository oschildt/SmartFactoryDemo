<?php
namespace MyApplication;

require "../../vendor/autoload.php";

use SmartFactory\Interfaces\ISessionManager;
use SmartFactory\JsonApiRequestManager;

use function SmartFactory\singleton;

singleton(ISessionManager::class)->startSession();

$rmanager = singleton(JsonApiRequestManager::class);

//-----------------------------------------------------------------
$rmanager->registerDefaultHandler("MyApplication\\Handlers\\DefaultHandler");
$rmanager->registerPreProcessHandler("MyApplication\\Handlers\\PreProcessHandler");
$rmanager->registerPostProcessHandler("MyApplication\\Handlers\\PostProcessHandler");

$rmanager->registerApiRequestHandler("login", "MyApplication\\Handlers\\LoginHandler");
//-----------------------------------------------------------------

$rmanager->handleApiRequest();
