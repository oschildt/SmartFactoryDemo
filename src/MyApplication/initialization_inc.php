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
use SmartFactory\SessionMessageManager;
use SmartFactory\UserSettingsManager;
use SmartFactory\DebugProfiler;
use SmartFactory\ErrorHandler;
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
// Overriding the default bindings of the standard SmartFactory classes with own intialization
//-------------------------------------------------------------------
ObjectFactory::bindClass(IErrorHandler::class, ErrorHandler::class, function ($instance) {
    $instance->init(["log_path" => approot() . "logs/"]);

    if (config_settings()->getParameter("trace_programming_warnings")) {
        $instance->enableTrace();
    } else {
        $instance->disableTrace();
    }
});
//-------------------------------------------------------------------
ObjectFactory::bindClass(IDebugProfiler::class, DebugProfiler::class, function ($instance) {
    $instance->init([
        "log_path" => approot() . "logs/"
    ]);

    // we do it in the second step to be able to use debugging also in the ConfigSettingsManager methods
    $instance->enableFileAndLineDetails(config_settings()->getParameter("write_source_file_and_line_by_debug", 0));
});
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
        "settings_table" => "settings",
        "settings_column" => "data"
    ]);

    $instance->setValidator(new RuntimeSettingsValidator());
});
//-------------------------------------------------------------------
ObjectFactory::bindClass(UserSettingsManager::class, UserSettingsManager::class, function ($instance) {
    $instance->init([
        "dbworker" => app_dbworker(),

        "settings_tables" => [
            "users" => [
                "id" => DBWorker::DB_NUMBER,
                "language" => DBWorker::DB_STRING,
                "time_zone" => DBWorker::DB_STRING
            ],
            "user_forum_settings" => [
                "user_id" => DBWorker::DB_NUMBER,
                "signature" => DBWorker::DB_STRING,
                "status" => DBWorker::DB_STRING,
                "hide_pictures" => DBWorker::DB_NUMBER,
                "hide_signatures" => DBWorker::DB_NUMBER
            ]
        ],

        "multichoice_tables" => [
            "user_colors" => [
                "user_id" => DBWorker::DB_NUMBER,
                "color" => DBWorker::DB_STRING
            ]
        ]
    ]);

    $instance->setValidator(new UserSettingsValidator());
});
//-------------------------------------------------------------------
ObjectFactory::bindClass(ILanguageManager::class, LanguageManager::class, function ($instance) {
    $additional_localization_files = [];

    $modules_path = approot() . "modules/";
    $modules = scandir($modules_path);

    foreach ($modules as $module) {
        if ($module == "." || $module == ".." || !is_dir($modules_path . $module)) {
            continue;
        }

        $module_dir = $modules_path . $module . "/";

        if (file_exists($module_dir . "localization/texts.json")) {
            $additional_localization_files[] = $module_dir . "localization/texts.json";
        }
    }

    $instance->init([
        "localization_path" => approot() . "localization/",
        "additional_localization_files" => $additional_localization_files,
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

    $params["access_token_ttl_minutes"] = 10;
    $params["refresh_token_ttl_days"] = 14;

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
ObjectFactory::bindClass(IMessageManager::class, SessionMessageManager::class, function ($instance) {
    $instance->init(["debug_mode" => config_settings()->getParameter("debug_mode")]);
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
