<?php

namespace MyApplication;

use OAuth2\Interfaces\ITokenStorage;
use OAuth2\AuthSystemException;
use OAuth2\InvalidTokenException;
use OAuth2\MissingParametersException;
use OAuth2\TokenExpiredException;

/**
 * Demonstrates process-safe XML token storage with hashed bearer credentials.
 * A stable .lock file serializes all reads and writes. Complete snapshots replace the XML
 * atomically. Consumed refresh hashes are retained until their original expiry for family
 * revocation on replay. Verification updates activity without extending token lifetimes. Legacy
 * array XML is migrated to version 2 under the lock. Use a transactional database for high-volume
 * production workloads.
 *
 * @author Oleg Schildt
 */
class DemoTokenStorage implements ITokenStorage
{

    /**
     * Stores the canonical local XML path.
     *
     * @var string
     *
     * @author Oleg Schildt
     */
    protected string $storage_file = "";

    /**
     * Caches sessions only while the current operation owns the lock.
     *
     * @var list<array<string, int|string>>
     *
     * @author Oleg Schildt
     */
    protected array $records = [];

    /**
     * Caches consumed token hashes, bindings, family IDs and original expirations.
     *
     * @var list<array<string, int|string>>
     *
     * @author Oleg Schildt
     */
    protected array $consumed = [];

    /**
     * Guards against nested operations and unlocked snapshot access.
     *
     * @var bool
     *
     * @author Oleg Schildt
     */
    private bool $locked = false;

    /**
     * Configures a local XML file without reading or clearing existing sessions.
     *
     * @param array{storage_file: string} $parameters
     * A file in an existing writable directory. Missing storage is created on the first successful
     * mutation.
     *
     * @return void
     * Publishes a validated path; failed initialization preserves the previous path.
     *
     * @throws AuthSystemException
     * If a parameter is unknown, the file is not local, is a directory or symbolic link,
     * or its directory is inaccessible.
     *
     * @throws \LogicException
     * If this instance is already processing a locked operation.
     *
     * @author Oleg Schildt
     */
    public function init(array $parameters): void
    {
        if ($this->locked) {
            throw new \LogicException('Token storage cannot be initialized during an active operation.');
        }

        if (array_diff_key($parameters, ['storage_file' => true]) !== []) {
            throw new AuthSystemException('Unknown demo token storage parameter.', 'config_error');
        }

        $file = $parameters['storage_file'] ?? null;

        if (
            !is_string($file) || trim($file) === '' || str_contains($file, "\0")
            || str_contains($file, '://') || is_dir($file) || is_link($file)
            || in_array(basename($file), ['.', '..'], true)
        ) {
            throw new AuthSystemException('The storage_file must be a local regular file path.', 'config_error');
        }

        $directory = realpath(dirname($file));

        if (
            $directory === false || !is_dir($directory) || !is_writable($directory)
            || (file_exists($file) && (!is_file($file) || !is_readable($file) || !is_writable($file)))
        ) {
            throw new AuthSystemException('The token storage directory or file is inaccessible.', 'config_error');
        }

        $this->storage_file = $directory . DIRECTORY_SEPARATOR . basename($file);
    } // init

    /**
     * Supplies the storage wall clock for expiry and activity checks.
     *
     * @return int
     * Returns the current Unix timestamp in seconds.
     *
     * @author Oleg Schildt
     */
    protected function getCurrentTime(): int
    {
        return time();
    } // getCurrentTime

    /**
     * Validates storage identifiers and tokens without coercing their types.
     *
     * @param mixed $value
     * The supplied value; the string "0" is valid.
     *
     * @param string $name
     * The field name for diagnostics.
     *
     * @return string
     * Returns the exact nonempty UTF-8 value, at most 4096 bytes and without controls.
     *
     * @throws MissingParametersException
     * If the value is null or empty.
     *
     * @throws InvalidTokenException
     * If its type, length, encoding or characters are invalid.
     *
     * @author Oleg Schildt
     */
    protected function requireValue(mixed $value, string $name): string
    {
        if ($value === null || $value === '') {
            throw new MissingParametersException("The '$name' is not specified.");
        }

        if (
            !is_string($value) || strlen($value) > 4096 || preg_match('//u', $value) !== 1
            || preg_match('/[\x00-\x1f\x7f]/', $value) !== 0
        ) {
            throw new InvalidTokenException("The '$name' must be a valid nonempty UTF-8 string.");
        }

        return $value;
    } // requireValue

