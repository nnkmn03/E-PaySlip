<?php
/**
 * auth.php
 * ---------------------------------------------------------------
 * Single shared HR login for the whole app. One row lives in the
 * `hr_credentials` table (see hr_login.sql), password stored
 * hashed -- never in plain text.
 *
 * To change the password later:
 *   1. php hash_password.php "yourNewPassword"
 *   2. Paste the UPDATE statement it prints into phpMyAdmin's SQL tab
 * ---------------------------------------------------------------
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function is_logged_in(): bool
{
    return !empty($_SESSION['hr_authenticated']);
}

/** Call at the very top of any full HTML page that requires login. */
function require_login_page(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/** Call at the very top of any api/*.php endpoint that requires login. */
function require_login_api(): void
{
    if (!is_logged_in()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Not logged in']);
        exit;
    }
}
