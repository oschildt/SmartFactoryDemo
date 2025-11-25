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

<p>We define an interface and implement a class of it.</p>

<pre class="code">
interface IUser
{
    public function getUserFirstName();
    public function getUserLastName();
}

class User implements IUser
{
    public $first_name = "John";

    public function getUserFirstName()
    {
        return $this->first_name;
    }

    public function getUserLastName()
    {
        return "Smith";
    }
}

class UserExtended implements IUser
{
    public $first_name = "John!";

    public function getUserFirstName()
    {
        return $this->first_name;
    }

    public function getUserLastName()
    {
        return "Smith!";
    }
}
</pre>

<p>We bind the current implementation to the interface. If we need to change the
    implementation, we just bind another implementation of the interface.</p>

<pre class="code">
ObjectFactory::bindClass(IUser::class, User::class);
ObjectFactory::bindClassContext("extended", IUser::class, UserExtended::class);
</pre>

<p>We request the object over the factory.</p>

<pre class="code">
$user = singleton(IUser::class);
$ext_user = singleton(IUser::class, "extended");

echo "First name: " . $user->getUserFirstName();
echo "Last name: " . $user->getUserLastName();
</pre>

<?php
$user = singleton(IUser::class);

echo "<p>First name: " . $user->getUserFirstName() . "</p>";
echo "<p>Last name: " . $user->getUserLastName() . "</p>";

$ext_user = singleton(IUser::class, "extended");

echo "<p>First name (ext): " . $ext_user->getUserFirstName() . "</p>";
echo "<p>Last name (ext): " . $ext_user->getUserLastName() . "</p>";
?>

</body>
</html>
