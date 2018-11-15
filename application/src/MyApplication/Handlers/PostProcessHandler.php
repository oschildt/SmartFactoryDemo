<?php
namespace MyApplication\Handlers;

use SmartFactory\Interfaces\IJsonApiRequestHandler;

//-------------------------------------------------------------------
// class PostProcessHandler
//-------------------------------------------------------------------
class PostProcessHandler implements IJsonApiRequestHandler
{
  public function handle($rmanager, $api_request, &$response_data, &$additional_headers)
  {
    $response_data["postprocess_message"] = "This message is added to every response by the PostProcessHandler!";
  }
} // PostProcessHandler
//-------------------------------------------------------------------
