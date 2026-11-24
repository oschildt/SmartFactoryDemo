<?php

/**
 * Identifies the editor field and tab that failed catalog validation.
 *
 * @author Oleg Schildt
 */
class LocalizationValidationException extends \InvalidArgumentException
{
    /**
     * Creates a validation error associated with an editor control.
     *
     * @param string $message
     * The validation failure to display as escaped text.
     *
     * @param string $field
     * The form control name to focus, or an empty string for a general error.
     *
     * @param string $tab
     * The tab containing the control.
     *
     * @author Oleg Schildt
     */
    public function __construct(
        string $message,
        public readonly string $field = "",
        public readonly string $tab = "texts"
    ) {
        parent::__construct($message);
    } // __construct
} // LocalizationValidationException

/**
 * Reads a JSON document and captures the hash of exactly the bytes decoded.
 *
 * @param string $file
 * The JSON file path.
 *
 * @param array<string, string|null> $hashes
 * File fingerprints updated by reference; null denotes a missing optional file.
 *
 * @param bool $optional
 * Whether a missing file should produce an empty document.
 *
 * @return array<array-key, mixed>
 * Returns the root as an array, including an empty array for {} or [].
 * Nested JSON objects retain their object type so unrelated configuration is preserved.
 *
 * @throws \RuntimeException
 * If the required file cannot be read or the document is not an object or array.
 *
 * @throws \JsonException
 * If the file does not contain valid UTF-8 JSON within the decoding depth limit.
 *
 * @author Oleg Schildt
 */
function localization_read_json(string $file, array &$hashes, bool $optional = false): array
{
    if ($optional && !file_exists($file)) {
        $hashes[$file] = null;

        return [];
    }

    $contents = @file_get_contents($file);

    if ($contents === false) {
        throw new \RuntimeException("Translation file '$file' cannot be loaded.");
    }

    try {
        $data = json_decode($contents, false, 512, JSON_THROW_ON_ERROR);
    } catch (\JsonException $exception) {
        throw new \JsonException(
            "Translation file '$file': " . $exception->getMessage(),
            $exception->getCode(),
            $exception
        );
    }

    if (!is_array($data) && !($data instanceof \stdClass)) {
        throw new \RuntimeException("Translation file '$file' must contain a JSON object.");
    }

    $hashes[$file] = hash("sha256", $contents);

    return (array) $data;
} // localization_read_json

/**
 * Validates a catalog's translation maps and normalizes legacy scalar values.
 *
 * @param array<array-key, mixed> $entries
 * Decoded entries indexed by identifier and language code.
 *
 * @param string $file
 * The source path included in validation errors.
 *
 * @return array<array-key, array<string, string>>
 * Returns translations as strings; legacy numbers are stringified and null becomes empty.
 *
 * @throws \RuntimeException
 * If an entry is not a language map or a translation is not a string, number, or null.
 *
 * @author Oleg Schildt
 */
function localization_validate_catalog(array $entries, string $file): array
{
    $catalog = [];

    foreach ($entries as $code => $translations) {
        if ($translations instanceof \stdClass) {
            $translations = (array) $translations;
        }

        if (!is_array($translations)) {
            throw new \RuntimeException("Entry '$code' in '$file' must contain a language map.");
        }

        $catalog[$code] = [];

        foreach ($translations as $language => $value) {
            if ($value !== null && !is_string($value) && !is_int($value) && !is_float($value)) {
                throw new \RuntimeException("Translation '$code/$language' in '$file' must be text.");
            }

            $catalog[$code][(string) $language] = (string) ($value ?? "");
        }
    }

    return $catalog;
} // localization_validate_catalog

/**
 * Finds missing or empty translations without treating the string "0" as empty.
 *
 * @param array<array-key, array<string, string>> $entries
 * The catalog to inspect.
 *
 * @param list<string> $languages
 * Language codes required for every entry.
 *
 * @return array<array-key, list<string>>
 * Returns missing language codes grouped by entry identifier.
 *
 * @author Oleg Schildt
 */
function localization_missing_translations(array $entries, array $languages): array
{
    $missing = [];

    foreach ($entries as $code => $translations) {
        foreach ($languages as $language) {
            if (($translations[$language] ?? "") === "") {
                $missing[$code][] = $language;
            }
        }
    }

    return $missing;
} // localization_missing_translations

/**
 * Reads a submitted string while rejecting array injection and invalid UTF-8.
 *
 * @param mixed $value
 * The submitted value, with null representing an omitted optional field.
 *
 * @param string $field
 * The form control name used in validation errors.
 *
 * @param string $tab
 * The tab containing the control.
 *
 * @return string
 * Returns the original string or an empty string for null.
 *
 * @throws LocalizationValidationException
 * If the value is neither a string nor null, or is not valid UTF-8.
 *
 * @author Oleg Schildt
 */
function localization_request_string(mixed $value, string $field, string $tab): string
{
    if ($value === null) {
        return "";
    }

    if (!is_string($value) || !mb_check_encoding($value, "UTF-8")) {
        throw new LocalizationValidationException("The field must contain valid UTF-8 text.", $field, $tab);
    }

    return $value;
} // localization_request_string

/**
 * Validates the nested form structure before submitted values reach the template.
 *
 * @param array<array-key, mixed> $request
 * POST fields, including row maps, language maps, and deletion lists.
 *
 * @return void
 * Returns normally when all field names and scalar values can be rendered safely.
 *
 * @throws LocalizationValidationException
 * If a nested value has an invalid shape, has excessive nesting, or contains invalid UTF-8 text.
 *
 * @author Oleg Schildt
 */
function localization_validate_request(array $request): void
{
    $pending = [[$request, 0]];

    while ($pending !== []) {
        [$values, $depth] = array_pop($pending);

        if ($depth > 8) {
            throw new LocalizationValidationException("The submitted form is nested too deeply.");
        }

        foreach ($values as $key => $value) {
            localization_request_string((string) $key, "", "texts");

            if (is_array($value)) {
                $pending[] = [$value, $depth + 1];
            } else {
                localization_request_string($value, "", "texts");
            }
        }
    }

    foreach (["act", "tab", "csrf_token", "catalog_version"] as $field) {
        localization_request_string($request[$field] ?? null, $field, "texts");
    }

    foreach ($request as $field => $value) {
        if (!in_array($field, ["act", "tab", "csrf_token", "catalog_version"], true) && !is_array($value)) {
            throw new LocalizationValidationException("The submitted form has an invalid field structure.");
        }
    }

    $rows = [];

    foreach (["language", "country"] as $kind) {
        $new_entry = $request["new_" . $kind . "_entry"] ?? [];
        $rows[] = $new_entry;

        foreach ($request[$kind . "_entries"] ?? [] as $row) {
            $rows[] = $row;
        }

        localization_deletion_list($request["delete_" . $kind . "_entries"] ?? null);
    }

    foreach (array_merge(
        array_values($request["text_entries"] ?? []),
        array_values($request["override_text_entries"] ?? [])
    ) as $file_rows) {
        if (!is_array($file_rows)) {
            throw new LocalizationValidationException("The submitted text rows must be grouped by file.");
        }

        foreach ($file_rows as $row) {
            $rows[] = $row;
        }
    }

    foreach ($request["new_text_entry"] ?? [] as $row) {
        $rows[] = $row;
    }

    foreach ($rows as $row) {
        if (!is_array($row) || (isset($row["langs"]) && !is_array($row["langs"]))) {
            throw new LocalizationValidationException("The submitted catalog row has an invalid structure.");
        }

        localization_request_string($row["code"] ?? null, "", "texts");

        localization_request_string($row["enabled"] ?? null, "", "texts");

        localization_request_string($row["override"] ?? null, "", "texts");

        foreach ($row["langs"] ?? [] as $translation) {
            localization_request_string($translation, "", "texts");
        }
    }

    foreach ($request["new_language"] ?? [] as $value) {
        localization_request_string($value, "", "configuration");
    }

    foreach (["delete_text_entries", "move_to_master_text_entries"] as $field) {
        foreach ($request[$field] ?? [] as $selection) {
            localization_deletion_list($selection);
        }
    }

    localization_deletion_list($request["delete_supported_languages"] ?? null);
} // localization_validate_request

/**
 * Validates a list of original identifiers selected for deletion.
 *
 * @param mixed $values
 * A submitted list of string identifiers, or null if no entries are selected.
 *
 * @return list<string>
 * Returns identifiers without changing their contents or comparing numeric strings loosely.
 *
 * @throws LocalizationValidationException
 * If the submitted selection is not an array of valid UTF-8 strings.
 *
 * @author Oleg Schildt
 */
function localization_deletion_list(mixed $values): array
{
    if ($values === null) {
        return [];
    }

    if (!is_array($values)) {
        throw new LocalizationValidationException("The deletion selection must be a list.");
    }

    $selected = [];

    foreach ($values as $value) {
        $selected[] = localization_request_string($value, "", "texts");
    }

    return $selected;
} // localization_deletion_list

/**
 * Applies the same edit, rename, deletion, and language rules to any catalog.
 *
 * @param array<array-key, array<string, string>> $entries
 * Loaded entries in their displayed row order.
 *
 * @param array<array-key, mixed> $submitted
 * Submitted row overrides indexed by the displayed row number.
 *
 * @param list<string> $deleted
 * Original identifiers selected for deletion; deletion takes precedence over renaming.
 *
 * @param array<array-key, mixed> $new_entry
 * Optional new entry with code and langs fields.
 *
 * @param array{languages: list<string>, new_language: string, copy_source: string,
 *     field: string, new_field: string, tab: string} $options
 * Remaining languages, optional language-copy settings, and validation control names.
 *
 * @return array<array-key, array<string, string>>
 * Returns the complete updated catalog, including an empty array when all entries are deleted.
 *
 * @throws LocalizationValidationException
 * If rows or language maps have invalid shapes, codes are empty or duplicated,
 * or submitted translations are not valid UTF-8 strings.
 *
 * @author Oleg Schildt
 */
function localization_prepare_catalog(
    array $entries,
    array $submitted,
    array $deleted,
    array $new_entry,
    array $options
): array {
    $result = [];
    $rows = [];
    $counter = 0;

    foreach ($entries as $original_code => $translations) {
        $field = $options["field"] . "[$counter]";
        $row = $submitted[$counter] ?? [];
        $counter++;

        // Check the original identifier so renaming a selected row cannot cancel deletion.
        if (in_array((string) $original_code, $deleted, true)) {
            continue;
        }

        if (!is_array($row)) {
            throw new LocalizationValidationException("The submitted row must be an object.", $field, $options["tab"]);
        }

        $code = localization_request_string(
            $row["code"] ?? (string) $original_code,
            "{$field}[code]",
            $options["tab"]
        );
        $rows[] = [$code, $translations, $row, $field];
    }

    $new_code = localization_request_string(
        $new_entry["code"] ?? null,
        $options["new_field"] . "[code]",
        $options["tab"]
    );

    if ($new_code !== "") {
        $rows[] = [$new_code, [], $new_entry, $options["new_field"]];
    }

    foreach ($rows as [$code, $translations, $row, $field]) {
        if ($code === "") {
            throw new LocalizationValidationException("The code cannot be empty.", "{$field}[code]", $options["tab"]);
        }

        if (array_key_exists($code, $result)) {
            throw new LocalizationValidationException(
                "The code '$code' is used more than once.",
                "{$field}[code]",
                $options["tab"]
            );
        }

        $overrides = $row["langs"] ?? [];

        if (!is_array($overrides)) {
            throw new LocalizationValidationException("Translations must be a language map.", $field, $options["tab"]);
        }

        // Merge overrides before filtering languages so a removed language can still be a copy source.
        foreach ($overrides as $language => $value) {
            $translations[$language] = localization_request_string(
                $value,
                "{$field}[langs][$language]",
                $options["tab"]
            );
        }

        $result[$code] = [];

        foreach ($options["languages"] as $language) {
            $result[$code][$language] = $translations[$language] ?? "";
        }

        if ($options["new_language"] !== "") {
            $result[$code][$options["new_language"]] = $options["copy_source"] === ""
                ? ""
                : ($translations[$options["copy_source"]] ?? "");
        }
    }

    return $result;
} // localization_prepare_catalog

