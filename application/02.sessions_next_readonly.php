<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\singleton;
use function SmartFactory\session;
use function SmartFactory\checkempty;

session()->startSession(true);
?><!DOCTYPE html>
<html lang="en">
<head>
<title>Sessions</title>

<link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Sessions - next step readonly</h2>

<div class="code">echo "session name: " . session()->getSessionName();

echo "session id: " . session()->getSessionId();
</div>

<?php
echo "<p>session name: " . session()->getSessionName() . "</p>";

echo "<p>session id: " . session()->getSessionId() . "</p>";
?>

<h3>Getting session vars</h3>

<div class="code">echo "user name value: " . session()->vars()["user"]["name"];

echo "user age value: " . session()->vars()["user"]["age"];

echo "user age sex: " . session()->vars()["user"]["sex"];
</div>

<?php
echo "<p>user name value: " . session()->vars()["user"]["name"] . "</p>";

echo "<p>user age value: " . session()->vars()["user"]["age"] . "</p>";

echo "<p>user age sex: " . session()->vars()["user"]["sex"] . "</p>";
?>

<h3>Destroying session has no effect in redonly mode</h3>

<div class="code">// destroySession has no effect in redonly mode
session()->destroySession();
</div>

<?php
session()->destroySession();

echo "<p>user name value: " . checkempty(session()->vars()["user"]["name"]) . "</p>";

echo "<p>user age value: " . checkempty(session()->vars()["user"]["age"]) . "</p>";

echo "<p>user age sex: " . checkempty(session()->vars()["user"]["sex"]) . "</p>";
?>

<h3>Unsetting variables in redonly mode is possible</h3>

<div class="code">
session()->clearSession();
</div>

<?php
session()->clearSession();

echo "<p>user name value: " . checkempty(session()->vars()["user"]["name"]) . "</p>";

echo "<p>user age value: " . checkempty(session()->vars()["user"]["age"]) . "</p>";

echo "<p>user age sex: " . checkempty(session()->vars()["user"]["sex"]) . "</p>";
?>

</body>
</html>

