<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\debugger;
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Debugging, logging, profiling</title>

    <link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Debugging, logging, profiling</h2>

<p>We bind a class to the IDebugProfiler interface and initialize it.</p>

<div class="code">
    FactoryBuilder::bindClass(IDebugProfiler::class, DebugProfiler::class, function ($instance) {
    $instance->init(["log_path" => approot() . "../logs/"]);
    });
</div>

<h3>Logging a message to a user defined file logs/mylog.log</h3>

<div class="code">debugger()->logMessageToFile("some data 1 ...", "mylog.log");
    debugger()->logMessageToFile("some data 2 ...", "mylog.log");
</div>

<p>Listing: logs/mylog.log</p>

<div class="code"><?php
    debugger()->clearLogFiles();
    
    debugger()->logMessageToFile("some data 1 ...", "mylog.log");
    debugger()->logMessageToFile("some data 2 ...", "mylog.log");
    
    echo file_get_contents(approot() . "logs/mylog.log");
    ?></div>

<h3>Logging a message to the debug file logs/debug.log</h3>

<div class="code">debugger()->debugMessage("some debug data 1 ...");
    debugger()->debugMessage("some debug data 2 ...");
</div>

<p>Listing: logs/debug.log</p>

<div class="code"><?php
    debugger()->debugMessage("some debug data 1 ...");
    debugger()->debugMessage("some debug data 2 ...");
    
    echo file_get_contents(approot() . "logs/debug.log");
    ?></div>

<h3>Profiling long operations and logging to the profile file logs/profile.log</h3>

<?php
debugger()->startProfilePoint("Profiling started");

// Long running operation #1
sleep(3);

debugger()->fixProfilePoint("Long running operation #1 completed");

// Long running operation #2
sleep(2);

debugger()->fixProfilePoint("Long running operation #2 completed");

// Long running operation #3
// we do not want to profile
sleep(2);

//restart time measurement, to exclude the execution time 
// of the operation #3
debugger()->startProfilePoint("Long running operation #4 started");

// Long running operation #4
sleep(4);

debugger()->fixProfilePoint("Long running operation #4 completed");
?>

<div class="code">debugger()->startProfilePoint("Profiling started");

    // Long running operation #1
    sleep(3);

    debugger()->fixProfilePoint("Long running operation #1 completed");

    // Long running operation #2
    sleep(2);

    debugger()->fixProfilePoint("Long running operation #2 completed");

    // Long running operation #3
    // we do not want to profile
    sleep(2);

    //restart time measurement, to exclude the execution
    // time of the operation #3
    debugger()->startProfilePoint("Long running operation #4 started");

    // Long running operation #4
    sleep(4);

    debugger()->fixProfilePoint("Long running operation #4 completed");
</div>

<p>Listing: logs/profile.log</p>

<div class="code"><?php
    echo file_get_contents(approot() . "logs/profile.log");
    ?></div>

<h3>Useful functions</h3>

<div class="code">debugger()->clearLogFile("mylog.log");
    debugger()->clearLogFiles();
</div>

</body>
</html>
