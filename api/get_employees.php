<?php
header('Content-Type: application/json');
$pdo = require __DIR__ . '/../db.php';

$stmt = $pdo->query('SELECT id, name FROM employees ORDER BY name ASC');
$employees = $stmt->fetchAll();

echo json_encode($employees);
