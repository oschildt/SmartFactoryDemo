<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\session;
use function SmartFactory\messenger;

session()->startSession();
?><!DOCTYPE html>
<html lang="en">
<head>
<title>Messages</title>

<link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Messages - next step</h2>

<p>Display error and warning messages collected over the execution.</p>

<?php
report_messages();
?>

</body>
</html>


