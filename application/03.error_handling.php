<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\debugger;
?><!DOCTYPE html>
<html lang="en">

<head>
    <title>Error handling</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="css/examples.css" type="text/css" />
    <script src="js/examples.js" defer></script>
</head>

<body>

    <div class="header"></div>
    <div class="content">

        <div class="left_links"></div>
        <div class="right_links">
            <p><a href="index.php">List of examples</a></p>
        </div>
        <div style="clear: both;"></div>

        <h1>Error handling</h1>

        <p>This example demonstrates how programming warnings and their call stacks are recorded in a trace log.</p>

        <p>Producing some warnings and calling function with warnings.</p>

        <p>Error details are traced to the trace file logs/trace.log.</p>

        <pre class="code">
// force a warning
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
do_action("save", ["red", "green", "blue"], ["p1" => "John", "p2" => 2000], $obj);
</pre>

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

        <pre class="code">
<?php
echo file_get_contents(approot() . "logs/trace.log");
?>
</pre>

    </div>

    <div class="_sys_load_lightbox" id="sys_progress_indicator" role="status" aria-live="polite" aria-hidden="true">
        <div>
            <div>
                <div class="progress_caption">Loading data ...</div>
                <div id="loading_indicator"><img src="images/loading.gif" width="220" height="19" alt=""></div>
            </div>
        </div>
    </div>

</body>

</html>
