<?php

namespace MyApplication;

use MyApplication\Interfaces\IUser;

//-------------------------------------------------------------------
// class User
//-------------------------------------------------------------------
class User implements IUser
{
    public string $first_name = "John";
    
    //-----------------------------------------------------------------
    public function getUserFirstName(): string
    {
        return $this->first_name;
    } // getUserFirstName
    
    //-----------------------------------------------------------------
    public function getUserLastName(): string
    {
        return "Smith";
    } // getUserLastName
    //-----------------------------------------------------------------
} // User
//-------------------------------------------------------------------
