<?php
namespace MyApplication;

require "../vendor/autoload.php";

use function SmartFactory\messenger;
use function SmartFactory\session;
use function SmartFactory\input_text;
use function SmartFactory\input_password;
use function SmartFactory\text;
use function SmartFactory\textarea;
use function SmartFactory\select;
use function SmartFactory\checkbox;
use function SmartFactory\radiobutton;
use function SmartFactory\echo_html;
?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Form fields</title>

    <link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Form fields</h2>

<?php
function process_form()
{
    if (empty($_REQUEST["act"])) {
        return true;
    }
    
    messenger()->addInfoMessage(text("MsgSettingsSaved"));
} // process_form

process_form();
?>

<?php
report_messages();
?>

<form action="11.form_fields.php" method="post">

    <b>Input field:</b>
    <br>
    <?php input_text([
        "id" => "data_input_text",
        "name" => "data[input_text]",
        //"value" => "value",
        "class" => "my_class",
        "style" => "width: 300px",
        "placeholder" => "enter the data",
        "title" => "enter the data",
        "data-prop" => "some-prop",
        "onblur" => "alert('Hello on Blur!')"
    ]); ?>

    <br><br>
    <b>Input password:</b>
    <br>
    <?php input_password([
        "id" => "data_input_password",
        "name" => "data[input_password]",
        "class" => "my_class",
        "style" => "width: 300px",
        "placeholder" => "enter the password",
        "title" => "enter the password",
        "data-prop" => "some-prop"
    ]); ?>

    <br><br>
    <b>Text area:</b>
    <br>
    <?php textarea([
        "id" => "data_textarea",
        "name" => "data[textarea]",
        "class" => "my_class",
        //"value" => "value",
        "style" => "width: 300px; height: 150px;",
        "placeholder" => "enter the text",
        "title" => "enter the text",
        "data-prop" => "some-prop",
        "onblur" => "alert('Hello on Blur!')"
    ]); ?>

    <br><br>
    
    <?php
    $options = [
        "" => "-",
        "yellow" => "Yellow",
        "blue" => "Blue",
        "red" => "Red",
        "brown" => "Brown",
        "black" => "Black",
        "white" => "White",
        "green" => "Green"
    ];
    ?>

    <b>Select:</b>
    <br>
    <?php select([
        "id" => "data_select",
        "name" => "data[select]",
        "class" => "my_class",
        //"value" => "red",
        "style" => "width: 300px",
        "title" => "select single value",
        "data-prop" => "some-prop",
        "options" => $options
    ]); ?>

    <br>
    <br>
    
    <?php
    $options = [
        "yellow" => "Yellow",
        "blue" => "Blue",
        "red" => "Red",
        "brown" => "Brown",
        "black" => "Black",
        "white" => "White",
        "green" => "Green"
    ];
    ?>

    <b>Multi select:</b>
    <br>
    <?php select([
        "id" => "data_multiselect",
        "name" => "data[multiselect][]",
        "multiple" => "multiple",
        "class" => "my_class",
        //"value" => "red",
        "style" => "width: 300px; height: 180px",
        "title" => "select multi value",
        "data-prop" => "some-prop",
        "options" => $options
    ]); ?>

    <br>
    <br>

    <b>Checkbox:</b>
    <br><br>
    <?php checkbox([
        "id" => "data_checkbox",
        "name" => "data[checkbox]",
        "class" => "my_class",
        "value" => "1",
        //"checked" => true,
        "title" => "select checkbox",
        "data-prop" => "some-prop"
    ]); ?> <label for="data_checkbox">checkbox label</label>

    <br>
    <br>

    <b>Checkbox group:</b>
    <br><br>
    
    <?php
    $options = [
        "yellow" => "Yellow",
        "blue" => "Blue",
        "red" => "Red",
        "brown" => "Brown",
        "lack" => "Black",
        "white" => "White",
        "green" => "Green"
    ];
    
    foreach ($options as $val => $text):
        ?>
        
        <?php checkbox([
        "id" => "data_ckbxcolor_$val",
        "name" => "data[ckbxcolor][]",
        "class" => "my_class",
        "value" => $val,
        //"checked" => true,
        "title" => "select checkbox",
        "data-prop" => "some-prop"
    ]); ?> <label for="data_ckbxcolor_<?php echo_html($val); ?>"><?php echo_html($text); ?></label>

        <br>
    
    <?php endforeach; ?>

    <br>

    <b>Radio group:</b>
    <br><br>
    
    <?php
    $options = [
        "yellow" => "Yellow",
        "blue" => "Blue",
        "red" => "Red",
        "brown" => "Brown",
        "lack" => "Black",
        "white" => "White",
        "green" => "Green"
    ];
    
    foreach ($options as $val => $text):
        ?>
        
        <?php radiobutton([
        "id" => "data_radiocolor_$val",
        "name" => "data[radiocolor]",
        "class" => "my_class",
        "value" => $val,
        //"checked" => true,
        "title" => "select radiobutton",
        "data-prop" => "some-prop"
    ]); ?> <label for="data_radiocolor_<?php echo_html($val); ?>"><?php echo_html($text); ?></label>

        <br>
    
    <?php endforeach; ?>

    <br>

    <input type="submit" name="act" value="Submit">

</form>

</body>
</html>



