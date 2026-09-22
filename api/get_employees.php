<?php
require_once __DIR__ . '/../auth.php';
require_login_api();

// api/get_employees.php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';

$month = $_GET['month'] ?? '';

if (!empty($month)) {
    $stmt = $pdo->prepare("SELECT id, employee_name, nric, position, salary_month FROM payslips WHERE salary_month = :month ORDER BY employee_name ASC");
    $stmt->execute([':month' => $month]);
} else {
    $stmt = $pdo->query("SELECT id, employee_name, nric, position, salary_month FROM payslips ORDER BY id DESC LIMIT 50");
}

echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));