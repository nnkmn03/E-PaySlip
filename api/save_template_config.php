<?php
require_once __DIR__ . '/../auth.php';
require_login_api();

// api/save_template_config.php
// Accepts a JSON body of { field_key: { cell, label, show_label } } and
// persists it as the new live payslip cell mapping.

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../template_config.php';
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Bootstrap error: ' . $e->getMessage()]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method Not Allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$mapping = json_decode($raw, true);

if (!is_array($mapping) || empty($mapping)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'No mapping data received.']);
    exit;
}

$pdo = getOptionalPdo();
if ($pdo === null) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Could not connect to the database. Verify MariaDB is running.']);
    exit;
}

try {
    saveTemplateConfig($pdo, $mapping);
    echo json_encode(['status' => 'success', 'message' => 'Template mapping saved.']);
} catch (InvalidArgumentException $e) {
    http_response_code(422);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to save mapping: ' . $e->getMessage()]);
}
