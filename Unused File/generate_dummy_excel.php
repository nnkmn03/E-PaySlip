<?php
// generate_dummy_excel.php
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Payroll Data');

// 1. Define Headers mapping exactly to api/upload_payroll_excel.php
$headers = [
    'Employee Name',               // Col 0
    'NRIC',                        // Col 1
    'Position',                    // Col 2
    'Bank Account',                // Col 3
    'Basic Salary',                // Col 4
    'Overtime',                    // Col 5
    'Others',                      // Col 6
    'EPF (Employee)',              // Col 7
    'SOCSO (Employee)',            // Col 8
    'SOCSO Lindung 24 Jam',        // Col 9
    'Staff Loan',                  // Col 10
    'Employer EPF',                // Col 11
    'Employer SOCSO',              // Col 12
    'Employer EIS'                 // Col 13
];

// Write Header Row
$sheet->fromArray($headers, null, 'A1');

// Style Header Row
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '15803D']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
];
$sheet->getStyle('A1:N1')->applyFromArray($headerStyle);

// 2. Dummy Staff Rows
$data = [
    [
        'Muhammad Haris Bin Zulkifli',
        '920115-08-5421',
        'Senior Software Engineer',
        '164012349876 (Maybank)',
        4800.00,
        350.00,
        150.00,
        528.00,   // EPF Employee (11%)
        24.75,    // SOCSO Employee
        5.00,     // SOCSO 24 Jam
        0.00,     // Staff Loan
        624.00,   // Employer EPF (13%)
        86.65,    // Employer SOCSO
        9.90      // Employer EIS
    ],
    [
        'Siti Aisyah Binti Razali',
        '960704-03-5182',
        'HR & Admin Executive',
        '7058192341 (CIMB)',
        3100.00,
        0.00,
        100.00,
        341.00,
        15.75,
        0.00,
        100.00,
        403.00,
        55.15,
        6.30
    ],
    [
        'Tan Wei Lun',
        '910328-07-5933',
        'Accountant',
        '3189456201 (Public Bank)',
        4200.00,
        120.00,
        50.00,
        462.00,
        21.25,
        5.00,
        0.00,
        546.00,
        74.40,
        8.50
    ],
    [
        'Ramasamy A/L Subramaniam',
        '881112-08-5011',
        'Maintenance Supervisor',
        '112098456712 (Maybank)',
        2900.00,
        450.00,
        80.00,
        319.00,
        14.75,
        0.00,
        50.00,
        377.00,
        51.65,
        5.90
    ]
];

// Write Data Rows starting from A2
$sheet->fromArray($data, null, 'A2');

// Auto-size columns for neat formatting
foreach (range('A', 'N') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// 3. Save to file
$outputFile = __DIR__ . '/dummy_payroll_october_2026.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($outputFile);

echo "✅ Dummy file generated successfully: {$outputFile}\n";