<?php
namespace MyApplication;

require "../vendor/autoload.php";

use OAuth2\Interfaces\IOAuthManager;
use OAuth2\Interfaces\ITokenStorage;
use OAuth2\Interfaces\IUserAuthenticator;

use function SmartFactory\singleton;
use function SmartFactory\escape_html;

?><!DOCTYPE html>
<html lang="en">

<head>
    <title>OAuth authentication</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="css/examples.css" type="text/css" />
    <script src="js/examples.js" defer></script>
</head>

<body>

    <div class="header"></div>
    <div class="content">

        <div class="left_links"></div>
        <div class="right_links">
            <p><a href="index.php">List of examples</a></p>
        </div>
        <div style="clear: both;"></div>

        <h1>OAuth authentication</h1>

        <p>This example demonstrates user authentication, access and refresh token verification, token renewal, and user and client invalidation using <i>OAuthManager</i>.</p>

        <?php

        // $supported_algorithms = ['HS256', 'HS384', 'HS512', 'RS256', 'RS384', 'RS512'];

        $params = [];

        $params["access_token_ttl_minutes"] = 10;
        $params["refresh_token_ttl_days"] = 14;

        $params["token_storage"] = singleton(ITokenStorage::class);
        $params["token_storage"]->init(["storage_file" => approot() . "config/auth_tokens.xml"]);

        $params["user_authenticator"] = singleton(IUserAuthenticator::class);

        $params["encryption_algorithm"] = "RS256";
        $params["issuer"] = "smartfactory-demo";
        $params["audience"] = "smartfactory-demo-api";
        $params["key_id"] = "demo-rsa-1";

        $params["public_key"] = approot() . "config/public_key.pem";
        $params["private_key"] = approot() . "config/private_key.pem";
        $params["pass_phrase"] = "termin";

        $oam = singleton(IOAuthManager::class);
        $oam->init($params);

        $response = [];
        $credentials = [];

        $credentials["client_id"] = $_SERVER['HTTP_USER_AGENT'] ?? 'demo-client';
        $credentials["user_login"] = "john";
        $credentials["user_password"] = "qwerty";

        $user_id = null;
        $refresh_token = "";

        echo "<h2>Authentication</h2>";

        try {
            echo "<h3>Response from authenticateUser</h3>";
            $oam->authenticateUser($credentials, $response);

            echo "<pre>" . escape_html(print_r($response, true)) . "</pre>";

            $refresh_token = $response["refresh_token"];
            $user_id = $response["user_id"];

            echo "<h3>Verifying access token</h3>";

            // Verify before emitting markup so an exception cannot leave an unclosed preformatted block.
            $token_data = $oam->verifyJwtAccessToken($response["jwt_access_token"], true);

            echo "<pre>" . escape_html(print_r($token_data, true)) . "</pre>";

            echo "<p style='color: green; font-weight: bold'>Verifying successful.</p>";

            echo "<h3>Verifying refresh token</h3>";
            $oam->verifyRefreshToken($refresh_token, $user_id, $credentials["client_id"]);

            echo "<p style='color: green; font-weight: bold'>Verifying successful.</p>";
        } catch (\Exception $ex) {
            echo "<p style='color: red; font-weight: bold'>" . escape_html($ex->getMessage()) . "</p>";
        }

        echo "<h3>Refreshing tokens</h3>";

        try {
            $oam->refreshTokens($refresh_token, $user_id, $credentials["client_id"], $response);

            echo "<h2>Response from refreshTokens</h2>";

            echo "<pre>" . escape_html(print_r($response, true)) . "</pre>";

            $refresh_token = $response["refresh_token"];
            $user_id = $response["user_id"];

            echo "<h2>Verifying access token</h2>";

            $token_data = $oam->verifyJwtAccessToken($response["jwt_access_token"], true);

            echo "<pre>" . escape_html(print_r($token_data, true)) . "</pre>";

            echo "<p style='color: green; font-weight: bold'>Verifying successful.</p>";

            echo "<h2>Verifying refresh token</h2>";
            $oam->verifyRefreshToken($refresh_token, $user_id, $credentials["client_id"]);

            echo "<p style='color: green; font-weight: bold'>Verifying successful.</p>";
        } catch (\Exception $ex) {
            echo "<p style='color: red; font-weight: bold'>" . escape_html($ex->getMessage()) . "</p>";
        }

        echo "<h3>Invalidation of the user</h3>";

        try {
            $oam->invalidateUser($user_id, $credentials["client_id"], $refresh_token);
            echo "<p style='color: green; font-weight: bold'>Invalidation succeeded.</p>";

            echo "<h3>Verifying access token (must fail)</h3>";

            $token_data = $oam->verifyJwtAccessToken($response["jwt_access_token"], true);

            echo "<pre>" . escape_html(print_r($token_data, true)) . "</pre>";
        } catch (\Exception $ex) {
            echo "<p style='color: red; font-weight: bold'>" . escape_html($ex->getMessage()) . "</p>";
        }

        echo "<h2>Re-authentication</h2>";

        try {
            echo "<h3>Response from authenticateUser</h3>";
            $oam->authenticateUser($credentials, $response);

            echo "<pre>" . escape_html(print_r($response, true)) . "</pre>";

            $refresh_token = $response["refresh_token"];
            $user_id = $response["user_id"];

            echo "<h3>Verifying access token</h3>";

            $token_data = $oam->verifyJwtAccessToken($response["jwt_access_token"], true);

            echo "<pre>" . escape_html(print_r($token_data, true)) . "</pre>";

            echo "<p style='color: green; font-weight: bold'>Verifying successful.</p>";

            echo "<h3>Verifying refresh token</h3>";
            $oam->verifyRefreshToken($refresh_token, $user_id, $credentials["client_id"]);

            echo "<p style='color: green; font-weight: bold'>Verifying successful.</p>";
        } catch (\Exception $ex) {
            echo "<p style='color: red; font-weight: bold'>" . escape_html($ex->getMessage()) . "</p>";
        }

        echo "<h3>Invalidation of the client</h3>";

        try {
            $oam->invalidateClient($user_id, $credentials["client_id"], $refresh_token);
            echo "<p style='color: green; font-weight: bold'>Invalidation succeeded.</p>";

            echo "<h3>Verifying access token (must fail)</h3>";

            $token_data = $oam->verifyJwtAccessToken($response["jwt_access_token"], true);

            echo "<pre>" . escape_html(print_r($token_data, true)) . "</pre>";
        } catch (\Exception $ex) {
            echo "<p style='color: red; font-weight: bold'>" . escape_html($ex->getMessage()) . "</p>";
        }

        echo "<h2>Re-authentication</h2>";

        try {
            echo "<h3>Response from authenticateUser</h3>";
            $oam->authenticateUser($credentials, $response);

            echo "<pre>" . escape_html(print_r($response, true)) . "</pre>";

            $refresh_token = $response["refresh_token"];
            $user_id = $response["user_id"];

            echo "<h3>Verifying access token</h3>";

            $token_data = $oam->verifyJwtAccessToken($response["jwt_access_token"], true);

            echo "<pre>" . escape_html(print_r($token_data, true)) . "</pre>";

            echo "<p style='color: green; font-weight: bold'>Verifying successful.</p>";

            echo "<h3>Verifying refresh token</h3>";
            $oam->verifyRefreshToken($refresh_token, $user_id, $credentials["client_id"]);

            echo "<p style='color: green; font-weight: bold'>Verifying successful.</p>";
        } catch (\Exception $ex) {
            echo "<p style='color: red; font-weight: bold'>" . escape_html($ex->getMessage()) . "</p>";
        }

        echo "<h3>Invalidation of the access token</h3>";

        try {
            $oam->invalidateJwtAccessToken($response["jwt_access_token"]);
            echo "<p style='color: green; font-weight: bold'>Invalidation succeeded.</p>";

            echo "<h2>Verifying access token (must fail)</h2>";

            $token_data = $oam->verifyJwtAccessToken($response["jwt_access_token"], true);

            echo "<pre>" . escape_html(print_r($token_data, true)) . "</pre>";
        } catch (\Exception $ex) {
            echo "<p style='color: red; font-weight: bold'>" . escape_html($ex->getMessage()) . "</p>";
        }

        echo "<h2>Re-authentication</h2>";

        try {
            echo "<h3>Response from authenticateUser</h3>";
            $oam->authenticateUser($credentials, $response);

            echo "<pre>" . escape_html(print_r($response, true)) . "</pre>";

            $refresh_token = $response["refresh_token"];
            $user_id = $response["user_id"];

            echo "<h3>Verifying access token</h3>";

            $token_data = $oam->verifyJwtAccessToken($response["jwt_access_token"], true);

            echo "<pre>" . escape_html(print_r($token_data, true)) . "</pre>";

            echo "<p style='color: green; font-weight: bold'>Verifying successful.</p>";

            echo "<h3>Verifying refresh token</h3>";
            $oam->verifyRefreshToken($refresh_token, $user_id, $credentials["client_id"]);

            echo "<p style='color: green; font-weight: bold'>Verifying successful.</p>";
        } catch (\Exception $ex) {
            echo "<p style='color: red; font-weight: bold'>" . escape_html($ex->getMessage()) . "</p>";
        }

        echo "<h3>Invalidation of the refresh token</h3>";

        try {
            $oam->invalidateRefreshToken($refresh_token);
            echo "<p style='color: green; font-weight: bold'>Invalidation succeeded.</p>";

            $refresh_token = $response["refresh_token"];
            $user_id = $response["user_id"];

            echo "<h3>Verifying refresh token (must fail)</h3>";

            $oam->verifyRefreshToken($refresh_token, $user_id, $credentials["client_id"]);
        } catch (\Exception $ex) {
            echo "<p style='color: red; font-weight: bold'>" . escape_html($ex->getMessage()) . "</p>";
        }
        ?>

    </div>

    <div class="_sys_load_lightbox" id="sys_progress_indicator" role="status" aria-live="polite" aria-hidden="true">
        <div>
            <div>
                <div class="progress_caption">Loading data ...</div>
                <div id="loading_indicator"><img src="images/loading.gif" width="220" height="19" alt=""></div>
            </div>
        </div>
    </div>

</body>

</html>
