<?php
namespace MyApplication;

use MyApplication\Interfaces\IUser;

//-------------------------------------------------------------------
// class User
//-------------------------------------------------------------------
class User implements IUser
{
  public $first_name = "John";
  //-----------------------------------------------------------------
  public function getUserFirstName()
  {
    return $this->first_name;
  } // getUserFirstName
  //-----------------------------------------------------------------
  public function getUserLastName()
  {
    return "Smith";
  } // getUserLastName
  //-----------------------------------------------------------------
} // User
//-------------------------------------------------------------------
