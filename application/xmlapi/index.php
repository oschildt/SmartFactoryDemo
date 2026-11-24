<?php

namespace MyApplication;

use MyApplication\Handlers\MyTestXmlHandler;
use SmartFactory\Interfaces\IRequestThrottlingManager;

use function SmartFactory\singleton;

require "../../vendor/autoload.php";

$handler = new MyTestXmlHandler();

$handler->init(["throttling_manager" => singleton(IRequestThrottlingManager::class)]);

$handler->handleRequest();
