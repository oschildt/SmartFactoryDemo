<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\debugger;
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Error handling</title>

    <link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Error handling</h2>

<p>Producing some warnings and calling function with warnings.</p>

<p>Error details are traced to the trace file logs/trace.log.</p>

<div class="code">// force a warning
    $a = $b;

    $dummy = function($a, $b)
    {

    };

    function test_function($param1, $param2, callable $param3)
    {
    $c = $d;
    }

    function do_action($action, $vars, $params, $obj)
    {
    global $dummy;

    test_function("100", "test", $dummy);
    }

    class SomeClass
    {

    }

    $obj = new SomeClass();

    // call function with the warning
    do_action("save", array("red", "green", "blue"), array("p1" => "John", "p2" => 2000), $obj);
</div>

<?php
debugger()->clearLogFiles();

// force a warning
$a = $b;

$dummy = function ($a, $b) {

};

function test_function($param1, $param2, callable $param3)
{
    $c = $d;
}

function do_action($action, $vars, $params, $obj)
{
    global $dummy;
    
    test_function("100", "test", $dummy);
}

class SomeClass
{

}

$obj = new SomeClass();

// call function with the warning
do_action("save", array("red", "green", "blue"), array("p1" => "John", "p2" => 2000), $obj);
?>

<p>Listing: logs/trace.log</p>

<div class="code"><?php
    echo file_get_contents(approot() . "../logs/trace.log");
    ?></div>

</body>
</html>

