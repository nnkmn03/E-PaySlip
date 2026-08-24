<?php
/**
 * generate.php
 * ---------------------------------------------------------------
 * Loads template.xlsx, injects the on-screen form data into fixed
 * cells, and forces a browser download of the completed payslip
 * in either Excel (.xlsx) or PDF (.pdf) format.
 * ---------------------------------------------------------------
 */

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

// =================================================================
// 🔧 CONFIG — edit these to match your template.xlsx
// =================================================================

// Path to your fixed local template file
const TEMPLATE_PATH = __DIR__ . '/template.xlsx';

// Which sheet to write into (by name). Set to null to use the active sheet.
const TEMPLATE_SHEET_NAME = null;

// Cell mapping: data key => cell reference in the template
const CELL_MAP = [
    'name'              => 'B4',   // Employee Name
    'ic_number'         => 'B5',   // NRIC
    'position'          => 'B6',   // Position
    'salary_month'      => 'H4',   // Salary Month, e.g. "July 2026"
    'payment_date'      => 'H5',   // Payment Date, e.g. "1 August 2026"
    'bank_account'      => 'H6',   // Bank Account No
    'basic_salary'      => 'F10',  // Basic Salary (Net Salary entered on screen)
    'epf_employee'      => 'K10',  // EPF (employee) — 11% of Net Salary
    'socso_employee'    => 'K11',  // SOCSO (employee) — manual
    'socso24_employee'  => 'K12',  // Employee's SOCSO Lindung 24 Jam — manual
    'epf_employer'      => 'F21',  // EPF (employer) — 13% of Net Salary
    'socso_employer'    => 'F22',  // SOCSO (employer) — manual
    'eis_employer'      => 'F23',  // EIS (employer) — manual
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

// EPF percentages
const EPF_EMPLOYEE_RATE = 0.11;
const EPF_EMPLOYER_RATE = 0.13;

// Human-readable month names
const MONTH_NAMES = [
    1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
    5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
    9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
];

// =================================================================
// Request handling
// =================================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die('This endpoint only accepts POST requests.');
}

if (!file_exists(TEMPLATE_PATH)) {
    http_response_code(500);
    die('Template file not found. Please place your template.xlsx in the project root.');
}

$format          = strtolower(trim($_POST['format'] ?? 'xlsx'));
$name            = trim($_POST['name'] ?? '');
$icNumber        = trim($_POST['ic_number'] ?? '');
$position        = trim($_POST['position'] ?? '');
$bankAccount     = trim($_POST['bank_account'] ?? '');
$netSalary       = (float) ($_POST['net_salary'] ?? 0);
$month           = (int) ($_POST['month'] ?? 0);
$year            = (int) ($_POST['year'] ?? 0);
$paymentDateRaw  = trim($_POST['payment_date'] ?? '');
$epfSocsoEnabled = ($_POST['epf_socso_enabled'] ?? '1') === '1';

if ($name === '') {
    http_response_code(422);
    die('Employee name is required to generate a payslip.');
}

// --- Salary Month, e.g. "July 2026" ---
$monthLabel  = MONTH_NAMES[$month] ?? (string) $month;
$salaryMonth = trim($monthLabel . ' ' . $year);

// --- Payment Date, e.g. "1 August 2026" ---
$paymentDateFormatted = '';
if ($paymentDateRaw !== '') {
    $dt = DateTime::createFromFormat('Y-m-d', $paymentDateRaw);
    if ($dt instanceof DateTime) {
        $paymentDateFormatted = $dt->format('j F Y');
    }
}

// --- EPF / SOCSO / EIS calculation ---
if ($epfSocsoEnabled) {
    $epfEmployee     = round($netSalary * EPF_EMPLOYEE_RATE, 2);
    $epfEmployer     = round($netSalary * EPF_EMPLOYER_RATE, 2);
    $socsoEmployee   = round((float) ($_POST['socso_employee'] ?? 0), 2);
    $socso24Employee = round((float) ($_POST['socso24_employee'] ?? 0), 2);
    $socsoEmployer   = round((float) ($_POST['socso_employer'] ?? 0), 2);
    $eisEmployer     = round((float) ($_POST['eis_employer'] ?? 0), 2);
    $staffLoan       = round((float) ($_POST['staff_loan'] ?? 0), 2);
    $overtime        = round((float) ($_POST['overtime'] ?? 0), 2);
    $others          = round((float) ($_POST['others'] ?? 0), 2);
} else {
    $epfEmployee = $epfEmployer = $socsoEmployee = $socso24Employee = $socsoEmployer = $eisEmployer = 0;
    $staffLoan   = round((float) ($_POST['staff_loan'] ?? 0), 2);
    $overtime    = round((float) ($_POST['overtime'] ?? 0), 2);
    $others      = round((float) ($_POST['others'] ?? 0), 2);
}

$values = [
    'name'              => $name,
    'ic_number'         => $icNumber,
    'position'          => $position,
    'salary_month'      => $salaryMonth,
    'payment_date'      => $paymentDateFormatted,
    'bank_account'      => $bankAccount,
    'basic_salary'      => $netSalary,
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

// =================================================================
// Load template & inject values
// =================================================================

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
    die('Could not find sheet "' . TEMPLATE_SHEET_NAME . '" in template.');
}

// Write values to cells
foreach (CELL_MAP as $field => $cellRef) {
    $valueToWrite = $values[$field] ?? '';
    
    if ($valueToWrite !== '' && isset(CELL_LABELS[$field])) {
        $valueToWrite = CELL_LABELS[$field] . ' ' . $valueToWrite;
    }
    
    $sheet->setCellValue($cellRef, $valueToWrite);
}

// =================================================================
// Force download (XLSX or PDF)
// =================================================================

$safeName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $name);

if ($format === 'pdf') {
    $filename = "Payslip_{$safeName}_{$monthLabel}_{$year}.pdf";

    // 1. Calculate all Excel formulas (Total Deductions, Net Pay, etc.)
    \PhpOffice\PhpSpreadsheet\Calculation\Calculation::getInstance($spreadsheet)->disableCalculationCache();
    \PhpOffice\PhpSpreadsheet\Calculation\Calculation::getInstance($spreadsheet)->clearCalculationCache();

    // 2. Set Dompdf as the PDF renderer
    IOFactory::registerWriter('Pdf', \PhpOffice\PhpSpreadsheet\Writer\Pdf\Dompdf::class);
    
    // 3. Switch to LANDSCAPE to match the second image
    $sheet->setShowGridLines(false);
    $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
    $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);

    // 4. Scale layout onto 1 page with balanced margins
    $sheet->getPageSetup()->setFitToPage(true);
    $sheet->getPageSetup()->setFitToWidth(1);
    $sheet->getPageSetup()->setFitToHeight(1);
    $sheet->getPageSetup()->setHorizontalCentered(true);

    // Set clean 10mm margins
    $sheet->getPageMargins()->setTop(0.4);
    $sheet->getPageMargins()->setBottom(0.4);
    $sheet->getPageMargins()->setLeft(0.4);
    $sheet->getPageMargins()->setRight(0.4);

    // 5. Output PDF headers
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = IOFactory::createWriter($spreadsheet, 'Pdf');
    
    // Enable formula pre-calculation during write
    $writer->setPreCalculateFormulas(true);
    $writer->save('php://output');
} else {
    $filename = "Payslip_{$safeName}_{$monthLabel}_{$year}.xlsx";

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
}

exit;