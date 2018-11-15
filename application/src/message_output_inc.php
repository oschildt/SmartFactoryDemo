<?php
namespace MyApplication;

use function SmartFactory\messenger;

function report_messages()
{
  $response = [];

  messenger()->addMessagesToResponse($response);
  
  if(!empty($response["FOCUS_ELEMENT"])) echo "Focus element: " . $response["FOCUS_ELEMENT"] . "<br>";
  if(!empty($response["ERROR_ELEMENT"])) echo "Error element: " . $response["ERROR_ELEMENT"] . "<br>";
  if(!empty($response["ACTIVE_TAB"])) echo "Active tab: " . $response["ACTIVE_TAB"] . "<br>";
    
  if(!empty($response["PROG_WARNINGS"]))  
  {
    echo "<div class='message_block'>";
    echo "<div class='message_header'>Porgrammer warning</div>";
    echo "<div class='message_body'>";
    print_r($response["PROG_WARNINGS"]);
    echo "</div>";
    echo "</div>";
  }
    
  if(!empty($response["ERROR_MESSAGES"]))  
  {
    echo "<div class='message_block'>";
    echo "<div class='message_header'>Error</div>";
    echo "<div class='message_body'>";
    print_r($response["ERROR_MESSAGES"]);
    echo "</div>";
    echo "</div>";
  }

  if(!empty($response["WARNING_MESSAGES"]))  
  {
    echo "<div class='message_block'>";
    echo "<div class='message_header'>Warning</div>";
    echo "<div class='message_body'>";
    print_r($response["WARNING_MESSAGES"]);
    echo "</div>";
    echo "</div>";
  }

  if(!empty($response["INFO_MESSAGES"]))  
  {
    echo "<div class='message_block'>";
    echo "<div class='message_header'>Information</div>";
    echo "<div class='message_body'>";
    print_r($response["INFO_MESSAGES"]);
    echo "</div>";
    echo "</div>";
  }
} // report_messages
?>
