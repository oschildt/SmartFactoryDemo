<?php

namespace MyApplication;

require "../../vendor/autoload.php";

use MyApplication\Handlers\MyTestJsonHandler;

(new MyTestJsonHandler())->handleRequest();
