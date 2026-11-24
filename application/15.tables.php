<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\table;
use function SmartFactory\format_number;
use function SmartFactory\text;
?><!DOCTYPE html>
<html lang="en">

<head>
    <title>Tables from array</title>
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

        <h1>Tables from array</h1>

        <p>This example demonstrates how to generate an HTML table from an array with column captions, CSS classes, and custom value formatting.</p>

        <?php
        $rows = [
            ["name" => "DB design", "employee" => "Alex", "time_estimation" => "5", "deadline" => time() + 3 * 24 * 3600, "comments" => "No comments"],
            ["name" => "Mask implementation", "employee" => "Boris", "time_estimation" => "3", "deadline" => time() + 6 * 24 * 3600, "comments" => "No comments"],
            ["name" => "Validation", "employee" => "Alex", "time_estimation" => "2", "deadline" => time() + 3 * 24 * 3600, "comments" => "No comments"],
            ["name" => "Settings", "employee" => "Boris", "time_estimation" => "8", "deadline" => time() + 6 * 24 * 3600, "comments" => "No comments"],
            ["name" => "About", "employee" => "Robert", "time_estimation" => "3", "deadline" => time() + 5 * 24 * 3600, "comments" => "No comments"],
            ["name" => "Initialization", "employee" => "Alon", "time_estimation" => "4", "deadline" => time() + 4 * 24 * 3600, "comments" => "No comments"]
        ];

        $captions = [
            "name" => "Task name",
            "employee" => "Employee",
            "time_estimation" => "Time estimation",
            "deadline" => "Deadline",
            "comments" => "Conmments"
        ];

        $formatter = function ($rownum, $colnum, $colname, $val) {

            if ($colname == "time_estimation") {
                return format_number($val, 2);
            }
            if ($colname == "deadline") {
                return date(text("DateTimeFormat"), $val);
            }

            return $val;
        };
        ?>

        <?php table(
            $rows,
            [
                "captions" => $captions,
                "class" => "example_table",
                "col_class_from_keys" => true,
                "no_escape_html" => false,
                "formatter" => $formatter
            ]
        ); ?>

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
