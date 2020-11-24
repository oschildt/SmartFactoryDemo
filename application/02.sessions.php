<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\singleton;
use function SmartFactory\session;

session()->startSession();
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Sessions</title>

    <link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Sessions</h2>

<h3>Starting session</h3>

<pre class="code">
session() = singleton(ISessionManager::class);
session()->startSession();

echo "session name: " . session()->getSessionName();

echo "session id: " . session()->getSessionId();
</pre>

<?php
echo "<p>session name: " . session()->getSessionName() . "</p>";

echo "<p>session id: " . session()->getSessionId() . "</p>";

session()->clearSession();
?>

<h3>Setting session vars</h3>

<?php
session()->vars()["user"]["name"] = "Alex";
session()->vars()["user"]["age"] = "22";

// it works also, & is important
$sessionvars = &session()->vars();

$sessionvars["user"]["sex"] = "M";
?>

<pre class="code">
session()->vars()["user"]["name"] = "Alex";
session()->vars()["user"]["age"] = "22";

// it works also, &amp; is important
$sessionvars = &amp;session()->vars();

$sessionvars["user"]["sex"] = "M";

echo "user name value: " . session()->vars()["user"]["name"];

echo "user age value: " . session()->vars()["user"]["age"];

echo "user age sex: " . session()->vars()["user"]["sex"];
</pre>

<?php
echo "<p>user name value: " . session()->vars()["user"]["name"] . "</p>";

echo "<p>user age value: " . session()->vars()["user"]["age"] . "</p>";

echo "<p>user age sex: " . session()->vars()["user"]["sex"] . "</p>";
?>

<p><a href="02.sessions_next.php" target="_blank">Next request</a>&nbsp;&nbsp;&nbsp;&nbsp;

    <a href="02.sessions_next_readonly.php" target="_blank">Next request with non-blocking readonly session</a></p>

</body>
</html>