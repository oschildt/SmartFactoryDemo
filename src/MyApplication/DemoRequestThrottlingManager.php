<?php

namespace MyApplication;

use SmartFactory\AbstractRequestThrottlingManager;

/**
 * Demonstrates shared rolling-window throttling using a local XML history file.
 *
 * A separate, stable .lock file serializes admission across processes. Accepted updates
 * replace the XML file atomically after a complete write; rejected requests never extend
 * the history. Client keys are stored only as SHA-256 hashes. All users of the same file
 * should use the same limits. The base class supplies policy validation, history expiry,
 * caller hashing and rolling-window checks for one caller. This class supplies XML storage,
 * file-wide locking, merging of caller updates and expiry cleanup for inactive callers.
 *
 * @author Oleg Schildt
 */
class DemoRequestThrottlingManager extends AbstractRequestThrottlingManager
{
    /**
     * Stores the absolute path of the XML history file.
     *
     * @var string
     *
     * @author Oleg Schildt
     */
    protected string $throttling_file = "";

    /**
     * Stores the caller hash owning the current file-wide atomic operation.
     *
     * @var string|null
     *
     * @author Oleg Schildt
     */
    private ?string $active_key = null;

    /**
     * Caches the full XML snapshot only while the current admission operation holds the file lock.
     *
     * @var array<string, list<float>>|null
     *
     * @author Oleg Schildt
     */
    private ?array $loaded_records = null;

    /**
     * Configures the file and rolling-window policy without opening or clearing request history.
     *
     * @param array{throttling_file: string, limits?: list<array{0: positive-int, 1: positive-int}>} $parameters
     * A local XML file path whose parent directory exists. A missing file is created on the
     * first admitted request. Limits default to DEFAULT_LIMITS; an empty list disables limits.
     *
     * @return void
     * Applies valid settings together; invalid initialization leaves the previous settings intact.
     *
     * @throws \InvalidArgumentException
     * If the file path is empty, contains a zero byte, uses a stream wrapper, names a directory,
     * has no existing parent directory, or limits are not a list of positive integer pairs.
     *
     * @throws \LogicException
     * If initialization is attempted while this instance is processing an admission operation.
     *
     * @author Oleg Schildt
     */
    public function init(array $parameters): void
    {
        if ($this->active_key !== null) {
            throw new \LogicException("The throttling manager cannot be reinitialized during admission.");
        }

        $file = $parameters["throttling_file"] ?? null;

        if (
            !is_string($file)
            || trim($file) === ""
            || str_contains($file, "\0")
            || str_contains($file, "://")
            || in_array(basename($file), [".", ".."], true)
            || is_dir($file)
        ) {
            throw new \InvalidArgumentException("The throttling_file must be a local file path.");
        }

        $directory = realpath(dirname($file));

        if ($directory === false || !is_dir($directory)) {
            throw new \InvalidArgumentException("The throttling file's parent directory must exist.");
        }

        parent::init($parameters);

        $this->throttling_file = $directory . DIRECTORY_SEPARATOR . basename($file);
    } // init

    /**
     * Protects one caller's admission operation with a stable, exclusive lock on the shared file.
     *
     * The file-wide lock is required even for different caller keys because updates replace one XML file.
     *
     * @param string $key
     * The lowercase SHA-256 caller hash supplied by the common admission algorithm.
     *
     * @param callable(): int $operation
     * The complete clock/read/check/write operation for this caller.
     *
     * @return int
     * Returns the admission result after releasing the file lock and discarding the cached snapshot.
     *
     * @throws \LogicException
     * If the same instance attempts a nested admission operation.
     *
     * @throws \RuntimeException
     * If the lock file cannot be opened or the exclusive lock cannot be acquired.
     *
     * @throws \Throwable
     * If the admission operation fails; the cached snapshot, lock and file handle are released before propagation.
     *
     * @author Oleg Schildt
     */
    protected function executeAtomically(string $key, callable $operation): int
    {
        if ($this->active_key !== null) {
            throw new \LogicException("Nested throttling admission operations are not supported.");
        }

        $lock = @fopen($this->throttling_file . ".lock", "c+b");

        if ($lock === false) {
            throw new \RuntimeException("The throttling lock file could not be opened.");
        }

        try {
            if (!flock($lock, LOCK_EX)) {
                throw new \RuntimeException("The throttling history could not be locked.");
            }

            $this->active_key = $key;
            $this->loaded_records = null;

            return $operation();
        } finally {
            $this->loaded_records = null;
            $this->active_key = null;

            flock($lock, LOCK_UN);

            fclose($lock);
        }
    } // executeAtomically

