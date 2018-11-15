<?php
namespace MyApplication;

use SmartFactory\Interfaces\ISettingsValidator;

//-------------------------------------------------------------------
// class ConfigSettingsValidator
//-------------------------------------------------------------------
class ConfigSettingsValidator implements ISettingsValidator
{
  //-----------------------------------------------------------------
  public function validate($settingsmanager, $context)
  {
    if($context == "server_settings")
    {
      if($settingsmanager->getParameter("smtp_server", true) == "")
      {
        messenger()->setError(text("ErrSmtpHostEmpty"));
        messenger()->setErrorElement("smtp_server");
        return false;
      }
    }
    
    if($context == "database_settings")
    {
      if($settingsmanager->getParameter("db_type", true) == "")
      {
        messenger()->setError(text("ErrDatabaseTypeEmpty"));
        messenger()->setErrorElement("db_type");
        return false;
      }

      if($settingsmanager->getParameter("db_server", true) == "")
      {
        messenger()->setError(text("ErrDatabaseServerEmpty"));
        messenger()->setErrorElement("db_server");
        return false;
      }

      if($settingsmanager->getParameter("db_name", true) == "")
      {
        messenger()->setError(text("ErrDatabaseNameEmpty"));
        messenger()->setErrorElement("db_name");
        return false;
      }

      if($settingsmanager->getParameter("db_user", true) == "")
      {
        messenger()->setError(text("ErrDatabaseUserEmpty"));
        messenger()->setErrorElement("db_user");
        return false;
      }

      if($settingsmanager->getParameter("db_password", true) == "")
      {
        messenger()->setError(text("ErrDatabasePasswordEmpty"));
        messenger()->setErrorElement("db_password");
        return false;
      }

      if($settingsmanager->getParameter("db_prefix", true) == "")
      {
        messenger()->setError(text("ErrDatabaseTablePrefixEmpty"));
        messenger()->setErrorElement("db_prefix");
        return false;
      }
    }
    
    return true;
  } // validate
  //-----------------------------------------------------------------
} // ConfigSettingsValidator
//-------------------------------------------------------------------
