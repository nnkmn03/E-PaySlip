<?php

// db.php - MariaDB PDO Connection

$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'epayslip_db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : '';

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $e->getMessage()]);
    exit;
}

/**
 * db.php
 * ---------------------------------------------------------------
 * Database Connection via Supabase IPv4 Pooler (Seoul Region)
 * ---------------------------------------------------------------
 */

// Hostname for Northeast Asia (Seoul)
// $host     = getenv('DB_HOST')     ?: 'aws-0-ap-northeast-2.pooler.supabase.com';
// $port     = getenv('DB_PORT')     ?: '6543'; // 6543 (transaction mode) or 5432 (session mode)
// $dbname   = getenv('DB_NAME')     ?: 'postgres';

// // Pooler username format: postgres.[PROJECT_REF]
// $user     = getenv('DB_USER')     ?: 'postgres.fekbudnrqmcdnbtuetki';
// $password = getenv('DB_PASSWORD') ?: 'Ayamgoyeng1@3';

// $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require";

// try {
//     $pdo = new PDO($dsn, $user, $password, [
//         PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
//         PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
//         PDO::ATTR_EMULATE_PREPARES   => false,
//     ]);
// } catch (PDOException $e) {
//     http_response_code(500);
//     die(json_encode([
//         'success' => false,
//         'error'   => 'Database connection failed: ' . $e->getMessage()
//     ]));
// }

// return $pdo;