<?php
// init_mariadb.php

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'epayslip_db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

try {
    // 1. Connect without selecting a db to create the database if missing
    $rootPdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $rootPdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbname}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "✅ Database '{$dbname}' confirmed/created.\n";

    // 2. Connect to the target database
    $pdo = new PDO("mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    // 3. Create payslips table
    $tableSql = "
    CREATE TABLE IF NOT EXISTS payslips (
        id INT AUTO_INCREMENT PRIMARY KEY,
        employee_name VARCHAR(255) NOT NULL,
        nric VARCHAR(20) NOT NULL,
        position VARCHAR(100),
        bank_account_no VARCHAR(50),
        salary_month VARCHAR(30) NOT NULL,
        payment_date DATE NULL,
        basic_salary DECIMAL(10, 2) DEFAULT 0.00,
        overtime DECIMAL(10, 2) DEFAULT 0.00,
        others DECIMAL(10, 2) DEFAULT 0.00,
        total_gross_pay DECIMAL(10, 2) DEFAULT 0.00,
        deduction_epf DECIMAL(10, 2) DEFAULT 0.00,
        deduction_socso DECIMAL(10, 2) DEFAULT 0.00,
        deduction_socso_lindung_24jam DECIMAL(10, 2) DEFAULT 0.00,
        hpcs_staff_loan DECIMAL(10, 2) DEFAULT 0.00,
        total_deductions DECIMAL(10, 2) DEFAULT 0.00,
        net_pay DECIMAL(10, 2) DEFAULT 0.00,
        employer_epf DECIMAL(10, 2) DEFAULT 0.00,
        employer_socso DECIMAL(10, 2) DEFAULT 0.00,
        employer_eis DECIMAL(10, 2) DEFAULT 0.00,
        total_employer_contributions DECIMAL(10, 2) DEFAULT 0.00,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY uq_employee_month (nric, salary_month)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";

    $pdo->exec($tableSql);
    echo "✅ Table 'payslips' verified/created.\n";

    // 4. Seed dummy data
    $dummyPayslips = [
        [
            'employee_name' => 'Ahmad Faiz Bin Rosli',
            'nric' => '940512-07-5531',
            'position' => 'Senior Technician',
            'bank_account_no' => '162012345678 (Maybank)',
            'salary_month' => 'August 2026',
            'payment_date' => '2026-08-28',
            'basic_salary' => 3200.00,
            'overtime' => 250.00,
            'others' => 100.00,
            'total_gross_pay' => 3550.00,
            'deduction_epf' => 352.00,
            'deduction_socso' => 17.25,
            'deduction_socso_lindung_24jam' => 5.00,
            'hpcs_staff_loan' => 0.00,
            'total_deductions' => 374.25,
            'net_pay' => 3175.75,
            'employer_epf' => 416.00,
            'employer_socso' => 60.35,
            'employer_eis' => 6.90,
            'total_employer_contributions' => 483.25
        ],
        [
            'employee_name' => 'Ahmad Faiz Bin Rosli',
            'nric' => '940512-07-5531',
            'position' => 'Senior Technician',
            'bank_account_no' => '162012345678 (Maybank)',
            'salary_month' => 'September 2026',
            'payment_date' => '2026-09-28',
            'basic_salary' => 3200.00,
            'overtime' => 400.00,
            'others' => 100.00,
            'total_gross_pay' => 3700.00,
            'deduction_epf' => 352.00,
            'deduction_socso' => 17.25,
            'deduction_socso_lindung_24jam' => 5.00,
            'hpcs_staff_loan' => 0.00,
            'total_deductions' => 374.25,
            'net_pay' => 3325.75,
            'employer_epf' => 416.00,
            'employer_socso' => 60.35,
            'employer_eis' => 6.90,
            'total_employer_contributions' => 483.25
        ],
        [
            'employee_name' => 'Nurul Hidayah Binti Osman',
            'nric' => '980320-02-6112',
            'position' => 'Admin Executive',
            'bank_account_no' => '7045129841 (CIMB)',
            'salary_month' => 'September 2026',
            'payment_date' => '2026-09-28',
            'basic_salary' => 2600.00,
            'overtime' => 0.00,
            'others' => 50.00,
            'total_gross_pay' => 2650.00,
            'deduction_epf' => 286.00,
            'deduction_socso' => 13.25,
            'deduction_socso_lindung_24jam' => 0.00,
            'hpcs_staff_loan' => 150.00,
            'total_deductions' => 449.25,
            'net_pay' => 2200.75,
            'employer_epf' => 338.00,
            'employer_socso' => 46.35,
            'employer_eis' => 5.30,
            'total_employer_contributions' => 389.65
        ]
    ];

    $insertSql = "
    INSERT INTO payslips (
        employee_name, nric, position, bank_account_no, salary_month, payment_date,
        basic_salary, overtime, others, total_gross_pay,
        deduction_epf, deduction_socso, deduction_socso_lindung_24jam, hpcs_staff_loan, total_deductions,
        net_pay, employer_epf, employer_socso, employer_eis, total_employer_contributions
    ) VALUES (
        :employee_name, :nric, :position, :bank_account_no, :salary_month, :payment_date,
        :basic_salary, :overtime, :others, :total_gross_pay,
        :deduction_epf, :deduction_socso, :deduction_socso_lindung_24jam, :hpcs_staff_loan, :total_deductions,
        :net_pay, :employer_epf, :employer_socso, :employer_eis, :total_employer_contributions
    ) ON DUPLICATE KEY UPDATE 
        basic_salary = VALUES(basic_salary),
        overtime = VALUES(overtime),
        total_gross_pay = VALUES(total_gross_pay),
        net_pay = VALUES(net_pay);
    ";

    $stmt = $pdo->prepare($insertSql);
    foreach ($dummyPayslips as $item) {
        $stmt->execute($item);
    }

    echo "✅ Seeded dummy data for August 2026 and September 2026 successfully.\n";

} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
}