<?php
/**
 * generate.php
 * ---------------------------------------------------------------
 * Loads template.xlsx, injects form data into fixed cells,
 * and delivers clean binary output for Excel (.xlsx) or PDF (.pdf).
 * ---------------------------------------------------------------
 */

// 1. Buffer all output immediately so no early warnings corrupt the file
ob_start();

// Disable display_errors during file streaming so warnings don't pollute binary data
ini_set('display_errors', 0);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

const TEMPLATE_PATH = __DIR__ . '/template.xlsx';
const TEMPLATE_SHEET_NAME = null;

const CELL_MAP = [
    'name'              => 'B4',   // Employee Name
    'ic_number'         => 'B5',   // NRIC
    'position'          => 'B6',   // Position
    'salary_month'      => 'H4',   // Salary Month
    'payment_date'      => 'H5',   // Payment Date
    'bank_account'      => 'H6',   // Bank Account No
    'basic_salary'      => 'F10',  // Basic Salary
    'epf_employee'      => 'K10',  // EPF (employee)
    'socso_employee'    => 'K11',  // SOCSO (employee)
    'socso24_employee'  => 'K12',  // Employee's SOCSO Lindung 24 Jam
    'epf_employer'      => 'F21',  // EPF (employer)
    'socso_employer'    => 'F22',  // SOCSO (employer)
    'eis_employer'      => 'F23',  // EIS (employer)
    'staff_loan'        => 'K13',  // Staff Loan
    'overtime'          => 'F11',  // Overtime
    'others'            => 'F12',  // Others
];

const CELL_LABELS = [
    'name'              => 'Employee Name    :', 
    'ic_number'         => 'NRIC             :',
    'position'          => 'Position         :',
    'salary_month'      => 'Salary Month    :',
    'payment_date'      => 'Payment Date    :',
    'bank_account'      => 'Bank Account No :',
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('This endpoint only accepts POST requests.');
}

if (!file_exists(TEMPLATE_PATH)) {
    http_response_code(500);
    die('Template file not found. Please verify template.xlsx is in the project root.');
}

$format          = strtolower(trim($_POST['format'] ?? 'xlsx'));
$name            = trim($_POST['name'] ?? '');
$icNumber        = trim($_POST['ic_number'] ?? '');
$position        = trim($_POST['position'] ?? '');
$bankAccount     = trim($_POST['bank_account'] ?? '');
$salaryMonth     = trim($_POST['salary_month'] ?? '');
$paymentDateRaw  = trim($_POST['payment_date'] ?? '');

// Earnings & Deductions
$basicSalary     = (float) ($_POST['basic_salary'] ?? ($_POST['net_salary'] ?? 0));
$overtime        = (float) ($_POST['overtime'] ?? 0);
$others          = (float) ($_POST['others'] ?? 0);

$epfEmployee     = (float) ($_POST['epf_employee'] ?? 0);
$socsoEmployee   = (float) ($_POST['socso_employee'] ?? 0);
$socso24Employee = (float) ($_POST['socso24_employee'] ?? 0);
$staffLoan       = (float) ($_POST['staff_loan'] ?? 0);

$epfEmployer     = (float) ($_POST['employer_epf'] ?? ($_POST['epf_employer'] ?? 0));
$socsoEmployer   = (float) ($_POST['employer_socso'] ?? ($_POST['socso_employer'] ?? 0));
$eisEmployer     = (float) ($_POST['employer_eis'] ?? ($_POST['eis_employer'] ?? 0));

if ($name === '') {
    http_response_code(422);
    die('Employee name is required to generate a payslip.');
}

// Payment Date formatting
$paymentDateFormatted = '';
if ($paymentDateRaw !== '') {
    $timestamp = strtotime($paymentDateRaw);
    if ($timestamp) {
        $paymentDateFormatted = date('j F Y', $timestamp);
    }
}

$values = [
    'name'              => $name,
    'ic_number'         => $icNumber,
    'position'          => $position,
    'salary_month'      => $salaryMonth,
    'payment_date'      => $paymentDateFormatted,
    'bank_account'      => $bankAccount,
    'basic_salary'      => $basicSalary,
    'epf_employee'      => $epfEmployee,
    'socso_employee'    => $socsoEmployee,
    'socso24_employee'  => $socso24Employee,
    'epf_employer'      => $epfEmployer,
    'socso_employer'    => $socsoEmployer,
    'eis_employer'      => $eisEmployer,
    'staff_loan'        => $staffLoan,
    'overtime'          => $overtime,
    'others'            => $others
];

try {
    /** @var Spreadsheet $spreadsheet */
    $spreadsheet = IOFactory::load(TEMPLATE_PATH);
} catch (Exception $e) {
    http_response_code(500);
    die('Failed to load template: ' . $e->getMessage());
}

$sheet = TEMPLATE_SHEET_NAME
    ? $spreadsheet->getSheetByName(TEMPLATE_SHEET_NAME)
    : $spreadsheet->getActiveSheet();

if (!$sheet) {
    http_response_code(500);
    die('Could not find worksheet in template.');
}

// Write values to cells
foreach (CELL_MAP as $field => $cellRef) {
    $valueToWrite = $values[$field] ?? '';
    
    if ($valueToWrite !== '' && isset(CELL_LABELS[$field])) {
        $valueToWrite = CELL_LABELS[$field] . ' ' . $valueToWrite;
    }
    
    $sheet->setCellValue($cellRef, $valueToWrite);
}

// Safe sanitized filename
$safeName  = preg_replace('/[^A-Za-z0-9_\-]/', '_', $name);
$safeMonth = preg_replace('/[^A-Za-z0-9_\-]/', '_', $salaryMonth);

// Completely purge all output buffers before sending binary headers
while (ob_get_level()) {
    ob_end_clean();
}

if ($format === 'pdf') {
    $filename = "Payslip_{$safeName}_{$safeMonth}.pdf";

    // Set Dompdf as the PDF renderer
    IOFactory::registerWriter('Pdf', \PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf::class);
    
    $sheet->setShowGridLines(false);
    $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
    $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

    $sheet->getPageSetup()->setFitToPage(true);
    $sheet->getPageSetup()->setFitToWidth(1);
    $sheet->getPageSetup()->setFitToHeight(1);
    $sheet->getPageSetup()->setHorizontalCentered(true);

    $sheet->getPageMargins()->setTop(0.4);
    $sheet->getPageMargins()->setBottom(0.4);
    $sheet->getPageMargins()->setLeft(0.4);
    $sheet->getPageMargins()->setRight(0.4);

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');

    $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
    $writer->save('php://output');
} else {
    $filename = "Payslip_{$safeName}_{$safeMonth}.xlsx";

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');

    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
}

exit;