<?php

namespace MyApplication;

use SmartFactory\Interfaces\IMessageManager;
use SmartFactory\Interfaces\IDebugProfiler;
use SmartFactory\Interfaces\IErrorHandler;
use SmartFactory\Interfaces\ILanguageManager;
use SmartFactory\Interfaces\IRecordsetManager;

use SmartFactory\ObjectFactory;
use SmartFactory\ConfigSettingsManager;
use SmartFactory\RuntimeSettingsManager;
use SmartFactory\UserSettingsManager;
use SmartFactory\DebugProfiler;
use SmartFactory\ErrorHandler;
use SmartFactory\MessageManager;
use SmartFactory\LanguageManager;
use SmartFactory\RecordsetManager;

use SmartFactory\DatabaseWorkers\DBWorker;

use function SmartFactory\config_settings;
use function SmartFactory\singleton;

use OAuth2\Interfaces\IOAuthManager;
use OAuth2\Interfaces\ITokenStorage;
use OAuth2\Interfaces\IUserAuthenticator;

use OAuth2\OAuthManager;

use MyApplication\Interfaces\IUser;

//-------------------------------------------------------------------
function approot()
{
    $aroot = __DIR__;
    $aroot = str_replace("\\", "/", $aroot);
    $aroot = str_replace("src/MyApplication", "", $aroot);
    
    return $aroot;
} // approot
//-------------------------------------------------------------------
function app_dbworker()
{
    try {
        $parameters = [];
        
        $parameters["db_type"] = config_settings()->getParameter("db_type");
        $parameters["db_server"] = config_settings()->getParameter("db_server");
        $parameters["db_name"] = config_settings()->getParameter("db_name");
        $parameters["db_user"] = config_settings()->getParameter("db_user");
        $parameters["db_password"] = config_settings()->getParameter("db_password");
        $parameters["autoconnect"] = true;
        
        return \SmartFactory\dbworker($parameters);
    } catch (\Exception $ex) {
        throw new \Exception("Please ensure that you have created the demo database with the script 'database/create_database_mysql.sql' and adjust the DB password and other connection data in 'config/settings.json'!");
    }
}

