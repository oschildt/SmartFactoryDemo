<?php
namespace MyApplication;

require __DIR__ . "/../vendor/autoload.php";

use function SmartFactory\debugger;
?><!DOCTYPE html>
<html lang="en">

<head>
    <title>Debugging, logging, profiling</title>
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

        <h1>Debugging, logging, profiling</h1>

        <p>This example demonstrates how to log messages, include call stacks, and measure execution times using
            <i>DebugProfiler</i>.
        </p>

        <p>We bind a class to the IDebugProfiler interface and initialize it.</p>

        <pre class="code">
ObjectFactory::bindFactory(IDebugProfiler::class, function (): DebugProfiler {
    $instance = new DebugProfiler();

    $instance->init([
        "log_path" => approot() . "logs/",
        "source_root" => approot()
    ]);

    return $instance;
});
</pre>

        <p>Each entry includes a timestamp and a request identifier. Enable source details with
            <code>debugger()->enableFileAndLineDetails(true)</code>. File names must not contain directory components.
        </p>

        <h2>Logging a message to a user defined file logs/mylog.log</h2>

        <pre class="code">
debugger()->debugMessage("some data 1 ...", false, "mylog.log");

debugger()->debugMessage("some data 2 with call stack ...", true, "mylog.log");
</pre>

        <p>Listing: logs/mylog.log</p>

        <pre class="code">
<?php
debugger()->clearLogFiles();

debugger()->debugMessage("some data 1 ...", false, "mylog.log");

debugger()->debugMessage("some data 2 with call stack ...", true, "mylog.log");

echo htmlspecialchars(file_get_contents(approot() . "logs/mylog.log"), ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
?>
</pre>

        <h2>Logging a message to the debug file logs/debug.log</h2>

        <pre class="code">
debugger()->debugMessage("some debug data 1 ...");

debugger()->debugMessage("some debug data 2 with call stack ...", true);
</pre>

        <p>Listing: logs/debug.log</p>

        <pre class="code">
<?php
debugger()->debugMessage("some debug data 1 ...");

debugger()->debugMessage("some debug data 2 with call stack ...", true);

echo htmlspecialchars(file_get_contents(approot() . "logs/debug.log"), ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
?>
</pre>

        <h2>Profiling long operations and logging to the profile file logs/profile.log</h2>

        <p>Each profiler instance measures its own intervals with a monotonic timer. Writing the log
            is excluded from the intervals. This example takes approximately 11 seconds.</p>

        <?php
        debugger()->startProfilePoint("Profiling started");

        // Long-running operation #1.
        sleep(3);

        debugger()->fixProfilePoint("Long running operation #1 completed");

        // Long-running operation #2.
        sleep(2);

        debugger()->fixProfilePoint("Long running operation #2 completed");

        // Operation #3 is intentionally excluded from profiling.
        sleep(2);

        // Restart the timer to exclude operation #3 from the next interval.
        debugger()->startProfilePoint("Long running operation #4 started");

        // Long-running operation #4.
        sleep(4);

        debugger()->fixProfilePoint("Long running operation #4 completed");
        ?>

        <pre class="code">
debugger()->startProfilePoint("Profiling started");

// Long-running operation #1.
sleep(3);

debugger()->fixProfilePoint("Long running operation #1 completed");

// Long-running operation #2.
sleep(2);

debugger()->fixProfilePoint("Long running operation #2 completed");

// Operation #3 is intentionally excluded from profiling.
sleep(2);

// Restart the timer to exclude operation #3 from the next interval.
debugger()->startProfilePoint("Long running operation #4 started");

// Long-running operation #4.
sleep(4);

debugger()->fixProfilePoint("Long running operation #4 completed");
</pre>

        <p>Listing: logs/profile.log</p>

        <pre class="code">
<?php
echo htmlspecialchars(file_get_contents(approot() . "logs/profile.log"), ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
?>
</pre>

        <h2>Useful functions</h2>

        <p>Deleting a missing file succeeds. Failure to delete an existing file throws an exception.
            The demo clears all <code>.log</code> files in the application's log directory when it starts.</p>

        <pre class="code">
debugger()->clearLogFile("mylog.log");

debugger()->clearLogFiles();
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
