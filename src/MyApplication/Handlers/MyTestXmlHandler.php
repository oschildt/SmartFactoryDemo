<?php

namespace MyApplication\Handlers;

use SmartFactory\XmlRequestHandler;
use SmartFactory\SmartException;

use function SmartFactory\messenger;
use function SmartFactory\reqvar_value;

//-------------------------------------------------------------------
// class MyTestXmlHandler
//-------------------------------------------------------------------
class MyTestXmlHandler extends XmlRequestHandler
{
    protected function parseInput()
    {
        try {
            //$content_type = empty($this->request_headers["Content-Type"]) ? "" : $this->request_headers["Content-Type"];
            //if (!preg_match("/application\/xml.*/", $content_type)) {
            //    throw new SmartException(sprintf("Content type 'application/xml' is expected, got '%s'!", $content_type), SmartException::ERR_CODE_INVALID_CONTENT_TYPE);
            //}

            //$xmldata = trim(file_get_contents("php://input"));

            // For demo we take not frow rawdata.

            $xmldata = reqvar_value("xmldata");

            if (empty($xmldata)) {
                throw new SmartException("The request XML is empty!", SmartException::ERR_CODE_MISSING_REQUEST_DATA);
            }

            $this->request_xmldoc = new \DOMDocument("1.0", "UTF-8");
            $this->request_xmldoc->formatOutput = true;
            if (!@$this->request_xmldoc->loadXML($xmldata)) {
                throw new SmartException("Error by XML parsing!", SmartException::ERR_CODE_XML_PARSE_ERROR);
            }
        } catch (SmartException $ex) {
            throw $ex;
        } catch (\Throwable $ex) {
            throw new SmartException($ex->getMessage(), SmartException::ERR_CODE_XML_PARSE_ERROR);
        }
    } // parseInput

    function addMessagesToResponse()
    {
        if(messenger()->hasErrors()) {
            $xmlerrors = $this->response_xmldoc->createElement("Errors");
            $this->response_xmldoc->appendChild($xmlerrors);

            $errors = messenger()->getErrors();

            foreach ($errors as $error) {
                $node = $this->response_xmldoc->createElement("Error");

                $node->setAttribute("Code", $error["code"]);
                $node->setAttribute("Type", $error["type"]);
                $text = $this->response_xmldoc->createTextNode($error["message"]);
                $node->appendChild($text);

                $xmlerrors->appendChild($node);
            }
        }
    }

    function get_rooms()
    {
        $xsdpath = new \DOMXPath($this->request_xmldoc);

        $nodes = $xsdpath->evaluate("/Request/City");
        if ($nodes->length == 0) {
            messenger()->addError("City is undefined!", [], "", "no_city");

            messenger()->addError("Price preferences is undefined!", [], "", "no_price_preferences");

            return;
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
