<?php

namespace MyApplication;

use MyApplication\Interfaces\IUser;

//-------------------------------------------------------------------
// class UserExtended
//-------------------------------------------------------------------
class UserExtended implements IUser
{
    public string $first_name = "John!";
    
    //-----------------------------------------------------------------
    public function getUserFirstName(): string
    {
        return $this->first_name;
    } // getUserFirstName
    
    //-----------------------------------------------------------------
    public function getUserLastName(): string
    {
        return "Smith!";
    } // getUserLastName
    //-----------------------------------------------------------------
} // UserExtended
//-------------------------------------------------------------------
