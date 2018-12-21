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
    <title>Auth Test</title>

    <link rel="stylesheet" href="css/examples.css" type="text/css"/>
</head>
<body>
<h2>Auth Test</h2>

<?php

// $supported_algorithms = ['HS256', 'HS384', 'HS512', 'RS256', 'RS384', 'RS512'];

$params = [];

$params["access_token_ttl"] = 600; // 10 min
$params["refresh_token_ttl"] = 3600; // 1 hours

$params["secret_key"] = "OLEG";

$params["token_storage"] = singleton(ITokenStorage::class);
$params["token_storage"]->init(["storage_file" => approot() . "../config/auth_tokens.xml"]);

$params["user_authenticator"] = singleton(IUserAuthenticator::class);

$params["encryption_algorithm"] = "RS256";

$params["secret_key"] = "OLEG";

$params["public_key"] = approot() . "../config/public_key.pem";
$params["private_key"] = approot() . "../config/private_key.pem";
$params["pass_phrase"] = "termin";

$oam = singleton(IOAuthManager::class);
$oam->init($params);

$response = [];
$credentials = [];

$credentials["client_id"] = "sddssdfasr23424dsfdswf";
$credentials["user_login"] = "john";
$credentials["user_password"] = "smith";

$user_id = null;
$refresh_token = "";

try {
    $oam->authenticateUser($credentials, $response);
    
    echo "Response from authenticateUser:<br>";
    echo "<pre>";
    print_r($response);
    echo "</pre>";
    
    $refresh_token = $response["refresh_token"];
    $user_id = $response["user_id"];
    
    echo "verified:" .  $oam->verifyJwtAccessToken($response["jwt_access_token"], $response["user_id"], $credentials["client_id"]) . "<br>";
} catch (\SmartFactory\SmartException $ex) {
    echo "[" . $ex->getErrorCode() . "]: " . $ex->getMessage() . "<br>";
}

sleep(2);

try {
    $oam->refreshAccessToken($refresh_token, $user_id, $credentials["client_id"], $response);
    
    $refresh_token = $response["refresh_token"];

    echo "Response from refreshAccessToken:<br>";
    echo "<pre>";
    print_r($response);
    echo "</pre>";
    
    echo "verified:" .  $oam->verifyJwtAccessToken($response["jwt_access_token"], $user_id, $credentials["client_id"]) . "<br>";
} catch (\SmartFactory\SmartException $ex) {
    echo "[" . $ex->getErrorCode() . "]: " . $ex->getMessage() . "<br>";
}

try {
    echo "invalidateUser:<br>";
    echo "result:" . $oam->invalidateUser($user_id, $credentials["client_id"], $refresh_token);
    echo "<br>";
} catch (\SmartFactory\SmartException $ex) {
    echo "[" . $ex->getErrorCode() . "]: " . $ex->getMessage() . "<br>";
}

try {
    $oam->authenticateUser($credentials, $response);
    $refresh_token = $response["refresh_token"];
} catch (\SmartFactory\SmartException $ex) {
    echo "[" . $ex->getErrorCode() . "]: " . $ex->getMessage() . "<br>";
}

try {
    echo "invalidateClient:<br>";
    echo "result:" . $oam->invalidateClient($user_id, $credentials["client_id"], $refresh_token);
    echo "<br>";
} catch (\SmartFactory\SmartException $ex) {
    echo "[" . $ex->getErrorCode() . "]: " . $ex->getMessage() . "<br>";
}

try {
    $oam->authenticateUser($credentials, $response);
} catch (\SmartFactory\SmartException $ex) {
    echo "[" . $ex->getErrorCode() . "]: " . $ex->getMessage() . "<br>";
}

try {
    echo "invalidateJwtAccessToken:<br>";
    echo "result:" . $oam->invalidateJwtAccessToken($response["jwt_access_token"]);
    echo "<br>";
} catch (\SmartFactory\SmartException $ex) {
    echo $ex->getMessage() . "<br>";
}

try {
    $oam->authenticateUser($credentials, $response);

    $refresh_token = $response["refresh_token"];
} catch (\SmartFactory\SmartException $ex) {
    echo "[" . $ex->getErrorCode() . "]: " . $ex->getMessage() . "<br>";
}

try {
    echo "invalidateRefreshToken:<br>";
    echo "result:" . $oam->invalidateRefreshToken($refresh_token);
    echo "<br>";
} catch (\SmartFactory\SmartException $ex) {
    echo "[" . $ex->getErrorCode() . "]: " . $ex->getMessage() . "<br>";
}

?>

</body>
</html>
