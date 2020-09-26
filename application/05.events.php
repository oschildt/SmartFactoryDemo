<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\event;
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Events</title>

    <link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Events</h2>

<h3>Initialization</h3>

<div class="code">function evt1_handler1($event, $params)
 {
    echo "evt1_handler1 called: $event<br>";
    echo "
    <pre>";
    print_r($params);
    echo "</pre>
    ";
}

$evt1_handler2 = function($event, $params)
{
    echo "evt1_handler2 called: $event<br>";
    echo "
    <pre>";
    print_r($params);
    echo "</pre>
    ";
};

$params = ["param1" => "value1", "param2" => "value2"];

// the same handlers are NOT called twice. The handler evt1_handler1
// should be called only one per fireEvent call
event()->addHandler("event1", "evt1_handler1");
event()->addHandler("event1", "evt1_handler1");

// the same handlers are NOT called twice. The handler evt1_handler2
// should be called only one per fireEvent call
event()->addHandler("event1", $evt1_handler2);
event()->addHandler("event1", $evt1_handler2);

// Closure
event()->addHandler("event1", function($event, $params)
{
    echo "evt1_handler3 called: $event<br>";
    echo "
    <pre>";
    print_r($params);
    echo "</pre>
    ";
});

event()->addHandler("event2", function($event, $params)
{
    echo "evt2_handler1 called: $event<br>";
    echo "
    <pre>";
    print_r($params);
    echo "</pre>
    ";
});
</div>

<?php
function evt1_handler1($event, $params)
{
    echo "evt1_handler1 called: $event<br>";
    echo "<pre>";
    print_r($params);
    echo "</pre>";
}

$evt1_handler2 = function ($event, $params) {
    echo "evt1_handler2 called: $event<br>";
    echo "<pre>";
    print_r($params);
    echo "</pre>";
};

$params = ["param1" => "value1", "param2" => "value2"];

// the same handlers are NOT called twice. The handler evt1_handler1
// should be called only one per fireEvent call 
event()->addHandler("event1", "\\MyApplication\\evt1_handler1");
event()->addHandler("event1", "\\MyApplication\\evt1_handler1");

// the same handlers are NOT called twice. The handler evt1_handler2
// should be called only one per fireEvent call 
event()->addHandler("event1", $evt1_handler2);
event()->addHandler("event1", $evt1_handler2);

// Closure
event()->addHandler("event1", function ($event, $params) {
    echo "evt1_handler3 called: $event<br>";
    echo "<pre>";
    print_r($params);
    echo "</pre>";
});

event()->addHandler("event2", function ($event, $params) {
    echo "evt2_handler1 called: $event<br>";
    echo "<pre>";
    print_r($params);
    echo "</pre>";
});
?>

<h3>Firing events</h3>

<div class="code">$params = ["p1" => 100, "p2" => 200];

event()->fireEvent("event1", $params);
event()->fireEvent("event2", $params);
</div>
<br>
<?php
event()->fireEvent("event1", $params);
event()->fireEvent("event2", $params);
?>

<h3>Suspending event1</h3>

<div class="code">event()->suspendEvent("event1");

event()->fireEvent("event1", $params);
event()->fireEvent("event2", $params);
</div>
<br>

<?php
event()->suspendEvent("event1");

event()->fireEvent("event1", $params);
event()->fireEvent("event2", $params);
?>

<p>No calls on event1, but event2 still fires.</p>

<h3>Resuming event1</h3>

<div class="code">event()->resumeEvent("event1");

event()->fireEvent("event1", $params);
event()->fireEvent("event2", $params);
</div>
<br>

<?php
event()->resumeEvent("event1");

event()->fireEvent("event1", $params);
event()->fireEvent("event2", $params);
?>

<p>Both events event1 and event2 fire again.</p>


<h3>Deleting handlers evt1_handler1 and evt1_handler2</h3>

<div class="code">// It works only with named
event()->deleteHandler("event1", "evt1_handler1");
event()->deleteHandler("event1", $evt1_handler2);

event()->fireEvent("event1", $params);
event()->fireEvent("event2", $params);
</div>
<br>

<?php
// It works only with named
event()->deleteHandler("event1", "\\MyApplication\\evt1_handler1");
event()->deleteHandler("event1", $evt1_handler2);

event()->fireEvent("event1", $params);
event()->fireEvent("event2", $params);
?>

<p>evt1_handler1 and evt1_handler2 are not called anymore by firing.</p>


<h3>Deleting all handlers for event1</h3>

<div class="code">event()->deleteHandlers("event1");

event()->fireEvent("event1", $params);
event()->fireEvent("event2", $params);
</div>
<br>

<?php
event()->deleteHandlers("event1");

event()->fireEvent("event1", $params);
event()->fireEvent("event2", $params);
?>

<p>No calls on event1, but event2 still fires.</p>


<h3>Deleting all handlers</h3>

<div class="code">event()->deleteAllHandlers();

event()->fireEvent("event1", $params);
event()->fireEvent("event2", $params);
</div>
<br>

<?php
event()->deleteAllHandlers();

event()->fireEvent("event1", $params);
event()->fireEvent("event2", $params);
?>
<p>No event calls anymore.</p>

</body>
</html>
