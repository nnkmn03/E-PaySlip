<?php
require_once __DIR__ . '/../auth.php';
require_login_api();

// api/upload_payroll_excel.php
header('Content-Type: application/json');

// Report any hidden PHP errors
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../db.php';
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Bootstrap error: " . $e->getMessage()]);
    exit;
}

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["status" => "error", "message" => "Method Not Allowed"]);
    exit;
}

if (!isset($_FILES['payroll_file']) || $_FILES['payroll_file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    $uploadErr = $_FILES['payroll_file']['error'] ?? 'No file';
    echo json_encode(["status" => "error", "message" => "Excel upload failed (Code: {$uploadErr})"]);
    exit;
}

$salaryMonth = trim($_POST['salary_month'] ?? '');
$rawPaymentDate = trim($_POST['payment_date'] ?? '');

// Robust date parsing (converts 01-Nov-2026 or 2026-11-01 to standard SQL YYYY-MM-DD)
if (!empty($rawPaymentDate)) {
    $timestamp = strtotime($rawPaymentDate);
    $paymentDate = $timestamp ? date('Y-m-d', $timestamp) : date('Y-m-d');
} else {
    $paymentDate = date('Y-m-d');
}

if (empty($salaryMonth)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Salary month is required (e.g., 'October 2026')."]);
    exit;
}

$filePath = $_FILES['payroll_file']['tmp_name'];

try {
    $spreadsheet = IOFactory::load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray(null, true, false, false);

    if (count($rows) <= 1) {
        echo json_encode(["status" => "error", "message" => "The uploaded spreadsheet contains no data rows."]);
        exit;
    }

    $sql = "
    INSERT INTO payslips (
        employee_name, nric, position, bank_account_no, salary_month, payment_date,
        basic_salary, overtime, others, total_gross_pay,
        deduction_epf, deduction_socso, deduction_socso_lindung_24jam, hpcs_staff_loan, total_deductions,
        net_pay, employer_epf, employer_socso, employer_eis, total_employer_contributions
    ) VALUES (
        :name, :nric, :pos, :bank, :month, :pdate,
        :basic, :ot, :others, :gross,
        :d_epf, :d_socso, :d_lindung, :loan, :tot_ded,
        :net, :e_epf, :e_socso, :e_eis, :tot_empl
    ) ON DUPLICATE KEY UPDATE
        employee_name = VALUES(employee_name),
        position = VALUES(position),
        bank_account_no = VALUES(bank_account_no),
        payment_date = VALUES(payment_date),
        basic_salary = VALUES(basic_salary),
        overtime = VALUES(overtime),
        others = VALUES(others),
        total_gross_pay = VALUES(total_gross_pay),
        deduction_epf = VALUES(deduction_epf),
        deduction_socso = VALUES(deduction_socso),
        deduction_socso_lindung_24jam = VALUES(deduction_socso_lindung_24jam),
        hpcs_staff_loan = VALUES(hpcs_staff_loan),
        total_deductions = VALUES(total_deductions),
        net_pay = VALUES(net_pay),
        employer_epf = VALUES(employer_epf),
        employer_socso = VALUES(employer_socso),
        employer_eis = VALUES(employer_eis),
        total_employer_contributions = VALUES(total_employer_contributions);
    ";

    $pdo->beginTransaction();
    $stmt = $pdo->prepare($sql);
    $insertedCount = 0;

    for ($i = 1; $i < count($rows); $i++) {
        $r = $rows[$i];

        $name = trim($r[0] ?? '');
        $nric = trim($r[1] ?? '');
        if (empty($name) || empty($nric)) continue;

        $pos = trim($r[2] ?? '');
        $bank = trim($r[3] ?? '');

        $basic = floatval(str_replace(',', '', $r[4] ?? 0));
        $ot = floatval(str_replace(',', '', $r[5] ?? 0));
        $others = floatval(str_replace(',', '', $r[6] ?? 0));
        $gross = $basic + $ot + $others;

        $d_epf = floatval(str_replace(',', '', $r[7] ?? 0));
        $d_socso = floatval(str_replace(',', '', $r[8] ?? 0));
        $d_lindung = floatval(str_replace(',', '', $r[9] ?? 0));
        $loan = floatval(str_replace(',', '', $r[10] ?? 0));
        $tot_ded = $d_epf + $d_socso + $d_lindung + $loan;

        $net = $gross - $tot_ded;

        $e_epf = floatval(str_replace(',', '', $r[11] ?? 0));
        $e_socso = floatval(str_replace(',', '', $r[12] ?? 0));
        $e_eis = floatval(str_replace(',', '', $r[13] ?? 0));
        $tot_empl = $e_epf + $e_socso + $e_eis;

        $stmt->execute([
            ':name' => $name,
            ':nric' => $nric,
            ':pos' => $pos,
            ':bank' => $bank,
            ':month' => $salaryMonth,
            ':pdate' => $paymentDate,
            ':basic' => $basic,
            ':ot' => $ot,
            ':others' => $others,
            ':gross' => $gross,
            ':d_epf' => $d_epf,
            ':d_socso' => $d_socso,
            ':d_lindung' => $d_lindung,
            ':loan' => $loan,
            ':tot_ded' => $tot_ded,
            ':net' => $net,
            ':e_epf' => $e_epf,
            ':e_socso' => $e_socso,
            ':e_eis' => $e_eis,
            ':tot_empl' => $tot_empl
        ]);
        $insertedCount++;
    }

    $pdo->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Successfully processed {$insertedCount} employee payslip(s) for {$salaryMonth}."
    ]);

} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Excel processing error: " . $e->getMessage()]);
}