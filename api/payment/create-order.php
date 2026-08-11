<?php
/**
 * Create Razorpay Order API
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../../admin/app/Core/Database.php';
require_once __DIR__ . '/../../admin/app/Services/RazorpayService.php';

use App\Core\Database;
use App\Services\RazorpayService;

try {
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid request data');
    }
    
    // Validate required fields
    $requiredFields = ['booking_id', 'user_id'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    $bookingId = (int) $input['booking_id'];
    $userId = (int) $input['user_id'];
    
    // Get booking details
    $db = Database::getInstance();
    $booking = $db->fetch(
        "SELECT b.*, v.name as venue_name, u.name as user_name, u.email as user_email, u.phone as user_phone
        FROM bookings b
        JOIN venues v ON b.venue_id = v.id
        JOIN users u ON b.user_id = u.id
        WHERE b.id = ? AND b.user_id = ?",
        [$bookingId, $userId]
    );
    
    if (!$booking) {
        throw new Exception('Booking not found');
    }

    // Always use server-side booking amount — never trust client amount
    $amount = (float) $booking['amount'];
    if ($amount <= 0) {
        throw new Exception('Invalid booking amount');
    }
    
    // Check if already paid
    if ($booking['payment_status'] === 'paid') {
        throw new Exception('Booking already paid');
    }
    
    // Initialize Razorpay
    $razorpay = new RazorpayService();
    
    if (!$razorpay->isConfigured()) {
        throw new Exception('Payment gateway not configured');
    }
    
    // Create Razorpay order
    $orderData = $razorpay->createOrder([
        'amount' => $amount,
        'currency' => 'INR',
        'receipt' => $booking['booking_reference'],
        'notes' => [
            'booking_id' => $bookingId,
            'user_id' => $userId,
            'venue_name' => $booking['venue_name']
        ]
    ]);
    
    // Save order details to database
    $db->insert(
        "INSERT INTO payments (user_id, type, subject_id, amount, currency, gateway, razorpay_order_id, status, created_at, updated_at)
        VALUES (?, 'booking', ?, ?, 'INR', 'razorpay', ?, 'pending', NOW(), NOW())",
        [$userId, $bookingId, $amount, $orderData['id']]
    );
    
    // Update booking — never downgrade admin-set paid/refunded
    $db->execute(
        "UPDATE bookings
        SET payment_status = 'pending', updated_at = NOW()
        WHERE id = ? AND payment_status NOT IN ('paid', 'refunded')",
        [$bookingId]
    );
    
    // Return response
    echo json_encode([
        'success' => true,
        'data' => [
            'order_id' => $orderData['id'],
            'amount' => $orderData['amount'],
            'currency' => $orderData['currency'],
            'key_id' => $razorpay->getKeyId(),
            'booking_reference' => $booking['booking_reference']
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
