<?php

namespace MyApplication\Handlers;

use SmartFactory\XmlRequestHandler;
use SmartFactory\SmartException;

use function SmartFactory\messenger;

/**
 * Demonstrates XML API actions using an XML body submitted through an HTML form field.
 *
 * @author Oleg Schildt
 */
class MyTestXmlHandler extends XmlRequestHandler
{
    /**
     * Parses the demo form's xmldata field using the standard XML payload validation.
     *
     * The HTML example submits form data rather than an application/xml request body.
     *
     * @return void
     * Populates request_xmldoc without applying the raw-body Content-Type requirement.
     *
     * @throws SmartException
     * If xmldata is absent, is not a string, is blank, or contains malformed XML.
     *
     * @author Oleg Schildt
     */
    protected function parseInput(): void
    {
        $xml = $_REQUEST["xmldata"] ?? "";

        if (!is_string($xml)) {
            throw new SmartException("The XML form field must be a string!", SmartException::ERR_CODE_INVALID_REQUEST_DATA);
        }

        $this->parseXml($xml);
    } // parseInput

    /**
     * Consumes queued errors and builds a single XML error document.
     *
     * @return void
     * Replaces any partial success document with Errors and its Error children.
     *
     * @throws \Throwable
     * If retrieving messages or constructing the error document fails.
     *
     * @author Oleg Schildt
     */
    protected function addMessagesToResponse(): void
    {
        if (!messenger()->hasErrors()) {
            return;
        }

        $this->response_xmldoc = new \DOMDocument("1.0", "UTF-8");
        $this->response_xmldoc->formatOutput = true;
        $xmlerrors = $this->response_xmldoc->createElement("Errors");

        $this->response_xmldoc->appendChild($xmlerrors);

        $errors = messenger()->getErrors();

        foreach ($errors as $error) {
            $node = $this->response_xmldoc->createElement("Error");

            $node->setAttribute("Code", $error["code"]);

            // MessageManager error entries have no separate type field.
            $node->setAttribute("Type", "error");

            $text = $this->response_xmldoc->createTextNode($error["message"]);

            $node->appendChild($text);

            $xmlerrors->appendChild($node);
        }
    } // addMessagesToResponse

    /**
     * Builds a response that echoes the first /Request/City element and lists two fixed example rooms.
     *
     * @return void
     * Builds a Response document or queues validation errors when the city is absent.
     *
     * @throws \Throwable
     * If resolving messages, evaluating XPath or constructing the response document fails.
     *
     * @author Oleg Schildt
     */
    public function get_rooms(): void
    {
        $xpath = new \DOMXPath($this->request_xmldoc);
        $nodes = $xpath->evaluate("/Request/City");

        if ($nodes->length === 0) {
            messenger()->addError("City is undefined!", [], "", "no_city");

            messenger()->addError("Price preferences are undefined!", [], "", "no_price_preferences");

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

        $node->setAttribute("Price", "100");

        $rooms->appendChild($node);

        $text = $this->response_xmldoc->createTextNode("Single");

        $node->appendChild($text);

        $node = $this->response_xmldoc->createElement("Room");

        $node->setAttribute("Price", "200");

        $rooms->appendChild($node);

        $text = $this->response_xmldoc->createTextNode("Double");

        $node->appendChild($text);
    } // get_rooms
} // MyTestXmlHandler

