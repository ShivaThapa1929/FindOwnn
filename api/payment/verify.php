<?php
/**
 * Verify Razorpay Payment API
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
require_once __DIR__ . '/../../admin/app/Services/WhatsAppService.php';

use App\Core\Database;
use App\Services\RazorpayService;
use App\Services\WhatsAppService;

try {
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid request data');
    }
    
    // Validate required fields
    $requiredFields = ['razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature', 'booking_id'];
    foreach ($requiredFields as $field) {
        if (!isset($input[$field])) {
            throw new Exception("Missing required field: $field");
        }
    }
    
    $orderId = $input['razorpay_order_id'];
    $paymentId = $input['razorpay_payment_id'];
    $signature = $input['razorpay_signature'];
    $bookingId = (int) $input['booking_id'];
    
    // Initialize Razorpay
    $razorpay = new RazorpayService();
    
    // Verify signature
    $isValid = $razorpay->verifyPaymentSignature([
        'razorpay_order_id' => $orderId,
        'razorpay_payment_id' => $paymentId,
        'razorpay_signature' => $signature
    ]);
    
    if (!$isValid) {
        throw new Exception('Invalid payment signature');
    }
    
    $db = Database::getInstance();

    $paymentRow = $db->fetch(
        "SELECT * FROM payments WHERE razorpay_order_id = ? AND type = 'booking' LIMIT 1",
        [$orderId]
    );
    if (!$paymentRow) {
        throw new Exception('Payment record not found for this order');
    }
    if ((int) $paymentRow['subject_id'] !== $bookingId) {
        throw new Exception('Booking does not match this payment order');
    }
    
    // Get payment details from Razorpay
    $paymentDetails = $razorpay->getPayment($paymentId);
    
    // Update database
    
    // Begin transaction
    $db->beginTransaction();
    
    try {
        // Update payment record
        $db->execute(
            "UPDATE payments 
             SET razorpay_payment_id = ?, 
                 razorpay_signature = ?,
                 payment_method = ?,
                 gateway_txn_id = ?,
                 status = 'success',
                 paid_at = NOW(),
                 updated_at = NOW()
             WHERE razorpay_order_id = ?",
            [
                $paymentId,
                $signature,
                $paymentDetails['method'] ?? null,
                $paymentId,
                $orderId
            ]
        );
        
        // Update booking status — preserve completed/cancelled set by admin
        $db->execute(
            "UPDATE bookings
             SET payment_status = 'paid',
                 payment_id = ?,
                 status = CASE
                     WHEN status IN ('completed', 'cancelled') THEN status
                     ELSE 'confirmed'
                 END,
                 updated_at = NOW()
             WHERE id = ?",
            [$paymentId, $bookingId]
        );
        
        // Commit transaction
        $db->commit();
        
        // Get booking details for WhatsApp
        $booking = $db->fetch(
            "SELECT b.*, v.name as venue_name, v.address as venue_address,
                    u.name as user_name, u.email as user_email, u.phone as user_phone, u.whatsapp_number,
                    s.name as sport_name
             FROM bookings b
             JOIN venues v ON b.venue_id = v.id
             JOIN users u ON b.user_id = u.id
             LEFT JOIN sports s ON b.sport_id = s.id
             WHERE b.id = ?",
            [$bookingId]
        );
        
        // Send WhatsApp notifications (async, don't wait)
        try {
            $whatsapp = new WhatsAppService();
            
            if ($whatsapp->isConfigured() && !empty($booking['whatsapp_number'])) {
                // Send booking confirmation
                $whatsapp->sendBookingConfirmation($booking);
                
                // Send payment confirmation
                $payment = $db->fetch(
                    "SELECT * FROM payments WHERE razorpay_payment_id = ?",
                    [$paymentId]
                );
                $whatsapp->sendPaymentConfirmation($booking, $payment);
            }
        } catch (Exception $e) {
            // Log error but don't fail the payment
            error_log("WhatsApp Error: " . $e->getMessage());
        }
        
        // Return success response
        echo json_encode([
            'success' => true,
            'message' => 'Payment verified successfully',
            'booking_reference' => $booking['booking_reference'],
            'payment_id' => $paymentId
        ]);
        
    } catch (Exception $e) {
        $db->rollback();
        throw $e;
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
