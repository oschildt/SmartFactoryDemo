<?php
namespace MyApplication;

require "../vendor/autoload.php";

use OAuth2\Interfaces\IOAuthManager;
use OAuth2\Interfaces\ITokenStorage;
use OAuth2\Interfaces\IUserAuthenticator;

use function SmartFactory\singleton;

?><!DOCTYPE html>
<html lang="en">
<head>
    <title>Authentication Test</title>

    <link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Authentication Test</h2>

<pre>

<?php

// $supported_algorithms = ['HS256', 'HS384', 'HS512', 'RS256', 'RS384', 'RS512'];

$params = [];

$params["access_token_ttl"] = 600; // 10 min
$params["refresh_token_ttl"] = 3600; // 1 hours
$params["max_token_inactivity_days"] = 7; // 7 days

$params["token_storage"] = singleton(ITokenStorage::class);
$params["token_storage"]->init(["storage_file" => approot() . "config/auth_tokens.xml"]);

$params["user_authenticator"] = singleton(IUserAuthenticator::class);

$params["encryption_algorithm"] = "RS256";

$params["secret_key"] = "OLEG";

$params["public_key"] = approot() . "config/public_key.pem";
$params["private_key"] = approot() . "config/private_key.pem";
$params["pass_phrase"] = "termin";

$oam = singleton(IOAuthManager::class);
$oam->init($params);

$response = [];
$credentials = [];

$credentials["client_id"] = $_SERVER['HTTP_USER_AGENT'];
$credentials["user_login"] = "john";
$credentials["user_password"] = "qwerty";

$user_id = null;
$refresh_token = "";

echo "<h2>Authentication</h2>";

try {
    echo "<h3>Response from authenticateUser</h3>";
    $oam->authenticateUser($credentials, $response);
    print_r($response);

    $refresh_token = $response["refresh_token"];
    $user_id = $response["user_id"];

    echo "<h3>Verifying access token</h3>";

    print_r($oam->verifyJwtAccessToken($response["jwt_access_token"], true));

    echo "<p style='color: green; font-weight: bold'>Verifying successful.</p>";

    echo "<h3>Verifying resefresh token</h3>";
    $oam->verifyRefreshToken($refresh_token, $user_id, $credentials["client_id"]);

    echo "<p style='color: green; font-weight: bold'>Verifying successful.</p>";
} catch (\Exception $ex) {
    echo "<p style='color: red; font-weight: bold'>" . $ex->getMessage() . "</p>";
}

echo "<h2>Refreshing tokens</h2>";

try {
    $oam->refreshTokens($refresh_token, $user_id, $credentials["client_id"], $response);
    
    echo "<h3>Response from refreshTokens</h3>";
    print_r($response);

    $refresh_token = $response["refresh_token"];
    $user_id = $response["user_id"];

    echo "<h3>Verifying access token</h3>";

    print_r($oam->verifyJwtAccessToken($response["jwt_access_token"], true));

    echo "<p style='color: green; font-weight: bold'>Verifying successful.</p>";

    echo "<h3>Verifying resefresh token</h3>";
    $oam->verifyRefreshToken($refresh_token, $user_id, $credentials["client_id"]);

    echo "<p style='color: green; font-weight: bold'>Verifying successful.</p>";
} catch (\Exception $ex) {
    echo "<p style='color: red; font-weight: bold'>" . $ex->getMessage() . "</p>";
}

echo "<h2>Invalidation of the user</h2>";

try {
    $oam->invalidateUser($user_id, $credentials["client_id"], $refresh_token);
    echo "<p style='color: green; font-weight: bold'>Invalidation succeeded.</p>";

    echo "<h3>Verifying access token (must fail)</h3>";

    print_r($oam->verifyJwtAccessToken($response["jwt_access_token"], true));
} catch (\Exception $ex) {
    echo "<p style='color: red; font-weight: bold'>" . $ex->getMessage() . "</p>";
}

echo "<h2>Re-authentication</h2>";

