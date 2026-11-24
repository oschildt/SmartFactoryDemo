<?php

namespace MyApplication\Interfaces;

/**
 * Defines the name accessors shared by the example user implementations.
 *
 * @author Oleg Schildt
 */
interface IUser
{
    /**
     * Retrieves the user's first name.
     *
     * @return string
     * Returns the first name supplied by the implementation.
     *
     * @author Oleg Schildt
     */
    public function getUserFirstName();

    /**
     * Retrieves the user's last name.
     *
     * @return string
     * Returns the last name supplied by the implementation.
     *
     * @author Oleg Schildt
     */
    public function getUserLastName();
} // IUser
?>

