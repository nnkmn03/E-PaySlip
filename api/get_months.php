<?php
// api/get_months.php
header('Content-Type: application/json');
require_once __DIR__ . '/../db.php';

try {
    $stmt = $pdo->query("
        SELECT salary_month 
        FROM payslips 
        GROUP BY salary_month 
        ORDER BY MAX(created_at) DESC
    ");
    $months = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(["status" => "success", "months" => $months]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}