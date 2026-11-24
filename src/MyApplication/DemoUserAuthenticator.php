<?php

namespace MyApplication;

use OAuth2\Interfaces\IUserAuthenticator;
use OAuth2\AuthSystemException;
use OAuth2\InvalidCredentialsException;
use OAuth2\MissingParametersException;

/**
 * Demonstrates credential validation with password hashes and optional configured users.
 * The built-in demonstration account is john / qwerty with user ID 129. Production applications
 * must replace this demo identity store and apply request throttling at their authentication
 * endpoint.
 *
 * @author Oleg Schildt
 */
class DemoUserAuthenticator implements IUserAuthenticator
{
    /**
     * Supplies the built-in demo password hash and a dummy unknown-user check.
     *
     * @author Oleg Schildt
     */
    private const string DEMO_PASSWORD_HASH = '$2y$10$l/tXprxdDiS76FWe5LWzQeUijuX68yTNwONTl79Bw10SdnjS6LDhW';

    /**
     * Stores demo identities as user IDs and one-way password hashes.
     *
     * @var array<array-key, array{user_id: int|string, password_hash: string}>
     *
     * @author Oleg Schildt
     */
    protected array $users = [
        'john' => ['user_id' => 129, 'password_hash' => self::DEMO_PASSWORD_HASH],
    ];

    /**
     * Configures an in-memory demo identity map without accepting plaintext passwords.
     *
     * @param array{users?: array<array-key, array{user_id: int|string, password_hash: string}>} $parameters
     * Optional identities indexed by exact login. PHP stores integer-form login keys as integers.
     * An empty users map disables all accounts. Omitting it restores the john / qwerty demo account.
     *
     * @return void
     * Publishes the complete validated identity map; failed initialization preserves prior identities.
     *
     * @throws AuthSystemException
     * If a parameter other than users is supplied or the identity map contains an invalid login,
     * user ID, or password hash. A non-array users value is also rejected.
     *
     * @author Oleg Schildt
     */
    public function init(array $parameters): void
    {
        if (array_diff_key($parameters, ['users' => true]) !== []) {
            throw new AuthSystemException('Unknown demo authenticator parameter.', 'config_error');
        }

        $users = array_key_exists('users', $parameters)
            ? $parameters['users']
            : ['john' => ['user_id' => 129, 'password_hash' => self::DEMO_PASSWORD_HASH]];

        if (!is_array($users)) {
            throw new AuthSystemException('The users parameter must be an identity map.', 'config_error');
        }

        foreach ($users as $login => $user) {
            if (
                (string) $login === '' || strlen((string) $login) > 4096
                || preg_match('//u', (string) $login) !== 1
                || preg_match('/[\x00-\x1f\x7f]/', (string) $login) || !is_array($user)
                || !isset($user['user_id'], $user['password_hash'])
                || (!is_int($user['user_id']) && !is_string($user['user_id']))
                || (string) $user['user_id'] === '' || strlen((string) $user['user_id']) > 4096
                || preg_match('//u', (string) $user['user_id']) !== 1
                || preg_match('/[\x00-\x1f\x7f]/', (string) $user['user_id'])
                || !is_string($user['password_hash']) || password_get_info($user['password_hash'])['algoName'] === 'unknown'
            ) {
                throw new AuthSystemException('A demo identity or password hash is invalid.', 'config_error');
            }
        }

        $this->users = $users;
    } // init

    /**
     * Authenticates exact login/password strings using password_verify().
     * Unknown users also perform password verification. The rejection message does not reveal
     * whether the login exists; applications should use consistent password-hashing policies.
     *
     * @param array<string, mixed> $credentials
     * Nonempty string user_login and user_password. Additional manager fields such as client_id are
     * ignored.
     *
     * @return int|string
     * Returns the configured user ID, preserving its type and allowing zero or the string "0".
     *
     * @throws MissingParametersException
     * If a required credential is null, empty or absent.
     *
     * @throws InvalidCredentialsException
     * If a credential has an invalid type or size, contains a zero byte, or fails authentication.
     *
     * @author Oleg Schildt
     */
    public function authenticateUser(array $credentials): int|string
    {
        foreach (['user_login', 'user_password'] as $name) {
            if (!array_key_exists($name, $credentials) || $credentials[$name] === null || $credentials[$name] === '') {
                throw new MissingParametersException("The '$name' is not specified.");
            }

            if (
                !is_string($credentials[$name]) || strlen($credentials[$name]) > 4096
                || str_contains($credentials[$name], "\0")
            ) {
                throw new InvalidCredentialsException('The user login or password is invalid.');
            }
        }

        // Run the password hash check even when the login is unknown.
        $user = $this->users[$credentials['user_login']] ?? null;
        $hash = $user['password_hash'] ?? self::DEMO_PASSWORD_HASH;
        $verified = password_verify($credentials['user_password'], $hash);

        if (!$verified || $user === null) {
            throw new InvalidCredentialsException('The user login or password is invalid.');
        }

        return $user['user_id'];
    } // authenticateUser
} // DemoUserAuthenticator

