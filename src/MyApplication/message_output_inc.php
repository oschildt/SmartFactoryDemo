<?php

namespace MyApplication;

use function SmartFactory\messenger;

/**
 * Renders queued programming warnings, errors, warnings, and informational messages as HTML blocks.
 *
 * @return void
 * Writes one block per nonempty category and clears that category as its messages are fetched.
 *
 * @throws \Throwable
 * If resolving the message manager or accessing a message category fails.
 *
 * @author Oleg Schildt
 */
function report_messages(): void
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

