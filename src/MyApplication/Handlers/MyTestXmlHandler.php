<?php

namespace MyApplication\Handlers;

use SmartFactory\XmlRequestHandler;
use SmartFactory\SmartException;

use function SmartFactory\reqvar_value;

//-------------------------------------------------------------------
// class MyTestXmlHandler
//-------------------------------------------------------------------
class MyTestXmlHandler extends XmlRequestHandler
{
    protected function exitWithErrors()
    {
        $errors = $this->response_xmldoc->createElement("Errors");
        $this->response_xmldoc->appendChild($errors);

        if (!empty($this->errors)) {
            foreach ($this->errors as $error) {
                $node = $this->response_xmldoc->createElement("Error");

                $node->setAttribute("Code", $error["error_code"]);
                $node->setAttribute("Type", $error["error_type"]);
                $text = $this->response_xmldoc->createTextNode($error["error_text"]);
                $node->appendChild($text);

                $errors->appendChild($node);
            }
        }

        $this->sendXmlResponse();
        exit;
    } // exitWithErrors

    protected function parseXmlInput()
    {
        try {
            //$content_type = empty($this->request_headers["Content-Type"]) ? "" : $this->request_headers["Content-Type"];
            //if (!preg_match("/application\/xml.*/", $content_type)) {
            //    throw new SmartException(sprintf("Content type 'application/xml' is expected, got '%s'!", $content_type), SmartException::ERR_CODE_INVALID_CONTENT_TYPE, SmartException::ERR_TYPE_PROGRAMMING_ERROR);
            //}

            //$xmldata = trim(file_get_contents("php://input"));

            // For demo we take not frow rawdata.

            $xmldata = reqvar_value("xmldata");

            if (empty($xmldata)) {
                throw new SmartException("The request XML is empty!", SmartException::ERR_CODE_MISSING_REQUEST_DATA, SmartException::ERR_TYPE_PROGRAMMING_ERROR);
            }

            $this->request_xmldoc = new \DOMDocument("1.0", "UTF-8");
            $this->request_xmldoc->formatOutput = true;
            if (!@$this->request_xmldoc->loadXML($xmldata)) {
                throw new SmartException("Error by XML parsing!", SmartException::ERR_CODE_XML_PARSE_ERROR, SmartException::ERR_TYPE_PROGRAMMING_ERROR);
            }
        } catch (SmartException $ex) {
            throw $ex;
        } catch (\Throwable $ex) {
            throw new SmartException($ex->getMessage(), SmartException::ERR_CODE_XML_PARSE_ERROR, SmartException::ERR_TYPE_PROGRAMMING_ERROR);
        }
    } // parseXmlInput

    function get_rooms()
    {
        $xsdpath = new \DOMXPath($this->request_xmldoc);

        $nodes = $xsdpath->evaluate("/Request/City");
        if ($nodes->length == 0) {
            throw new SmartException("City is undefined!", "no_city", SmartException::ERR_TYPE_USER_ERROR);
        }
        $city = $nodes->item(0)->nodeValue;

        $response = $this->response_xmldoc->createElement("Response");
        $this->response_xmldoc->appendChild($response);

        $node = $this->response_xmldoc->createElement("City");
        $response->appendChild($node);

        $text = $this->response_xmldoc->createTextNode($city);
        $node->appendChild($text);

        $rooms = $this->response_xmldoc->createElement("Rooms");
        $response->appendChild($rooms);

        $node = $this->response_xmldoc->createElement("Room");
        $node->setAttribute("Price", 100);
        $rooms->appendChild($node);
        $text = $this->response_xmldoc->createTextNode("Single");
        $node->appendChild($text);

        $node = $this->response_xmldoc->createElement("Room");
        $node->setAttribute("Price", 200);
        $rooms->appendChild($node);
        $text = $this->response_xmldoc->createTextNode("Double");
        $node->appendChild($text);
    } // get_rooms
} // MyTestXmlHandler
//-------------------------------------------------------------------
