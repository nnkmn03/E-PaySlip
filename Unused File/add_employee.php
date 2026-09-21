<?php
header('Content-Type: application/json');
$pdo = require __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
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
    echo json_encode(['success' => false, 'error' => 'Employee name is required']);
    exit;
}

try {
    $stmt = $pdo->prepare('
        INSERT INTO employees (name, ic_number, position, bank_account, net_salary, epf_socso_enabled)
        VALUES (?, ?, ?, ?, ?, ?)
        RETURNING id
    ');
    $stmt->execute([$name, $icNumber, $position, $bankAccount, $netSalary, $epfSocsoEnabled]);
    $newId = $stmt->fetchColumn();

    echo json_encode([
        'success' => true,
        'id'      => $newId,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Insert failed: ' . $e->getMessage(),
    ]);
}