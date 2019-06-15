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
        if ($context == "general_settings") {
            if ($settingsmanager->getParameter("hotel_name", true) == "") {
                messenger()->setError("Hotel name cannot be empty!");
                messenger()->setErrorElement("hotel_name");
                return false;
            }
            
            if ($settingsmanager->getParameter("hotel_email", true) == "") {
                messenger()->setError("Hotel email cannot be empty!");
                messenger()->setErrorElement("hotel_email");
                return false;
            }
        }
        
        if ($context == "data_exchange_settings") {
            if ($settingsmanager->getParameter("booking_url", true) == "") {
                messenger()->setError("Booking url cannot be empty!");
                messenger()->setErrorElement("booking_url");
                return false;
            }
            
            if ($settingsmanager->getParameter("hotel_id", true) == "") {
                messenger()->setError("Hotel ID url cannot be empty!");
                messenger()->setErrorElement("hotel_id");
                return false;
            }
            
            if ($settingsmanager->getParameter("default_rate", true) == "") {
                messenger()->setError("Default rate cannot be empty!");
                messenger()->setErrorElement("default_rate");
                return false;
            }
        }
        
        return true;
    } // validate
    //-----------------------------------------------------------------
} // RuntimeSettingsValidator
//-------------------------------------------------------------------
