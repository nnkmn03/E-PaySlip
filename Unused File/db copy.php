<?php
/**
 * db.php
 * ---------------------------------------------------------------
 * Central SQLite connection helper.
 * - Auto-creates /database/epayslip.sqlite on first run.
 * - Auto-creates the `employees` table if it doesn't exist yet.
 * - Auto-migrates older databases by adding any missing columns,
 *   so existing installs don't need to delete their .sqlite file.
 * - Every other script just does:  $pdo = require __DIR__ . '/db.php';
 * ---------------------------------------------------------------
 */

$dbDir  = __DIR__ . '/database';
$dbPath = $dbDir . '/epayslip.sqlite';

if (!is_dir($dbDir)) {
    mkdir($dbDir, 0777, true);
}

$isNewDatabase = !file_exists($dbPath);

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON');
} catch (PDOException $e) {
    http_response_code(500);
    die('Database connection failed: ' . $e->getMessage());
}

if ($isNewDatabase) {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS employees (
            id                  INTEGER PRIMARY KEY AUTOINCREMENT,
            name                TEXT NOT NULL,
            ic_number           TEXT,
            position            TEXT,
            bank_account        TEXT,
            net_salary          REAL DEFAULT 0,
            epf_socso_enabled   INTEGER NOT NULL DEFAULT 1,
            created_at          TEXT DEFAULT CURRENT_TIMESTAMP
        )
    ");
} else {
    // Migration: add any columns that older installs don't have yet.
    $existingColumns = array_column(
        $pdo->query("PRAGMA table_info(employees)")->fetchAll(),
        'name'
    );

    $requiredColumns = [
        'position'          => "ALTER TABLE employees ADD COLUMN position TEXT",
        'epf_socso_enabled' => "ALTER TABLE employees ADD COLUMN epf_socso_enabled INTEGER NOT NULL DEFAULT 1",
    ];

    foreach ($requiredColumns as $column => $alterSql) {
        if (!in_array($column, $existingColumns, true)) {
            $pdo->exec($alterSql);
        }
    }
}

return $pdo;
