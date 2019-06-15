<?php

namespace MyApplication;

use SmartFactory\Interfaces\ISettingsValidator;
use function SmartFactory\messenger;

//-------------------------------------------------------------------
// class UserSettingsValidator
//-------------------------------------------------------------------
class UserSettingsValidator implements ISettingsValidator
{
    //-----------------------------------------------------------------
    public function validate($settingsmanager, $context)
    {
        if ($context == "general_settings") {
            if ($settingsmanager->getParameter("LANGUAGE", true) == "") {
                messenger()->setError("Language cannot be empty!");
                messenger()->setErrorElement("LANGUAGE");
                return false;
            }
            
            if ($settingsmanager->getParameter("TIME_ZONE", true) == "") {
                messenger()->setError("Time zone cannot be empty!");
                messenger()->setErrorElement("TIME_ZONE");
                return false;
            }
        }
        
        if ($context == "forum_settings") {
            if ($settingsmanager->getParameter("STATUS", true) == "") {
                messenger()->setError("Status cannot be empty!");
                messenger()->setErrorElement("STATUS");
                return false;
            }
        }
        
        return true;
    } // validate
    //-----------------------------------------------------------------
} // UserSettingsValidator
//-------------------------------------------------------------------
