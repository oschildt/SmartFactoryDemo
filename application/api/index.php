<?php

namespace MyApplication;

use MyApplication\Handlers\MyTestJsonHandler;
use SmartFactory\Interfaces\IRequestThrottlingManager;

use function SmartFactory\singleton;

require "../../vendor/autoload.php";

$handler = new MyTestJsonHandler();

$handler->init(["throttling_manager" => singleton(IRequestThrottlingManager::class)]);

$handler->handleRequest();
