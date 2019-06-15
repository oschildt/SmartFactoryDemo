<?php

namespace MyApplication\Handlers;

use SmartFactory\Interfaces\IJsonApiRequestHandler;

//-------------------------------------------------------------------
// class LoginHandler
//-------------------------------------------------------------------
class LoginHandler implements IJsonApiRequestHandler
{
    public function handle($rmanager, $api_request, &$response_data, &$additional_headers)
    {
        /*
        if(!empty($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] != 'POST')
        {
          $response_data["result"] = "error";
          
          $response_data["errors"][] = ["error_code" => "method_not_supported", "error_text" => "POST request expected!"];
          
          $additional_headers[] = 'HTTP/1.1 401 Unauthorized';
          
          return;
        }
        */
        
        if (empty($_REQUEST["user"]) || empty($_REQUEST["password"])) {
            $response_data["result"] = "error";
            
            $response_data["errors"][] = ["error_code" => "login_failed", "error_text" => "Wrong login or password!"];
            
            $additional_headers[] = 'HTTP/1.1 401 Unauthorized';
            
            return;
        }
        
        $response_data["result"] = "success";
        
        $response_data["user"] = [
            "first_name" => "John",
            "Last_name" => "Smith"
        ];
    }
} // LoginHandler
//-------------------------------------------------------------------
