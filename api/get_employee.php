<?php
require_once __DIR__ . '/../auth.php';
require_login_api();

// api/get_employee.php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';

$id = $_GET['id'] ?? null;
$nric = $_GET['nric'] ?? null;
$month = $_GET['month'] ?? null;

if (!$id && (!$nric || !$month)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing identifier (id or nric + month)."]);
    exit;
}

try {
    if ($id) {
        $stmt = $pdo->prepare("SELECT * FROM payslips WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
    } else {
        $stmt = $pdo->prepare("SELECT * FROM payslips WHERE nric = :nric AND salary_month = :month LIMIT 1");
        $stmt->execute([':nric' => $nric, ':month' => $month]);
    }

    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$employee) {
        http_response_code(404);
        echo json_encode(["status" => "error", "message" => "Payslip record not found."]);
        exit;
    }

    echo json_encode($employee);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}