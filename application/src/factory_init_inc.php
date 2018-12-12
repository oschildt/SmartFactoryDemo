<?php

namespace MyApplication;

use SmartFactory\Interfaces\ISessionManager;
use SmartFactory\Interfaces\IDebugProfiler;
use SmartFactory\Interfaces\IErrorHandler;
use SmartFactory\Interfaces\ILanguageManager;

use SmartFactory\FactoryBuilder;
use SmartFactory\ConfigSettingsManager;
use SmartFactory\ApplicationSettingsManager;
use SmartFactory\UserSettingsManager;
use SmartFactory\DebugProfiler;
use SmartFactory\ErrorHandler;
use SmartFactory\LanguageManager;

use SmartFactory\DatabaseWorkers\DBWorker;

use function SmartFactory\dbworker;

use MyApplication\Interfaces\IUser;
use MyApplication\HotelXmlApiRequestHandler;
use MyApplication\MySessionManager;

//-------------------------------------------------------------------
// Overriding the default binding
FactoryBuilder::bindClass(ISessionManager::class, MySessionManager::class);
//-------------------------------------------------------------------
FactoryBuilder::bindClass(IDebugProfiler::class, DebugProfiler::class, function ($instance) {
    $instance->init(["log_path" => approot() . "logs/"]);
});
//-------------------------------------------------------------------
FactoryBuilder::bindClass(IErrorHandler::class, ErrorHandler::class, function ($instance) {
    $instance->init(["app_root" => approot(), "log_path" => approot() . "logs/"]);
});
//-------------------------------------------------------------------
FactoryBuilder::bindClass(ILanguageManager::class, LanguageManager::class, function ($instance) {
    $instance->init(["localization_path" => approot() . "localization/"]);
    $instance->loadDictionary();
    $instance->detectLanguage();
});
//-------------------------------------------------------------------
FactoryBuilder::bindClass(ConfigSettingsManager::class, ConfigSettingsManager::class, function ($instance) {
    $instance->init([
        "save_path" => approot() . "../config/settings.xml",
        "config_file_must_exist" => false
        //"save_encrypted" => true,
        //"salt_key" => "demotest"
    ]);
    $instance->loadSettings();
    
    $instance->setValidator(new ConfigSettingsValidator());
});
//-------------------------------------------------------------------
FactoryBuilder::bindClass(ApplicationSettingsManager::class, ApplicationSettingsManager::class, function ($instance) {
    $instance->init([
        "dbworker" => dbworker(),
        "settings_table" => "SETTINGS",
        "settings_column" => "DATA"
    ]);
    $instance->loadSettings();
    
    $instance->setValidator(new ApplicationSettingsValidator());
});
//-------------------------------------------------------------------
FactoryBuilder::bindClass(UserSettingsManager::class, UserSettingsManager::class, function ($instance) {
    $instance->init([
        "dbworker" => dbworker(),
        "user_table" => "USERS",
        "settings_fields" => [
            "ID" => DBWorker::DB_NUMBER,
            "SIGNATURE" => DBWorker::DB_STRING,
            "STATUS" => DBWorker::DB_STRING,
            "HIDE_PICTURES" => DBWorker::DB_NUMBER,
            "HIDE_SIGNATURES" => DBWorker::DB_NUMBER,
            "LANGUAGE" => DBWorker::DB_STRING,
            "TIME_ZONE" => DBWorker::DB_STRING
        ],
        "user_id_field" => "ID",
        "user_id_getter" => function () {
            return 1;
        }
    ]);
    $instance->loadSettings();
    
    $instance->setValidator(new UserSettingsValidator());
});

//-------------------------------------------------------------------
// Own binding
FactoryBuilder::bindClass(IUser::class, User::class);
//-------------------------------------------------------------------
// Own binding
FactoryBuilder::bindClass(HotelXmlApiRequestManager::class, HotelXmlApiRequestManager::class);
//-------------------------------------------------------------------
