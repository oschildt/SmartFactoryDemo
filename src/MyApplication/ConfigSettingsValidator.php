<?php

namespace MyApplication;

use SmartFactory\Interfaces\ISettingsManager;
use SmartFactory\Interfaces\ISettingsValidator;

use SmartFactory\SmartExceptionCollection;

use function SmartFactory\text;

/**
 * Validates the demo application's server and database configuration forms.
 *
 * @author Oleg Schildt
 */
class ConfigSettingsValidator implements ISettingsValidator
{
    /**
     * Collects localized, field-specific errors for empty values in the selected configuration form.
     *
     * @param ISettingsManager $settingsmanager
     * The settings manager containing the values to validate.
     *
     * @param string $context
     * Use server_settings to check the SMTP host or database_settings to check database type,
     * server, name, user, password, and table prefix. Other contexts have no rules.
     *
     * @return void
     * Completes when no configured field in the selected form compares equal to an empty string.
     *
     * @throws SmartExceptionCollection
     * If one or more fields in the selected form compare equal to an empty string.
     *
     * @throws \Throwable
     * If loading the settings or resolving a validation message translation fails.
     *
     * @author Oleg Schildt
     */
    public function validate(ISettingsManager $settingsmanager, string $context): void
    {
        $error_collection = new SmartExceptionCollection();

        // A true default lets absent keys pass while explicitly empty values are reported.
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
} // ConfigSettingsValidator

