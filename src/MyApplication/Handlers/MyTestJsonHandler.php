<?php

namespace MyApplication\Handlers;

use SmartFactory\JsonRequestHandler;
use SmartFactory\SmartException;

use function SmartFactory\messenger;
use function SmartFactory\reqvar_value;

//-------------------------------------------------------------------
// class MyTestJsonHandler
//-------------------------------------------------------------------
class MyTestJsonHandler extends JsonRequestHandler
{
    protected function parseInput()
    {
        try {
            //$content_type = empty($this->request_headers["Content-Type"]) ? "" : $this->request_headers["Content-Type"];
            //if (!preg_match("/application\/json.*/", $content_type)) {
            //    throw new SmartException(sprintf("Content type 'application/json' is expected, got '%s'!", $content_type), SmartException::ERR_CODE_INVALID_CONTENT_TYPE);
            //}

            //$json = trim(file_get_contents("php://input"));

            // For demo we take not frow rawdata.

            $json = reqvar_value("jsondata");

            if (empty($json)) {
                throw new SmartException("The request JSON is empty!", SmartException::ERR_CODE_MISSING_REQUEST_DATA);
            }

            $json = json_decode($json, true);

            if (empty($json) && !is_array($json)) {
                throw new SmartException(json_last_error_msg(), SmartException::ERR_CODE_JSON_PARSE_ERROR);
            }

            $this->request_data = array_merge($this->request_data, $json);
        } catch (SmartException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            throw new SmartException($ex->getMessage(), SmartException::ERR_CODE_JSON_PARSE_ERROR);
        }
    } // parseInput

    protected function preprocessRequest()
    {
        $this->response_data["preprocess_message"] = "This message is added to every response by the PreProcessHandler!";
    } // preprocessRequest

    protected function postprocessRequest()
    {
        $this->response_data["postprocess_message"] = "This message is added to every response by the PostProcessHandler!";
    } // postprocessRequest

    function addMessagesToResponse()
    {
        if(messenger()->hasErrors()) {
            $this->response_data = [];

            $this->response_data["result"] = "error";
            $this->response_data["errors"] = messenger()->getErrors();
        } else {
            $this->response_data["result"] = "success";
        }

        if(messenger()->hasWarnings()) {
            $this->response_data["warnings"] = messenger()->getWarnings();
        }

        if(messenger()->hasProgWarnings()) {
            $this->response_data["prog_warnings"] = messenger()->getProgWarnings();
        }

        if(messenger()->hasDebugMessages()) {
            $this->response_data["debug_messages"] = messenger()->getDebugMessages();
        }

        if(messenger()->hasInfoMessages()) {
            $this->response_data["info_messages"] = messenger()->getInfoMessages();
        }

        if(messenger()->hasBubbleMessages()) {
            $this->response_data["bubble_messages"] = messenger()->getBubbleMessages();
        }
    }

    function authenticate()
    {
        if (empty($this->request_data["login"]) || empty($this->request_data["password"]) ||
            $this->request_data["login"] != "admin" || $this->request_data["password"] != "qwerty") {

            messenger()->addError("Wrong login or password!", [], "", "wrong_login");

            messenger()->addError("Some other error!", [], "", "some_error");

            return;
        }

        $this->response_data["welcome_msg"] = "Welcome, admin!";
    }
} // MyTestJsonHandler
//-------------------------------------------------------------------
