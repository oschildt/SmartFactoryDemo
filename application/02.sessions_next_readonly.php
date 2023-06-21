<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\session;
use function SmartFactory\session_vars;
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

<pre class="code">
echo "session name: " . session()->getSessionName();

echo "session id: " . session()->getSessionId();
</pre>

<?php
echo "<p>session name: " . session()->getSessionName() . "</p>";

echo "<p>session id: " . session()->getSessionId() . "</p>";
?>

<h3>Getting session vars</h3>

<pre class="code">
echo "user name value: " . session()->vars()["user"]["name"];

echo "user age value: " . session()->vars()["user"]["age"];

echo "user age sex: " . session_vars()["user"]["sex"];
</pre>

<?php
echo "<p>user name value: " . session()->vars()["user"]["name"] . "</p>";

echo "<p>user age value: " . session()->vars()["user"]["age"] . "</p>";

echo "<p>user age sex: " . session_vars()["user"]["sex"] . "</p>";
?>

<h3>Unsetting a session variable</h3>

<pre class="code">
unset(session()->vars()["user"]["name"]);
unset(session_vars()["user"]["sex"]);
</pre>

<?php
unset(session()->vars()["user"]["name"]);
unset(session_vars()["user"]["sex"]);

echo "<p>user name value: " . checkempty(session()->vars()["user"]["name"]) . "</p>";

echo "<p>user age value: " . checkempty(session()->vars()["user"]["age"]) . "</p>";

echo "<p>user age sex: " . checkempty(session_vars()["user"]["sex"]) . "</p>";
?>

<h3>Clearing session variables</h3>

<pre class="code">
session()->clearSession();
</pre>

<?php
session()->clearSession();

echo "<p>user name value: " . checkempty(session()->vars()["user"]["name"]) . "</p>";

echo "<p>user age value: " . checkempty(session()->vars()["user"]["age"]) . "</p>";

echo "<p>user age sex: " . checkempty(session_vars()["user"]["sex"]) . "</p>";
?>

</body>
</html>

