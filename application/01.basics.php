<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\singleton;

use MyApplication\Interfaces\IUser;
?><!DOCTYPE html>
<html lang="en">
<head>
<title>Object creation over factory</title>

<link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Object creation over factory</h2>

<?php
echo "<p>Application root: " . approot() . "</p>";

$user = singleton(IUser::class);
?>

<div class="code">$user = singleton(IUser::class);

echo "First name: " . $user->getUserFirstName();

echo "Last name: " . $user->getUserLastName();
</div>

<?php
echo "<p>First name: " . $user->getUserFirstName() . "</p>";

echo "<p>Last name: " . $user->getUserLastName() . "</p>";
?>

</body>
</html>
