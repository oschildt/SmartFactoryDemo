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
        $result = true;

        if ($context == "general_settings") {
            if ($settingsmanager->getParameter("language", true) == "") {
                messenger()->addError("Language cannot be empty!", [], "language");
                $result = false;
            }
            
            if ($settingsmanager->getParameter("time_zone", true) == "") {
                messenger()->addError("Time zone cannot be empty!", [], "time_zone");
                $result = false;
            }
        }
        
        if ($context == "forum_settings") {
            if ($settingsmanager->getParameter("status", true) == "") {
                messenger()->addError("Status cannot be empty!", [], "status");
                $result = false;
            }
        }
        
        return $result;
    } // validate
    //-----------------------------------------------------------------
} // UserSettingsValidator
//-------------------------------------------------------------------
