<?php

namespace MyApplication;

use SmartFactory\XmlApiRequestManager;

//-------------------------------------------------------------------
// class HotelXmlApiRequestManager
//-------------------------------------------------------------------
class HotelXmlApiRequestManager extends XmlApiRequestManager
{
    //-----------------------------------------------------------------
    protected function parseXML(&$api_request, &$xmldoc)
    {
        $response_data = [];
        
        // raw post data
        //$xmldata = file_get_contents("php://input");
        // postvar
        $xmldata = !empty($_POST["xmldata"]) ? $_POST["xmldata"] : "";
        
        $xmldoc = new \DOMDocument();
        $xmldoc->formatOutput = true;
        if (empty($xmldata) || !@$xmldoc->loadXML($xmldata)) {
            $response_data["errors"] = [
                ["error_code" => "xml_invalid", "error_text" => "The submitted data is not a valid XML structure!"]
            ];
            
            $this->reportErrors($response_data);
            return false;
        }
        
        $xsdpath = new \DOMXPath($xmldoc);
        
        $nodes = $xsdpath->evaluate("/Request/User/Login");
        if ($nodes->length == 0) {
            $response_data["errors"] = [
                ["error_code" => "no_login_data", "error_text" => "The Authentication block User with Login and Password is missing!"]
            ];
            
            $this->reportErrors($response_data, ['HTTP/1.1 401 Unauthorized']);
            return false;
        }
        $login = $nodes->item(0)->nodeValue;
        
        $nodes = $xsdpath->evaluate("/Request/User/Password");
        if ($nodes->length == 0) {
            $response_data["errors"] = [
                ["error_code" => "no_login_data", "error_text" => "The authentication block User with Login and Password is missing!"]
            ];
            
            $this->reportErrors($response_data, ['HTTP/1.1 401 Unauthorized']);
            return false;
        }
        $password = $nodes->item(0)->nodeValue;
        
        if (empty($login) || empty($password)) {
            $response_data["errors"] = [
                ["error_code" => "login_failed", "error_text" => "Wrong login or password!"]
            ];
            
            $this->reportErrors($response_data, ['HTTP/1.1 401 Unauthorized']);
            return false;
        }
        
        $nodes = $xsdpath->evaluate("/Request/Action");
        if ($nodes->length == 0) {
            $response_data["errors"] = [
                ["error_code" => "no_action_defined", "error_text" => "The request is unrecognized, the Action block is missing!"]
            ];
            
            $this->reportErrors($response_data);
            return false;
        }
        
        $api_request = $nodes->item(0)->nodeValue;
        
        return $xmldoc;
    } // parseXML
    
    //-----------------------------------------------------------------
    public function reportErrors($response_data, $headers = [])
    {
        if (!empty($headers)) {
            if (is_array($headers)) {
                foreach ($headers as $header) {
                    header($header);
                }
            }
        }
        
        $outxmldoc = new \DOMDocument("1.0", "UTF-8");
        $outxmldoc->formatOutput = true;
        
        $errors = $outxmldoc->createElement("Errors");
        $outxmldoc->appendChild($errors);
        
        if (!empty($response_data["errors"])) {
            foreach ($response_data["errors"] as $error) {
                $node = $outxmldoc->createElement("Error");
            }
            $node->setAttribute("Code", $error["error_code"]);
            $text = $outxmldoc->createTextNode($error["error_text"]);
            $node->appendChild($text);
            
            $errors->appendChild($node);
        }
        
        $this->sendXMLResponse($outxmldoc);
        
        return true;
    } // reportErrors
    //-----------------------------------------------------------------
} // HotelXmlApiRequestManager
//-------------------------------------------------------------------
