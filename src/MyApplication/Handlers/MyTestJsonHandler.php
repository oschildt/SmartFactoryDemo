<?php

namespace MyApplication\Handlers;

use SmartFactory\JsonRequestHandler;
use SmartFactory\SmartException;

use function SmartFactory\messenger;

/**
 * Demonstrates JSON API actions using a JSON body submitted through an HTML form field.
 *
 * @author Oleg Schildt
 */
class MyTestJsonHandler extends JsonRequestHandler
{
    /**
     * Parses the demo form's jsondata field using the standard JSON payload validation.
     *
     * The HTML example submits form data rather than an application/json request body.
     *
     * @return void
     * Merges decoded fields into request_data, letting submitted JSON values replace existing keys.
     *
     * @throws SmartException
     * If jsondata is absent, is not a string, is blank, is malformed, or has a top-level value other than an object or array.
     *
     * @author Oleg Schildt
     */
    protected function parseInput(): void
    {
        $json = $_REQUEST["jsondata"] ?? "";

        if (!is_string($json)) {
            throw new SmartException("The JSON form field must be a string!", SmartException::ERR_CODE_INVALID_REQUEST_DATA);
        }

        $this->parseJson($json);
    } // parseInput

    /**
     * Adds the shared demonstration message before an action.
     *
     * @return void
     * Updates the response payload.
     *
     * @author Oleg Schildt
     */
    protected function preprocessRequest(): void
    {
        $this->response_data["preprocess_message"] = "This message is added to every response by the preprocessing hook!";
    } // preprocessRequest

    /**
     * Adds the shared demonstration message after action processing, including failures.
     *
     * @return void
     * Updates the response payload before message assembly.
     *
     * @author Oleg Schildt
     */
    protected function postprocessRequest(): void
    {
        $this->response_data["postprocess_message"] = "This message is added to every response by the postprocessing hook!";
    } // postprocessRequest

    /**
     * Consumes queued messages and replaces partial success data when errors exist.
     *
     * @return void
     * Adds the result and available message categories to the JSON response.
     *
     * @throws \Throwable
     * If resolving the message manager or retrieving its messages fails.
     *
     * @author Oleg Schildt
     */
    protected function addMessagesToResponse(): void
    {
        if (messenger()->hasErrors()) {
            $this->response_data = [];
            $this->response_data["result"] = "error";
            $this->response_data["errors"] = messenger()->getErrors();
        } else {
            $this->response_data["result"] = "success";
        }

        if (messenger()->hasWarnings()) {
            $this->response_data["warnings"] = messenger()->getWarnings();
        }

        if (messenger()->hasProgWarnings()) {
            $this->response_data["prog_warnings"] = messenger()->getProgWarnings();
        }

        if (messenger()->hasDebugMessages()) {
            $this->response_data["debug_messages"] = messenger()->getDebugMessages();
        }

        if (messenger()->hasInfoMessages()) {
            $this->response_data["info_messages"] = messenger()->getInfoMessages();
        }

        if (messenger()->hasBubbleMessages()) {
            $this->response_data["bubble_messages"] = messenger()->getBubbleMessages();
        }
    } // addMessagesToResponse

    /**
     * Demonstrates authentication against fixed example credentials.
     *
     * @return void
     * Adds a welcome message for admin/qwerty or queues example validation errors.
     *
     * @throws \Throwable
     * If resolving the message manager or adding validation errors fails.
     *
     * @author Oleg Schildt
     */
    public function authenticate(): void
    {
        if (
            empty($this->request_data["login"]) || empty($this->request_data["password"]) ||
            $this->request_data["login"] != "admin" || $this->request_data["password"] != "qwerty"
        ) {
            messenger()->addError("Wrong login or password!", [], "", "wrong_login");

            messenger()->addError("Some other error!", [], "", "some_error");

            return;
        }

        $this->response_data["welcome_msg"] = "Welcome, admin!";
    } // authenticate
} // MyTestJsonHandler

