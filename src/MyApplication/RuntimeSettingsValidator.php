<?php

namespace MyApplication;

use SmartFactory\Interfaces\ISettingsManager;
use SmartFactory\Interfaces\ISettingsValidator;
use SmartFactory\SmartExceptionCollection;

use function SmartFactory\messenger;

//-------------------------------------------------------------------
// class RuntimeSettingsValidator
//-------------------------------------------------------------------
class RuntimeSettingsValidator implements ISettingsValidator
{
    //-----------------------------------------------------------------
    public function validate(ISettingsManager $settingsmanager, string $context): void
    {
        $error_collection = new SmartExceptionCollection();

        if ($context == "general_settings") {
            if ($settingsmanager->getParameter("hotel_name", true) == "") {
                $error_collection->addError("Hotel name cannot be empty!", [], "hotel_name");
            }
            
            if ($settingsmanager->getParameter("hotel_email", true) == "") {
                $error_collection->addError("Hotel email cannot be empty!", [], "hotel_email");
            }
        }
        
        if ($context == "data_exchange_settings") {
            if ($settingsmanager->getParameter("booking_url", true) == "") {
                $error_collection->addError("Booking url cannot be empty!", [], "booking_url");
            }
            
            if ($settingsmanager->getParameter("hotel_id", true) == "") {
                $error_collection->addError("Hotel ID url cannot be empty!", [], "hotel_id");
            }
            
            if ($settingsmanager->getParameter("default_rate", true) == "") {
                $error_collection->addError("Default rate cannot be empty!", [], "default_rate");
            }
        }

        $error_collection->throwIfErrors();
    } // validate
    //-----------------------------------------------------------------
} // RuntimeSettingsValidator
//-------------------------------------------------------------------
