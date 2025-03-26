<?php

namespace MyApplication;

use SmartFactory\Interfaces\ISettingsValidator;
use function SmartFactory\messenger;

//-------------------------------------------------------------------
// class RuntimeSettingsValidator
//-------------------------------------------------------------------
class RuntimeSettingsValidator implements ISettingsValidator
{
    //-----------------------------------------------------------------
    public function validate($settingsmanager, $context)
    {
        $result = true;

        if ($context == "general_settings") {
            if ($settingsmanager->getParameter("hotel_name", true) == "") {
                messenger()->addError("Hotel name cannot be empty!", [], "hotel_name");
                $result = false;
            }
            
            if ($settingsmanager->getParameter("hotel_email", true) == "") {
                messenger()->addError("Hotel email cannot be empty!", [], "hotel_email");
                $result = false;
            }
        }
        
        if ($context == "data_exchange_settings") {
            if ($settingsmanager->getParameter("booking_url", true) == "") {
                messenger()->addError("Booking url cannot be empty!", [], "booking_url");
                $result = false;
            }
            
            if ($settingsmanager->getParameter("hotel_id", true) == "") {
                messenger()->addError("Hotel ID url cannot be empty!", [], "hotel_id");
                $result = false;
            }
            
            if ($settingsmanager->getParameter("default_rate", true) == "") {
                messenger()->addError("Default rate cannot be empty!", [], "default_rate");
                $result = false;
            }
        }
        
        return $result;
    } // validate
    //-----------------------------------------------------------------
} // RuntimeSettingsValidator
//-------------------------------------------------------------------
