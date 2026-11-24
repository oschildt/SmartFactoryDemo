<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\event;
?><!DOCTYPE html>
<html lang="en">

<head>
    <title>Events</title>
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

        <h1>Events</h1>

        <p>This example demonstrates how to register event handlers, fire events, suspend and resume events, and remove
            handlers.</p>

        <h2>Initialization</h2>

        <pre class="code">
function evt1_handler1(string $event, array $params)
{
    echo "evt1_handler1 called: $event&lt;br&gt;";
    echo "&lt;pre&gt;";
    print_r($params);
    echo "&lt;/pre&gt;";
}

$evt1_handler2 = function(string $event, array $params)
{
    echo "evt1_handler2 called: $event&lt;br&gt;";
    echo "&lt;pre&gt;";
    print_r($params);
    echo "&lt;/pre&gt;";
};

$params = ["param1" =&gt; "value1", "param2" =&gt; "value2"];

// the same handlers are NOT called twice. The handler evt1_handler1
// should be called only one per fireEvent call
event()-&gt;addHandler("event1", "evt1_handler1");
event()-&gt;addHandler("event1", "evt1_handler1");

// the same handlers are NOT called twice. The handler evt1_handler2
// should be called only one per fireEvent call
event()-&gt;addHandler("event1", $evt1_handler2);
event()-&gt;addHandler("event1", $evt1_handler2);

// Closure
event()-&gt;addHandler("event1", function(string $event, array $params)
{
    echo "evt1_handler3 called: $event&lt;br&gt;";
    echo "&lt;pre&gt;";
    print_r($params);
    echo "&lt;/pre&gt;";
});

event()-&gt;addHandler("event2", function(string $event, array $params)
{
    echo "evt2_handler1 called: $event&lt;br&gt;";
    echo "&lt;pre&gt;";
    print_r($params);
    echo "&lt;/pre&gt;";
});
</pre>

        <?php
        function evt1_handler1(string $event, array $params)
        {
            echo "evt1_handler1 called: $event<br>";
            echo "<pre>";
            print_r($params);
            echo "</pre>";
        }

        $evt1_handler2 = function (string $event, array $params) {
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

        <h2>Firing events</h2>

        <pre class="code">
$params = ["p1" => 100, "p2" => 200];

event()->fireEvent("event1", $params);
event()->fireEvent("event2", $params);
</pre>
        <br>
        <?php
        event()->fireEvent("event1", $params);
        event()->fireEvent("event2", $params);
        ?>

        <h2>Suspending event1</h2>

        <pre class="code">
event()->suspendEvent("event1");

event()->fireEvent("event1", $params);
event()->fireEvent("event2", $params);
</pre>
        <br>

        <?php
        event()->suspendEvent("event1");

        event()->fireEvent("event1", $params);
        event()->fireEvent("event2", $params);
        ?>

        <p>No calls on event1, but event2 still fires.</p>

        <h2>Resuming event1</h2>

        <pre class="code">
event()->resumeEvent("event1");

event()->fireEvent("event1", $params);
event()->fireEvent("event2", $params);
</pre>
        <br>

        <?php
        event()->resumeEvent("event1");

        event()->fireEvent("event1", $params);
        event()->fireEvent("event2", $params);
        ?>

        <p>Both events event1 and event2 fire again.</p>

        <h2>Deleting handlers evt1_handler1 and evt1_handler2</h2>

        <pre class="code">
// It works only with named
event()->deleteHandler("event1", "evt1_handler1");
event()->deleteHandler("event1", $evt1_handler2);

event()->fireEvent("event1", $params);
event()->fireEvent("event2", $params);
</pre>
        <br>

        <?php
        // It works only with named
        event()->deleteHandler("event1", "\\MyApplication\\evt1_handler1");
        event()->deleteHandler("event1", $evt1_handler2);

        event()->fireEvent("event1", $params);
        event()->fireEvent("event2", $params);
        ?>

        <p>evt1_handler1 and evt1_handler2 are not called anymore by firing.</p>

        <h2>Deleting all handlers for event1</h2>

        <pre class="code">
event()->deleteHandlers("event1");

event()->fireEvent("event1", $params);
event()->fireEvent("event2", $params);
</pre>
        <br>

        <?php
        event()->deleteHandlers("event1");

        event()->fireEvent("event1", $params);
        event()->fireEvent("event2", $params);
        ?>

        <p>No calls on event1, but event2 still fires.</p>

        <h2>Deleting all handlers</h2>

        <pre class="code">
event()->deleteAllHandlers();

event()->fireEvent("event1", $params);
event()->fireEvent("event2", $params);
</pre>
        <br>

        <?php
        event()->deleteAllHandlers();

        event()->fireEvent("event1", $params);
        event()->fireEvent("event2", $params);
        ?>
        <p>No event calls anymore.</p>

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