    /**
     * Serializes a complete read/check/write operation with a stable file lock.
     *
     * @param callable(): mixed $operation
     * The operation to execute after loading a fresh snapshot.
     *
     * @return mixed
     * Returns the callback result after releasing the lock and clearing cached data.
     *
     * @throws AuthSystemException
     * If initialization, locking or snapshot loading fails.
     *
     * @throws \LogicException
     * If this instance attempts a nested operation.
     *
     * @throws \Throwable
     * If the callback fails; locks and cached snapshots are released before propagation.
     *
     * @author Oleg Schildt
     */
    protected function executeAtomically(callable $operation): mixed
    {
        if ($this->storage_file === '') {
            throw new AuthSystemException('Token storage has not been initialized.', 'config_error');
        }

        if ($this->locked) {
            throw new \LogicException('Nested token storage operations are not supported.');
        }

        $lock = @fopen($this->storage_file . '.lock', 'c+b');

        if ($lock === false) {
            throw new AuthSystemException('The token storage lock could not be opened.');
        }

        try {
            if (!flock($lock, LOCK_EX)) {
                throw new AuthSystemException('The token storage lock could not be acquired.');
            }

            $this->locked = true;

            $this->loadRecords();

            return $operation();
        } finally {
            $this->records = [];
            $this->consumed = [];
            $this->locked = false;

            flock($lock, LOCK_UN);

            fclose($lock);
        }
    } // executeAtomically

    /**
     * Prevents snapshot access outside the atomic operation.
     *
     * @return void
     * Permits access only while this instance owns its lock.
     *
     * @throws \LogicException
     * If no locked operation is active.
     *
     * @author Oleg Schildt
     */
    protected function assertLocked(): void
    {
        if (!$this->locked) {
            throw new \LogicException('Token snapshot access requires an active storage lock.');
        }
    } // assertLocked

    /**
     * Validates a new session and replaces raw bearer credentials with SHA-256 hashes.
     *
     * @param array<string, mixed> $record
     * Raw token inputs and integer timestamps required by ITokenStorage.
     *
     * @return array<string, int|string>
     * Returns normalized session fields without either raw token.
     *
     * @throws MissingParametersException
     * If a required identifier or token is absent.
     *
     * @throws InvalidTokenException
     * If a field has an invalid type, encoding or lifetime.
     *
     * @author Oleg Schildt
     */
    protected function normalizeRecord(array $record): array
    {
        $normalized = [];

        foreach (['user_id', 'client_id', 'access_token', 'refresh_token'] as $name) {
            $normalized[$name] = $this->requireValue($record[$name] ?? null, $name);
        }

        foreach (['access_token_expire', 'refresh_token_expire', 'last_activity'] as $name) {
            if (!isset($record[$name]) || !is_int($record[$name]) || $record[$name] < 0) {
                throw new InvalidTokenException("The '$name' must be a nonnegative integer timestamp.");
            }

            $normalized[$name] = $record[$name];
        }

        if (
            $normalized['access_token_expire'] <= $normalized['last_activity']
            || $normalized['refresh_token_expire'] <= $normalized['last_activity']
        ) {
            throw new InvalidTokenException('New token expiration must follow its issue time.');
        }

        $normalized['access_token_hash'] = hash('sha256', $normalized['access_token']);
        $normalized['refresh_token_hash'] = hash('sha256', $normalized['refresh_token']);

        unset($normalized['access_token'], $normalized['refresh_token']);

        return $normalized;
    } // normalizeRecord

    /**
     * Collects XML child elements while rejecting non-whitespace mixed content.
     *
     * @param \DOMElement $parent
     * The validated parent element.
     *
     * @return list<\DOMElement>
     * Returns child elements in document order.
     *
     * @throws AuthSystemException
     * If non-element nodes contain unexpected text.
     *
     * @author Oleg Schildt
     */
    protected function elementChildren(\DOMElement $parent): array
    {
        $elements = [];

        foreach ($parent->childNodes as $node) {
            if ($node instanceof \DOMElement) {
                $elements[] = $node;
            } elseif ($node->nodeType !== XML_COMMENT_NODE && trim($node->textContent) !== '') {
                throw new AuthSystemException('Token storage contains unexpected XML content.');
            }
        }

        return $elements;
    } // elementChildren

