<?php

namespace MyApplication;

use OAuth2\Interfaces\IUserAuthenticator;

class DemoUserAuthenticator implements IUserAuthenticator
{
    public function init($parameters)
    {
    
    }
    
    public function authenticateUser($credentials)
    {
        if (empty($credentials["user_login"])) {
            throw new \OAuth2\InvalidCredentialsException("The user login is not specified!");
        }
        
        if (empty($credentials["user_password"])) {
            throw new \OAuth2\InvalidCredentialsException("The user password is not specified");
        }
        
        if ($credentials["user_login"] != "john" || $credentials["user_password"] != "smith") {
            throw new \OAuth2\InvalidCredentialsException("User login or password are invalid!");
        }
        
        // Fake user id. In a real implementation, the login and password should be tested against the database,
        // and the user id should be retrieved.
        return 129;
    }
}