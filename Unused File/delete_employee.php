<?php
header('Content-Type: application/json');
$pdo = require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$id = (int) ($_POST['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid employee id']);
    exit;
}

$stmt = $pdo->prepare('DELETE FROM employees WHERE id = ?');
$stmt->execute([$id]);

echo json_encode(['success' => true]);
