<!DOCTYPE html>
<html lang="en">

<head>
    <title>Examples of usage of the library SmartFactory</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="css/examples.css" type="text/css" />
    <script src="js/examples.js" defer></script>
</head>

<body>

    <div class="header"></div>
    <div class="content">

        <h1>Examples of usage of the library SmartFactory</h1>

        <p>This set of examples demonstrates the usage of the library <i>SmartFactory</i>.</p>

        <h2>Object creation over factory</h2>

        <p>This example demonstrates the creation of objects using the factory <i>ObjectFactory</i>.</p>

        <p><a href="01.factory.php">01.factory.php</a></p>

        <h2>Debugging, logging, profiling</h2>

        <p>This example demonstrates how to log messages, include call stacks, and measure execution times using <i>DebugProfiler</i>.</p>

        <p><a href="02.debug_logging.php">02.debug_logging.php</a></p>

        <h2>Error handling</h2>

        <p>This example demonstrates how programming warnings and their call stacks are recorded in a trace log.</p>

        <p><a href="03.error_handling.php">03.error_handling.php</a></p>

        <h2>Events</h2>

        <p>This example demonstrates how to register event handlers, fire events, suspend and resume events, and remove handlers.</p>

        <p><a href="04.events.php">04.events.php</a></p>

        <h2>JSON API</h2>

        <p>This example demonstrates how to send a JSON authentication request and view the response, including validation errors.</p>

        <p><a href="05.json_api.php">05.json_api.php</a></p>

        <h2>XML API</h2>

        <p>This example demonstrates how to send an XML request for hotel rooms and view the response, including validation errors.</p>

        <p><a href="06.xml_api.php">06.xml_api.php</a></p>

        <h2>Messages</h2>

        <p>This example demonstrates how to collect and display errors, warnings, and information messages using <i>Messenger</i>, which preserves messages across requests.</p>

        <p><a href="07.messages.php">07.messages.php</a></p>

        <h2>Database connections</h2>

        <p>These examples demonstrate database connections, queries, prepared statements, transactions, stored procedures, and large data handling with MSSQL, MySQL, Oracle, and PostgreSQL.</p>

        <p><a href="08.mysql_connect.php">08.mysql_connect.php</a></p>

        <p><a href="08.mssql_connect.php">08.mssql_connect.php</a></p>

        <p><a href="08.oracle_connect.php">08.oracle_connect.php</a></p>

        <p><a href="08.postgres_connect.php">08.postgres_connect.php</a></p>

        <h2>Localization</h2>

        <p>This example demonstrates how to switch languages, retrieve translated text, and work with language and country names using <i>LanguageManager</i>.</p>

        <p><a href="09.localization.php">09.localization.php</a></p>

        <h2>Configuration settings</h2>

        <p>These examples demonstrate how to edit, validate, save, and display server and database configuration settings using <i>ConfigSettingsManager</i>.</p>

        <p><a href="10.config_settings_main.php">10.config_settings_main.php</a></p>

        <p><a href="10.config_settings_db.php">10.config_settings_db.php</a></p>

        <p><a href="10.config_settings_summary.php">10.config_settings_summary.php</a></p>

        <h2>Form fields</h2>

        <p>This example demonstrates how to generate form fields, retain submitted values, and display validation messages.</p>

        <p><a href="11.form_fields.php">11.form_fields.php</a></p>

        <h2>Recordsets</h2>

        <p>These examples demonstrate how to load and save database records using <i>Recordset</i>, including multilingual page content and a grid of room prices.</p>

        <p><a href="12.recordsets.php">12.recordsets.php</a></p>

        <p><a href="12.recordsets_grid.php">12.recordsets_grid.php</a></p>

        <h2>Application settings</h2>

        <p>These examples demonstrate how to edit, validate, save, and display application settings stored in a database using <i>ApplicationSettingsManager</i>.</p>

        <p><a href="13.application_settings_main.php">13.application_settings_main.php</a></p>

        <p><a href="13.application_settings_hotel.php">13.application_settings_hotel.php</a></p>

        <p><a href="13.application_settings_summary.php">13.application_settings_summary.php</a></p>

        <h2>User settings</h2>

        <p>These examples demonstrate how to edit, validate, save, and display settings for an individual user using <i>UserSettingsManager</i>.</p>

        <p><a href="14.user_settings_main.php">14.user_settings_main.php</a></p>

        <p><a href="14.user_settings_forum.php">14.user_settings_forum.php</a></p>

        <p><a href="14.user_settings_summary.php">14.user_settings_summary.php</a></p>

        <h2>Tables from arrays</h2>

        <p>This example demonstrates how to generate an HTML table from an array with column captions, CSS classes, and custom value formatting.</p>

        <p><a href="15.tables.php">15.tables.php</a></p>

        <h2>MySQL shards and load balancing</h2>

        <p>This example demonstrates how to register database shards, select a connection from a load balancing group, and enforce read-only access using <i>ShardManager</i>.</p>

        <p><a href="16.mysql_shards.php">16.mysql_shards.php</a></p>

        <h2>OAuth authentication</h2>

        <p>This example demonstrates user authentication, access and refresh token verification, token renewal, and user and client invalidation using <i>OAuthManager</i>.</p>

        <p><a href="17.oauth.php">17.oauth.php</a></p>

    </div>

    <div class="_sys_load_lightbox" id="sys_progress_indicator" role="status" aria-live="polite" aria-hidden="true">
        <div><div>
            <div class="progress_caption">Loading data ...</div>
            <div id="loading_indicator"><img src="images/loading.gif" width="220" height="19" alt=""></div>
        </div></div>
    </div>

</body>

</html>
