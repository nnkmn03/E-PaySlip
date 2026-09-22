<?php
require_once __DIR__ . '/../auth.php';
require_login_api();

// api/get_template_grid.php
// Returns the current template.xlsx as a JSON grid (cell text previews +
// merged-cell ranges) plus the live field -> cell/label mapping, so the
// template editor can render an Excel-like clickable grid.

header('Content-Type: application/json');
ini_set('display_errors', 0);
error_reporting(E_ALL);

try {
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../template_config.php';
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Bootstrap error: ' . $e->getMessage()]);
    exit;
}

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

const TEMPLATE_PATH_GRID = __DIR__ . '/../template.xlsx';

if (!file_exists(TEMPLATE_PATH_GRID)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'template.xlsx not found.']);
    exit;
}

try {
    $spreadsheet = IOFactory::load(TEMPLATE_PATH_GRID);
    $sheet = $spreadsheet->getActiveSheet();

    $highestRow = $sheet->getHighestRow();
    $highestColIndex = Coordinate::columnIndexFromString($sheet->getHighestColumn());

    // Give HR a little breathing room to map fields into currently-empty
    // cells beyond whatever the template already fills.
    $rowCount = max($highestRow + 6, 20);
    $colCount = max($highestColIndex + 3, 12);

    $cells = [];
    for ($r = 1; $r <= $rowCount; $r++) {
        for ($c = 1; $c <= $colCount; $c++) {
            $colLetter = Coordinate::stringFromColumnIndex($c);
            $coord = $colLetter . $r;
            $cell = $sheet->getCell($coord);
            $raw = $cell ? $cell->getValue() : null;

            if ($raw === null || $raw === '') {
                continue; // skip blanks to keep the payload small
            }

            $preview = is_string($raw) ? $raw : (string) $raw;
            $preview = trim(preg_replace('/\s+/', ' ', $preview));
            if (mb_strlen($preview) > 40) {
                $preview = mb_substr($preview, 0, 40) . '…';
            }

            $cells[$coord] = $preview;
        }
    }

    $merges = [];
    foreach ($sheet->getMergeCells() as $range) {
        // rangeBoundaries() returns [[colIdx, rowIdx], [colIdx, rowIdx]]
        $boundaries = Coordinate::rangeBoundaries($range);
        $merges[] = [
            'range'    => $range,
            'startCol' => $boundaries[0][0],
            'startRow' => $boundaries[0][1],
            'endCol'   => $boundaries[1][0],
            'endRow'   => $boundaries[1][1],
        ];
    }

    $pdo = getOptionalPdo();
    $mapping = getTemplateConfig($pdo);

    $fields = [];
    foreach (FIELD_DEFINITIONS as $fieldKey => $def) {
        $fields[] = [
            'key'        => $fieldKey,
            'name'       => $def[0],
            'cell'       => $mapping[$fieldKey]['cell'] ?? '',
            'label'      => $mapping[$fieldKey]['label'] ?? '',
            'show_label' => $mapping[$fieldKey]['show_label'] ?? false,
        ];
    }

    echo json_encode([
        'status'   => 'success',
        'rowCount' => $rowCount,
        'colCount' => $colCount,
        'cells'    => $cells,
        'merges'   => $merges,
        'fields'   => $fields,
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Failed to read template: ' . $e->getMessage()]);
}
