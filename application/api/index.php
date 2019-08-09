<?php

namespace MyApplication;

require "../../vendor/autoload.php";

use SmartFactory\JsonApiRequestManager;

use function SmartFactory\singleton;

try {
    $rmanager = singleton(JsonApiRequestManager::class);
    
    $rmanager->registerDefaultHandler("MyApplication\\Handlers\\DefaultHandler");
    $rmanager->registerPreProcessHandler("MyApplication\\Handlers\\PreProcessHandler");
    $rmanager->registerPostProcessHandler("MyApplication\\Handlers\\PostProcessHandler");
    
    $rmanager->registerApiRequestHandler("login", "MyApplication\\Handlers\\LoginHandler");
} catch (\Throwable $ex) {
    $rmanager->exitWithException($ex);
}

$rmanager->handleApiRequest();