//-------------------------------------------------------------------
// Defining bindings for the standard SmartFactory classes with own intialization
//-------------------------------------------------------------------
// IMPORTANT: The ConfigSettingsManager must be bound first,
// because other bindings might use the config settings by
// initialization.
//-------------------------------------------------------------------
ObjectFactory::bindClass(ConfigSettingsManager::class, ConfigSettingsManager::class, function ($instance) {
    $instance->init([
        "save_path" => approot() . "config/settings.json",
        "config_file_must_exist" => false,
        "use_apcu" => false
        //"save_encrypted" => true,
        //"salt_key" => "demotest"
    ]);
    
    $instance->setValidator(new ConfigSettingsValidator());
});
//-------------------------------------------------------------------
ObjectFactory::bindClass(RuntimeSettingsManager::class, RuntimeSettingsManager::class, function ($instance) {
    $instance->init([
        "dbworker" => app_dbworker(),
        "settings_table" => "SETTINGS",
        "settings_column" => "DATA"
    ]);
    
    $instance->setValidator(new RuntimeSettingsValidator());
});
//-------------------------------------------------------------------
ObjectFactory::bindClass(UserSettingsManager::class, UserSettingsManager::class, function ($instance) {
    $instance->init([
        "dbworker" => app_dbworker(),
        
        "settings_tables" => [
            "USERS" => [
                "ID" => DBWorker::DB_NUMBER,
                "LANGUAGE" => DBWorker::DB_STRING,
                "TIME_ZONE" => DBWorker::DB_STRING
            ],
            "USER_FORUM_SETTINGS" => [
                "USER_ID" => DBWorker::DB_NUMBER,
                "SIGNATURE" => DBWorker::DB_STRING,
                "STATUS" => DBWorker::DB_STRING,
                "HIDE_PICTURES" => DBWorker::DB_NUMBER,
                "HIDE_SIGNATURES" => DBWorker::DB_NUMBER
            ]
        ],
        
        "multichoice_tables" => [
            "USER_COLORS" => [
                "USER_ID" => DBWorker::DB_NUMBER,
                "COLOR" => DBWorker::DB_STRING
            ]
        ]
    ]);
    
    $instance->setValidator(new UserSettingsValidator());
});
//-------------------------------------------------------------------
ObjectFactory::bindClass(ILanguageManager::class, LanguageManager::class, function ($instance) {
    $instance->init([
        "localization_path" => approot() . "localization/",
        "module_localization_path" => approot() . "modules/",
        "use_apcu" => false,
        "warn_missing" => true
    ]);
    $instance->loadDictionary();
    $instance->detectLanguage();
});
//-------------------------------------------------------------------
ObjectFactory::bindClass(IRecordsetManager::class, RecordsetManager::class, function ($instance) {
    $instance->setDBWorker(app_dbworker());
});
//-------------------------------------------------------------------
ObjectFactory::bindClass(IOAuthManager::class, OAuthManager::class, function ($instance) {
    $params = [];
    
    $params["access_token_ttl"] = 600; // 10 min
    $params["refresh_token_ttl"] = 3600; // 1 hours
    $params["max_token_inactivity_days"] = 7; // 7 days
    
    $params["token_storage"] = singleton(ITokenStorage::class);
    $params["token_storage"]->init(["storage_file" => approot() . "config/auth_tokens.xml"]);
    
    $params["user_authenticator"] = singleton(IUserAuthenticator::class);
    
    // $supported_algorithms = ['HS256', 'HS384', 'HS512', 'RS256', 'RS384', 'RS512'];
    $params["encryption_algorithm"] = "RS256";
    
    $params["secret_key"] = "OLEG";
    
    $params["public_key"] = approot() . "config/public_key.pem";
    $params["private_key"] = approot() . "config/private_key.pem";
    $params["pass_phrase"] = "termin";

    $instance->init($params);
});
//-------------------------------------------------------------------
// Overriding the default bindings of the standard SmartFactory classes with own intialization
//-------------------------------------------------------------------
ObjectFactory::bindClass(IErrorHandler::class, ErrorHandler::class, function ($instance) {
    $instance->init(["log_path" => approot() . "logs/"]);
    
    if (config_settings()->getParameter("tracing_enabled", 0)) {
        $instance->enableTrace();
    } else {
        $instance->disableTrace();
    }
});
//-------------------------------------------------------------------
ObjectFactory::bindClass(IDebugProfiler::class, DebugProfiler::class, function ($instance) {
    $instance->init(["log_path" => approot() . "logs/"]);
});
//-------------------------------------------------------------------
ObjectFactory::bindClass(IMessageManager::class, MessageManager::class, function ($instance) {
    $instance->init(["auto_hide_time" => 3]);
    
    if (config_settings()->getParameter("show_message_details", 0)) {
        $instance->enableDetails();
    } else {
        $instance->disableDetails();
    }
    
    if (config_settings()->getParameter("show_prog_warnings", 0)) {
        $instance->enableProgWarnings();
    } else {
        $instance->disableProgWarnings();
    }
});
//-------------------------------------------------------------------
// Binding of own class implementations to SmartFactory interfaces
//-------------------------------------------------------------------
ObjectFactory::bindClass(ITokenStorage::class, DemoTokenStorage::class);
//-------------------------------------------------------------------
ObjectFactory::bindClass(IUserAuthenticator::class, DemoUserAuthenticator::class);
//-------------------------------------------------------------------
// Binding of own class implementations to own interfaces
//-------------------------------------------------------------------
ObjectFactory::bindClass(IUser::class, User::class);
//-------------------------------------------------------------------
