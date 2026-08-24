<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/db.php';

try {
    // 1. Check active connection
    $stmt = $pdo->query("SELECT version();");
    $version = $stmt->fetchColumn();

    // 2. Fetch employee rows
    $empStmt = $pdo->query("SELECT * FROM employees ORDER BY id ASC;");
    $employees = $empStmt->fetchAll();

    echo "<h2>✅ Database Connected Successfully!</h2>";
    echo "<p><strong>PostgreSQL Version:</strong> " . htmlspecialchars($version) . "</p>";
    echo "<h3>Employees in Supabase (" . count($employees) . "):</h3>";
    echo "<pre>" . print_r($employees, true) . "</pre>";

} catch (Exception $e) {
    echo "<h2>❌ Connection Failed</h2>";
    echo "<p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
}