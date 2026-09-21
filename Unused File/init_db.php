<?php
/**
 * init_db.php
 * ---------------------------------------------------------------
 * Optional: visit this file once in your browser
 * (http://localhost/e-payslip/init_db.php) to confirm the SQLite
 * database and table were created successfully.
 *
 * You don't strictly need to run this manually — db.php auto-creates
 * everything the first time any page connects to the database.
 * ---------------------------------------------------------------
 */

$pdo = require __DIR__ . '/db.php';

$tableExists = $pdo->query("
    SELECT name FROM sqlite_master WHERE type='table' AND name='employees'
")->fetch();

if ($tableExists) {
    echo "✅ Database initialized successfully at: database/epayslip.sqlite<br>";
    echo "✅ Table 'employees' is ready.";
} else {
    echo "❌ Something went wrong — the 'employees' table was not created.";
}
