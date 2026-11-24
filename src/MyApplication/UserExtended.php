<?php

namespace MyApplication;

use MyApplication\Interfaces\IUser;

/**
 * Provides the user implementation bound to the extended factory context.
 *
 * @author Oleg Schildt
 */
class UserExtended implements IUser
{
    /**
     * The first name returned by this example user.
     *
     * @author Oleg Schildt
     */
    public string $first_name = "John!";

    /**
     * Reads the current first name of the extended example user.
     *
     * @return string
     * Returns the value of the public first_name property.
     *
     * @author Oleg Schildt
     */
    public function getUserFirstName(): string
    {
        return $this->first_name;
    } // getUserFirstName

    /**
     * Provides the fixed last name of the extended example user.
     *
     * @return string
     * Returns "Smith!" to distinguish this implementation in the factory example.
     *
     * @author Oleg Schildt
     */
    public function getUserLastName(): string
    {
        return "Smith!";
    } // getUserLastName
} // UserExtended

