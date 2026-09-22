<?php
/**
 * generate.php
 * ---------------------------------------------------------------
 * Loads template.xlsx, injects form data into fixed cells,
 * and delivers clean binary output for Excel (.xlsx) or PDF (.pdf).
 *
 * PDF output uses PHP's COM automation to drive the real, locally
 * installed Microsoft Excel (Excel.Application) to open the
 * generated file and export it to PDF itself -- the same thing you
 * do manually via File > Export > PDF. This means the PDF matches
 * Excel's own rendering exactly (correct column widths, wrapping,
 * page layout), instead of approximating it with a third-party
 * HTML/CSS renderer like Dompdf, which does not read Excel's layout
 * engine and mis-wraps text that fits fine in real Excel.
 *
 * REQUIREMENTS for the PDF button to work:
 *   1. This must run on Windows (Laragon), with Microsoft Excel
 *      actually installed on the same machine.
 *   2. The php_com_dotnet extension must be enabled in php.ini:
 *        extension=com_dotnet
 *      (Laragon: Menu > PHP > Extensions > com_dotnet, then restart)
 *   3. Laragon should run as your normal logged-in desktop session
 *      (not as a Windows service under a different account) -- Office
 *      apps are not designed to automate reliably from a
 *      non-interactive service session.
 * ---------------------------------------------------------------
 */

// 1. Buffer all output immediately so no early warnings corrupt the file
ob_start();

// Disable display_errors during file streaming so warnings don't pollute binary data
ini_set('display_errors', 0);
error_reporting(E_ALL);

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/template_config.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

const TEMPLATE_PATH = __DIR__ . '/template.xlsx';
const TEMPLATE_SHEET_NAME = null;
const TEMP_DIR = __DIR__ . '/temp';

// Cell mapping + label config is now editable by HR via template_editor.php
// and stored in the database (payslip_template_fields table). getOptionalPdo()
// never exits/echoes on failure (unlike db.php), so a DB hiccup can't corrupt
// the binary file this endpoint streams -- getTemplateConfig() just falls
// back to the original fixed layout in that case.
$templateConfig = getTemplateConfig(getOptionalPdo());

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

// Write values to cells, using the HR-configurable mapping loaded above
// instead of a fixed CELL_MAP/CELL_LABELS pair.
foreach ($templateConfig as $field => $fieldConfig) {
    $cellRef = $fieldConfig['cell'] ?? '';
    if ($cellRef === '') {
        continue; // field not mapped to any cell -- skip it
    }

    $valueToWrite = $values[$field] ?? '';

    if ($valueToWrite !== '' && !empty($fieldConfig['show_label']) && !empty($fieldConfig['label'])) {
        $valueToWrite = $fieldConfig['label'] . ' ' . $valueToWrite;
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

    // -------------------------------------------------------------
    // PDF via real Excel (COM automation) -- see file header for
    // the requirements this needs on the host machine.
    // -------------------------------------------------------------

    if (!class_exists('COM')) {
        http_response_code(500);
        die(
            "PDF export needs the php_com_dotnet extension, which isn't enabled.\n" .
            "In Laragon: Menu > PHP > Extensions > com_dotnet (tick it), then restart Laragon.\n" .
            "This also only works on Windows with Microsoft Excel installed."
        );
    }

    if (!is_dir(TEMP_DIR)) {
        mkdir(TEMP_DIR, 0777, true);
    }

    $uniqueId    = uniqid('payslip_', true);
    $tempXlsxAbs = TEMP_DIR . "/{$uniqueId}.xlsx";
    $tempPdfAbs  = TEMP_DIR . "/{$uniqueId}.pdf";

    // Excel's COM interface needs a real, absolute, Windows-style path
    $tempXlsxWin = str_replace('/', '\\', $tempXlsxAbs);
    $tempPdfWin  = str_replace('/', '\\', $tempPdfAbs);

    // 1. Save the filled-in workbook to a temp file first
    $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save($tempXlsxAbs);

    $excel = null;
    $workbook = null;

    try {
        $excel = new COM('Excel.Application');
        $excel->Visible = false;
        $excel->DisplayAlerts = false;

        $workbook = $excel->Workbooks->Open($tempXlsxWin);

        // 0 = xlTypePDF
        $workbook->ExportAsFixedFormat(0, $tempPdfWin);

        $workbook->Close(false);
        $excel->Quit();
    } catch (\Throwable $e) {
        // Best-effort cleanup of the Excel instance even if something failed
        if ($workbook !== null) {
            try { $workbook->Close(false); } catch (\Throwable $ignored) {}
        }
        if ($excel !== null) {
            try { $excel->Quit(); } catch (\Throwable $ignored) {}
        }
        @unlink($tempXlsxAbs);

        http_response_code(500);
        die(
            "Excel automation failed: " . $e->getMessage() . "\n\n" .
            "Common causes: Excel isn't installed, Laragon is running as a " .
            "Windows service (not your interactive desktop session), or a " .
            "leftover EXCEL.EXE process is stuck -- check Task Manager."
        );
    } finally {
        // Release COM objects so Excel actually exits
        $workbook = null;
        $excel = null;
    }

    if (!file_exists($tempPdfAbs)) {
        @unlink($tempXlsxAbs);
        http_response_code(500);
        die('Excel did not produce a PDF file. Please try again.');
    }

    $filename = "Payslip_{$safeName}_{$safeMonth}.pdf";

    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . filesize($tempPdfAbs));
    header('Cache-Control: max-age=0');
    header('Pragma: public');

    readfile($tempPdfAbs);

    @unlink($tempXlsxAbs);
    @unlink($tempPdfAbs);
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
