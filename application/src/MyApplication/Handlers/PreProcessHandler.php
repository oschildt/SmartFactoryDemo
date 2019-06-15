<?php

namespace MyApplication\Handlers;

use SmartFactory\Interfaces\IJsonApiRequestHandler;

//-------------------------------------------------------------------
// class PreProcessHandler
//-------------------------------------------------------------------
class PreProcessHandler implements IJsonApiRequestHandler
{
    public function handle($rmanager, $api_request, &$response_data, &$additional_headers)
    {
        $response_data["preprocess_message"] = "This message is added to every response by the PreProcessHandler!";
    }
} // PreProcessHandler
//-------------------------------------------------------------------
