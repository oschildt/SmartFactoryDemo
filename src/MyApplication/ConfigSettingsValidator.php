<?php

namespace MyApplication;

use SmartFactory\Interfaces\ISettingsValidator;
use function SmartFactory\messenger;
use function SmartFactory\text;

//-------------------------------------------------------------------
// class ConfigSettingsValidator
//-------------------------------------------------------------------
class ConfigSettingsValidator implements ISettingsValidator
{
    //-----------------------------------------------------------------
    public function validate($settingsmanager, $context)
    {
        $result = true;

        if ($context == "server_settings") {
            if ($settingsmanager->getParameter("smtp_server", true) == "") {
                messenger()->addError(text("ErrSmtpHostEmpty"), [], "smtp_server");
                $result = false;
            }
        }
        
        if ($context == "database_settings") {
            if ($settingsmanager->getParameter("db_type", true) == "") {
                messenger()->addError(text("ErrDatabaseTypeEmpty"), [], "db_type");
                $result = false;
            }
            
            if ($settingsmanager->getParameter("db_server", true) == "") {
                messenger()->addError(text("ErrDatabaseServerEmpty"), [], "db_server");
                $result = false;
            }
            
            if ($settingsmanager->getParameter("db_name", true) == "") {
                messenger()->addError(text("ErrDatabaseNameEmpty"), [], "db_name");
                $result = false;
            }
            
            if ($settingsmanager->getParameter("db_user", true) == "") {
                messenger()->addError(text("ErrDatabaseUserEmpty"), [], "db_user");
                $result = false;
            }
            
            if ($settingsmanager->getParameter("db_password", true) == "") {
                messenger()->addError(text("ErrDatabasePasswordEmpty"), [], "db_password");
                $result = false;
            }
            
            if ($settingsmanager->getParameter("db_prefix", true) == "") {
                messenger()->addError(text("ErrDatabaseTablePrefixEmpty"), [], "db_prefix");
                $result = false;
            }
        }
        
        return $result;
    } // validate
    //-----------------------------------------------------------------
} // ConfigSettingsValidator
//-------------------------------------------------------------------
