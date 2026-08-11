<?php
/**
 * Developer Setup & Database Migration Script
 * Automatically sets up MySQL database and imports master schema.
 */

set_time_limit(300);

echo "========================================================\n";
echo "   FINDOWNN SPORTS TECH — DEVELOPER DATABASE SETUP      \n";
echo "========================================================\n\n";

$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'findownn_admin';
$sqlFile = __DIR__ . '/findownn_database_master.sql';

if (!file_exists($sqlFile)) {
    die("❌ ERROR: Master database SQL file '$sqlFile' not found!\n");
}

try {
    echo "[1/3] Connecting to MySQL server at $host...\n";
    $pdo = new PDO("mysql:host=$host", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);

    echo "[2/3] Creating database '$dbName' if not exists...\n";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbName`");

    echo "[3/3] Importing master schema and seed data from findownn_database_master.sql...\n";
    $sqlContent = file_get_contents($sqlFile);
    
    // Disable foreign key checks during import
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");
    $pdo->exec($sqlContent);
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    echo "\n✓ SUCCESS: Database '$dbName' successfully imported!\n";

    // Create required directories
    $dirs = [
        __DIR__ . '/admin/storage/backups',
        __DIR__ . '/admin/storage/cache',
        __DIR__ . '/admin/storage/logs',
        __DIR__ . '/assets/uploads',
        __DIR__ . '/uploads/venues',
        __DIR__ . '/uploads/courts',
    ];

    foreach ($dirs as $dir) {
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
            echo "  + Created directory: " . basename($dir) . "\n";
        }
    }

    echo "\n========================================================\n";
    echo "   DEVELOPER SETUP COMPLETED SUCCESSFULLY!              \n";
    echo "========================================================\n";
    echo "URLs:\n";
    echo "  • Public Website : http://localhost/findownn_website/\n";
    echo "  • Admin Panel    : http://localhost/findownn_website/admin/\n";
    echo "  • API Endpoint   : http://localhost/findownn_website/api/v1/\n\n";
    echo "Initial Super Admin Login:\n";
    echo "  • Email: superadmin@findownn.com\n";
    echo "  • Password: password\n";
    echo "========================================================\n";

} catch (PDOException $e) {
    echo "\n❌ DATABASE ERROR: " . $e->getMessage() . "\n";
    echo "Please ensure MySQL is running in XAMPP / Laragon.\n";
    exit(1);
}