/**
 * Prepares master translations and optional user overrides in the displayed row order.
 *
 * @param string $file
 * The master catalog path used in submitted field names.
 *
 * @param array<array-key, array<string, string>> $master
 * Original master entries; user-only entries are appended when building the row order.
 *
 * @param array<array-key, array<string, string>> $user
 * Original user entries, including empty override maps.
 *
 * @param array<array-key, mixed> $request
 * Validated editor fields, deletion selections, override switches, and move selections.
 *
 * @param array{languages: list<string>, new_language: string, copy_source: string} $options
 * Remaining languages and optional language-copy settings.
 *
 * @return array{master: array<array-key, array<string, string>>, user: array<array-key, array<string, string>>}
 * Returns separate catalogs; deletion wins over moving, and unchecked overrides are omitted.
 *
 * @throws LocalizationValidationException
 * If an identifier is empty or duplicated, or a submitted row or translation is invalid.
 *
 * @author Oleg Schildt
 */
function localization_prepare_text_catalogs(
    string $file,
    array $master,
    array $user,
    array $request,
    array $options
): array {
    $entries = $master + $user;
    $submitted = $request["text_entries"][$file] ?? [];
    $overrides = $request["override_text_entries"][$file] ?? [];
    $deleted = localization_deletion_list($request["delete_text_entries"][$file] ?? null);
    $moved = localization_deletion_list($request["move_to_master_text_entries"][$file] ?? null);
    $new_entry = $request["new_text_entry"][$file] ?? [];
    $user_source = [];
    $user_rows = [];
    $counter = 0;

    foreach ($entries as $code => $translations) {
        $user_source[$code] = $user[$code] ?? [];
        $user_rows[$counter] = $overrides[$counter] ?? [];
        $user_rows[$counter]["code"] = $submitted[$counter]["code"] ?? (string) $code;
        $counter++;
    }

    // Validate identifiers across both destinations before deciding which file owns each row.
    $prepared_master = localization_prepare_catalog(
        $entries,
        $submitted,
        $deleted,
        $new_entry,
        $options + ["field" => "text_entries[$file]", "new_field" => "new_text_entry[$file]", "tab" => "texts"]
    );
    $prepared_user = localization_prepare_catalog(
        $user_source,
        $user_rows,
        $deleted,
        $new_entry,
        $options + ["field" => "override_text_entries[$file]", "new_field" => "new_text_entry[$file]", "tab" => "texts"]
    );
    $result = ["master" => [], "user" => []];
    $counter = 0;

    foreach ($entries as $original_code => $translations) {
        $row = $counter++;

        if (in_array((string) $original_code, $deleted, true)) {
            continue;
        }

        $code = $submitted[$row]["code"] ?? (string) $original_code;

        if (array_key_exists($original_code, $master)) {
            $result["master"][$code] = $prepared_master[$code];

            if (isset($overrides[$row]["enabled"])) {
                $result["user"][$code] = $prepared_user[$code];
            }
        } elseif (in_array((string) $original_code, $moved, true)) {
            $result["master"][$code] = $prepared_user[$code];
        } else {
            $result["user"][$code] = $prepared_user[$code];
        }
    }

    if (($new_entry["code"] ?? "") !== "") {
        $destination = isset($new_entry["override"]) ? "user" : "master";
        $result[$destination][$new_entry["code"]] = $prepared_master[$new_entry["code"]];
    }

    return $result;
} // localization_prepare_text_catalogs

/**
 * Produces a stable version token for every loaded or expected catalog file.
 *
 * @param array<string, string|null> $hashes
 * SHA-256 fingerprints indexed by file path, with null for absent optional files.
 *
 * @return string
 * Returns a SHA-256 token independent of the order in which files were loaded.
 *
 * @author Oleg Schildt
 */
function localization_catalog_version(array $hashes): string
{
    ksort($hashes);

    return hash("sha256", serialize($hashes));
} // localization_catalog_version

/**
 * Checks the request method and the session-bound token before processing a form action.
 *
 * @param string $method
 * The HTTP request method.
 *
 * @param mixed $submitted_token
 * The CSRF token submitted in the form.
 *
 * @param string $session_token
 * The expected token stored in the current session.
 *
 * @return bool
 * Returns true only for POST requests carrying the nonempty session token.
 *
 * @author Oleg Schildt
 */
function localization_valid_form_token(string $method, mixed $submitted_token, string $session_token): bool
{
    return $method === "POST"
        && $session_token !== ""
        && is_string($submitted_token)
        && hash_equals($session_token, $submitted_token);
} // localization_valid_form_token

/**
 * Stages a complete file beside its destination without changing the destination.
 *
 * @param string $file
 * The destination path whose directory must already exist.
 *
 * @param string $contents
 * The exact bytes to stage.
 *
 * @return string
 * Returns the temporary path; the caller must rename or remove it.
 *
 * @throws \RuntimeException
 * If a temporary file cannot be created, fully written, or assigned the destination's permissions.
 *
 * @author Oleg Schildt
 */
function localization_stage_file(string $file, string $contents): string
{
    $directory = dirname($file);

    if (!is_dir($directory)) {
        throw new \RuntimeException("Directory '$directory' does not exist.");
    }

    $temporary = @tempnam($directory, ".localization-");

    if ($temporary === false) {
        throw new \RuntimeException("Cannot create a temporary file for '$file'.");
    }

    try {
        if (realpath(dirname($temporary)) !== realpath($directory)) {
            throw new \RuntimeException("The temporary file for '$file' must be created beside its destination.");
        }

        if (@file_put_contents($temporary, $contents, LOCK_EX) !== strlen($contents)) {
            throw new \RuntimeException("Translation file '$file' cannot be staged completely.");
        }

        if (is_file($file) && !@chmod($temporary, fileperms($file) & 0777)) {
            throw new \RuntimeException("Cannot preserve permissions for '$file'.");
        }

        return $temporary;
    } catch (\Throwable $exception) {
        @unlink($temporary);

        throw $exception;
    }
} // localization_stage_file

/**
 * Encodes and stages all catalogs before replacing any destination files.
 *
 * Serializes editor writers with an exclusive lock, checks for intervening file
 * changes, and restores replaced files if a later replacement fails. Each rename
 * is atomic; a process crash between renames is not a multi-file transaction.
 *
 * @param array<string, array<array-key, mixed>> $documents
 * Complete documents indexed by destination path; root maps are encoded as objects.
 *
 * @param array<string, string|null> $hashes
 * Fingerprints captured when loading all relevant files, including unchanged ones.
 *
 * @param string $lock_file
 * A persistent lock file shared by every editor request for these catalogs.
 *
 * @return void
 * Returns after all destination files have been replaced successfully.
 *
 * @throws \JsonException
 * If any document cannot be encoded as valid UTF-8 JSON; no destination is changed.
 *
 * @throws \RuntimeException
 * If locking, version checking, staging, replacement, or rollback fails.
 * A rollback failure lists the affected paths and requires manual recovery.
 *
 * @author Oleg Schildt
 */
function localization_write_catalogs(array $documents, array $hashes, string $lock_file): void
{
    $encoded = [];

    // Encode the entire batch first; encoding failures must never truncate existing files.
    foreach ($documents as $file => $data) {
        if (!array_key_exists($file, $hashes)) {
            throw new \RuntimeException("Translation file '$file' was not loaded by the editor.");
        }

        $encoded[$file] = json_encode(
            (object) $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR
        ) . PHP_EOL;
    }

    $lock = @fopen($lock_file, "c+b");

    if ($lock === false) {
        throw new \RuntimeException("The translation editor lock cannot be opened.");
    }

    $staged = [];
    $backups = [];
    $replaced = [];
    $recovery_files = [];

    try {
        if (!flock($lock, LOCK_EX)) {
            throw new \RuntimeException("The translation editor lock cannot be acquired.");
        }

        $originals = [];

        foreach ($hashes as $file => $expected_hash) {
            $contents = is_file($file) ? @file_get_contents($file) : null;

            if ($contents === false || (file_exists($file) && $contents === null)) {
                throw new \RuntimeException("Translation file '$file' cannot be checked for changes.");
            }

            $actual_hash = $contents === null ? null : hash("sha256", $contents);

            if ($actual_hash !== $expected_hash) {
                throw new \RuntimeException("Translation file '$file' has changed. Reload the editor before saving.");
            }

            $originals[$file] = $contents;
        }

        // Keep complete rollback files on the same filesystem as their destinations.
        foreach ($encoded as $file => $contents) {
            $staged[$file] = localization_stage_file($file, $contents);
            $backups[$file] = $originals[$file] === null
                ? null
                : localization_stage_file($file, $originals[$file]);
        }

        foreach ($staged as $file => $temporary) {
            if (!@rename($temporary, $file)) {
                throw new \RuntimeException("Translation file '$file' cannot be replaced.");
            }

            $replaced[] = $file;
        }
    } catch (\Throwable $exception) {
        foreach (array_reverse($replaced) as $file) {
            $restored = $backups[$file] === null ? @unlink($file) : @rename($backups[$file], $file);

            if (!$restored) {
                $recovery_files[] = $backups[$file] ?? $file;
            }
        }

        if ($recovery_files !== []) {
            throw new \RuntimeException(
                $exception->getMessage() . " Recovery files: " . implode(", ", $recovery_files),
                0,
                $exception
            );
        }

        throw $exception;
    } finally {
        foreach (array_merge(array_values($staged), array_values($backups)) as $temporary) {
            if ($temporary !== null && !in_array($temporary, $recovery_files, true) && is_file($temporary)) {
                @unlink($temporary);
            }
        }

        flock($lock, LOCK_UN);

        fclose($lock);
    }
} // localization_write_catalogs

/**
 * Formats an escaped error message and an optional link to its form control.
 *
 * @param string $message
 * Plain error text; HTML in this value is displayed literally.
 *
 * @param string $field
 * The form control name, or an empty string to omit the navigation link.
 *
 * @param string $tab
 * The tab containing the control.
 *
 * @return string
 * Returns an HTML paragraph whose dynamic values are escaped for HTML attributes and text.
 *
 * @author Oleg Schildt
 */
