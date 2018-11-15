<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\singleton;
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
<h2>Messages</h2>

<p>Setting some warnings, errors etc. then redirecting. Messages are not
lost even over many requests. They are cleared only after they are retrieved for display.</p>

<div class="code">messenger()->setError("Error message 1");

messenger()->setError("Error message 2", "Error 2 details");

// setting redundantly 
messenger()->setError("Error message 1");

messenger()->setWarning("Warning 1");

messenger()->setWarning("Warning 1");

messenger()->setErrorElement("first_name");
messenger()->setActiveTab(2);
messenger()->setInfo("Data saved successfully!", "", true);

// produce prog warning
$a = $b;
</div>

<?php
messenger()->clearAll();

messenger()->setError("Error message 1");

messenger()->setError("Error message 2", "Error 2 details");

// setting redundantly 
messenger()->setError("Error message 1");

messenger()->setWarning("Warning 1");

messenger()->setWarning("Warning 1");

messenger()->setErrorElement("first_name");
messenger()->setActiveTab(2);
messenger()->setInfo("Data saved successfully!", "", true);

// produce prog warning
$a = $b;
?>

<p><a href="08.messages_next.php" target="_blank">Next request</a></p>

</body>
</html>


