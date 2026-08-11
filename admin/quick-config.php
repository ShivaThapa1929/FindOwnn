<?php
/**
 * Quick Configuration Script
 * Enter your credentials here and run to auto-configure
 */

// ============================================
// ENTER YOUR CREDENTIALS HERE
// ============================================

$credentials = [
    // Razorpay (Get from: https://razorpay.com/ → Settings → API Keys)
    'razorpay_key_id' => 'rzp_test_XXXXXXXXXXXX',  // Replace with your Key ID
    'razorpay_key_secret' => 'XXXXXXXXXXXXXXXX',   // Replace with your Key Secret
    
    // Twilio (Get from: https://www.twilio.com/ → Console)
    'twilio_account_sid' => 'ACXXXXXXXXXXXXXXXX',  // Replace with your Account SID
    'twilio_auth_token' => 'XXXXXXXXXXXXXXXX',     // Replace with your Auth Token
    'twilio_whatsapp_number' => '+14155238886',    // Default sandbox number
];

// ============================================
// AUTO-CONFIGURATION (Don't edit below)
// ============================================

$host = 'localhost';
$db   = 'findownn_admin';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔧 Configuring Payment & WhatsApp...\n\n";
    
    // Update settings
    $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE `key` = ?");
    
    foreach ($credentials as $key => $value) {
        if ($value && $value !== 'XXXXXXXXXXXX' && $value !== 'XXXXXXXXXXXXXXXX') {
            $stmt->execute([$value, $key]);
            echo "✅ {$key}: Configured\n";
        } else {
            echo "⚠️  {$key}: Not configured (placeholder value)\n";
        }
    }
    
    echo "\n";
    echo "╔════════════════════════════════════════════╗\n";
    echo "║                                            ║\n";
    echo "║  ✅ CONFIGURATION COMPLETED!               ║\n";
    echo "║                                            ║\n";
    echo "║  🔗 Admin Panel:                           ║\n";
    echo "║     http://localhost:8000/admin/settings   ║\n";
    echo "║                                            ║\n";
    echo "║  🧪 Test Now:                              ║\n";
    echo "║     Create a booking & test payment        ║\n";
    echo "║                                            ║\n";
    echo "╚════════════════════════════════════════════╝\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
