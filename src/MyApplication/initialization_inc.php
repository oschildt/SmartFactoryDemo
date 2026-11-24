<?php

namespace MyApplication;

use SmartFactory\Interfaces\IDebugProfiler;
use SmartFactory\Interfaces\IErrorHandler;
use SmartFactory\Interfaces\ILanguageManager;
use SmartFactory\Interfaces\IRequestThrottlingManager;

use SmartFactory\ObjectFactory;
use SmartFactory\ConfigSettingsManager;
use SmartFactory\ApplicationSettingsManager;
use SmartFactory\UserSettingsManager;
use SmartFactory\DebugProfiler;
use SmartFactory\ErrorHandler;
use SmartFactory\LanguageManager;

use SmartFactory\DatabaseWorkers\DBWorker;

use function SmartFactory\config_settings;
use function SmartFactory\singleton;

use OAuth2\Interfaces\IOAuthManager;
use OAuth2\Interfaces\ITokenStorage;
use OAuth2\Interfaces\IUserAuthenticator;

use OAuth2\OAuthManager;

use MyApplication\Interfaces\IUser;

/**
 * Resolves the filesystem root of the demo application from this include's location.
 *
 * @return string
 * Returns the absolute project path with forward slashes and a trailing slash.
 *
 * @author Oleg Schildt
 */
function approot()
{
    $aroot = __DIR__;
    $aroot = str_replace("\\", "/", $aroot);
    $aroot = str_replace("src/MyApplication", "", $aroot);

    return $aroot;
} // approot

/**
 * Gets the demo application's database worker using the configured connection settings.
 *
 * @return DBWorker
 * Returns the connected worker for the configured database type.
 *
 * @throws \Exception
 * If resolving the configuration or creating or connecting the worker throws an exception.
 * The replacement exception provides database setup and configuration guidance.
 *
 * @throws \Error
 * If a PHP error occurs while resolving the configuration or initializing the worker.
 *
 * @author Oleg Schildt
 */
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
} // app_dbworker

// Register SmartFactory services with the demo application's paths and settings.
ObjectFactory::bindFactory(IErrorHandler::class, function (): ErrorHandler {
    $instance = new ErrorHandler();

    $instance->init(["log_path" => approot() . "logs/"]);

    if (config_settings()->getParameter("trace_programming_warnings")) {
        $instance->enableTrace();
    } else {
        $instance->disableTrace();
    }

    return $instance;
});

ObjectFactory::bindFactory(IDebugProfiler::class, function (): DebugProfiler {
    $instance = new DebugProfiler();

    $instance->init([
        "log_path" => approot() . "logs/",
        "source_root" => approot()
    ]);

    // Include source locations in debug logs only when the application enables them.
    $instance->enableFileAndLineDetails(config_settings()->getParameter("write_source_file_and_line_by_debug", 0));

    return $instance;
});

ObjectFactory::bindFactory(ConfigSettingsManager::class, function (): ConfigSettingsManager {
    $instance = new ConfigSettingsManager();

    $instance->init([
        "save_path" => approot() . "config/settings.json",
        "config_file_must_exist" => false,
        "use_apcu" => false
        //"save_encrypted" => true,
        //"salt_key" => "demotest"
    ]);

    $instance->setValidator(new ConfigSettingsValidator());

    return $instance;
});

ObjectFactory::bindFactory(ApplicationSettingsManager::class, function (): ApplicationSettingsManager {
    $instance = new ApplicationSettingsManager();

    $instance->init([
        "dbworker" => app_dbworker(),
        "settings_table" => "settings",
        "settings_column" => "data",
        "settings_id_column" => "id",
        "settings_id" => 1
    ]);

    $instance->setValidator(new ApplicationSettingsValidator());

    return $instance;
});

ObjectFactory::bindFactory(UserSettingsManager::class, function (): UserSettingsManager {
    $instance = new UserSettingsManager();

    $instance->init([
        "dbworker" => app_dbworker(),

        "identity_fields" => ["users" => "id"],

        // These tables store one row per user, while each selected color gets its own row.
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

    return $instance;
});

ObjectFactory::bindFactory(ILanguageManager::class, function (): LanguageManager {
    $instance = new LanguageManager();

    $additional_localization_files = [];

    $modules_path = approot() . "modules/";
    $modules = scandir($modules_path);

    // A module can contribute translations by providing its own localization/texts.json file.
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

    return $instance;
});

ObjectFactory::bindFactory(IOAuthManager::class, function (): OAuthManager {
    $instance = new OAuthManager();

    $params = [];

    $params["access_token_ttl_minutes"] = 10;
    $params["refresh_token_ttl_days"] = 14;

    $params["token_storage"] = singleton(ITokenStorage::class);

    $params["user_authenticator"] = singleton(IUserAuthenticator::class);

    // The demo uses RS256 with its PEM key pair. The manager also supports HS256, HS384, HS512, RS384, and RS512.
    $params["encryption_algorithm"] = "RS256";
    $params["issuer"] = "smartfactory-demo";
    $params["audience"] = "smartfactory-demo-api";
    $params["key_id"] = "demo-rsa-1";

    $params["public_key"] = approot() . "config/public_key.pem";
    $params["private_key"] = approot() . "config/private_key.pem";
    $params["pass_phrase"] = "termin";

    $instance->init($params);

    return $instance;
});

// Bind the demo implementations of request throttling and OAuth services.
ObjectFactory::bindFactory(IRequestThrottlingManager::class, function (): DemoRequestThrottlingManager {
    $instance = new DemoRequestThrottlingManager();

    $instance->init(["throttling_file" => approot() . "config/throttling.xml"]);

    return $instance;
});

ObjectFactory::bindFactory(ITokenStorage::class, function (): DemoTokenStorage {
    $instance = new DemoTokenStorage();

    $instance->init(["storage_file" => approot() . "config/auth_tokens.xml"]);

    return $instance;
});

ObjectFactory::bindFactory(IUserAuthenticator::class, function (): DemoUserAuthenticator {
    $instance = new DemoUserAuthenticator();

    return $instance;
});

// Bind both user examples to the same interface under different factory contexts.
ObjectFactory::bindFactory(IUser::class, function (): User {
    $instance = new User();

    return $instance;
});

ObjectFactory::bindFactory(IUser::class, function (): UserExtended {
    $instance = new UserExtended();

    return $instance;
}, "extended");

