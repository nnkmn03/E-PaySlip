<?php
header('Content-Type: application/json');
$pdo = require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$name             = trim($_POST['name'] ?? '');
$icNumber         = trim($_POST['ic_number'] ?? '');
$position         = trim($_POST['position'] ?? '');
$bankAccount      = trim($_POST['bank_account'] ?? '');
$netSalary        = (float) ($_POST['net_salary'] ?? 0);
$epfSocsoEnabled  = isset($_POST['epf_socso_enabled']) && $_POST['epf_socso_enabled'] === '1' ? 1 : 0;

if ($name === '') {
    http_response_code(422);
    echo json_encode(['error' => 'Employee name is required']);
    exit;
}

$stmt = $pdo->prepare('
    INSERT INTO employees (name, ic_number, position, bank_account, net_salary, epf_socso_enabled)
    VALUES (?, ?, ?, ?, ?, ?)
');
$stmt->execute([$name, $icNumber, $position, $bankAccount, $netSalary, $epfSocsoEnabled]);

echo json_encode([
    'success' => true,
    'id'      => $pdo->lastInsertId(),
]);
