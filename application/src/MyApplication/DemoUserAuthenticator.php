<?php

namespace MyApplication;

use OAuth2\Interfaces\IUserAuthenticator;

class DemoUserAuthenticator implements IUserAuthenticator
{
    public function init($parameters) {
    
    }
    
    public function authenticateUser($credentials) {
        if (empty($credentials["user_login"])) {
            throw new \SmartFactory\SmartException("The user login is not specified!","user_login_empty");
        }
    
        if (empty($credentials["user_password"])) {
            throw new \SmartFactory\SmartException("The user password is not specified", "user_password_empty");
        }
    
        if ($credentials["user_login"] != "john" || $credentials["user_password"] != "smith") {
            throw new \SmartFactory\SmartException("User login or password are invalid!", "invalid_credentials");
        }
        
        // Fake user id. In a real implementation, the login and password should be tested against the database,
        // and the user id should be retrieved.
        return 129;
    }
}