<?php
/**
 * Setup Payment & WhatsApp Integration
 * Creates necessary tables and settings
 */

$host = 'localhost';
$db   = 'findownn_admin';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔗 Connected to database: $db\n\n";
    
    // ========================================
    // 1. UPDATE SETTINGS TABLE
    // ========================================
    echo "📝 Adding Payment & WhatsApp settings...\n";
    
    $settings = [
        // Payment Settings
        ['payment', 'razorpay_key_id', '', 'text', 'Razorpay Key ID'],
        ['payment', 'razorpay_key_secret', '', 'password', 'Razorpay Key Secret'],
        ['payment', 'razorpay_webhook_secret', '', 'password', 'Razorpay Webhook Secret'],
        ['payment', 'payment_mode', 'test', 'select', 'Payment Mode (test/live)'],
        ['payment', 'payment_gateway', 'razorpay', 'select', 'Payment Gateway'],
        ['payment', 'auto_capture', '1', 'checkbox', 'Auto Capture Payments'],
        
        // WhatsApp Settings  
        ['whatsapp', 'whatsapp_provider', 'openwa', 'select', 'WhatsApp Provider (twilio/meta/openwa)'],
        ['whatsapp', 'twilio_account_sid', '', 'text', 'Twilio Account SID'],
        ['whatsapp', 'twilio_auth_token', '', 'password', 'Twilio Auth Token'],
        ['whatsapp', 'twilio_whatsapp_number', '', 'text', 'Twilio WhatsApp Number'],
        ['whatsapp', 'meta_access_token', '', 'password', 'Meta Access Token'],
        ['whatsapp', 'meta_phone_number_id', '', 'text', 'Meta Phone Number ID'],
        ['whatsapp', 'meta_business_account_id', '', 'text', 'Meta Business Account ID'],
        ['whatsapp', 'openwa_base_url', '', 'text', 'OpenWA Base URL'],
        ['whatsapp', 'openwa_api_key', '', 'password', 'OpenWA API Key'],
        ['whatsapp', 'openwa_session_id', 'findownn', 'text', 'OpenWA Session ID'],
        ['whatsapp', 'openwa_webhook_secret', '', 'password', 'OpenWA Webhook Secret'],
        ['whatsapp', 'send_booking_confirmation', '1', 'checkbox', 'Send Booking Confirmation'],
        ['whatsapp', 'send_payment_confirmation', '1', 'checkbox', 'Send Payment Confirmation'],
        ['whatsapp', 'send_reminder', '1', 'checkbox', 'Send Booking Reminders'],
        ['whatsapp', 'reminder_hours_before', '24', 'number', 'Reminder Hours Before Booking'],
    ];
    
    $stmt = $pdo->prepare(
        "INSERT INTO settings (`group`, `key`, value, type, label, created_at, updated_at) 
         VALUES (?, ?, ?, ?, ?, NOW(), NOW())
         ON DUPLICATE KEY UPDATE 
         type = VALUES(type), 
         label = VALUES(label), 
         updated_at = NOW()"
    );
    
    foreach ($settings as $setting) {
        $stmt->execute($setting);
    }
    
    echo "✅ Settings added successfully!\n\n";
    
    // ========================================
    // 2. CREATE WHATSAPP_MESSAGES TABLE
    // ========================================
    echo "📱 Creating whatsapp_messages table...\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS whatsapp_messages (
            id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            user_id INT(10) UNSIGNED NULL,
            booking_id INT(10) UNSIGNED NULL,
            recipient_number VARCHAR(20) NOT NULL,
            message_type ENUM('booking_confirmation', 'payment_confirmation', 'reminder', 'cancellation', 'custom') NOT NULL DEFAULT 'custom',
            message_content TEXT NOT NULL,
            provider VARCHAR(20) NOT NULL DEFAULT 'twilio',
            provider_message_id VARCHAR(100) NULL,
            status ENUM('pending', 'sent', 'delivered', 'read', 'failed') NOT NULL DEFAULT 'pending',
            error_message TEXT NULL,
            sent_at DATETIME NULL,
            delivered_at DATETIME NULL,
            read_at DATETIME NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            INDEX idx_user_id (user_id),
            INDEX idx_booking_id (booking_id),
            INDEX idx_status (status),
            INDEX idx_created_at (created_at),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
            FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "✅ whatsapp_messages table created!\n\n";
    
    // ========================================
    // 3. UPDATE PAYMENTS TABLE
    // ========================================
    echo "💳 Updating payments table...\n";
    
    // Check if columns exist first
    $columns = $pdo->query("SHOW COLUMNS FROM payments")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('razorpay_order_id', $columns)) {
        $pdo->exec("ALTER TABLE payments ADD COLUMN razorpay_order_id VARCHAR(100) NULL AFTER gateway_txn_id");
        echo "   ✓ Added razorpay_order_id column\n";
    }
    
    if (!in_array('razorpay_payment_id', $columns)) {
        $pdo->exec("ALTER TABLE payments ADD COLUMN razorpay_payment_id VARCHAR(100) NULL AFTER razorpay_order_id");
        echo "   ✓ Added razorpay_payment_id column\n";
    }
    
    if (!in_array('razorpay_signature', $columns)) {
        $pdo->exec("ALTER TABLE payments ADD COLUMN razorpay_signature VARCHAR(255) NULL AFTER razorpay_payment_id");
        echo "   ✓ Added razorpay_signature column\n";
    }
    
    if (!in_array('payment_method', $columns)) {
        $pdo->exec("ALTER TABLE payments ADD COLUMN payment_method VARCHAR(50) NULL AFTER type");
        echo "   ✓ Added payment_method column\n";
    }
    
    echo "✅ payments table updated!\n\n";
    
    // ========================================
    // 4. UPDATE USERS TABLE
    // ========================================
    echo "👤 Updating users table...\n";
    
    $userColumns = $pdo->query("SHOW COLUMNS FROM users")->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('whatsapp_number', $userColumns)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN whatsapp_number VARCHAR(20) NULL AFTER phone");
        echo "   ✓ Added whatsapp_number column\n";
    }
    
    // Copy phone to whatsapp_number if empty
    $pdo->exec("UPDATE users SET whatsapp_number = phone WHERE whatsapp_number IS NULL OR whatsapp_number = ''");
    echo "   ✓ Copied phone numbers to whatsapp_number\n";
    
    echo "✅ users table updated!\n\n";
    
    // ========================================
    // 5. CREATE WEBHOOK LOGS TABLE
    // ========================================
    echo "📊 Creating webhook_logs table...\n";
    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS webhook_logs (
            id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            source VARCHAR(50) NOT NULL,
            event_type VARCHAR(100) NOT NULL,
            payload TEXT NOT NULL,
            signature VARCHAR(255) NULL,
            is_verified TINYINT(1) NOT NULL DEFAULT 0,
            processed TINYINT(1) NOT NULL DEFAULT 0,
            response TEXT NULL,
            error_message TEXT NULL,
            created_at DATETIME NOT NULL,
            INDEX idx_source (source),
            INDEX idx_event_type (event_type),
            INDEX idx_processed (processed),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    
    echo "✅ webhook_logs table created!\n\n";
    
    // ========================================
    // SUMMARY
    // ========================================
    echo "\n";
    echo "╔══════════════════════════════════════════════════════════╗\n";
    echo "║                                                          ║\n";
    echo "║  ✅ PAYMENT & WHATSAPP SETUP COMPLETED SUCCESSFULLY!     ║\n";
    echo "║                                                          ║\n";
    echo "╠══════════════════════════════════════════════════════════╣\n";
    echo "║                                                          ║\n";
    echo "║  📦 Created Tables:                                      ║\n";
    echo "║     • whatsapp_messages                                  ║\n";
    echo "║     • webhook_logs                                       ║\n";
    echo "║                                                          ║\n";
    echo "║  🔧 Updated Tables:                                      ║\n";
    echo "║     • payments (added Razorpay columns)                  ║\n";
    echo "║     • users (added whatsapp_number)                      ║\n";
    echo "║     • settings (added payment & whatsapp config)         ║\n";
    echo "║                                                          ║\n";
    echo "║  📝 Next Steps:                                          ║\n";
    echo "║     1. Configure Razorpay credentials in admin panel     ║\n";
    echo "║     2. Configure WhatsApp (Twilio/Meta) credentials      ║\n";
    echo "║     3. Test payment flow                                 ║\n";
    echo "║     4. Test WhatsApp messages                            ║\n";
    echo "║                                                          ║\n";
    echo "║  📖 Read: PAYMENT_WHATSAPP_SETUP_GUIDE.md               ║\n";
    echo "║                                                          ║\n";
    echo "╚══════════════════════════════════════════════════════════╝\n";
    echo "\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