function localization_error_html(string $message, string $field = "", string $tab = "texts"): string
{
    $html = '<p class="editor_error">' . htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");

    if ($field !== "") {
        $html .= ' [<a href="#" class="goto" data-error-tab="'
            . htmlspecialchars($tab, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8")
            . '" data-error-field="' . htmlspecialchars($field, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8")
            . '">go to</a>]';
    }

    return $html . '</p>';
} // localization_error_html

/**
 * Loads a directory's text and privilege catalogs into the shared editor indexes.
 *
 * Records duplicate identifiers and file fingerprints. Missing text catalogs are
 * displayed as empty, and privilege catalogs are loaded independently of texts.json.
 * User overrides share their master's table. Codes absent from both master catalogs
 * are appended to the texts.json table; both tables share the directory's user_texts.json.
 *
 * @param array<string, array<array-key, array<string, string>>> $text_entries
 * Catalogs indexed by source path, updated by reference.
 *
 * @param string $dir
 * The application or module directory path, including its trailing slash.
 *
 * @return bool
 * Returns true after collecting available catalogs, or false after reporting a read or schema error.
 *
 * @author Oleg Schildt
 */
function collect_directory_texts(array &$text_entries, string $dir): bool
{
    global $messages, $flat_text_entries, $entry_files, $duplicate_definitions, $catalog_hashes;
    global $master_text_entries, $user_text_entries;

    try {
        $catalogs = [];

        foreach (["texts.json", "privs.json"] as $name) {
            $file = $dir . "localization/" . $name;
            $catalogs[$name] = localization_validate_catalog(localization_read_json($file, $catalog_hashes, true), $file);
        }

        $user_file = $dir . "localization/user_texts.json";
        $user_entries = localization_validate_catalog(
            localization_read_json($user_file, $catalog_hashes, true),
            $user_file
        );

        foreach ($catalogs as $name => $entries) {
            $file = $dir . "localization/" . $name;

            if ($name === "privs.json" && $catalog_hashes[$file] === null) {
                continue;
            }

            $master_text_entries[$file] = $entries;
            // Each user code belongs to one table so shared user files are not saved twice independently.
            $user_text_entries[$file] = $name === "privs.json"
                ? array_intersect_key($user_entries, $entries)
                : array_diff_key($user_entries, $catalogs["privs.json"]);
            $text_entries[$file] = $entries + $user_text_entries[$file];

            foreach ($text_entries[$file] as $code => $translations) {
                if (isset($entry_files[$code])) {
                    $duplicate_definitions[$code][$entry_files[$code]] = $entry_files[$code];
                    $duplicate_definitions[$code][$file] = $file;
                }

                $entry_files[$code] = $file;
                $flat_text_entries[$code] = array_replace(
                    $translations,
                    array_filter($user_text_entries[$file][$code] ?? [], static fn(string $value): bool => $value !== "")
                );
            }
        }

        return true;
    } catch (\JsonException | \RuntimeException $exception) {
        $messages .= localization_error_html($exception->getMessage());

        return false;
    }
} // collect_directory_texts

/**
 * Loads and validates localization data and records incomplete translations.
 *
 * Populates the shared editor indexes and file fingerprints. Loading failures
 * are reported to the message buffer and prevent subsequent saving.
 *
 * @return bool
 * Returns true when configuration and catalogs are valid, or false after a read,
 * JSON, schema, or module-directory error.
 *
 * @author Oleg Schildt
 */
function load_language_texts(): bool
{
    global $messages, $supported_languages, $language_entries, $country_entries, $text_entries;
    global $flat_text_entries, $entry_files, $duplicate_definitions, $catalog_hashes, $localization_config;
    global $incomplete_text_translations, $incomplete_language_name_translations, $incomplete_country_name_translations;
    global $master_text_entries, $user_text_entries;

    $supported_languages = [];
    $text_entries = [];
    $master_text_entries = [];
    $user_text_entries = [];
    $flat_text_entries = [];
    $entry_files = [];
    $duplicate_definitions = [];
    $catalog_hashes = [];
    $language_entries = [];
    $country_entries = [];
    $incomplete_text_translations = [];
    $incomplete_language_name_translations = [];
    $incomplete_country_name_translations = [];

    try {
        $localization_config = localization_read_json("config.json", $catalog_hashes);
        $languages = $localization_config["interface_languages"] ?? null;

        if (!is_array($languages) || $languages === []) {
            throw new \RuntimeException("Configuration must contain a nonempty interface_languages list.");
        }

        foreach ($languages as $language) {
            if (!is_string($language) || !preg_match('/\A[A-Za-z][A-Za-z0-9]*(?:[-_][A-Za-z0-9]+)*\z/', $language)) {
                throw new \RuntimeException("Configuration contains an invalid interface language code.");
            }

            $supported_languages[$language] = $language;
        }

        $language_entries = localization_validate_catalog(
            localization_read_json("languages.json", $catalog_hashes),
            "languages.json"
        );
        $country_entries = localization_validate_catalog(
            localization_read_json("countries.json", $catalog_hashes),
            "countries.json"
        );

        if (!collect_directory_texts($text_entries, "../")) {
            return false;
        }

        if (is_dir("../modules/")) {
            $modules = @scandir("../modules/");

            if ($modules === false) {
                throw new \RuntimeException("The modules directory cannot be read.");
            }

            foreach ($modules as $module) {
                if ($module === "." || $module === ".." || !is_dir("../modules/$module")) {
                    continue;
                }

                if (!collect_directory_texts($text_entries, "../modules/$module/")) {
                    return false;
                }
            }
        }

        $languages = array_values($supported_languages);

        // These scans only read entries; references would unnecessarily bind loop variables to catalogs.
        foreach ($master_text_entries as $entries) {
            foreach (localization_missing_translations($entries, $languages) as $code => $missing) {
                $incomplete_text_translations[$code] = array_values(array_unique(array_merge(
                    $incomplete_text_translations[$code] ?? [],
                    $missing
                )));
            }
        }

        $incomplete_language_name_translations = localization_missing_translations($language_entries, $languages);
        $incomplete_country_name_translations = localization_missing_translations($country_entries, $languages);

        return true;
    } catch (\JsonException | \RuntimeException $exception) {
        $messages .= localization_error_html($exception->getMessage());

        return false;
    }
} // load_language_texts

/**
 * Outputs the error CSS class for the field that failed validation.
 *
 * @param string $name
 * The form field name to compare with the shared validation error field.
 *
 * @return void
 * Outputs "error_field" for the matching field and an empty string otherwise.
 *
 * @author Oleg Schildt
 */
function error_class(string $name): void
{
    global $error_field;

    if ($error_field === $name) {
        echo "error_field";
    }
} // error_class

/**
 * Outputs escaped translation text without introducing whitespace inside its wrapper.
 *
 * @param string $string
 * The translation whose spaces and line breaks must be preserved exactly.
 *
 * @return void
 * Outputs a div using the pre_wrap CSS class; HTML in the translation is escaped.
 *
 * @author Oleg Schildt
 */
function echo_pre_wrap(string $string): void
{
    // Emit the complete wrapper in PHP so template indentation cannot become translation content.
    echo '<div class="pre_wrap">';
    echo htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
    echo '</div>';
} // echo_pre_wrap

/**
 * Validates the submitted editor state and saves the complete catalog batch.
 *
 * Reuses loaded values for untouched controls, validates final identifiers before
 * writing, and preserves unknown configuration settings. File hashes protect the
 * loaded snapshot against concurrent changes; the controller also checks the form version.
 *
 * @return bool
 * Returns true after a successful save, or false after reporting a loading,
 * validation, encoding, conflict, staging, or replacement error.
 *
 * @author Oleg Schildt
 */
function save_data(): bool
{
    global $supported_languages, $text_entries, $language_entries, $country_entries, $request;
    global $error_field, $error_tab, $messages, $load_result, $catalog_hashes, $localization_config;
    global $master_text_entries, $user_text_entries;

    if (!$load_result) {
        $messages .= localization_error_html("Saving is disabled because localization data could not be loaded.");

        return false;
    }

    try {
        localization_validate_request($request);

        $deleted_languages = localization_deletion_list($request["delete_supported_languages"] ?? null);
        $languages = array_values(array_diff(array_values($supported_languages), $deleted_languages));
        $new_language = localization_request_string(
            $request["new_language"]["code"] ?? null,
            "new_language[code]",
            "configuration"
        );
        $copy_source = localization_request_string(
            $request["new_language"]["copy_source"] ?? null,
            "new_language[copy_source]",
            "configuration"
        );

        if ($languages === [] || in_array("en", $deleted_languages, true)) {
            throw new LocalizationValidationException(
                "The English interface language cannot be deleted.",
                "",
                "configuration"
            );
        }

        if ($new_language !== "") {
            if (!preg_match('/\A[A-Za-z][A-Za-z0-9]*(?:[-_][A-Za-z0-9]+)*\z/', $new_language)) {
                throw new LocalizationValidationException(
                    "The language code has an invalid format.",
                    "new_language[code]",
                    "configuration"
                );
            }

            if (array_key_exists($new_language, $supported_languages)) {
                throw new LocalizationValidationException(
                    "The language '$new_language' already exists.",
                    "new_language[code]",
                    "configuration"
                );
            }

            if ($copy_source !== "" && !array_key_exists($copy_source, $supported_languages)) {
                throw new LocalizationValidationException(
                    "The copy source language does not exist.",
                    "new_language[copy_source]",
                    "configuration"
                );
            }
        }

        $options = ["languages" => $languages, "new_language" => $new_language, "copy_source" => $copy_source];
        $config = $localization_config;
        $config["interface_languages"] = $new_language === "" ? $languages : [...$languages, $new_language];
        $documents = ["config.json" => $config];

        // Language and country catalogs differ only in their source entries and form field names.
        foreach (["language" => $language_entries, "country" => $country_entries] as $kind => $entries) {
            $tab = $kind === "language" ? "languages" : "countries";
            $catalog = localization_prepare_catalog(
                $entries,
                $request[$kind . "_entries"] ?? [],
                localization_deletion_list($request["delete_" . $kind . "_entries"] ?? null),
                $request["new_" . $kind . "_entry"] ?? [],
                $options + ["field" => $kind . "_entries", "new_field" => "new_" . $kind . "_entry", "tab" => $tab]
            );

            ksort($catalog);

            $documents[$tab . ".json"] = $catalog;
        }

        $identifiers = [];

        foreach ($text_entries as $file => $entries) {
            $catalogs = localization_prepare_text_catalogs(
                $file,
                $master_text_entries[$file],
                $user_text_entries[$file],
                $request,
                $options
            );
            $catalog = $catalogs["master"];

            foreach ($catalog + $catalogs["user"] as $code => $translations) {
                if (array_key_exists($code, $identifiers)) {
                    throw new LocalizationValidationException(
                        "The text code '$code' occurs in both '$file' and '{$identifiers[$code]}'."
                    );
                }

                $identifiers[$code] = $file;
            }

            // Do not create absent module catalogs unless an entry was added, but persist empty existing catalogs.
            if ($catalog !== [] || $catalog_hashes[$file] !== null) {
                $documents[$file] = $catalog;
            }

            $user_file = dirname($file) . "/user_texts.json";

            if ($catalogs["user"] !== [] || $catalog_hashes[$user_file] !== null) {
                $documents[$user_file] = ($documents[$user_file] ?? []) + $catalogs["user"];
            }
        }

        localization_write_catalogs($documents, $catalog_hashes, ".editor.lock");

        $_SESSION["message"] = '<p class="editor_success">Data saved successfully.</p>';

        return true;
    } catch (LocalizationValidationException $exception) {
        $error_field = $exception->field;
        $error_tab = $exception->tab;
        $messages .= localization_error_html($exception->getMessage(), $error_field, $error_tab);

        return false;
    } catch (\JsonException | \RuntimeException $exception) {
        $messages .= localization_error_html($exception->getMessage());

        return false;
    }
} // save_data
?>

<?php
chdir(__DIR__);

session_start(["cookie_httponly" => true, "cookie_samesite" => "Lax", "use_strict_mode" => true]);

$messages = "";
$error_field = "";
$error_tab = "";
$files_processed = 0;
$supported_languages = [];
$text_entries = [];
$master_text_entries = [];
$user_text_entries = [];
$flat_text_entries = [];
$entry_files = [];
$duplicate_definitions = [];
$language_entries = [];
$country_entries = [];
$catalog_hashes = [];
$localization_config = [];
$found_entries = [];
$missing_definitions = [];
$never_used = [];
$incomplete_text_translations = [];
$incomplete_language_name_translations = [];
$incomplete_country_name_translations = [];
$method = $_SERVER["REQUEST_METHOD"] ?? "GET";

$tabs = [
    "texts" => ["caption" => "Texts"],
    "languages" => ["caption" => "Languages"],
    "countries" => ["caption" => "Countries"],
    "configuration" => ["caption" => "Configuration"],
    "check" => ["caption" => "Check"]
];

if (!in_array($method, ["GET", "POST"], true) || ($method === "GET" && isset($_GET["act"]))) {
    http_response_code(405);

    header("Allow: GET, POST");

    exit("Editor actions must be submitted using POST.");
}

if (!isset($_SESSION["localization_csrf_token"]) || !is_string($_SESSION["localization_csrf_token"])) {
    $_SESSION["localization_csrf_token"] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION["localization_csrf_token"];

if ($method === "POST" && !localization_valid_form_token($method, $_POST["csrf_token"] ?? null, $csrf_token)) {
    http_response_code(403);

    exit("The form token is invalid. Reload the editor before submitting again.");
}

$request = $method === "POST" ? $_POST : ["tab" => $_GET["tab"] ?? "texts"];
$save_result = true;

try {
    localization_validate_request($request);
} catch (LocalizationValidationException $exception) {
    // Discard malformed shapes before the template passes their values to string functions.
    $request = [];
    $save_result = false;
    $messages .= localization_error_html($exception->getMessage());

    http_response_code(422);
}

$active_tab = $request["tab"] ?? "texts";

if (!array_key_exists($active_tab, $tabs)) {
    $active_tab = "texts";
}

$load_result = load_language_texts();
$catalog_version = localization_catalog_version($catalog_hashes);
$form_catalog_version = $method === "POST" ? ($request["catalog_version"] ?? "") : $catalog_version;
$action = $request["act"] ?? "";

if ($method === "POST" && $save_result && $action === "Cancel") {
    header("Location: edit.php?" . http_build_query(["tab" => $active_tab]), true, 303);

    exit;
}

if ($method === "POST" && $save_result && $action === "Apply") {
    if ($active_tab === "check") {
        header("Location: edit.php?tab=check", true, 303);

        exit;
    }

    if (!hash_equals($catalog_version, $form_catalog_version)) {
        $save_result = false;
        $messages .= localization_error_html("The catalogs have changed. Reload the editor before saving.");

        http_response_code(409);
    } elseif ($save_result = save_data()) {
        header("Location: edit.php?" . http_build_query(["tab" => $active_tab]), true, 303);

        exit;
    } else {
        http_response_code(422);
    }
}

if (!empty($_SESSION["message"])) {
    $messages .= $_SESSION["message"];

    unset($_SESSION["message"]);
}

if ($messages === "") {
    $messages = "<p>&nbsp;</p>";
}
?><!DOCTYPE html>
<html lang="en">

<head>
    <title>Translation editor (Backend)</title>

    <style>
        html,
        body {
            margin: 0px;
        }

        body,
        p,
        li,
        td,
        th,
        input,
        select,
        option,
        textarea {
            font-family: Arial Narrow;
            font-size: 18px;
        }

        h1 {
            font-size: 22px;
        }

        h2 {
            font-size: 20px;
        }

        input[type=submit],
        input[type=button] {
            border: 1px solid white;
            background-color: #0078D7;
            color: white;
            min-width: 100px;
            padding: 3px;
            cursor: pointer;
        }

        input[type=submit]:hover,
        input[type=button]:hover {
            background-color: #103DBF;
        }

        input[type=text] {
            border: 1px solid gray;
            height: 30px;
            padding: 4px;
        }

        input[type=checkbox] {
            --checkbox-check-color: #333;
            appearance: none;
            display: inline-grid;
            place-items: center;
            box-sizing: border-box;
            width: 20px;
            height: 20px;
            margin: 0;
            padding: 0;
            border: 1px solid gray;
            border-radius: 0;
            background-color: white;
            vertical-align: middle;
            cursor: pointer;
        }

        input[type=checkbox]::before {
            content: "";
            box-sizing: border-box;
            width: 6px;
            height: 11px;
            border-right: 2px solid var(--checkbox-check-color);
            border-bottom: 2px solid var(--checkbox-check-color);
            transform: translateY(-2px) rotate(45deg);
            visibility: hidden;
        }

        input[type=checkbox]:checked::before {
            visibility: visible;
        }

        input[type=checkbox]:focus-visible {
            outline: 2px solid #0078D7;
            outline-offset: 2px;
        }

        select {
            border: 1px solid gray;
            height: 30px;
            background-color: white;
        }

        a.goto:link,
        a.goto:active,
        a.goto:visited,
        a.goto:focus {
            text-decoration: none;
        }

        .header {
            height: 90px;
            background-color: #2F4B60;
            position: fixed;
            top: 0px;
            width: 100%;
            min-width: 800px;
            border-bottom: 1px solid lightgray;
        }

        .content_area {
            margin-top: 90px;
            padding: 1px 10px 10px 10px;
        }

        .clear_both {
            clear: both;
        }

        .left_panel {
            float: left;
            color: white;
            font-weight: bold;
            font-size: 22px;
            padding: 5px 5px 5px 10px;
        }

        .right_panel {
            float: right;
            padding: 20px 10px 0px 0px;
        }

        .right_panel input[type=submit] {
            margin-top: 30px;
        }

        .tab_area {
            position: absolute;
            bottom: -1px;
            left: 10px;
        }

        a.tab:link,
        a.tab:active,
        a.tab:visited,
        a.tab:focus {
            display: block;
            width: 110px;
            float: left;
            text-align: center;
            color: white;
            border: 1px solid lightgray;
            border-right: 0;
            padding: 5px;
            cursor: pointer;
            text-decoration: none;
        }

        a.tab:hover {
            background-color: #0078D7;
        }

        a.tab:nth-last-child(2) {
            border-right: 1px solid lightgray;
        }

        a.active_tab:link,
        a.active_tab:active,
        a.active_tab:visited,
        a.active_tab:focus {
            border: 1px solid white;
            background-color: #0078D7;
            border-bottom: 1px solid lightgray;
            text-decoration: underline;
        }

        a.after_active:link,
        a.after_active:active,
        a.after_active:visited,
        a.after_active:focus {
            border-left: 0;
        }

        .edit_table {
            border-collapse: collapse;
        }

        .edit_table td,
        .edit_table th {
            border: 1px solid gray;
            padding: 3px;
        }

        .edit_table td {
            background-color: #dddddd;
            height: 24px;
            vertical-align: middle;
        }

        .edit_table th {
            background-color: #aaaaaa;
            color: white;
        }

        .edit_table th:first-child {
            width: 22px;
        }

        .edit_table input[type=text] {
            width: 300px;
            border: 1px solid gray;
            display: block;
            box-sizing: border-box;
        }

        .edit_table textarea {
            border: 1px solid gray;
            width: 300px;
            height: 80px;
            resize: none;
            margin: 0;
            display: block;
            box-sizing: border-box;
            padding: 4px;
        }

        .edit_table input[type=text].modified_field,
        .edit_table textarea.modified_field {
            border: 1px solid #DEB41C;
            background-color: #FAFAD2;
        }

        .edit_table input[type=text].empty_field,
        .edit_table textarea.empty_field,
        .edit_table input[type=text].error_field,
        .edit_table textarea.error_field {
            border: 1px solid red;
            background-color: #FFD9EC;
        }

        .edit_table input[type=text]:hover {
            border: 1px solid #2F4B60;
        }

        .edit_table textarea:hover {
            border: 1px solid #2F4B60;
        }

        .edit_table .div_input,
        .edit_table .div_textarea {
            padding: 4px;
            background-color: white;
            cursor: text;
            border: 1px solid gray;
            box-sizing: border-box;
        }

        .edit_table .div_input {
            display: block;
            width: 300px;
            height: 30px;
            padding-top: 3px;
            overflow: hidden;
            white-space: nowrap;
        }

        .edit_table .div_textarea {
            display: block;
            width: 300px;
            height: 80px;
            overflow: auto;
        }

        .pre_wrap {
            white-space: pre-wrap;
        }

        .editor_error {
            color: red;
            font-weight: bold;
        }

        .editor_success {
            color: green;
            font-weight: bold;
        }

        .edit_table .div_textarea.empty_field,
        .edit_table .div_input.empty_field,
        .edit_table .div_textarea.error_field,
        .edit_table .div_input.error_field {
            border: 1px solid red;
            background-color: #FFD9EC;
        }

        .edit_table .div_input:hover,
        .edit_table .div_textarea:hover {
            border: 1px solid #2F4B60;
        }

        .edit_table textarea.overridden_field,
        .edit_table .div_textarea.overridden_field {
            border: 1px solid #579957;
            background-color: #EDF7ED;
        }

        .edit_table textarea.overridden_field.modified_field {
            border: 1px solid #DEB41C;
            background-color: #FAFAD2;
        }

        .edit_table .div_textarea+.override_translation,
        .edit_table textarea+.override_translation {
            margin-top: 4px;
        }

        .override_translation[hidden] {
            display: none;
        }

        .edit_table td.text_code {
            vertical-align: top;
        }

        .text_code input[type=text] {
            display: block;
            width: 300px;
            box-sizing: border-box;
        }

        .text_code .div_input {
            display: block;
            width: 300px;
            box-sizing: border-box;
        }

        .short_code input[type=text] {
            display: block;
            width: 50px;
            box-sizing: border-box;
        }

        .short_code .div_input {
            display: block;
            width: 50px;
            box-sizing: border-box;
        }

        /* begin: progress indicator */

        ._sys_load_lightbox {
            display: none;
            height: 100%;
            width: 100%;
            position: fixed;
            top: 0%;
            left: 0%;
            background: none repeat scroll 0 0 rgba(0, 0, 0, 0.5);
        }

        ._sys_load_lightbox>div {
            vertical-align: middle;
            display: table-cell;
            text-align: center;
        }

        ._sys_load_lightbox>div>div {
            display: inline-block;
            position: relative;
            padding: 5px 0px 13px 0px;
        }

        ._sys_load_lightbox>div>div {
            background: #fff;
            text-align: left;
            box-shadow: 0 0 10px #fff;
            border-radius: 4px;
            z-index: 100000;
            width: 400px;
            text-align: center;
        }

        .progress_caption {
            margin-top: 5px;
            margin-bottom: 8px;
        }

        /* end: progress indicator */
    </style>

    <script>
        const progress_captions = { loading: "Loading data ...", saving: "Saving data ..." };
        var progress_navigation_frame = null;
        var progress_submitter_field = null;
        var progress_previous_modified = null;

        /**
         * Shows or hides the progress overlay during navigation or form submission.
         * The caption is displayed as plain text when the overlay is shown.
         *
         * @param {boolean} state
         * Whether to show the overlay; false hides it.
         *
         * @param {string} [caption=progress_captions.saving]
         * The plain-text caption displayed above the animation when showing it.
         *
         * @returns {void}
         * Updates the caption and visibility, or does nothing if the overlay is absent.
         *
         * @author Oleg Schildt
         */
        function show_sys_progress_indicator(state, caption = progress_captions.saving) {
            var msg = document.getElementById("sys_progress_indicator");

            if (!msg) {
                return;
            }

            if (!state) {
                msg.style.display = "none";

                return;
            }

            var progress_caption = msg.querySelector(".progress_caption");

            if (progress_caption) {
                progress_caption.textContent = caption;
            }

            // The table layout vertically centers the dialog in its table-cell container.
            msg.style.display = "table";
        } // show_sys_progress_indicator

        /**
         * Clears pending progress work and restores the editor after interrupted navigation.
         *
         * @returns {void}
         * Hides the overlay, removes any temporary submitter field, and restores the
         * unsaved-change flag captured before deferred submission.
         *
         * @author Oleg Schildt
         */
        function reset_progress_indicator() {
            if (progress_navigation_frame !== null) {
                window.cancelAnimationFrame(progress_navigation_frame);

                progress_navigation_frame = null;
            }

            if (progress_submitter_field && progress_submitter_field.parentNode) {
                progress_submitter_field.parentNode.removeChild(progress_submitter_field);
            }

            progress_submitter_field = null;

            if (progress_previous_modified !== null) {
                modified = progress_previous_modified;
                progress_previous_modified = null;
            }

            cancelling = false;

            show_sys_progress_indicator(false);
        } // reset_progress_indicator

        /**
         * Gives the browser a frame to paint the overlay before running an action.
         * Additional requests are ignored until navigation completes or is reset.
         *
         * @param {function(): void} action
         * The navigation or submission action to execute after painting.
         *
         * @param {string} caption
         * The plain-text progress caption.
         *
         * @returns {void}
         * Schedules the action after two animation callbacks. If the deferred action
         * throws, restores the editor before rethrowing the error.
         *
         * @author Oleg Schildt
         */
        function run_with_progress(action, caption) {
            if (progress_navigation_frame !== null) {
                return;
            }

            show_sys_progress_indicator(true, caption);

            // A second callback allows the first frame to paint before the request starts.
            progress_navigation_frame = window.requestAnimationFrame(function () {
                progress_navigation_frame = window.requestAnimationFrame(function () {
                    try {
                        action();
                    } catch (error) {
                        reset_progress_indicator();

                        throw error;
                    }
                });
            });
        } // run_with_progress

        /**
         * Handles ordinary tab clicks without intercepting alternate-tab navigation.
         *
         * @param {MouseEvent} event
         * The click event attached directly to an editor tab link.
         *
         * @returns {void}
         * Switches local tabs immediately, or defers a server-generated Check page
         * until the loading overlay has painted. Modified clicks keep browser behavior.
         *
         * @author Oleg Schildt
         */
        function handle_editor_tab_link(event) {
            var link = event.currentTarget;

            if (event.defaultPrevented || event.button !== 0
                || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey
                || (link.target && link.target.toLowerCase() !== "_self")
                || link.hasAttribute("download")
            ) {
                return;
            }

            event.preventDefault();

            var tab = link.getAttribute("data-id");

            if (tab === "check") {
                run_with_progress(function () {
                    window.location.assign(link.href);
                }, progress_captions.loading);
            } else {
                goto_tab(tab, false);
            }
        } // handle_editor_tab_link

        window.addEventListener("pageshow", function (event) {
            if (event.persisted) {
                reset_progress_indicator();
            }
        });

        /**
         * Replaces a displayed translation value with a text input when editing begins.
         *
         * @param {HTMLElement} elm
         * The placeholder element whose data-content attribute supplies the value.
         *
         * @param {string} name
         * The form field name assigned to the new input.
         *
         * @returns {void}
         * Replaces the placeholder and focuses the input.
         *
         * @author Oleg Schildt
         */
        function replace_input(elm, name) {
            var ti = document.createElement("input");
            ti.type = "text";
            ti.name = name;
            ti.value = elm.getAttribute("data-content");

            /**
             * Marks the newly created input as modified when its value changes.
             *
             * @returns {void}
             * Updates the change indicator and empty-value highlighting.
             *
             * @author Oleg Schildt
             */
            ti.onchange = function () {
                check_changes(ti, true);
            };

            if (ti.value == "") {
                ti.classList.add("empty_field");
            } else {
                ti.classList.remove("empty_field");
            }

            elm.parentNode.replaceChild(ti, elm);

            ti.focus();
        } // replace_input

        /**
         * Replaces a displayed translation with a textarea and positions its caret.
         * Preserves override styling, including for an empty translation.
         *
         * @param {HTMLElement} elm
         * The placeholder element whose data-content attribute supplies the value.
         *
         * @param {string} name
         * The form field name assigned to the new textarea.
         *
         * @returns {void}
         * Replaces the placeholder and, if selection is supported, focuses the textarea.
         *
         * @author Oleg Schildt
         */
        function replace_textarea(elm, name) {
            var ta = document.createElement("textarea");
            ta.name = name;
            ta.value = elm.getAttribute("data-content");

            if (elm.classList.contains("overridden_field")) {
                ta.classList.add("overridden_field");
            }

            /**
             * Marks the newly created textarea as modified when its value changes.
             *
             * @returns {void}
             * Updates the change indicator and empty-value highlighting.
             *
             * @author Oleg Schildt
             */
            ta.onchange = function () {
                check_changes(ta, true);
            };

            if (ta.value == "" && !ta.classList.contains("overridden_field")) {
                ta.classList.add("empty_field");
            } else {
                ta.classList.remove("empty_field");
            }

            elm.parentNode.replaceChild(ta, elm);

            if (ta.setSelectionRange) {
                ta.focus();

                // Keep the caret at the end of the existing translation.
                ta.setSelectionRange(ta.value.length, ta.value.length);
            }
        } // replace_textarea

        /**
         * Activates an editor tab and synchronizes the form and browser history.
         *
         * @param {string} tab
         * The tab identifier used by the tab and content element IDs.
         *
         * @param {boolean} nopush
         * Whether to skip creating a history entry while restoring previous navigation.
         *
         * @returns {false}
         * Returns false to suppress default navigation, also if a required element is absent.
         *
         * @author Oleg Schildt
         */
        function goto_tab(tab, nopush) {
            var elm = document.getElementById("tab_" + active_tab);

            if (!elm) {
                return false;
            }

            elm.classList.remove('active_tab');

            if (elm.nextSibling.classList && elm.nextSibling.classList.contains('after_active')) {
                elm.nextSibling.classList.remove('after_active');
            }

            elm = document.getElementById("tab_context_" + active_tab);

            if (!elm) {
                return false;
            }

            elm.style.display = "none";

            elm = document.getElementById("tab_" + tab);

            if (!elm) {
                return false;
            }

            elm.classList.add('active_tab');

            if (elm.nextSibling.classList && elm.nextSibling.classList.contains('tab')) {
                elm.nextSibling.classList.add('after_active');
            }

            elm = document.getElementById("tab_context_" + tab);

            if (!elm) {
                return false;
            }

            elm.style.display = "block";
            active_tab = tab;

            // History restoration must not create another entry for the same navigation.
            if (!nopush) {
                window.history.pushState({ tab: tab }, null, "edit.php?tab=" + tab);
            }

            elm = document.getElementById("tab");

            if (!elm) {
                return false;
            }

            elm.value = active_tab;

            elm = document.getElementById("action_buttons");

            if (!elm) {
                return false;
            }

            if (tab == "check") {
                elm.style.display = "none";
            } else {
                elm.style.display = "block";
            }

            return false;
        } // goto_tab

        /**
         * Opens the tab containing a validation error and focuses its form field.
         *
         * @param {string} tab
         * The identifier of the tab containing the invalid field.
         *
         * @param {string} field
         * The field name used to look up the control in the editor form.
         *
         * @returns {false|undefined}
         * Returns false if the form is absent, or undefined after attempting to focus.
         *
         * @author Oleg Schildt
         */
        function show_error_field(tab, field) {
            goto_tab(tab, false);

            var form = document.getElementById('main_form');

            if (!form) {
                return false;
            }

            try {
                if (form.elements[field]) {
                    form.elements[field].focus();
                }
            } catch (e) {
                // Keep navigation usable when the browser cannot focus the requested control.
            }
        } // show_error_field

        /**
         * Restores the selected tab when navigating through browser history.
         * Clears stale progress and defers a server-generated Check refresh until painting.
         *
         * @param {PopStateEvent} e
         * The history event whose state contains the tab identifier.
         *
         * @returns {true|undefined}
         * Returns true if no state exists, or undefined after restoring the tab.
         *
         * @author Oleg Schildt
         */
        window.onpopstate = function (e) {
            reset_progress_indicator();

            if (typeof e.state == 'undefined' || !e.state) {
                return true;
            }

            goto_tab(e.state.tab, true);

            // Check results are generated on the server and must be refreshed.
            if (e.state.tab == "check") {
                run_with_progress(function () {
                    window.location.reload();
                }, progress_captions.loading);
            }
        };

        var active_tab = "<?php echo ($active_tab); ?>";

        window.history.replaceState({ tab: active_tab }, null, "edit.php?tab=" + active_tab);

        var modified = <?php echo ($save_result) ? "false" : "true"; ?>;
        var cancelling = false;

        /**
         * Records a deletion selection and dims or restores its table row.
         *
         * @param {HTMLInputElement} elm
         * The deletion checkbox contained in a table row.
         *
         * @returns {void}
         * Marks the form as modified and updates the nearest row's opacity if present.
         *
         * @author Oleg Schildt
         */
        function check_deleted(elm) {
            modified = true;
            var row = elm.closest("tr");

            if (!row) {
                return;
            }

            row.style.opacity = elm.checked ? "0.2" : "1";
        } // check_deleted

        /**
         * Shows or hides a row's override controls without discarding their values.
         *
         * @param {HTMLInputElement} elm
         * The override checkbox in the same table row as the translation controls.
         *
         * @returns {void}
         * Updates visibility and marks the form as modified.
         *
         * @author Oleg Schildt
         */
        function check_override(elm) {
            modified = true;

            elm.closest("tr").querySelectorAll(".override_translation").forEach(function (field) {
                field.hidden = !elm.checked;
            });
        } // check_override

        /**
         * Records an unsaved edit and updates the control's visual indicators.
         *
         * @param {HTMLInputElement|HTMLTextAreaElement} elm
         * The edited text control.
         *
         * @param {boolean} highlight
         * Whether to mark an empty value; override fields always allow empty translations.
         *
         * @returns {void}
         * Marks the editor as modified and updates the applicable CSS classes.
         *
         * @author Oleg Schildt
         */
        function check_changes(elm, highlight) {
            modified = true;

            if (elm.type == "textarea" || elm.type == "text") {
                elm.classList.add("modified_field");
            }

            if (highlight) {
                if (elm.value == "" && !elm.classList.contains("overridden_field")) {
                    elm.classList.add("empty_field");
                } else {
                    elm.classList.remove("empty_field");
                }
            }
        } // check_changes

        /**
         * Confirms selected deletions and defers submission until progress is visible.
         *
         * @param {HTMLFormElement} form
         * The editor form containing the deletion checkbox groups.
         *
         * @param {SubmitEvent} event
         * The native submission event supplying the clicked Apply or Cancel control.
         *
         * @returns {false}
         * Suppresses immediate submission. Confirmed submissions run after painting,
         * preserving the submitter's name and value. Declined confirmations leave
         * the editor unchanged; cancellation bypasses deletion confirmation.
         *
         * @author Oleg Schildt
         */
        function check_submit(form, event) {
            if (progress_navigation_frame !== null) {
                return false;
            }

            // A selector handles a single checkbox and any number of catalog groups uniformly.
            var need_ask = form.querySelector('input[type="checkbox"][name^="delete_"]:checked') !== null;

            if (!cancelling && need_ask) {
                if (!confirm("Are you sure to delete the selected entries?")) {
                    return false;
                }
            }

            var submitter = event.submitter;
            progress_previous_modified = modified;
            modified = false;

            run_with_progress(function () {
                // Native submit() omits submit buttons; retain the chosen editor action.
                if (submitter && submitter.name) {
                    progress_submitter_field = document.createElement("input");
                    progress_submitter_field.type = "hidden";
                    progress_submitter_field.name = submitter.name;
                    progress_submitter_field.value = submitter.value;

                    form.appendChild(progress_submitter_field);
                }

                // Browser validation already passed before the original submit event.
                HTMLFormElement.prototype.submit.call(form);
            }, cancelling ? progress_captions.loading : progress_captions.saving);

            return false;
        } // check_submit

        /**
         * Creates an edit control when a translation placeholder receives focus.
         *
         * @param {FocusEvent} event
         * The bubbling focus event from the editor form.
         *
         * @returns {void}
         * Replaces the focused placeholder using its data attributes.
         *
         * @author Oleg Schildt
         */
        function handle_editor_focus(event) {
            var elm = event.target;

            // Field names remain HTML data, including quotes, rather than executable JavaScript.
            if (elm.dataset.editor === "textarea") {
                replace_textarea(elm, elm.dataset.field);
            } else if (elm.dataset.editor === "input") {
                replace_input(elm, elm.dataset.field);
            }
        } // handle_editor_focus

        /**
         * Tracks edits to existing and newly added form controls.
         *
         * @param {Event} event
         * An input or change event from the editor form.
         *
         * @returns {void}
         * Marks text controls, selects, and checkboxes as unsaved changes.
         *
         * @author Oleg Schildt
         */
        function handle_editor_change(event) {
            if (event.target.matches('input[type="text"], textarea, select, input[type="checkbox"]')) {
                modified = true;
            }
        } // handle_editor_change

        /**
         * Navigates to a validation control without embedding its name in JavaScript source.
         *
         * @param {MouseEvent} event
         * A click event whose target may be inside a validation navigation link.
         *
         * @returns {void}
         * Opens the linked tab and focuses its field, suppressing the anchor navigation.
         *
         * @author Oleg Schildt
         */
        function handle_editor_error_link(event) {
            var link = event.target.closest("[data-error-field]");

            if (!link) {
                return;
            }

            event.preventDefault();

            show_error_field(link.dataset.errorTab, link.dataset.errorField);
        } // handle_editor_error_link

        /**
         * Attaches delegated editing and navigation handlers after the form exists.
         *
         * @returns {void}
         * Registers handlers for current controls and controls created later during editing.
         *
         * @author Oleg Schildt
         */
        document.addEventListener("DOMContentLoaded", function () {
            var form = document.getElementById("main_form");

            form.addEventListener("focusin", handle_editor_focus);

            form.addEventListener("input", handle_editor_change);

            form.addEventListener("change", handle_editor_change);

            form.addEventListener("click", handle_editor_error_link);

            document.querySelectorAll(".tab_area a.tab").forEach(function (link) {
                link.addEventListener("click", handle_editor_tab_link);
            });
        });

        /**
         * Requests a browser warning when leaving the editor with unsaved changes.
         *
         * @param {BeforeUnloadEvent} ev
         * The browser's unload event; its properties are not modified.
         *
         * @returns {string|undefined}
         * Returns a warning string for unsaved changes, or undefined to leave freely.
         * Clears progress before asking so declining navigation leaves the editor usable.
         *
         * @author Oleg Schildt
         */
        window.onbeforeunload = function (ev) {
            if (!modified) {
                return undefined;
            }

            reset_progress_indicator();

            return 'Are you sure to dismiss your changes?';
        }
    </script>

</head>

<body>

    <form action="edit.php" id="main_form" method="post" onsubmit="return check_submit(this, event);">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES); ?>">
        <input type="hidden" name="catalog_version"
            value="<?php echo htmlspecialchars($form_catalog_version, ENT_QUOTES); ?>">
        <input type="hidden" name="tab" id="tab" value="<?php echo (htmlspecialchars($active_tab, ENT_QUOTES)); ?>">

        <div class="header">
            <div class="left_panel">
                Translation editor (Backend)
            </div>

            <div class="right_panel" id="action_buttons"
                style="display: <?php echo ($active_tab == "check" ? "none" : "block"); ?>">
                <input type="submit" name="act" value="Apply">
                <input type="submit" name="act" value="Cancel"
                    onclick="cancelling = confirm('Are you sure to dismiss your changes?'); return cancelling;">
            </div>

            <div class="clear_both">
            </div>

            <div class="tab_area">
                <?php
                $active_class = "";
                $after_active_class = "";
                foreach ($tabs as $tab => $tabinfo):

                    if (!empty($after_active_class)) {
                        $after_active_class = "";
                    }

                    if (!empty($active_class)) {
                        $active_class = "";
                        $after_active_class = "after_active";
                    }

                    if ($tab == $active_tab) {
                        $active_class = "active_tab";
                    }
                    ?><a id="tab_<?php echo (htmlspecialchars($tab, ENT_QUOTES)); ?>"
                        class="tab <?php echo ($active_class . " " . $after_active_class); ?>"
                        data-id="<?php echo (htmlspecialchars($tab, ENT_QUOTES)); ?>"
                        href="edit.php?tab=<?php echo (htmlspecialchars($tab, ENT_QUOTES)); ?>"><?php echo (htmlspecialchars($tabinfo["caption"], ENT_QUOTES)); ?></a><?php endforeach; ?>


                <div class="clear_both"></div>
            </div>
        </div>

        <div class="content_area">

            <!-- BEGIN: texts -->
            <div id="tab_context_texts" class="tab_content"
                style="display: <?php echo ($active_tab == "texts") ? "block" : "none"; ?>">

                <h1>Text translations</h1>

                <?php echo ($messages); ?>

                <?php foreach ($text_entries as $file => $file_text_entries): ?>

                    <h2>File: <?php echo (htmlspecialchars($file, ENT_QUOTES)); ?></h2>

                    <table class="edit_table">
                        <tr>
                            <th style="font-weight: normal">✘</th>
                            <th>Code</th>

                            <?php foreach ($supported_languages as $lang):
                                $caption = $lang;
                                if (!empty($language_entries[$lang]["en"])) {
                                    $caption = $language_entries[$lang]["en"] . " [" . $lang . "]";
                                }
                                ?>
                                <th><?php echo (htmlspecialchars($caption, ENT_QUOTES)); ?></th>
                            <?php endforeach; ?>

                        </tr>

                        <tr>
                            <td></td>
                            <td class="text_code">
                                <?php
                                $class = "";
                                if ($error_field == "new_text_entry[$file][code]") {
                                    $class = "error_field";
                                }
                                ?>
                                <input type="text" placeholder="new" class="<?php echo ($class); ?>"
                                    name="new_text_entry[<?php echo (htmlspecialchars($file, ENT_QUOTES)); ?>][code]"
                                    value="<?php echo (htmlspecialchars($request["new_text_entry"][$file]["code"] ?? "", ENT_QUOTES)); ?>"
                                    onchange="check_changes(this, true)">

                                <label style="display: block; margin-top: 4px;"><input tabindex="-1" type="checkbox"
                                        name="new_text_entry[<?php echo htmlspecialchars($file, ENT_QUOTES); ?>][override]"
                                        value="1" title="Add to user_texts.json"
                                        <?php echo isset($request["new_text_entry"][$file]["override"]) ? "checked" : ""; ?>>
                                    Add to user_texts.json
                                </label>
                            </td>

                            <?php
                            foreach ($supported_languages as $lang):
                                $class = "";
                                if ($error_field == "new_text_entry[$file][langs][$lang]") {
                                    $class = "error_field";
                                }
                                ?>
                                <td><textarea placeholder="new" class="<?php echo ($class); ?>"
                                        name="new_text_entry[<?php echo (htmlspecialchars($file, ENT_QUOTES)); ?>][langs][<?php echo (htmlspecialchars($lang, ENT_QUOTES)); ?>]"
                                        onchange="check_changes(this, true)"><?php echo (htmlspecialchars($request["new_text_entry"][$file]["langs"][$lang] ?? "", ENT_QUOTES)); ?></textarea>
                                </td>
                            <?php endforeach; ?>
                        </tr>

                        <?php
                        $cols = 2 + count($supported_languages);
                        ?>

                        <?php if (count($text_entries) > 0): ?>

                            <tr>
                                <td colspan="<?php echo ($cols); ?>" style="text-align: right"><input tabindex="-1"
                                        type="submit" name="act" value="Apply"></td>
                            </tr>

                            <?php if (!empty($file_text_entries)): ?>

                                <tr>
                                    <th style="font-weight: normal">✘</th>
                                    <th>Code</th>

                                    <?php foreach ($supported_languages as $lang):
                                        $caption = $lang;
                                        if (!empty($language_entries[$lang]["en"])) {
                                            $caption = $language_entries[$lang]["en"] . " [" . $lang . "]";
                                        }
                                        ?>
                                        <th><?php echo (htmlspecialchars($caption, ENT_QUOTES)); ?></th>
                                    <?php endforeach; ?>

                                </tr>

                                <?php
                                $counter = 0;
                                foreach ($file_text_entries as $code => $entry_data):
                                    $original_code = (string) $code;
                                    $user_only = !array_key_exists($code, $master_text_entries[$file]);
                                    $override_enabled = ($request["act"] ?? "") === "Apply"
                                        ? isset($request["override_text_entries"][$file][$counter]["enabled"])
                                        : array_key_exists($code, $user_text_entries[$file]);
                                    $deleted = in_array($original_code, $request["delete_text_entries"][$file] ?? [], true);

                                    if (isset($request["text_entries"][$file][$counter]["code"])) {
                                        $code = $request["text_entries"][$file][$counter]["code"];
                                    }
                                    ?>

                                    <tr style="opacity: <?php echo $deleted ? "0.2" : "1"; ?>">
                                        <td class="text_code"><input tabindex="-1" type="checkbox"
                                                name="delete_text_entries[<?php echo htmlspecialchars($file, ENT_QUOTES); ?>][]"
                                                value="<?php echo htmlspecialchars($original_code, ENT_QUOTES); ?>" title="Click to delete"
                                                <?php echo $deleted ? "checked" : ""; ?>
                                                onchange="check_deleted(this)"></td>
                                        <td class="text_code">

                                            <?php
                                            $class = "";
                                            if ((($request["text_entries"][$file][$counter]["code"] ?? "") === "")) {
                                                $class = "empty_field";
                                            }
                                            if ($error_field == "text_entries[$file][$counter][code]") {
                                                $class = "error_field";
                                            }
                                            ?>

                                            <?php if (isset($request["text_entries"][$file][$counter]["code"])): ?>
                                                <input type="text" class="<?php echo ($class); ?>"
                                                    name="text_entries[<?php echo (htmlspecialchars($file, ENT_QUOTES)); ?>][<?php echo ($counter); ?>][code]"
                                                    value="<?php echo (htmlspecialchars($code, ENT_QUOTES)); ?>"
                                                    onchange="check_changes(this, true)">
                                            <?php else:
                                                $class = "";
                                                if (((string) $code === "")) {
                                                    $class = "empty_field";
                                                }
                                                ?>
                                                <div tabindex="0" class="div_input <?php echo ($class); ?>" title="Click to edit"
                                                    data-content="<?php echo (htmlspecialchars($code, ENT_QUOTES)); ?>" data-editor="input"
                                                    data-field="text_entries[<?php echo (htmlspecialchars($file, ENT_QUOTES)); ?>][<?php echo ($counter); ?>][code]">
                                                    <?php echo (htmlspecialchars($code, ENT_QUOTES)); ?>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($user_only): ?>
                                                <label style="display: block; margin-top: 4px;"><input tabindex="-1" type="checkbox"
                                                        name="move_to_master_text_entries[<?php echo htmlspecialchars($file, ENT_QUOTES); ?>][]"
                                                        value="<?php echo htmlspecialchars($original_code, ENT_QUOTES); ?>" title="Move to texts.json"
                                                        <?php echo in_array($original_code, $request["move_to_master_text_entries"][$file] ?? [], true) ? "checked" : ""; ?>>
                                                    Move to texts.json
                                                </label>
                                            <?php else: ?>
                                                <label style="display: block; margin-top: 4px;"><input tabindex="-1" type="checkbox"
                                                        name="override_text_entries[<?php echo htmlspecialchars($file, ENT_QUOTES); ?>][<?php echo $counter; ?>][enabled]"
                                                        value="1" title="Click to override" onchange="check_override(this)"
                                                        <?php echo $override_enabled ? "checked" : ""; ?>>
                                                    Override in user_texts.json
                                                </label>
                                            <?php endif; ?>
                                        </td>

                                        <?php
                                        foreach ($supported_languages as $lang):
                                            $class = "";
                                            if ((($request["text_entries"][$file][$counter]["langs"][$lang] ?? "") === "")) {
                                                $class = "empty_field";
                                            }
                                            if ($error_field == "text_entries[$file][$counter][langs][$lang]") {
                                                $class = "error_field";
                                            }

                                            ?>
                                            <td>
                                                <?php if (!$user_only): ?>
                                                    <?php if (isset($request["text_entries"][$file][$counter]["langs"][$lang])): ?>
                                                        <textarea class="<?php echo ($class); ?>"
                                                            name="text_entries[<?php echo (htmlspecialchars($file, ENT_QUOTES)); ?>][<?php echo ($counter); ?>][langs][<?php echo (htmlspecialchars($lang, ENT_QUOTES)); ?>]"
                                                            onchange="check_changes(this, true)"><?php echo (htmlspecialchars($request["text_entries"][$file][$counter]["langs"][$lang] ?? "", ENT_QUOTES)); ?></textarea>
                                                    <?php else:
                                                        $class = "";
                                                        if ((($entry_data[$lang] ?? "") === "")) {
                                                            $class = "empty_field";
                                                        }
                                                        ?>
                                                        <div tabindex="0" class="div_textarea <?php echo ($class); ?>" title="Click to edit"
                                                            data-editor="textarea"
                                                            data-field="text_entries[<?php echo (htmlspecialchars($file, ENT_QUOTES)); ?>][<?php echo ($counter); ?>][langs][<?php echo (htmlspecialchars($lang, ENT_QUOTES)); ?>]"
                                                            data-content="<?php echo (htmlspecialchars($entry_data[$lang] ?? "", ENT_QUOTES)); ?>">
                                                            <?php echo_pre_wrap($entry_data[$lang] ?? ""); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php endif; ?>

                                                <?php
                                                $override_value = $request["override_text_entries"][$file][$counter]["langs"][$lang]
                                                    ?? $user_text_entries[$file][$original_code][$lang] ?? "";
                                                $override_class = "overridden_field";

                                                if ($override_value !== ($user_text_entries[$file][$original_code][$lang] ?? "")) {
                                                    $override_class .= " modified_field";
                                                }
                                                ?>
                                                <div class="override_translation" <?php echo ($user_only || $override_enabled) ? "" : "hidden"; ?>>
                                                    <?php if (isset($request["override_text_entries"][$file][$counter]["langs"][$lang])): ?>
                                                        <textarea class="<?php echo $override_class; ?>"
                                                            name="override_text_entries[<?php echo htmlspecialchars($file, ENT_QUOTES); ?>][<?php echo $counter; ?>][langs][<?php echo htmlspecialchars($lang, ENT_QUOTES); ?>]"
                                                            onchange="check_changes(this, true)"><?php echo htmlspecialchars($override_value, ENT_QUOTES); ?></textarea>
                                                    <?php else: ?>
                                                        <div tabindex="0" class="div_textarea overridden_field" title="Click to edit"
                                                            data-editor="textarea"
                                                            data-field="override_text_entries[<?php echo htmlspecialchars($file, ENT_QUOTES); ?>][<?php echo $counter; ?>][langs][<?php echo htmlspecialchars($lang, ENT_QUOTES); ?>]"
                                                            data-content="<?php echo htmlspecialchars($override_value, ENT_QUOTES); ?>"><?php echo_pre_wrap($override_value); ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>

                                    <?php
                                    $counter++;
                                endforeach;
                                ?>

                                <tr>
                                    <td colspan="<?php echo ($cols); ?>" style="text-align: right"><input tabindex="-1"
                                            type="submit" name="act" value="Apply"></td>
                                </tr>
                            <?php endif; ?>

                        <?php endif; ?>

                    </table>

                <?php endforeach; ?>

                <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

            </div>
            <!-- END: texts -->

            <!-- BEGIN: languages -->
            <div id="tab_context_languages" class="tab_content"
                style="display: <?php echo ($active_tab == "languages") ? "block" : "none"; ?>">
                <h1>Language name translations</h1>

                <?php echo ($messages); ?>

                <table class="edit_table">
                    <tr>
                        <th style="font-weight: normal">✘</th>
                        <th>Code</th>

                        <?php foreach ($supported_languages as $lang):
                            $caption = $lang;
                            if (!empty($language_entries[$lang]["en"])) {
                                $caption = $language_entries[$lang]["en"] . " [" . $lang . "]";
                            }
                            ?>
                            <th><?php echo (htmlspecialchars($caption, ENT_QUOTES)); ?></th>
                        <?php endforeach; ?>

                    </tr>

                    <tr>
                        <td></td>
                        <td class="short_code">
                            <?php
                            $class = "";
                            if ($error_field == "new_language_entry[code]") {
                                $class = "error_field";
                            }
                            ?>

                            <input type="text" placeholder="new" class="<?php echo ($class); ?>"
                                name="new_language_entry[code]"
                                value="<?php echo (htmlspecialchars($request["new_language_entry"]["code"] ?? "", ENT_QUOTES)); ?>"
                                onchange="check_changes(this, true)">
                        </td>

                        <?php
                        foreach ($supported_languages as $lang):
                            $class = "";
                            if ($error_field == "new_language_entry[langs][$lang]") {
                                $class = "error_field";
                            }
                            ?>
                            <td><input type="text" placeholder="new" class="<?php echo ($class); ?>"
                                    name="new_language_entry[langs][<?php echo (htmlspecialchars($lang, ENT_QUOTES)); ?>]"
                                    onchange="check_changes(this, true)"
                                    value="<?php echo (htmlspecialchars($request["new_language_entry"]["langs"][$lang] ?? "", ENT_QUOTES)); ?>">
                            </td>
                        <?php endforeach; ?>
                    </tr>

                    <?php
                    $cols = 2 + count($supported_languages);
                    ?>

                    <?php if (count($language_entries) > 0): ?>

                        <tr>
                            <td colspan="<?php echo ($cols); ?>" style="text-align: right"><input tabindex="-1"
                                    type="submit" name="act" value="Apply"></td>
                        </tr>

                        <?php
                        $counter = 0;
                        foreach ($language_entries as $code => $entry_data):
                            $original_code = (string) $code;
                            $deleted = in_array($original_code, $request["delete_language_entries"] ?? [], true);

                            if (isset($request["language_entries"][$counter]["code"])) {
                                $code = $request["language_entries"][$counter]["code"];
                            }
                            ?>

                            <tr style="opacity: <?php echo $deleted ? "0.2" : "1"; ?>">
                                <td><input tabindex="-1" type="checkbox" name="delete_language_entries[]"
                                        value="<?php echo htmlspecialchars($original_code, ENT_QUOTES); ?>" title="Click to delete"
                                        <?php echo $deleted ? "checked" : ""; ?>
                                        onchange="check_deleted(this)"></td>
                                <td class="short_code">
                                    <?php
                                    $class = "";
                                    if ((($request["language_entries"][$counter]["code"] ?? "") === "")) {
                                        $class = "empty_field";
                                    }
                                    if ($error_field == "language_entries[$counter][code]") {
                                        $class = "error_field";
                                    }
                                    ?>

                                    <?php if (isset($request["language_entries"][$counter]["code"])): ?>
                                        <input type="text" class="<?php echo ($class); ?>"
                                            name="language_entries[<?php echo ($counter); ?>][code]"
                                            value="<?php echo (htmlspecialchars($request["language_entries"][$counter]["code"] ?? "", ENT_QUOTES)); ?>"
                                            onchange="check_changes(this, true)">
                                    <?php else:
                                        $class = "";
                                        if (((string) $code === "")) {
                                            $class = "empty_field";
                                        }
                                        ?>
                                        <div tabindex="0" class="div_input <?php echo ($class); ?>" title="Click to edit"
                                            data-content="<?php echo (htmlspecialchars($code, ENT_QUOTES)); ?>" data-editor="input"
                                            data-field="language_entries[<?php echo ($counter); ?>][code]">
                                            <?php echo (htmlspecialchars($code, ENT_QUOTES)); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <?php
                                foreach ($supported_languages as $lang):
                                    $class = "";
                                    if ((($request["language_entries"][$counter]["langs"][$lang] ?? "") === "")) {
                                        $class = "empty_field";
                                    }
                                    if ($error_field == "language_entries[$counter][langs][$lang]") {
                                        $class = "error_field";
                                    }
                                    ?>
                                    <td>
                                        <?php if (isset($request["language_entries"][$counter]["langs"][$lang])): ?>
                                            <input type="text" class="<?php echo ($class); ?>"
                                                name="language_entries[<?php echo ($counter); ?>][langs][<?php echo (htmlspecialchars($lang, ENT_QUOTES)); ?>]"
                                                onchange="check_changes(this, true)"
                                                value="<?php echo (htmlspecialchars($request["language_entries"][$counter]["langs"][$lang] ?? "", ENT_QUOTES)); ?>">
                                        <?php else:
                                            $class = "";
                                            if ((($entry_data[$lang] ?? "") === "")) {
                                                $class = "empty_field";
                                            }
                                            ?>
                                            <div tabindex="0" class="div_input <?php echo ($class); ?>" title="Click to edit"
                                                data-content="<?php echo (htmlspecialchars($entry_data[$lang] ?? "", ENT_QUOTES)); ?>"
                                                data-editor="input"
                                                data-field="language_entries[<?php echo ($counter); ?>][langs][<?php echo (htmlspecialchars($lang, ENT_QUOTES)); ?>]">
                                                <?php echo (htmlspecialchars($entry_data[$lang] ?? "", ENT_QUOTES)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>

                            <?php
                            $counter++;
                        endforeach;
                        ?>

                    <?php endif; ?>

                    <tr>
                        <td colspan="<?php echo ($cols); ?>" style="text-align: right"><input tabindex="-1"
                                type="submit" name="act" value="Apply"></td>
                    </tr>

                </table>

                <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

            </div>
            <!-- END: languages -->

            <!-- BEGIN: countries -->
            <div id="tab_context_countries" class="tab_content"
                style="display: <?php echo ($active_tab == "countries") ? "block" : "none"; ?>">
                <h1>Country name translations</h1>

                <?php echo ($messages); ?>

                <table class="edit_table">
                    <tr>
                        <th style="font-weight: normal">✘</th>
                        <th>Code</th>

                        <?php foreach ($supported_languages as $lang):
                            $caption = $lang;
                            if (!empty($language_entries[$lang]["en"])) {
                                $caption = $language_entries[$lang]["en"] . " [" . $lang . "]";
                            }
                            ?>
                            <th><?php echo (htmlspecialchars($caption, ENT_QUOTES)); ?></th>
                        <?php endforeach; ?>

                    </tr>

                    <tr>
                        <td></td>
                        <td class="short_code">
                            <?php
                            $class = "";
                            if ($error_field == "new_country_entry[code]") {
                                $class = "error_field";
                            }
                            ?>
                            <input type="text" placeholder="new" class="<?php echo ($class); ?>"
                                name="new_country_entry[code]"
                                value="<?php echo (htmlspecialchars($request["new_country_entry"]["code"] ?? "", ENT_QUOTES)); ?>"
                                onchange="check_changes(this, true)">
                        </td>

                        <?php
                        foreach ($supported_languages as $lang):
                            $class = "";
                            if ($error_field == "new_country_entry[langs][$lang]") {
                                $class = "error_field";
                            }
                            ?>
                            <td><input type="text" placeholder="new" class="<?php echo ($class); ?>"
                                    name="new_country_entry[langs][<?php echo (htmlspecialchars($lang, ENT_QUOTES)); ?>]"
                                    onchange="check_changes(this, true)"
                                    value="<?php echo (htmlspecialchars($request["new_country_entry"]["langs"][$lang] ?? "", ENT_QUOTES)); ?>">
                            </td>
                        <?php endforeach; ?>
                    </tr>

                    <?php
                    $cols = 2 + count($supported_languages);
                    ?>

                    <?php if (count($country_entries) > 0): ?>

                        <tr>
                            <td colspan="<?php echo ($cols); ?>" style="text-align: right"><input tabindex="-1"
                                    type="submit" name="act" value="Apply"></td>
                        </tr>

                        <?php
                        $counter = 0;
                        foreach ($country_entries as $code => $entry_data):
                            $original_code = (string) $code;
                            $deleted = in_array($original_code, $request["delete_country_entries"] ?? [], true);

                            if (isset($request["country_entries"][$counter]["code"])) {
                                $code = $request["country_entries"][$counter]["code"];
                            }
                            ?>

                            <tr style="opacity: <?php echo $deleted ? "0.2" : "1"; ?>">
                                <td><input tabindex="-1" type="checkbox" name="delete_country_entries[]"
                                        value="<?php echo htmlspecialchars($original_code, ENT_QUOTES); ?>" title="Click to delete"
                                        <?php echo $deleted ? "checked" : ""; ?>
                                        onchange="check_deleted(this)"></td>
                                <td class="short_code">
                                    <?php
                                    $class = "";
                                    if ((($request["country_entries"][$counter]["code"] ?? "") === "")) {
                                        $class = "empty_field";
                                    }
                                    if ($error_field == "country_entries[$counter][code]") {
                                        $class = "error_field";
                                    }
                                    ?>

                                    <?php if (isset($request["country_entries"][$counter]["code"])): ?>
                                        <input type="text" class="<?php echo ($class); ?>"
                                            name="country_entries[<?php echo ($counter); ?>][code]"
                                            value="<?php echo (htmlspecialchars($request["country_entries"][$counter]["code"] ?? "", ENT_QUOTES)); ?>"
                                            onchange="check_changes(this, true)">
                                    <?php else:
                                        $class = "";
                                        if (((string) $code === "")) {
                                            $class = "empty_field";
                                        }
                                        ?>
                                        <div tabindex="0" class="div_input <?php echo ($class); ?>" title="Click to edit"
                                            data-content="<?php echo (htmlspecialchars($code, ENT_QUOTES)); ?>" data-editor="input"
                                            data-field="country_entries[<?php echo ($counter); ?>][code]">
                                            <?php echo (htmlspecialchars($code, ENT_QUOTES)); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <?php
                                foreach ($supported_languages as $lang):
                                    $class = "";
                                    if ((($request["country_entries"][$counter]["langs"][$lang] ?? "") === "")) {
                                        $class = "empty_field";
                                    }
                                    if ($error_field == "country_entries[$counter][code]") {
                                        $class = "error_field";
                                    }
                                    ?>
                                    <td>
                                        <?php if (isset($request["country_entries"][$counter]["langs"][$lang])): ?>
                                            <input type="text" class="<?php echo ($class); ?>"
                                                name="country_entries[<?php echo ($counter); ?>][langs][<?php echo (htmlspecialchars($lang, ENT_QUOTES)); ?>]"
                                                onchange="check_changes(this, true)"
                                                value="<?php echo (htmlspecialchars($request["country_entries"][$counter]["langs"][$lang] ?? "", ENT_QUOTES)); ?>">
                                        <?php else:
                                            $class = "";
                                            if ((($entry_data[$lang] ?? "") === "")) {
                                                $class = "empty_field";
                                            }
                                            ?>
                                            <div tabindex="0" class="div_input <?php echo ($class); ?>" title="Click to edit"
                                                data-content="<?php echo (htmlspecialchars($entry_data[$lang] ?? "", ENT_QUOTES)); ?>"
                                                data-editor="input"
                                                data-field="country_entries[<?php echo ($counter); ?>][langs][<?php echo (htmlspecialchars($lang, ENT_QUOTES)); ?>]">
                                                <?php echo (htmlspecialchars($entry_data[$lang] ?? "", ENT_QUOTES)); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>

                            <?php
                            $counter++;
                        endforeach;
                        ?>

                    <?php endif; ?>

                    <tr>
                        <td colspan="<?php echo ($cols); ?>" style="text-align: right"><input tabindex="-1"
                                type="submit" name="act" value="Apply"></td>
                    </tr>

                </table>

                <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

            </div>
            <!-- END: countries -->

            <!-- BEGIN: configuration -->
            <div id="tab_context_configuration" class="tab_content"
                style="display: <?php echo ($active_tab == "configuration") ? "block" : "none"; ?>">

                <h1>Supported languages</h1>

                <?php echo ($messages); ?>

                <table class="edit_table">
                    <tr>
                        <th style="font-weight: normal">✘</th>
                        <th>Code</th>
                        <th>Language</th>
                    </tr>

                    <?php if (count($supported_languages) > 0): ?>

                        <?php foreach ($supported_languages as $lang):
                            $caption = $lang;
                            if (!empty($language_entries[$lang]["en"])) {
                                $caption = $language_entries[$lang]["en"] . " [" . $lang . "]";
                            }
                            ?>

                            <tr>
                                <td><?php if ($lang != "en"): ?><input tabindex="-1" type="checkbox"
                                            name="delete_supported_languages[]"
                                            value="<?php echo (htmlspecialchars($lang, ENT_QUOTES)); ?>" title="Click to delete"
                                            <?php echo in_array($lang, $request["delete_supported_languages"] ?? [], true) ? "checked" : ""; ?>
                                            onchange="check_deleted(this)"><?php endif; ?></td>
                                <td><?php echo (htmlspecialchars($lang, ENT_QUOTES)); ?></td>
                                <td><?php echo (htmlspecialchars($caption, ENT_QUOTES)); ?></td>
                            </tr>

                        <?php endforeach; ?>

                    <?php endif; ?>

                    <tr>
                        <td></td>
                        <td><input type="text" class="<?php error_class("new_language[code]"); ?>" style="width:60px"
                                name="new_language[code]"
                                value="<?php echo (htmlspecialchars($request["new_language"]["code"] ?? "", ENT_QUOTES)); ?>"
                                placeholder="new"></td>
                        <td>
                            <select name="new_language[copy_source]">
                                <option value="">-- copy from --</option>
                                <?php foreach ($supported_languages as $lang):
                                    $caption = $lang;
                                    if (!empty($language_entries[$lang]["en"])) {
                                        $caption = $language_entries[$lang]["en"] . " [" . $lang . "]";
                                    }

                                    $selected = (($request["new_language"]["copy_source"] ?? "") == $lang) ? "selected" : "";
                                    ?>
                                    <option value="<?php echo (htmlspecialchars($lang, ENT_QUOTES)); ?>" <?php echo ($selected); ?>><?php echo (htmlspecialchars($caption, ENT_QUOTES)); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td colspan="3" style="text-align: right"><input type="submit" name="act" value="Apply"></td>
                    </tr>

                </table>

                <br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br><br>

            </div>
            <!-- END: configuration -->

            <!-- BEGIN: check -->
            <div id="tab_context_check" class="tab_content"
                style="display: <?php echo ($active_tab == "check") ? "block" : "none"; ?>">

                <?php
                //-----------------------------------------------------------------
                /**
                 * Scans PHP source for literal text identifiers and event message codes.
                 *
                 * Updates the global used-entry and missing-definition indexes. The
                 * regular expressions inspect source text, including comments, and do
                 * not resolve dynamically constructed identifiers.
                 *
                 * @param string $file
                 * The path to the PHP source file to inspect.
                 *
                 * @return bool
                 * Returns true after scanning, including for an empty file, or false
                 * if the file cannot be read; read errors are also output as escaped HTML.
                 *
                 * @author Oleg Schildt
                 */
                function process_php_file(string $file): bool
                {
                    global $found_entries;
                    global $flat_text_entries;
                    global $missing_definitions;

                    $contents = @file_get_contents($file);

                    if ($contents === false) {
                        echo localization_error_html("File '$file' cannot be read.");

                        return false;
                    }

                    // Match literal first arguments passed to text(), in either quote style.
                    if (
                        preg_match_all(
                            "/[^a-zA-Z0-9_]text\\(\\s*(\"([^\"]+)\"|'([^']+)')/",
                            $contents,
                            $matches,
                            PREG_SET_ORDER
                        )
                    ) {
                        foreach ($matches as $match) {
                            $entry = "";

                            if (($match[2] ?? "") !== "") {
                                $entry = $match[2];
                            }

                            if (($match[3] ?? "") !== "") {
                                $entry = $match[3];
                            }

                            if ($entry === "") {
                                continue;
                            }

                            $found_entries[$entry] = $entry;

                            if (!array_key_exists($entry, $flat_text_entries)) {
                                $missing_definitions[$entry][$file] = $file;
                            }
                        }
                    }

                    // Event message identifiers are supplied as the second logEvent() argument.
                    if (
                        preg_match_all(
                            "/logEvent\\([^,]+?,\s*(\"([^\"]+?)\"|'([^']+?)').*?\\)/",
                            $contents,
                            $matches,
                            PREG_SET_ORDER
                        )
                    ) {
                        foreach ($matches as $match) {
                            $entry = "";

                            if (($match[2] ?? "") !== "") {
                                $entry = $match[2];
                            }

                            if (($match[3] ?? "") !== "") {
                                $entry = $match[3];
                            }

                            if ($entry === "") {
                                continue;
                            }

                            $found_entries[$entry] = $entry;

                            if (!array_key_exists($entry, $flat_text_entries)) {
                                $missing_definitions[$entry][$file] = $file;
                            }
                        }
                    }

                    return true;
                } // process_php_file
                //-----------------------------------------------------------------
                /**
                 * Scans application, source, and module directories for text usage.
                 *
                 * @return bool
                 * Returns true if all directory scans complete, or false if a directory
                 * or PHP source file cannot be read.
                 *
                 * @author Oleg Schildt
                 */
                function process_application(): bool
                {
                    $dir = "../application/";

                    if (!process_dir($dir)) {
                        return false;
                    }

                    $dir = "../src/";

                    if (!process_dir($dir)) {
                        return false;
                    }

                    // Modules are optional; application and source directories are always scanned.
                    $modules_dir = "../modules/";

                    if (file_exists($modules_dir)) {
                        $modules = @scandir($modules_dir);

                        if ($modules === false) {
                            echo localization_error_html("The modules directory cannot be read.");

                            return false;
                        }

                        foreach ($modules as $module) {
                            if ($module == "." || $module == ".." || !is_dir($modules_dir . $module)) {
                                continue;
                            }

                            $module_dir = $modules_dir . $module . "/";

                            if (!process_dir($module_dir)) {
                                return false;
                            }
                        }
                    }

                    return true;
                } // process_application
                //-----------------------------------------------------------------
                /**
                 * Recursively scans a directory's PHP files for translation identifiers.
                 *
                 * Increments the global file counter for each PHP file attempted.
                 *
                 * @param string $dir
                 * The existing readable directory path, including its trailing slash.
                 *
                 * @return bool
                 * Returns true if all files are processed, or false if this directory,
                 * a nested directory, or a PHP source file cannot be read.
                 *
                 * @author Oleg Schildt
                 */
                function process_dir(string $dir): bool
                {
                    global $files_processed;

                    $files = @scandir($dir);

                    if ($files === false) {
                        echo localization_error_html("Directory '$dir' cannot be read.");

                        return false;
                    }

                    foreach ($files as $file) {
                        if ($file == "." || $file == "..") {
                            continue;
                        }

                        if (is_dir($dir . $file)) {
                            if (!process_dir($dir . $file . "/")) {
                                return false;
                            }

                            continue;
                        }

                        // Inspect only PHP source; translation catalogs and other assets are skipped.
                        $path_parts = pathinfo($dir . $file);

                        if (!empty($path_parts['extension']) && $path_parts['extension'] == "php") {
                            $files_processed++;

                            if (!process_php_file($dir . $file)) {
                                return false;
                            }
                        }
                    }

                    return true;
                } // process_dir
                //-----------------------------------------------------------------

                $success = false;

                if ($load_result && $active_tab == "check") {
                    echo "<h1>Check results</h1>";
                    echo "<p>&nbsp;</p>";

                    if (process_application()) {
                        echo "Supported languages: " . implode(", ", $supported_languages) . "<br>";
                        echo "Entries found: " . count($flat_text_entries) . "<br>";
                        echo "Files processed: " . $files_processed . "<br>";

                        $success = true;

                        if (!empty($incomplete_text_translations)) {
                            $success = false;

                            echo ("<p style='color:maroon; font-weight: bold'>Incomplete text translations found!</p>");

                            foreach ($incomplete_text_translations as $text => $langs) {
                                echo ("<b>" . htmlspecialchars((string) $text, ENT_QUOTES) . ":</b> "
                                    . htmlspecialchars(implode(", ", $langs), ENT_QUOTES) . "<br>");
                            }
                        }

                        if (!empty($incomplete_language_name_translations)) {
                            $success = false;

                            echo ("<p style='color:maroon; font-weight: bold'>Incomplete language name translations found!</p>");

                            foreach ($incomplete_language_name_translations as $text => $langs) {
                                echo ("<b>" . htmlspecialchars((string) $text, ENT_QUOTES) . ":</b> "
                                    . htmlspecialchars(implode(", ", $langs), ENT_QUOTES) . "<br>");
                            }
                        }

                        if (!empty($incomplete_country_name_translations)) {
                            $success = false;

                            echo ("<p style='color:maroon; font-weight: bold'>Incomplete country name translations found!</p>");

                            foreach ($incomplete_country_name_translations as $text => $langs) {
                                echo ("<b>" . htmlspecialchars((string) $text, ENT_QUOTES) . ":</b> "
                                    . htmlspecialchars(implode(", ", $langs), ENT_QUOTES) . "<br>");
                            }
                        }

                        if (!empty($missing_definitions)) {
                            $success = false;

                            echo ("<p style='color:maroon; font-weight: bold'>Missing text definitions found!</p>");

                            foreach ($missing_definitions as $text => $files) {
                                echo ("<b>" . htmlspecialchars((string) $text, ENT_QUOTES) . ":</b> "
                                    . htmlspecialchars(implode(", ", $files), ENT_QUOTES) . "<br>");
                            }
                        }

                        if (!empty($duplicate_definitions)) {
                            $success = false;

                            echo ("<p style='color:maroon; font-weight: bold'>Duplicate definitions found!</p>");

                            foreach ($duplicate_definitions as $text => $files) {
                                echo ("<b>" . htmlspecialchars((string) $text, ENT_QUOTES) . ":</b><br><br>");

                                foreach ($files as $file) {
                                    echo (htmlspecialchars($file, ENT_QUOTES) . "<br>");
                                }
                            }
                        }

                        $never_used = array_diff(array_keys($flat_text_entries), $found_entries);
                        if (!empty($never_used)) {
                            $success = false;

                            echo ("<p style='color:maroon; font-weight: bold'>These text definitions are never used!</p>");

                            foreach ($never_used as $text) {
                                echo ("<b>" . htmlspecialchars((string) $text, ENT_QUOTES) . ":</b> "
                                    . htmlspecialchars($entry_files[$text], ENT_QUOTES) . "<br>");
                            }
                        }
                    }
                }

                if ($success) {
                    echo ("<p style='color:green; font-weight: bold'>All is fine!</p>");
                }
                ?>
            </div>
            <!-- END: check -->

        </div>

    </form>

    <div class="_sys_load_lightbox" id="sys_progress_indicator">
        <div>
            <div>
                <div class="progress_caption">
                    Saving data ...
                </div>
                <div id="loading_indicator">
                    <img alt="Loading indicator"
                        src="data:image/gif;base64,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">
                </div>
            </div>
        </div>
    </div>

</body>

</html>
