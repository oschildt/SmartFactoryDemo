<?php

namespace MyApplication\Handlers;

use SmartFactory\JsonRequestHandler;
use SmartFactory\SmartException;

use function SmartFactory\reqvar_value;

//-------------------------------------------------------------------
// class MyTestJsonHandler
//-------------------------------------------------------------------
class MyTestJsonHandler extends JsonRequestHandler
{
    protected function preprocessRequest()
    {
        $this->response_data["preprocess_message"] = "This message is added to every response by the PreProcessHandler!";
    } // preprocessRequest

    protected function postprocessRequest()
    {
        $this->response_data["postprocess_message"] = "This message is added to every response by the PostProcessHandler!";
    } // postprocessRequest

    protected function parseJsonInput()
    {
        try {
            //$content_type = empty($this->request_headers["Content-Type"]) ? "" : $this->request_headers["Content-Type"];
            //if (!preg_match("/application\/json.*/", $content_type)) {
            //    throw new SmartException(sprintf("Content type 'application/json' is expected, got '%s'!", $content_type), SmartException::ERR_CODE_INVALID_CONTENT_TYPE, SmartException::ERR_TYPE_PROGRAMMING_ERROR);
            //}

            //$json = trim(file_get_contents("php://input"));

            // For demo we take not frow rawdata.

            $json = reqvar_value("jsondata");

            if (empty($json)) {
                throw new SmartException("The request JSON is empty!", SmartException::ERR_CODE_MISSING_REQUEST_DATA, SmartException::ERR_TYPE_PROGRAMMING_ERROR);
            }

            $json = json_decode($json, true);

            if (empty($json) && !is_array($json)) {
                throw new SmartException(json_last_error_msg(), SmartException::ERR_CODE_JSON_PARSE_ERROR, SmartException::ERR_TYPE_PROGRAMMING_ERROR);
            }

            $this->request_data = array_merge($this->request_data, $json);
        } catch (SmartException $ex) {
            throw $ex;
        } catch (\Exception $ex) {
            throw new SmartException($ex->getMessage(), SmartException::ERR_CODE_JSON_PARSE_ERROR, SmartException::ERR_TYPE_PROGRAMMING_ERROR);
        }
    } // parseRawJsonInput

    function login()
    {
        if (empty($this->request_data["login"]) || empty($this->request_data["password"]) ||
            $this->request_data["login"] != "admin" || $this->request_data["password"] != "qwerty") {
            throw new SmartException("Wrong login or password!", "wrong_login", SmartException::ERR_TYPE_USER_ERROR);
        }

        $this->response_data["welcome_msg"] = "Welcome, admin!";
    }
} // MyTestJsonHandler
//-------------------------------------------------------------------
