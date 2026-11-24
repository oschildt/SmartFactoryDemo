<?php

namespace MyApplication;

use SmartFactory\Interfaces\ISettingsManager;
use SmartFactory\Interfaces\ISettingsValidator;
use SmartFactory\SmartExceptionCollection;

/**
 * Validates the demo application's general and forum preferences.
 *
 * @author Oleg Schildt
 */
class UserSettingsValidator implements ISettingsValidator
{
    /**
     * Collects field-specific errors for empty values in the selected user settings form.
     *
     * @param ISettingsManager $settingsmanager
     * The settings manager containing the user's preferences.
     *
     * @param string $context
     * Use general_settings to check the language and time zone or forum_settings to check
     * the status. Other contexts have no rules.
     *
     * @return void
     * Completes when no configured field in the selected form compares equal to an empty string.
     *
     * @throws SmartExceptionCollection
     * If one or more fields in the selected form compare equal to an empty string.
     *
     * @throws \Throwable
     * If the settings manager fails while loading the user preferences.
     *
     * @author Oleg Schildt
     */
    public function validate(ISettingsManager $settingsmanager, string $context): void
    {
        $error_collection = new SmartExceptionCollection();

        // A true default lets absent keys pass while explicitly empty values are reported.
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
} // UserSettingsValidator

