<?php

namespace MyApplication;

use SmartFactory\Interfaces\ISettingsManager;
use SmartFactory\Interfaces\ISettingsValidator;

use SmartFactory\SmartException;
use SmartFactory\SmartExceptionCollection;

use function SmartFactory\messenger;
use function SmartFactory\text;

//-------------------------------------------------------------------
// class ConfigSettingsValidator
//-------------------------------------------------------------------
class ConfigSettingsValidator implements ISettingsValidator
{
    //-----------------------------------------------------------------
    public function validate(ISettingsManager $settingsmanager, string $context): void
    {
        $error_collection = new SmartExceptionCollection();

        if ($context == "server_settings") {
            if ($settingsmanager->getParameter("smtp_server", true) == "") {
                $error_collection->addError(text("ErrSmtpHostEmpty"), [], "smtp_server");
            }
        }
        
        if ($context == "database_settings") {
            if ($settingsmanager->getParameter("db_type", true) == "") {
                $error_collection->addError(text("ErrDatabaseTypeEmpty"), [], "db_type");
            }
            
            if ($settingsmanager->getParameter("db_server", true) == "") {
                $error_collection->addError(text("ErrDatabaseServerEmpty"), [], "db_server");
            }
            
            if ($settingsmanager->getParameter("db_name", true) == "") {
                $error_collection->addError(text("ErrDatabaseNameEmpty"), [], "db_name");
            }
            
            if ($settingsmanager->getParameter("db_user", true) == "") {
                $error_collection->addError(text("ErrDatabaseUserEmpty"), [], "db_user");
            }
            
            if ($settingsmanager->getParameter("db_password", true) == "") {
                $error_collection->addError(text("ErrDatabasePasswordEmpty"), [], "db_password");
            }
            
            if ($settingsmanager->getParameter("db_prefix", true) == "") {
                $error_collection->addError(text("ErrDatabaseTablePrefixEmpty"), [], "db_prefix");
            }
        }

        $error_collection->throwIfErrors();
    } // validate
    //-----------------------------------------------------------------
} // ConfigSettingsValidator
//-------------------------------------------------------------------
