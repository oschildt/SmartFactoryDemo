<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\messenger;
use function SmartFactory\session;

session()->startSession();
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Messages</title>

    <link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Messages</h2>

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

</body>
</html>