    /**
     * Validates the exact version-2 XML record shape and integer timestamps.
     *
     * @param \DOMElement $element
     * A Session or Consumed element.
     *
     * @param bool $consumed
     * Whether the element represents consumed refresh history.
     *
     * @return array<string, int|string>
     * Returns validated identifiers, hashes and timestamps.
     *
     * @throws AuthSystemException
     * If attributes, children, identifiers, hashes or timestamps violate the schema.
     *
     * @author Oleg Schildt
     */
    protected function readStoredRecord(\DOMElement $element, bool $consumed): array
    {
        $strings = $consumed ? ['user_id', 'client_id', 'family_id', 'refresh_token_hash']
            : ['user_id', 'client_id', 'family_id', 'access_token_hash', 'refresh_token_hash'];
        $times = $consumed ? ['refresh_token_expire'] : ['access_token_expire', 'refresh_token_expire', 'last_activity'];

        if ($element->attributes->length !== count($strings) + count($times) || $this->elementChildren($element) !== []) {
            throw new AuthSystemException('A stored token record has an unsupported structure.');
        }

        $record = [];

        foreach ($strings as $name) {
            $value = $element->getAttribute($name);

            if (
                ($name === 'user_id' || $name === 'client_id')
                ? ($value === '' || strlen($value) > 4096 || preg_match('//u', $value) !== 1 || preg_match('/[\x00-\x1f\x7f]/', $value))
                : !preg_match('/^[a-f0-9]{64}$/D', $value)
            ) {
                throw new AuthSystemException('A stored token identifier or hash is invalid.');
            }

            $record[$name] = $value;
        }

        foreach ($times as $name) {
            $value = $element->getAttribute($name);

            if (!preg_match('/^(0|[1-9][0-9]{0,18})$/D', $value) || (string) (int) $value !== $value) {
                throw new AuthSystemException('A stored token timestamp is invalid.');
            }

            $record[$name] = (int) $value;
        }

        return $record;
    } // readStoredRecord

    /**
     * Loads a fresh, validated XML snapshot while the caller owns the storage lock.
     *
     * @return void
     * Treats a missing file as empty storage; migrates legacy array XML under the same lock.
     *
     * @throws \LogicException
     * If called without an active lock.
     *
     * @throws AuthSystemException
     * If XML is unreadable, empty, oversized, malformed, contains a DTD, has an unsupported schema or
     * contains invalid or duplicate records.
     *
     * @author Oleg Schildt
     */
    protected function loadRecords(): void
    {
        $this->assertLocked();

        $this->records = [];
        $this->consumed = [];

        if (!file_exists($this->storage_file)) {
            return;
        }

        $xml = @file_get_contents($this->storage_file);

        if ($xml === false || $xml === '' || strlen($xml) > 16777216) {
            throw new AuthSystemException('The token storage cannot be read or exceeds its size limit.');
        }

        $previous = libxml_use_internal_errors(true);

        libxml_clear_errors();

        try {
            $document = new \DOMDocument();

            if (!$document->loadXML($xml, LIBXML_NONET) || $document->doctype !== null) {
                throw new AuthSystemException('Token storage contains invalid XML or a prohibited document type.');
            }
        } finally {
            libxml_clear_errors();

            libxml_use_internal_errors($previous);
        }

        $root = $document->documentElement;

        if ($root === null) {
            throw new AuthSystemException('Token storage has no root element.');
        }

        if ($root->nodeName === 'array') {
            $this->migrateLegacyRecords($root);

            return;
        }

        if ($root->nodeName !== 'OAuthTokens' || $root->getAttribute('version') !== '2' || $root->attributes->length !== 1) {
            throw new AuthSystemException('Token storage has an unsupported schema.');
        }

        $pairs = [];
        $hashes = [];
        $access_hashes = [];
        $families = [];

        foreach ($this->elementChildren($root) as $element) {
            if (!in_array($element->nodeName, ['Session', 'Consumed'], true)) {
                throw new AuthSystemException('Token storage contains an unknown record type.');
            }

            $consumed = $element->nodeName === 'Consumed';
            $record = $this->readStoredRecord($element, $consumed);
            $hash = $record['refresh_token_hash'];

            if (isset($hashes[$hash])) {
                throw new AuthSystemException('Token storage contains duplicate refresh hashes.');
            }

            $hashes[$hash] = true;

            if ($consumed) {
                // Expired refresh tokens no longer need to be retained for reuse detection.
                if ($record['refresh_token_expire'] > $this->getCurrentTime()) {
                    $this->consumed[] = $record;
                }
            } else {
                $pair = json_encode([$record['user_id'], $record['client_id']], JSON_THROW_ON_ERROR);

                if (
                    isset($pairs[$pair]) || isset($access_hashes[$record['access_token_hash']])
                    || isset($families[$record['family_id']])
                ) {
                    throw new AuthSystemException('Token storage contains duplicate sessions or credentials.');
                }

                $pairs[$pair] = true;
                $access_hashes[$record['access_token_hash']] = true;
                $families[$record['family_id']] = true;
                $this->records[] = $record;
            }
        }
    } // loadRecords