try {
    echo "<h3>Response from authenticateUser</h3>";
    $oam->authenticateUser($credentials, $response);
    print_r($response);

    $refresh_token = $response["refresh_token"];
    $user_id = $response["user_id"];

    echo "<h3>Verifying access token</h3>";

    print_r($oam->verifyJwtAccessToken($response["jwt_access_token"], true));

    echo "<p style='color: green; font-weight: bold'>Verifying successful.</p>";

    echo "<h3>Verifying resefresh token</h3>";
    $oam->verifyRefreshToken($refresh_token, $user_id, $credentials["client_id"]);

    echo "<p style='color: green; font-weight: bold'>Verifying successful.</p>";
} catch (\Exception $ex) {
    echo "<p style='color: red; font-weight: bold'>" . $ex->getMessage() . "</p>";
}

echo "<h2>Invalidation of the client</h2>";

try {
    $oam->invalidateClient($user_id, $credentials["client_id"], $refresh_token);
    echo "<p style='color: green; font-weight: bold'>Invalidation succeeded.</p>";

    echo "<h3>Verifying access token (must fail)</h3>";

    print_r($oam->verifyJwtAccessToken($response["jwt_access_token"], true));
} catch (\Exception $ex) {
    echo "<p style='color: red; font-weight: bold'>" . $ex->getMessage() . "</p>";
}

echo "<h2>Re-authentication</h2>";

try {
    echo "<h3>Response from authenticateUser</h3>";
    $oam->authenticateUser($credentials, $response);
    print_r($response);

    $refresh_token = $response["refresh_token"];
    $user_id = $response["user_id"];

    echo "<h3>Verifying access token</h3>";

    print_r($oam->verifyJwtAccessToken($response["jwt_access_token"], true));

    echo "<p style='color: green; font-weight: bold'>Verifying successful.</p>";

    echo "<h3>Verifying resefresh token</h3>";
    $oam->verifyRefreshToken($refresh_token, $user_id, $credentials["client_id"]);

    echo "<p style='color: green; font-weight: bold'>Verifying successful.</p>";
} catch (\Exception $ex) {
    echo "<p style='color: red; font-weight: bold'>" . $ex->getMessage() . "</p>";
}

echo "<h2>Invalidation of the access token</h2>";

try {
    $oam->invalidateJwtAccessToken($response["jwt_access_token"]);
    echo "<p style='color: green; font-weight: bold'>Invalidation succeeded.</p>";

    echo "<h3>Verifying access token (must fail)</h3>";

    print_r($oam->verifyJwtAccessToken($response["jwt_access_token"], true));
} catch (\Exception $ex) {
    echo "<p style='color: red; font-weight: bold'>" . $ex->getMessage() . "</p>";
}

echo "<h2>Re-authentication</h2>";

try {
    echo "<h3>Response from authenticateUser</h3>";
    $oam->authenticateUser($credentials, $response);
    print_r($response);

    $refresh_token = $response["refresh_token"];
    $user_id = $response["user_id"];

    echo "<h3>Verifying access token</h3>";

    print_r($oam->verifyJwtAccessToken($response["jwt_access_token"], true));

    echo "<p style='color: green; font-weight: bold'>Verifying successful.</p>";

    echo "<h3>Verifying resefresh token</h3>";
    $oam->verifyRefreshToken($refresh_token, $user_id, $credentials["client_id"]);

    echo "<p style='color: green; font-weight: bold'>Verifying successful.</p>";
} catch (\Exception $ex) {
    echo "<p style='color: red; font-weight: bold'>" . $ex->getMessage() . "</p>";
}

echo "<h2>Invalidation of the refresh token</h2>";

try {
    $oam->invalidateRefreshToken($refresh_token);
    echo "<p style='color: green; font-weight: bold'>Invalidation succeeded.</p>";

    $refresh_token = $response["refresh_token"];
    $user_id = $response["user_id"];

    echo "<h3>Verifying refrech token (must fail)</h3>";

    $oam->verifyRefreshToken($refresh_token, $user_id, $credentials["client_id"]);
} catch (\Exception $ex) {
    echo "<p style='color: red; font-weight: bold'>" . $ex->getMessage() . "</p>";
}
?>

</pre>

</body>
</html>
