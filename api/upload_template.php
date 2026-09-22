<?php
require_once __DIR__ . '/../auth.php';
require_login_api();

// api/upload_template.php
// Lets HR replace the master template.xlsx with a new file. The upload is
// validated by actually opening it with PhpSpreadsheet before it's allowed
// to replace anything, and the previous template is kept as a one-slot
// backup (template_backup.xlsx) in case the new layout needs reverting.

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../vendor/autoload.php';
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Bootstrap error: ' . $e->getMessage()]);
    exit;
}

use PhpOffice\PhpSpreadsheet\IOFactory;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

if (!isset($_FILES['template_file']) || $_FILES['template_file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    $uploadErr = $_FILES['template_file']['error'] ?? 'No file';
    echo json_encode(['status' => 'error', 'message' => "Template upload failed (Code: {$uploadErr})"]);
    exit;
}

$tmpPath = $_FILES['template_file']['tmp_name'];
$originalName = $_FILES['template_file']['name'];

$ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
if ($ext !== 'xlsx') {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'Only .xlsx files are accepted.']);
    exit;
}

// Validate that the uploaded file is actually a readable spreadsheet
// before it's allowed to overwrite the live template.
try {
    $testSpreadsheet = IOFactory::load($tmpPath);
    if (!$testSpreadsheet->getActiveSheet()) {
        throw new Exception('No worksheet found in the uploaded file.');
    }
} catch (Throwable $e) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => 'The uploaded file is not a valid Excel workbook: ' . $e->getMessage()]);
    exit;
}

$templatePath = __DIR__ . '/../template.xlsx';
$backupPath   = __DIR__ . '/../template_backup.xlsx';

try {
    if (file_exists($templatePath)) {
        // Single-slot backup: keep only the immediately previous version.
        copy($templatePath, $backupPath);
    }

    if (!move_uploaded_file($tmpPath, $templatePath)) {
        throw new Exception('Could not save the uploaded file to the template location.');
    }

    echo json_encode([
        'status'  => 'success',
        'message' => 'Template replaced successfully. Your previous template was kept as a backup.',
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