    /**
     * Verifies that storage access belongs to the caller owning this instance's current file lock.
     *
     * @param string $key
     * The caller hash being read or updated.
     *
     * @return void
     * Permits access only for the active atomic operation.
     *
     * @throws \LogicException
     * If no admission operation is active or the requested key differs from its caller key.
     *
     * @author Oleg Schildt
     */
    private function assertActiveOperation(string $key): void
    {
        if ($this->active_key !== $key) {
            throw new \LogicException("Throttling storage access requires the active caller's atomic operation.");
        }
    } // assertActiveOperation

    /**
     * Selects one caller's history from a validated XML snapshot read under the file lock.
     *
     * @param string $key
     * The lowercase SHA-256 caller hash supplied to executeAtomically().
     *
     * @return list<float>
     * Returns only this caller's ordered timestamps, or an empty list when the caller has no history.
     * The full file remains an internal storage detail and is cached until this atomic operation ends.
     *
     * @throws \LogicException
     * If access does not belong to the active caller's admission operation.
     *
     * @throws \RuntimeException
     * If the full XML file cannot be read or validated.
     *
     * @author Oleg Schildt
     */
    protected function loadRecords(string $key): array
    {
        $this->assertActiveOperation($key);

        $this->loaded_records ??= $this->readAllRecords();

        return $this->loaded_records[$key] ?? [];
    } // loadRecords

    /**
     * Merges one caller's updated history into the locked XML snapshot before replacing the file.
     *
     * The storage performs its own expiry sweep at write time using the same clock source and
     * retention helper as admission. Other callers' active history is preserved.
     *
     * @param string $key
     * The lowercase SHA-256 caller hash supplied to executeAtomically().
     *
     * @param list<float> $timestamps
     * This caller's updated, chronologically ordered timestamps prepared by the base class.
     *
     * @return void
     * Persists the merged snapshot atomically and retains it only until the current lock is released.
     *
     * @throws \LogicException
     * If the key is not active or history has not been loaded inside the current operation.
     *
     * @throws \RuntimeException
     * If the cleanup clock is invalid or the merged XML snapshot cannot be persisted completely.
     *
     * @throws \Throwable
     * If the overridden clock raises another exception or error during storage cleanup.
     *
     * @author Oleg Schildt
     */
    protected function saveRecords(string $key, array $timestamps): void
    {
        $this->assertActiveOperation($key);

        if ($this->loaded_records === null) {
            throw new \LogicException("Throttling history must be loaded before it can be saved.");
        }

        $records = $this->loaded_records;
        $records[$key] = $timestamps;
        $now = round($this->getCurrentTime(), 6);

        if (!is_finite($now) || $now < 0) {
            throw new \RuntimeException("The throttling cleanup clock returned an invalid timestamp.");
        }

        // Prune all histories so callers that stop sending requests do not accumulate in the file.
        foreach ($records as $client => $history) {
            $active = $this->pruneRecords($history, $now);

            if ($active === []) {
                unset($records[$client]);
            } else {
                $records[$client] = $active;
            }
        }

        $this->writeAllRecords($records);

        $this->loaded_records = $records;
    } // saveRecords

