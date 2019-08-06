<?php

namespace MyApplication;

use SmartFactory\Interfaces\ISessionManager;
use SmartFactory\Interfaces\IDebugProfiler;
use SmartFactory\Interfaces\IErrorHandler;
use SmartFactory\Interfaces\ILanguageManager;
use SmartFactory\Interfaces\IRecordsetManager;

use SmartFactory\FactoryBuilder;
use SmartFactory\ConfigSettingsManager;
use SmartFactory\RuntimeSettingsManager;
use SmartFactory\UserSettingsManager;
use SmartFactory\DebugProfiler;
use SmartFactory\ErrorHandler;
use SmartFactory\LanguageManager;
use SmartFactory\RecordsetManager;

use SmartFactory\DatabaseWorkers\DBWorker;

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
        
        $parameters["db_type"] = \SmartFactory\config_settings()->getParameter("db_type");
        $parameters["db_server"] = \SmartFactory\config_settings()->getParameter("db_server");
        $parameters["db_name"] = \SmartFactory\config_settings()->getParameter("db_name");
        $parameters["db_user"] = \SmartFactory\config_settings()->getParameter("db_user");
        $parameters["db_password"] = \SmartFactory\config_settings()->getParameter("db_password");
        $parameters["autoconnect"] = true;
        
        return \SmartFactory\dbworker($parameters);
    } catch (\Exception $ex) {
        throw new \Exception("Please ensure that you have created the demo database with the script 'database/create_database_mysql.sql' and adjust the DB password and other connection data in 'config/settings.cfg'!");
    }
}

//-------------------------------------------------------------------
// Overriding the default binding
FactoryBuilder::bindClass(ISessionManager::class, MySessionManager::class);
//-------------------------------------------------------------------
FactoryBuilder::bindClass(IDebugProfiler::class, DebugProfiler::class, function ($instance) {
    $instance->init(["log_path" => approot() . "../logs/"]);
});
//-------------------------------------------------------------------
FactoryBuilder::bindClass(IErrorHandler::class, ErrorHandler::class, function ($instance) {
    $instance->init(["app_root" => approot(), "log_path" => approot() . "../logs/"]);
});
//-------------------------------------------------------------------
FactoryBuilder::bindClass(ILanguageManager::class, LanguageManager::class, function ($instance) {
    $instance->init(["localization_path" => approot() . "localization/", "use_apcu" => false]);
    $instance->loadDictionary();
    $instance->detectLanguage();
});
//-------------------------------------------------------------------
FactoryBuilder::bindClass(ConfigSettingsManager::class, ConfigSettingsManager::class, function ($instance) {
    $instance->init([
        "save_path" => approot() . "../config/settings.cfg",
        "config_file_must_exist" => false,
        "use_apcu" => false
        //"save_encrypted" => true,
        //"salt_key" => "demotest"
    ]);
    
    $instance->setValidator(new ConfigSettingsValidator());
});
//-------------------------------------------------------------------
FactoryBuilder::bindClass(RuntimeSettingsManager::class, RuntimeSettingsManager::class, function ($instance) {
    $instance->init([
        "dbworker" => app_dbworker(),
        "settings_table" => "SETTINGS",
        "settings_column" => "DATA"
    ]);
    
    $instance->setValidator(new RuntimeSettingsValidator());
});
//-------------------------------------------------------------------
FactoryBuilder::bindClass(UserSettingsManager::class, UserSettingsManager::class, function ($instance) {
    $instance->init([
        "dbworker" => app_dbworker(),
        "user_table" => "USERS",
        "settings_fields" => [
            "ID" => DBWorker::DB_NUMBER,
            "SIGNATURE" => DBWorker::DB_STRING,
            "STATUS" => DBWorker::DB_STRING,
            "HIDE_PICTURES" => DBWorker::DB_NUMBER,
            "HIDE_SIGNATURES" => DBWorker::DB_NUMBER,
            "LANGUAGE" => DBWorker::DB_STRING,
            "TIME_ZONE" => DBWorker::DB_STRING,
            "USER_COLORS" => ["USER_ID", "COLOR", DBWorker::DB_STRING]
        ],
        "user_id_field" => "ID"
    ]);
    
    $instance->setValidator(new UserSettingsValidator());
});
//-------------------------------------------------------------------
FactoryBuilder::bindClass(IRecordsetManager::class, RecordsetManager::class, function ($instance) {
    $instance->setDBWorker(app_dbworker());
});
//-------------------------------------------------------------------
// Own binding
//-------------------------------------------------------------------
FactoryBuilder::bindClass(IUser::class, User::class);
//-------------------------------------------------------------------
FactoryBuilder::bindClass(HotelXmlApiRequestManager::class, HotelXmlApiRequestManager::class);
//-------------------------------------------------------------------
FactoryBuilder::bindClass(IOAuthManager::class, OAuthManager::class);
//-------------------------------------------------------------------
FactoryBuilder::bindClass(ITokenStorage::class, DemoTokenStorage::class);
//-------------------------------------------------------------------
FactoryBuilder::bindClass(IUserAuthenticator::class, DemoUserAuthenticator::class);
//-------------------------------------------------------------------
