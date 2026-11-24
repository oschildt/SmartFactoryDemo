<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\messenger;
?><!DOCTYPE html>
<html lang="en">

<head>
    <title>Messages</title>
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

        <h1>Messages</h1>

        <p>This example demonstrates how to collect and display errors, warnings, and information messages using <i>Messenger</i>, which preserves messages across requests.</p>

        <p>Setting some warnings, errors etc. then redirecting. Messages are not
            lost even over many requests. They are cleared only after they are retrieved for display.</p>

        <pre class="code">
messenger()->addError("Error message 1");

messenger()->addError("Error message 2", "last_name", "err_msg2", "Error 2 details");

// setting redundantly
messenger()->addError("Error message 1");

messenger()->addWarning("Warning 1");

messenger()->addWarning("Warning 2");

messenger()->addInfoMessage("Data saved successfully!", true);

// produce prog warning
$a = $b;
</pre>

        <?php
        messenger()->clearAll();

        messenger()->addError("Error message 1");

        messenger()->addError("Error message 2", ["max_count" => 4], "last_name", "err_msg2", "Error 2 details");

        // setting redundantly
        messenger()->addError("Error message 1");

        messenger()->addWarning("Warning 1");

        messenger()->addWarning("Warning 2");

        messenger()->addInfoMessage("Data imported successfully!", ["records_imported" => 120], true);

        // produce prog warning
        $a = $b;
        ?>

        <?php
        report_messages();
        ?>

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