    /**
     * Reads and validates shared history while the caller holds the exclusive admission lock.
     *
     * @return array<string, list<float>>
     * Returns ordered timestamps indexed by lowercase SHA-256 caller hashes.
     * A missing file represents empty history; an empty or malformed existing file is rejected.
     *
     * @throws \RuntimeException
     * If the file cannot be read, its XML is malformed, its schema is unsupported, or keys
     * or timestamps are invalid, duplicated or out of order.
     *
     * @author Oleg Schildt
     */
    private function readAllRecords(): array
    {
        if (!file_exists($this->throttling_file)) {
            return [];
        }

        $xml = @file_get_contents($this->throttling_file);

        if ($xml === false || $xml === "") {
            throw new \RuntimeException("The throttling history could not be read or is empty.");
        }

        $previous_mode = libxml_use_internal_errors(true);

        libxml_clear_errors();

        try {
            $document = new \DOMDocument();

            if (!$document->loadXML($xml, LIBXML_NONET) || $document->doctype !== null) {
                throw new \RuntimeException("The throttling history contains invalid XML.");
            }
        } finally {
            libxml_clear_errors();

            libxml_use_internal_errors($previous_mode);
        }

        $root = $document->documentElement;

        if ($root === null || $root->nodeName !== "Throttling" || $root->getAttribute("version") !== "1") {
            throw new \RuntimeException("The throttling history has an unsupported schema.");
        }

        $records = [];

        foreach ($root->childNodes as $client) {
            if (!$client instanceof \DOMElement) {
                if ($client->nodeType !== XML_COMMENT_NODE && trim($client->textContent) !== "") {
                    throw new \RuntimeException("The throttling history contains unexpected text.");
                }

                continue;
            }

            $key = $client->getAttribute("key");

            if ($client->nodeName !== "Client" || !preg_match('/^[a-f0-9]{64}$/D', $key) || array_key_exists($key, $records)) {
                throw new \RuntimeException("The throttling history contains an invalid or duplicate caller key.");
            }

            $timestamps = [];
            $previous = -1.0;

            foreach ($client->childNodes as $request) {
                if (!$request instanceof \DOMElement) {
                    if ($request->nodeType !== XML_COMMENT_NODE && trim($request->textContent) !== "") {
                        throw new \RuntimeException("The throttling history contains unexpected caller text.");
                    }

                    continue;
                }

                $value = $request->getAttribute("time");

                if ($request->nodeName !== "Request" || !preg_match('/^[0-9]+(?:\.[0-9]{1,6})?$/D', $value)) {
                    throw new \RuntimeException("The throttling history contains an invalid timestamp entry.");
                }

                $timestamp = (float) $value;

                if (!is_finite($timestamp) || $timestamp < $previous) {
                    throw new \RuntimeException("The throttling history timestamps are invalid or out of order.");
                }

                $timestamps[] = $timestamp;
                $previous = $timestamp;
            }

            $records[$key] = $timestamps;
        }

        return $records;
    } // readAllRecords

    /**
     * Replaces shared XML history only after writing and flushing the complete new document.
     *
     * A separate lock file remains stable across rename(), so concurrent callers cannot lock
     * different versions of the XML file. Temporary files are created in the same directory.
     *
     * @param array<string, list<float>> $records
     * Ordered timestamps indexed by caller hashes, already validated by the admission operation.
     *
     * @return void
     * Atomically replaces the history and removes any unfinished temporary file.
     *
     * @throws \RuntimeException
     * If serialization, temporary-name generation, file creation, writing, flushing or replacement fails.
     * The previous history remains intact when replacement does not complete.
     *
     * @author Oleg Schildt
     */
    private function writeAllRecords(array $records): void
    {
        $document = new \DOMDocument("1.0", "UTF-8");
        $document->formatOutput = true;
        $root = $document->createElement("Throttling");

        $root->setAttribute("version", "1");

        $document->appendChild($root);

        foreach ($records as $key => $timestamps) {
            $client = $document->createElement("Client");

            $client->setAttribute("key", $key);

            $root->appendChild($client);

            foreach ($timestamps as $timestamp) {
                $request = $document->createElement("Request");

                $request->setAttribute("time", sprintf("%.6F", $timestamp));

                $client->appendChild($request);
            }
        }

        $xml = $document->saveXML();

        if ($xml === false) {
            throw new \RuntimeException("The throttling history could not be serialized.");
        }

        try {
            $temporary_file = $this->throttling_file . "." . bin2hex(random_bytes(12)) . ".tmp";
        } catch (\Random\RandomException $exception) {
            throw new \RuntimeException("A temporary throttling history name could not be generated.", 0, $exception);
        }

        $stream = @fopen($temporary_file, "x+b");

        if ($stream === false) {
            throw new \RuntimeException("A temporary throttling history file could not be created.");
        }

        try {
            $offset = 0;
            $length = strlen($xml);

            while ($offset < $length) {
                $written = @fwrite($stream, substr($xml, $offset));

                if ($written === false || $written === 0) {
                    throw new \RuntimeException("The throttling history could not be written completely.");
                }

                $offset += $written;
            }

            if (!@fflush($stream) || !@fsync($stream)) {
                throw new \RuntimeException("The throttling history could not be flushed.");
            }

            fclose($stream);

            $stream = null;

            if (!@rename($temporary_file, $this->throttling_file)) {
                throw new \RuntimeException("The throttling history could not be replaced.");
            }
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }

            if (is_file($temporary_file)) {
                @unlink($temporary_file);
            }
        }
    } // writeAllRecords
} // DemoRequestThrottlingManager

