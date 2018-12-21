<?php

namespace MyApplication;

require "../../vendor/autoload.php";

use SmartFactory\Interfaces\ISessionManager;
use SmartFactory\JsonApiRequestManager;

use function SmartFactory\singleton;

singleton(ISessionManager::class)->startSession();

$rmanager = singleton(JsonApiRequestManager::class);

try {
    $rmanager->registerDefaultHandler("MyApplication\\Handlers\\DefaultHandler");
    $rmanager->registerPreProcessHandler("MyApplication\\Handlers\\PreProcessHandler");
    $rmanager->registerPostProcessHandler("MyApplication\\Handlers\\PostProcessHandler");
    
    $rmanager->registerApiRequestHandler("login", "MyApplication\\Handlers\\LoginHandler");
} catch (\SmartFactory\SmartException $ex) {
    $rmanager->exitWithException($ex);
}

$rmanager->handleApiRequest();