    /**
     * Converts legacy array XML into hashed records without clearing valid sessions.
     *
     * @param \DOMElement $root
     * The legacy array root; empty array roots are supported.
     *
     * @return void
     * Commits version-2 XML only after validating the complete legacy document.
     *
     * @throws AuthSystemException
     * If legacy records are malformed or duplicated, secure family ID generation fails, or persistence
     * fails.
     *
     * @author Oleg Schildt
     */
    protected function migrateLegacyRecords(\DOMElement $root): void
    {
        $pairs = [];

        try {
            foreach ($this->elementChildren($root) as $item) {
                if ($item->nodeName !== 'item' || $item->getAttribute('array') !== '1') {
                    throw new AuthSystemException('Legacy token storage contains an invalid record.');
                }

                $raw = [];

                foreach ($this->elementChildren($item) as $field) {
                    $name = $field->getAttribute('name');

                    if ($field->nodeName !== 'item' || isset($raw[$name]) || $field->getElementsByTagName('*')->length !== 0) {
                        throw new AuthSystemException('Legacy token storage contains an invalid field.');
                    }

                    $raw[$name] = $field->textContent;
                }

                $record = [];

                foreach (['user_id', 'client_id', 'access_token', 'refresh_token'] as $name) {
                    $record[$name] = $this->requireValue($raw[$name] ?? null, $name);
                }

                foreach (['access_token_expire', 'refresh_token_expire', 'last_activity'] as $name) {
                    $value = $raw[$name] ?? '';

                    if (!preg_match('/^(0|[1-9][0-9]{0,18})$/D', $value) || (string) (int) $value !== $value) {
                        throw new AuthSystemException('A legacy token timestamp is invalid.');
                    }

                    $record[$name] = (int) $value;
                }

                $pair = json_encode([$record['user_id'], $record['client_id']], JSON_THROW_ON_ERROR);

                if (isset($pairs[$pair])) {
                    throw new AuthSystemException('Legacy token storage contains duplicate sessions.');
                }

                $pairs[$pair] = true;
                $record['access_token_hash'] = hash('sha256', $record['access_token']);
                $record['refresh_token_hash'] = hash('sha256', $record['refresh_token']);
                $record['family_id'] = bin2hex(random_bytes(32));

                unset($record['access_token'], $record['refresh_token']);

                $this->assertUniqueTokens($record);

                $this->records[] = $record;
            }
        } catch (InvalidTokenException | MissingParametersException | \Random\RandomException $exception) {
            throw new AuthSystemException('Legacy token storage could not be migrated.', previous: $exception);
        }

        $this->saveRecords();
    } // migrateLegacyRecords

