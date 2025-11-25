<?php

namespace MyApplication;

use SmartFactory\Interfaces\ISettingsManager;
use SmartFactory\Interfaces\ISettingsValidator;
use SmartFactory\SmartExceptionCollection;

use function SmartFactory\messenger;

//-------------------------------------------------------------------
// class UserSettingsValidator
//-------------------------------------------------------------------
class UserSettingsValidator implements ISettingsValidator
{
    //-----------------------------------------------------------------
    public function validate(ISettingsManager $settingsmanager, string $context): void
    {
        $error_collection = new SmartExceptionCollection();

        if ($context == "general_settings") {
            if ($settingsmanager->getParameter("language", true) == "") {
                $error_collection->addError("Language cannot be empty!", [], "language");
            }
            
            if ($settingsmanager->getParameter("time_zone", true) == "") {
                $error_collection->addError("Time zone cannot be empty!", [], "time_zone");
            }
        }
        
        if ($context == "forum_settings") {
            if ($settingsmanager->getParameter("status", true) == "") {
                $error_collection->addError("Status cannot be empty!", [], "status");
            }
        }

        $error_collection->throwIfErrors();
    } // validate
    //-----------------------------------------------------------------
} // UserSettingsValidator
//-------------------------------------------------------------------
