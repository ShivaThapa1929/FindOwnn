<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Config;
use App\Core\Request;
use App\Core\Session;
use App\Models\Setting;
use App\Models\AuditLog;

class SettingsController extends Controller
{
    public function index(Request $request): void
    {
        $settingModel = new Setting();
        $groups = ['general', 'mail', 'payment', 'security'];
        $settings = [];
        foreach ($groups as $g) {
            $settings[$g] = $settingModel->getByGroup($g);
        }

        $this->render('settings.index', [
            'title'      => 'System Settings',
            'settings'   => $settings,
            'success'    => Session::getFlash('success'),
            'error'      => Session::getFlash('error'),
            'setup_logs' => ($raw = Session::getFlash('setup_logs')) ? json_decode($raw, true) : [],
        ]);
    }

    public function update(Request $request): void
    {
        $data = $_POST;
        unset($data['_csrf'], $data['_method']);

        foreach ($data as $key => $value) {
            Setting::setValue($key, $value);
        }

        AuditLog::log('SETTINGS_UPDATED', 'Setting', 0, [], $data);
        Session::flash('success', 'Settings saved successfully.');
        $this->redirect(url('/settings'));
    }

    public function backup(Request $request): void
    {
        try {
            // Database configuration
            $dbHost = \App\Core\Config::get('DB_HOST', 'localhost');
            $dbName = \App\Core\Config::get('DB_DATABASE');
            $dbUser = \App\Core\Config::get('DB_USERNAME');
            $dbPass = \App\Core\Config::get('DB_PASSWORD');
            
            // Backup directory
            $backupDir = ROOT_PATH . '/storage/backups';
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }
            
            // Generate filename
            $timestamp = date('Ymd_His');
            $filename = "{$dbName}_{$timestamp}.sql";
            $filepath = $backupDir . '/' . $filename;
            
            // Connect to database
            $pdo = new \PDO(
                "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
                $dbUser,
                $dbPass,
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );
            
            // Start SQL output
            $sql = "-- =============================================\n";
            $sql .= "-- Database Backup: {$dbName}\n";
            $sql .= "-- Generated: " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- =============================================\n\n";
            $sql .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            $sql .= "SET time_zone = \"+00:00\";\n\n";
            
            // Get all tables
            $tables = $pdo->query("SHOW TABLES")->fetchAll(\PDO::FETCH_COLUMN);
            
            foreach ($tables as $table) {
                // Table structure
                $sql .= "-- =============================================\n";
                $sql .= "-- Table structure for `{$table}`\n";
                $sql .= "-- =============================================\n\n";
                $sql .= "DROP TABLE IF EXISTS `{$table}`;\n\n";
                
                $createTable = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch(\PDO::FETCH_ASSOC);
                $sql .= $createTable['Create Table'] . ";\n\n";
                
                // Table data
                $sql .= "-- =============================================\n";
                $sql .= "-- Data for table `{$table}`\n";
                $sql .= "-- =============================================\n\n";
                
                $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll(\PDO::FETCH_ASSOC);
                
                if (!empty($rows)) {
                    $columns = array_keys($rows[0]);
                    $columnList = '`' . implode('`, `', $columns) . '`';
                    
                    $valueGroups = [];
                    foreach ($rows as $row) {
                        $values = [];
                        foreach ($row as $value) {
                            if ($value === null) {
                                $values[] = 'NULL';
                            } else {
                                $values[] = $pdo->quote($value);
                            }
                        }
                        $valueGroups[] = '(' . implode(', ', $values) . ')';
                    }
                    
                    // Split into chunks of 100 rows to avoid huge statements
                    $chunks = array_chunk($valueGroups, 100);
                    foreach ($chunks as $chunk) {
                        $sql .= "INSERT INTO `{$table}` ({$columnList}) VALUES\n";
                        $sql .= implode(",\n", $chunk) . ";\n\n";
                    }
                }
                
                $sql .= "\n";
            }
            
            // Write to file
            $bytesWritten = file_put_contents($filepath, $sql);
            
            if ($bytesWritten === false) {
                throw new \Exception("Failed to write backup file");
            }
            
            $fileSize = filesize($filepath);
            $fileSizeKB = round($fileSize / 1024, 2);
            
            // Log the backup
            AuditLog::log('DATABASE_BACKUP', 'System', 0, [], [
                'filename' => $filename,
                'size' => $fileSizeKB . ' KB',
                'tables' => count($tables)
            ]);
            
            $this->json([
                'success' => true,
                'file' => $filename,
                'size' => $fileSizeKB . ' KB',
                'tables' => count($tables),
                'message' => 'Database backup completed successfully!'
            ]);
            
        } catch (\PDOException $e) {
            $this->json([
                'success' => false,
                'error' => 'Database error: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * One-time DB setup: Razorpay columns, payment settings, payment_transactions table.
     */
    public function setupPayment(Request $request): void
    {
        $logs = [];

        try {
            $pdo = new \PDO(
                sprintf(
                    'mysql:host=%s;dbname=%s;charset=utf8mb4',
                    Config::get('DB_HOST', 'localhost'),
                    Config::get('DB_DATABASE')
                ),
                Config::get('DB_USERNAME'),
                Config::get('DB_PASSWORD'),
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );

            $addColumn = static function (string $table, string $column, string $definition) use ($pdo, &$logs): void {
                $stmt = $pdo->query("SHOW COLUMNS FROM `{$table}` LIKE " . $pdo->quote($column));
                if ($stmt->fetch()) {
                    $logs[] = "Column {$table}.{$column} already exists";
                    return;
                }
                $pdo->exec("ALTER TABLE `{$table}` ADD COLUMN {$column} {$definition}");
                $logs[] = "Added column {$table}.{$column}";
            };

            $addColumn('bookings', 'payment_status', "ENUM('pending','paid','failed','refunded') DEFAULT 'pending' AFTER status");
            $addColumn('bookings', 'payment_id', 'VARCHAR(100) NULL AFTER payment_status');
            $addColumn('bookings', 'payment_method', 'VARCHAR(50) NULL AFTER payment_id');
            $addColumn('bookings', 'razorpay_order_id', 'VARCHAR(100) NULL AFTER payment_method');
            $addColumn('bookings', 'razorpay_payment_id', 'VARCHAR(100) NULL AFTER razorpay_order_id');
            $addColumn('bookings', 'razorpay_signature', 'VARCHAR(255) NULL AFTER razorpay_payment_id');

            $paymentSettings = [
                ['razorpay_key_id', '', 'payment', 'Razorpay Key ID'],
                ['razorpay_key_secret', '', 'payment', 'Razorpay Key Secret'],
                ['razorpay_webhook_secret', '', 'payment', 'Razorpay Webhook Secret'],
                ['payment_mode', 'test', 'payment', 'Payment Mode (test/live)'],
            ];

            foreach ($paymentSettings as [$key, $value, $group, $label]) {
                $stmt = $pdo->prepare('SELECT id FROM settings WHERE `key` = ? LIMIT 1');
                $stmt->execute([$key]);
                if ($stmt->fetch()) {
                    $logs[] = "Setting {$key} already exists";
                    continue;
                }
                $pdo->prepare(
                    'INSERT INTO settings (`key`, `value`, `group`, `label`, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())'
                )->execute([$key, $value, $group, $label]);
                $logs[] = "Added setting {$key}";
            }

            $pdo->exec("CREATE TABLE IF NOT EXISTS payment_transactions (
                id INT AUTO_INCREMENT PRIMARY KEY,
                booking_id INT NOT NULL,
                user_id INT NOT NULL,
                amount DECIMAL(10,2) NOT NULL,
                currency VARCHAR(3) DEFAULT 'INR',
                payment_method VARCHAR(50) DEFAULT 'razorpay',
                transaction_id VARCHAR(100) NULL,
                razorpay_order_id VARCHAR(100) NULL,
                razorpay_payment_id VARCHAR(100) NULL,
                razorpay_signature VARCHAR(255) NULL,
                status ENUM('pending','success','failed','refunded') DEFAULT 'pending',
                gateway_response TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                INDEX idx_booking (booking_id),
                INDEX idx_user (user_id),
                INDEX idx_status (status)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            $logs[] = 'payment_transactions table ready';

            AuditLog::log('PAYMENT_SETUP', 'System', 0, [], ['steps' => count($logs)]);
            Session::flash('success', 'Payment gateway database setup completed. Enter your Razorpay keys below and save.');
            Session::flash('setup_logs', json_encode($logs));
        } catch (\Throwable $e) {
            Session::flash('error', 'Payment setup failed: ' . $e->getMessage());
        }

        $this->redirect(url('/settings'));
    }
}