    /**
     * Writes and flushes a complete snapshot before atomically replacing the XML file.
     * The separate lock file remains stable across replacement. Incomplete temporary files are removed
     * without modifying the previous snapshot.
     *
     * @return void
     * Persists only hashes, identifiers, activity and expiration timestamps.
     *
     * @throws \LogicException
     * If called without an active lock.
     *
     * @throws AuthSystemException
     * If serialization, secure filename generation, file creation, complete writing, flushing or
     * atomic replacement fails.
     *
     * @author Oleg Schildt
     */
    protected function saveRecords(): void
    {
        $this->assertLocked();

        $document = new \DOMDocument('1.0', 'UTF-8');
        $document->formatOutput = true;
        $root = $document->createElement('OAuthTokens');

        $root->setAttribute('version', '2');

        $document->appendChild($root);

        foreach (['Session' => $this->records, 'Consumed' => $this->consumed] as $type => $records) {
            foreach ($records as $record) {
                $element = $document->createElement($type);

                foreach ($record as $name => $value) {
                    $element->setAttribute($name, (string) $value);
                }

                $root->appendChild($element);
            }
        }

        $xml = $document->saveXML();

        if ($xml === false || strlen($xml) > 16777216) {
            throw new AuthSystemException('Token storage could not be serialized within its size limit.');
        }

        try {
            $temporary = $this->storage_file . '.' . bin2hex(random_bytes(12)) . '.tmp';
        } catch (\Random\RandomException $exception) {
            throw new AuthSystemException('A temporary storage filename could not be generated.', previous: $exception);
        }

        $stream = @fopen($temporary, 'x+b');

        if ($stream === false) {
            throw new AuthSystemException('A temporary token snapshot could not be created.');
        }

        try {
            $offset = 0;
            $length = strlen($xml);

            while ($offset < $length) {
                $written = @fwrite($stream, substr($xml, $offset));

                if ($written === false || $written === 0) {
                    throw new AuthSystemException('The token snapshot could not be written completely.');
                }

                $offset += $written;
            }

            if (!@fflush($stream) || !@fsync($stream)) {
                throw new AuthSystemException('The token snapshot could not be flushed.');
            }

            fclose($stream);

            $stream = null;

            if (!@rename($temporary, $this->storage_file)) {
                throw new AuthSystemException('The token snapshot could not be replaced.');
            }
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }

            if (is_file($temporary)) {
                @unlink($temporary);
            }
        }
    } // saveRecords

    /**
     * Locates an unexpired refresh token or commits family revocation on detected reuse.
     *
     * @param string $token
     * The validated raw refresh token.
     *
     * @param string $user_id
     * The exact user binding.
     *
     * @param string $client_id
     * The exact client binding.
     *
     * @return int
     * Returns the matching active record index under the current lock.
     *
     * @throws InvalidTokenException
     * If the token is unknown or a retained consumed token is reused; reuse revocation is persisted
     * before throwing.
     *
     * @throws TokenExpiredException
     * If the current token has reached its expiration time.
     *
     * @throws AuthSystemException
     * If replay revocation cannot be persisted.
     *
     * @throws \LogicException
     * If called outside the storage lock.
     *
     * @author Oleg Schildt
     */
    protected function findRefreshToken(string $token, string $user_id, string $client_id): int
    {
        $this->assertLocked();

        $hash = hash('sha256', $token);
        $now = $this->getCurrentTime();

        foreach ($this->records as $index => $record) {
            if (
                $record['user_id'] === $user_id && $record['client_id'] === $client_id
                && hash_equals($record['refresh_token_hash'], $hash)
            ) {
                if ($now >= $record['refresh_token_expire']) {
                    throw new TokenExpiredException('The refresh token has expired.');
                }

                return $index;
            }
        }

        foreach ($this->consumed as $record) {
            if (
                $record['user_id'] === $user_id && $record['client_id'] === $client_id
                && hash_equals($record['refresh_token_hash'], $hash)
            ) {
                $this->records = array_values(array_filter($this->records, static fn(array $active): bool => $active['family_id'] !== $record['family_id']));

                // Persist the family revocation before reporting replay to the caller.
                $this->saveRecords();

                throw new InvalidTokenException('Refresh-token reuse was detected; its active family was revoked.');
            }
        }

        throw new InvalidTokenException('The refresh token is invalid.');
    } // findRefreshToken

    /**
     * Prevents duplicate credentials across active sessions and retained refresh history.
     *
     * @param array<string, int|string> $candidate
     * The normalized new token hashes.
     *
     * @param ?int $replaced_index
     * An existing session being replaced after fresh authentication, or null.
     *
     * @return void
     * Permits hashes that do not conflict with another session or consumed history.
     *
     * @throws InvalidTokenException
     * If a new access or refresh hash conflicts with retained credentials.
     *
     * @author Oleg Schildt
     */
    protected function assertUniqueTokens(array $candidate, ?int $replaced_index = null): void
    {
        foreach ($this->records as $index => $record) {
            if (
                $index !== $replaced_index && (hash_equals($record['access_token_hash'], $candidate['access_token_hash'])
                    || hash_equals($record['refresh_token_hash'], $candidate['refresh_token_hash']))
            ) {
                throw new InvalidTokenException('A new token collides with an existing session.');
            }
        }

        foreach ($this->consumed as $record) {
            if (hash_equals($record['refresh_token_hash'], $candidate['refresh_token_hash'])) {
                throw new InvalidTokenException('A consumed refresh token cannot be issued again.');
            }
        }
    } // assertUniqueTokens

    /**
     * Atomically replaces one user/client session with a newly authenticated family.
     *
     * @param array<string, mixed> &$token_record
     * Nonempty string user_id, client_id, access_token and refresh_token; integer access_token_expire,
     * refresh_token_expire and last_activity. Expirations must follow activity. This array is not
     * modified.
     *
     * @return void
     * Stores only hashes and permits exactly one active record per user/client pair.
     *
     * @throws MissingParametersException
     * If a required identifier or token is absent.
     *
     * @throws InvalidTokenException
     * If fields are malformed or token hashes conflict with retained credentials.
     *
     * @throws AuthSystemException
     * If locking, random generation, XML loading or atomic persistence fails.
     *
     * @author Oleg Schildt
     */
    public function saveTokenRecord(array &$token_record): void
    {
        $record = $this->normalizeRecord($token_record);

        $this->executeAtomically(function () use ($record): void {
            $index = null;

            foreach ($this->records as $position => $existing) {
                if ($existing['user_id'] === $record['user_id'] && $existing['client_id'] === $record['client_id']) {
                    $index = $position;

                    break;
                }
            }

            $this->assertUniqueTokens($record, $index);

            try {
                $record['family_id'] = bin2hex(random_bytes(32));
            } catch (\Random\RandomException $exception) {
                throw new AuthSystemException('A token family identifier could not be generated.', previous: $exception);
            }

            if ($index === null) {
                $this->records[] = $record;
            } else {
                $this->records[$index] = $record;
            }

            $this->saveRecords();
        });
    } // saveTokenRecord

    /**
     * Consumes and replaces a refresh token under one exclusive file lock.
     *
     * @param ?string $refresh_token
     * The current raw token to consume once.
     *
     * @param array<string, mixed> &$token_record
     * A new raw token record as accepted by saveTokenRecord(), bound to the same user/client pair.
     * This array is not modified.
     *
     * @return void
     * Commits the consumed hash and replacement together, preserving the family ID.
     *
     * @throws MissingParametersException
     * If a required token or identifier is absent.
     *
     * @throws InvalidTokenException
     * If fields are invalid, the token is unknown, new hashes collide, or reuse is detected; reuse
     * revokes its active family.
     *
     * @throws TokenExpiredException
     * If the current token is expired when checked under the lock.
     *
     * @throws AuthSystemException
     * If locking, loading or atomic persistence fails.
     *
     * @author Oleg Schildt
     */
    public function rotateTokenRecord(?string $refresh_token, array &$token_record): void
    {
        $token = $this->requireValue($refresh_token, 'refresh_token');
        $record = $this->normalizeRecord($token_record);

        $this->executeAtomically(function () use ($token, $record): void {
            $index = $this->findRefreshToken($token, $record['user_id'], $record['client_id']);
            $previous = $this->records[$index];

            $this->assertUniqueTokens($record);

            $this->consumed[] = [
                'user_id' => $previous['user_id'],
                'client_id' => $previous['client_id'],
                'family_id' => $previous['family_id'],
                'refresh_token_hash' => $previous['refresh_token_hash'],
                'refresh_token_expire' => $previous['refresh_token_expire'],
            ];
            $record['family_id'] = $previous['family_id'];
            $this->records[$index] = $record;

            $this->saveRecords();
        });
    } // rotateTokenRecord

    /**
     * Loads an exact token/user/client match without recovering other bearer credentials.
     *
     * @param array<string, mixed> &$token_record
     * Input: user_id, client_id and exactly one raw lookup token. Output: stored hashes, family ID,
     * timestamps and only the supplied raw lookup token. Remains unchanged on failure.
     *
     * @return void
     * Replaces the supplied array with the matching record without checking or extending its expiration.
     *
     * @throws MissingParametersException
     * If a required lookup value is absent.
     *
     * @throws InvalidTokenException
     * If the lookup shape is invalid or no matching token exists.
     *
     * @throws AuthSystemException
     * If storage cannot be locked, loaded or migrated.
     *
     * @author Oleg Schildt
     */
    public function loadTokenRecord(array &$token_record): void
    {
        $user_id = $this->requireValue($token_record['user_id'] ?? null, 'user_id');
        $client_id = $this->requireValue($token_record['client_id'] ?? null, 'client_id');
        $has_access = array_key_exists('access_token', $token_record);
        $has_refresh = array_key_exists('refresh_token', $token_record);

        if ($has_access === $has_refresh) {
            throw new InvalidTokenException('Supply exactly one access or refresh lookup token.');
        }

        $type = $has_access ? 'access_token' : 'refresh_token';
        $token = $this->requireValue($token_record[$type], $type);
        $record = $this->executeAtomically(function () use ($user_id, $client_id, $type, $token): array {
            $hash = hash('sha256', $token);

            foreach ($this->records as $record) {
                if (
                    $record['user_id'] === $user_id && $record['client_id'] === $client_id
                    && hash_equals($record[$type . '_hash'], $hash)
                ) {
                    return $record;
                }
            }

            throw new InvalidTokenException('The token is invalid.');
        });
        $record[$type] = $token;
        $token_record = $record;
    } // loadTokenRecord

    /**
     * Authorizes and revokes sessions in the same locked operation.
     *
     * @param ?string $refresh_token
     * The current authorizing refresh token.
     *
     * @param ?string $user_id
     * The user owning the token.
     *
     * @param ?string $client_id
     * The client bound to the token.
     *
     * @param bool $all_user_sessions
     * Whether to revoke all sessions of this user or only the exact user/client pair.
     *
     * @return void
     * Preserves every session belonging to another user.
     *
     * @throws MissingParametersException
     * If a required argument is absent.
     *
     * @throws InvalidTokenException
     * If arguments or the authorizing token are invalid or reused.
     *
     * @throws TokenExpiredException
     * If the authorizing refresh token is expired.
     *
     * @throws AuthSystemException
     * If locking, loading or persistence fails.
     *
     * @author Oleg Schildt
     */
    public function revokeTokenRecords(
        ?string $refresh_token,
        ?string $user_id,
        ?string $client_id,
        bool $all_user_sessions = false
    ): void {
        $token = $this->requireValue($refresh_token, 'refresh_token');
        $user_id = $this->requireValue($user_id, 'user_id');
        $client_id = $this->requireValue($client_id, 'client_id');

        $this->executeAtomically(function () use ($token, $user_id, $client_id, $all_user_sessions): void {
            $this->findRefreshToken($token, $user_id, $client_id);

            $this->records = array_values(array_filter(
                $this->records,
                static fn(array $record): bool => $record['user_id'] !== $user_id
                    || (!$all_user_sessions && $record['client_id'] !== $client_id)
            ));

            $this->saveRecords();
        });
    } // revokeTokenRecords

    /**
     * Revokes sessions by a validated administrative key or possessed token.
     *
     * @param string $key
     * One of user_id, client_id, access_token or refresh_token. Global client_id deletion requires
     * external administrative authorization.
     *
     * @param ?string $value
     * The exact nonempty identifier or raw token. Consumed refresh hashes also identify their family.
     *
     * @return void
     * Atomically removes matching active families; absent matches are ignored.
     *
     * @throws MissingParametersException
     * If the value is null or empty.
     *
     * @throws InvalidTokenException
     * If the key or value is invalid.
     *
     * @throws AuthSystemException
     * If locking, loading or persistence fails.
     *
     * @author Oleg Schildt
     */
    public function deleteTokenRecordByKey(string $key, ?string $value): void
    {
        if (!in_array($key, ['user_id', 'client_id', 'access_token', 'refresh_token'], true)) {
            throw new InvalidTokenException('The token deletion key is unsupported.');
        }

        $value = $this->requireValue($value, $key);

        $this->executeAtomically(function () use ($key, $value): void {
            $families = [];
            $is_token = $key === 'access_token' || $key === 'refresh_token';
            $expected = $is_token ? hash('sha256', $value) : $value;
            $field = $is_token ? $key . '_hash' : $key;

            foreach ($this->records as $record) {
                if ($is_token ? hash_equals($record[$field], $expected) : $record[$field] === $expected) {
                    $families[$record['family_id']] = true;
                }
            }

            if ($key === 'refresh_token') {
                foreach ($this->consumed as $record) {
                    if (hash_equals($record['refresh_token_hash'], $expected)) {
                        $families[$record['family_id']] = true;
                    }
                }
            }

            if ($families !== []) {
                $this->records = array_values(array_filter($this->records, static fn(array $record): bool => !isset($families[$record['family_id']])));

                $this->saveRecords();
            }
        });
    } // deleteTokenRecordByKey

    /**
     * Verifies access-token binding and expiration under the storage lock.
     *
     * @param ?string $access_token
     * The raw token to verify.
     *
     * @param ?string $user_id
     * The exact user binding.
     *
     * @param ?string $client_id
     * The exact client binding.
     *
     * @return void
     * Updates last activity without extending either expiration.
     *
     * @throws MissingParametersException
     * If a required lookup value is absent.
     *
     * @throws InvalidTokenException
     * If no access token matches the supplied token, user ID, and client ID.
     *
     * @throws TokenExpiredException
     * If the matching token has reached its expiration time.
     *
     * @throws AuthSystemException
     * If locking, loading or activity persistence fails.
     *
     * @author Oleg Schildt
     */
    public function verifyAccessToken(?string $access_token, ?string $user_id, ?string $client_id): void
    {
        $token = $this->requireValue($access_token, 'access_token');
        $user_id = $this->requireValue($user_id, 'user_id');
        $client_id = $this->requireValue($client_id, 'client_id');

        $this->executeAtomically(function () use ($token, $user_id, $client_id): void {
            $index = null;
            $hash = hash('sha256', $token);

            foreach ($this->records as $position => $record) {
                if (
                    $record['user_id'] === $user_id && $record['client_id'] === $client_id
                    && hash_equals($record['access_token_hash'], $hash)
                ) {
                    $index = $position;

                    break;
                }
            }

            if ($index === null) {
                throw new InvalidTokenException('The access token is invalid.');
            }

            if ($this->getCurrentTime() >= $this->records[$index]['access_token_expire']) {
                throw new TokenExpiredException('The access token has expired.');
            }

            $this->records[$index]['last_activity'] = max($this->records[$index]['last_activity'], $this->getCurrentTime());

            $this->saveRecords();
        });
    } // verifyAccessToken

    /**
     * Verifies refresh-token binding and expiration under the storage lock.
     *
     * @param ?string $refresh_token
     * The raw token to verify.
     *
     * @param ?string $user_id
     * The exact user binding.
     *
     * @param ?string $client_id
     * The exact client binding.
     *
     * @return void
     * Updates last activity without extending either expiration. Refresh-token reuse revokes its
     * matching family.
     *
     * @throws MissingParametersException
     * If a required lookup value is absent.
     *
     * @throws InvalidTokenException
     * If the binding is invalid or refresh-token reuse is detected.
     *
     * @throws TokenExpiredException
     * If the matching token has reached its expiration time.
     *
     * @throws AuthSystemException
     * If locking, loading or activity persistence fails.
     *
     * @author Oleg Schildt
     */
    public function verifyRefreshToken(?string $refresh_token, ?string $user_id, ?string $client_id): void
    {
        $token = $this->requireValue($refresh_token, 'refresh_token');
        $user_id = $this->requireValue($user_id, 'user_id');
        $client_id = $this->requireValue($client_id, 'client_id');

        $this->executeAtomically(function () use ($token, $user_id, $client_id): void {
            $index = $this->findRefreshToken($token, $user_id, $client_id);

            $this->records[$index]['last_activity'] = max($this->records[$index]['last_activity'], $this->getCurrentTime());

            $this->saveRecords();
        });
    } // verifyRefreshToken
} // DemoTokenStorage

