<?php

namespace MyApplication;

use SmartFactory\Interfaces\ISettingsManager;
use SmartFactory\Interfaces\ISettingsValidator;
use SmartFactory\SmartExceptionCollection;

/**
 * Validates the demo application's centralized hotel and data exchange settings.
 *
 * @author Oleg Schildt
 */
class ApplicationSettingsValidator implements ISettingsValidator
{
    /**
     * Checks the required fields in the selected application settings form.
     * Missing values are treated as empty strings.
     *
     * @param ISettingsManager $settingsmanager
     * The settings manager containing the entered values.
     *
     * @param string $context
     * Use general_settings for hotel details or data_exchange_settings for booking integration.
     * Other contexts have no rules.
     *
     * @return void
     * Accepts the values when all fields required by the selected context are nonempty.
     *
     * @throws SmartExceptionCollection
     * If a hotel name, email, booking URL, hotel ID, or default rate required by the selected
     * form is missing or compares equal to an empty string.
     *
     * @throws \Throwable
     * If accessing the settings storage fails during validation.
     *
     * @author Oleg Schildt
     */
    public function validate(ISettingsManager $settingsmanager, string $context): void
    {
        $error_collection = new SmartExceptionCollection();

        if ($context === "general_settings") {
            if ($settingsmanager->getParameter("hotel_name", "") == "") {
                $error_collection->addError("Hotel name cannot be empty!", [], "hotel_name");
            }

            if ($settingsmanager->getParameter("hotel_email", "") == "") {
                $error_collection->addError("Hotel email cannot be empty!", [], "hotel_email");
            }
        }

        if ($context === "data_exchange_settings") {
            if ($settingsmanager->getParameter("booking_url", "") == "") {
                $error_collection->addError("Booking URL cannot be empty!", [], "booking_url");
            }

            if ($settingsmanager->getParameter("hotel_id", "") == "") {
                $error_collection->addError("Hotel ID cannot be empty!", [], "hotel_id");
            }

            if ($settingsmanager->getParameter("default_rate", "") == "") {
                $error_collection->addError("Default rate cannot be empty!", [], "default_rate");
            }
        }

        $error_collection->throwIfErrors();
    } // validate
} // ApplicationSettingsValidator

