<?php
namespace MyApplication;

use function SmartFactory\messenger;

function report_messages()
{
    if (messenger()->hasProgWarnings()) {
        echo "<div class='message_block'>";
        echo "<div class='message_header'>Porgrammer warnings</div>";
        echo "<div class='message_body'>";
        print_r(messenger()->getProgWarnings());
        echo "</div>";
        echo "</div>";
    }

    if (messenger()->hasErrors()) {
        echo "<div class='message_block'>";
        echo "<div class='message_header'>Errors</div>";
        echo "<div class='message_body'>";
        print_r(messenger()->getErrors());
        echo "</div>";
        echo "</div>";
    }

    if (messenger()->hasWarnings()) {
        echo "<div class='message_block'>";
        echo "<div class='message_header'>Warnings</div>";
        echo "<div class='message_body'>";
        print_r(messenger()->getWarnings());
        echo "</div>";
        echo "</div>";
    }

    if (messenger()->hasInfoMessages()) {
        echo "<div class='message_block'>";
        echo "<div class='message_header'>Information messages</div>";
        echo "<div class='message_body'>";
        print_r(messenger()->getInfoMessages());
        echo "</div>";
        echo "</div>";
    }
} // report_messages
?>
