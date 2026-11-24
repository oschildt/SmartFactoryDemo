<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\singleton;

use MyApplication\Interfaces\IUser;
?><!DOCTYPE html>
<html lang="en">

<head>
    <title>Object creation over factory</title>
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

        <h1>Object creation over factory</h1>

        <p>This example demonstrates the creation of objects using the factory <i>ObjectFactory</i>.</p>

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
ObjectFactory::bindFactory(IUser::class, function (): User {
    $instance = new User();

    return $instance;
});

ObjectFactory::bindFactory(IUser::class, function (): UserExtended {
    $instance = new UserExtended();

    return $instance;
}, "extended");
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
