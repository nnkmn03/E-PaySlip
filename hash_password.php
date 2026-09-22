<?php
/**
 * hash_password.php
 * ---------------------------------------------------------------
 * CLI-only helper for changing the shared HR login password.
 *
 * Usage (in Laragon's Terminal, from the project folder):
 *   php hash_password.php "yourNewPassword"
 *
 * It prints a ready-to-paste SQL UPDATE statement -- run that in
 * phpMyAdmin's SQL tab against the epayslip database to apply it.
 * Never runs from a browser -- it refuses if accessed that way.
 * ---------------------------------------------------------------
 */

if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('This script can only be run from the command line, not a browser.');
}

$password = $argv[1] ?? null;

if (!$password) {
    echo "Usage: php hash_password.php \"yourNewPassword\"\n";
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);

echo "\nNew password hash:\n{$hash}\n\n";
echo "Run this in phpMyAdmin's SQL tab (epayslip database) to apply it:\n\n";
echo "UPDATE hr_credentials SET password_hash = '{$hash}' WHERE username = 'hr';\n\n";
