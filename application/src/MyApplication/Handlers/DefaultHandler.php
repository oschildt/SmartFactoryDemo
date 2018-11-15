<?php
namespace MyApplication\Handlers;

use SmartFactory\Interfaces\IJsonApiRequestHandler;

//-------------------------------------------------------------------
// class DefaultHandler
//-------------------------------------------------------------------
class DefaultHandler implements IJsonApiRequestHandler
{
  public function handle($rmanager, $api_request, &$response_data, &$additional_headers)
  {
    $response_data["result"] = "success";

    $response_data["message"] = "Default handler action performed!";
      
    return false;
  }
} // DefaultHandler
//-------------------------------------------------------------------
