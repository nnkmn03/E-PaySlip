<?php
header('Content-Type: application/json');
$pdo = require __DIR__ . '/../db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid employee id']);
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM employees WHERE id = ?');
$stmt->execute([$id]);
$employee = $stmt->fetch();

if (!$employee) {
    http_response_code(404);
    echo json_encode(['error' => 'Employee not found']);
    exit;
}

echo json_encode($employee);
