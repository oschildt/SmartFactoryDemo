<?php

namespace MyApplication;

require "../../vendor/autoload.php";

use MyApplication\Handlers\MyTestXmlHandler;

(new MyTestXmlHandler())->handleRequest();
?>