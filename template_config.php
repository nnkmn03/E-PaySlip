<?php
/**
 * template_config.php
 * ---------------------------------------------------------------
 * Central definition of the payslip data fields and the (now
 * dynamic) mapping of "which field goes into which cell, with
 * what label". This used to be two hardcoded consts in
 * generate.php (CELL_MAP / CELL_LABELS) -- it's now stored in the
 * `payslip_template_fields` table so HR can edit it from
 * template_editor.php without touching code.
 *
 * FIELD_DEFINITIONS below is the single source of truth for which
 * 16 fields exist and their human-readable names / original
 * default cell+label -- it is NOT the live mapping. The live
 * mapping lives in the database and is seeded from these defaults
 * the first time the table is created.
 * ---------------------------------------------------------------
 */

// field_key => [ display label for the editor UI, default cell, default label text or null, default show_label ]
const FIELD_DEFINITIONS = [
    'name'              => ['Employee Name',              'B4',  'Employee Name    :', true],
    'ic_number'         => ['NRIC',                        'B5',  'NRIC             :', true],
    'position'          => ['Position',                    'B6',  'Position         :', true],
    'salary_month'      => ['Salary Month',                'H4',  'Salary Month    :', true],
    'payment_date'      => ['Payment Date',                'H5',  'Payment Date    :', true],
    'bank_account'      => ['Bank Account No',             'H6',  'Bank Account No :', true],
    'basic_salary'      => ['Basic Salary',                'F10', null, false],
    'overtime'          => ['Overtime',                    'F11', null, false],
    'others'            => ['Others',                      'F12', null, false],
    'epf_employee'      => ['EPF (Employee)',              'K10', null, false],
    'socso_employee'    => ['SOCSO (Employee)',            'K11', null, false],
    'socso24_employee'  => ["Employee's SOCSO Lindung 24 Jam", 'K12', null, false],
    'staff_loan'        => ['Staff Loan',                  'K13', null, false],
    'epf_employer'      => ['EPF (Employer)',              'F21', null, false],
    'socso_employer'    => ['SOCSO (Employer)',            'F22', null, false],
    'eis_employer'      => ['EIS (Employer)',              'F23', null, false],
];

/**
 * Open a DB connection the same way db.php does, but WITHOUT db.php's
 * behaviour of echoing a JSON error and exit()-ing the whole script on
 * failure. Used by generate.php, which streams a binary file and must
 * never have its output clobbered by a stray JSON error + exit.
 * Returns null (instead of throwing/exiting) if the connection fails.
 */
function getOptionalPdo(): ?PDO
{
    $host   = getenv('DB_HOST') ?: '127.0.0.1';
    $port   = getenv('DB_PORT') ?: '3306';
    $dbname = getenv('DB_NAME') ?: 'epayslip_db';
    $user   = getenv('DB_USER') ?: 'root';
    $pass   = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

    try {
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
        return new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (Throwable $e) {
        return null;
    }
}

/**
 * Make sure the mapping table exists and is seeded with the
 * original defaults. Safe to call on every request -- CREATE TABLE
 * IF NOT EXISTS / INSERT IGNORE are both no-ops once done.
 */
function ensureTemplateConfigTable(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS payslip_template_fields (
            field_key   VARCHAR(50) PRIMARY KEY,
            cell_ref    VARCHAR(10) NOT NULL,
            label_text  VARCHAR(150) DEFAULT NULL,
            show_label  TINYINT(1) NOT NULL DEFAULT 0,
            updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");

    $stmt = $pdo->prepare("
        INSERT IGNORE INTO payslip_template_fields (field_key, cell_ref, label_text, show_label)
        VALUES (:field_key, :cell_ref, :label_text, :show_label)
    ");

    foreach (FIELD_DEFINITIONS as $fieldKey => $def) {
        [$displayName, $defaultCell, $defaultLabel, $defaultShowLabel] = $def;
        $stmt->execute([
            ':field_key'  => $fieldKey,
            ':cell_ref'   => $defaultCell,
            ':label_text' => $defaultLabel,
            ':show_label' => $defaultShowLabel ? 1 : 0,
        ]);
    }
}

/**
 * Load the live field => cell/label mapping from the database.
 * Returns an associative array keyed by field_key:
 *   [ 'cell' => 'B4', 'label' => 'Employee Name    :', 'show_label' => true ]
 *
 * Falls back to the hardcoded FIELD_DEFINITIONS (old fixed
 * behaviour) if the database is unreachable, so payslip
 * generation never breaks just because the DB is briefly down.
 */
function getTemplateConfig(?PDO $pdo): array
{
    $config = [];

    if ($pdo !== null) {
        try {
            ensureTemplateConfigTable($pdo);
            $rows = $pdo->query("SELECT field_key, cell_ref, label_text, show_label FROM payslip_template_fields")
                        ->fetchAll();

            foreach ($rows as $row) {
                $config[$row['field_key']] = [
                    'cell'       => $row['cell_ref'],
                    'label'      => $row['label_text'],
                    'show_label' => (bool) $row['show_label'],
                ];
            }

            if (!empty($config)) {
                return $config;
            }
        } catch (Throwable $e) {
            // fall through to hardcoded defaults below
        }
    }

    foreach (FIELD_DEFINITIONS as $fieldKey => $def) {
        [$displayName, $defaultCell, $defaultLabel, $defaultShowLabel] = $def;
        $config[$fieldKey] = [
            'cell'       => $defaultCell,
            'label'      => $defaultLabel,
            'show_label' => $defaultShowLabel,
        ];
    }

    return $config;
}

/**
 * Persist a full field => {cell, label, show_label} mapping.
 * $mapping is validated (cell ref format, known field keys) before
 * anything is written; throws InvalidArgumentException on bad input.
 */
function saveTemplateConfig(PDO $pdo, array $mapping): void
{
    ensureTemplateConfigTable($pdo);

    $stmt = $pdo->prepare("
        UPDATE payslip_template_fields
        SET cell_ref = :cell_ref, label_text = :label_text, show_label = :show_label
        WHERE field_key = :field_key
    ");

    $pdo->beginTransaction();
    try {
        foreach ($mapping as $fieldKey => $data) {
            if (!isset(FIELD_DEFINITIONS[$fieldKey])) {
                continue; // ignore unknown keys rather than failing the whole save
            }

            $cellRef = strtoupper(trim($data['cell'] ?? ''));
            if ($cellRef === '' || !preg_match('/^[A-Z]{1,3}[1-9][0-9]{0,6}$/', $cellRef)) {
                throw new InvalidArgumentException("Invalid cell reference \"{$cellRef}\" for field \"{$fieldKey}\".");
            }

            $labelText = trim($data['label'] ?? '');
            $showLabel = !empty($data['show_label']);

            $stmt->execute([
                ':cell_ref'   => $cellRef,
                ':label_text' => $labelText === '' ? null : $labelText,
                ':show_label' => $showLabel ? 1 : 0,
                ':field_key'  => $fieldKey,
            ]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
