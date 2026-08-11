<?php
namespace Api\V1;

require_once __DIR__ . '/ApiController.php';

use App\Services\RazorpayService;
use App\Services\WhatsAppService;

class PaymentController extends ApiController
{
    public static function handle($method, $action, $body)
    {
        if ($action === 'webhook' && $method === 'POST') {
            return self::handleWebhook($body);
        }

        self::requireAuth();

        if ($action === 'initiate' && $method === 'POST') {
            return self::initiatePayment($body);
        }

        if ($action === 'verify' && $method === 'POST') {
            return self::verifyPayment($body);
        }

        return self::error('Invalid action', 404);
    }

    /**
     * Create Razorpay order for a booking
     * POST /api/v1/payments/initiate
     */
    private static function initiatePayment(array $data)
    {
        if (empty($data['booking_id'])) {
            return self::error('Booking ID required', 400, 'PAYMENT_002');
        }

        $bookingId = (int) $data['booking_id'];
        $userId = (int) self::$user['id'];

        $booking = self::$db->fetch(
            "SELECT b.*, v.name AS venue_name
             FROM bookings b
             JOIN venues v ON b.venue_id = v.id
             WHERE b.id = ? AND b.user_id = ?",
            [$bookingId, $userId]
        );

        if (!$booking) {
            return self::error('Booking not found', 404, 'BOOKING_001');
        }

        if ($booking['payment_status'] === 'paid') {
            return self::error('Booking already paid', 400, 'PAYMENT_003');
        }

        require_once __DIR__ . '/../../admin/app/Services/RazorpayService.php';
        $razorpay = new RazorpayService();

        if (!$razorpay->isConfigured()) {
            return self::error('Payment gateway not configured', 503, 'PAYMENT_004');
        }

        $amount = (float) ($booking['amount'] ?? 0);
        if ($amount <= 0) {
            return self::error('Invalid booking amount', 400, 'PAYMENT_005');
        }

        try {
            $orderData = $razorpay->createOrder([
                'amount'   => $amount,
                'currency' => 'INR',
                'receipt'  => $booking['booking_reference'],
                'notes'    => [
                    'booking_id' => $bookingId,
                    'user_id'    => $userId,
                    'venue_name' => $booking['venue_name'],
                ],
            ]);
        } catch (\Exception $e) {
            return self::error($e->getMessage(), 502, 'PAYMENT_006');
        }

        self::$db->insert(
            "INSERT INTO payments (user_id, type, subject_id, amount, currency, gateway, razorpay_order_id, status, created_at, updated_at)
             VALUES (?, 'booking', ?, ?, 'INR', 'razorpay', ?, 'pending', NOW(), NOW())",
            [$userId, $bookingId, $amount, $orderData['id']]
        );

        // Do not downgrade admin-set paid/refunded status when re-initiating checkout
        self::$db->execute(
            "UPDATE bookings
             SET payment_status = 'pending', updated_at = NOW()
             WHERE id = ? AND payment_status NOT IN ('paid', 'refunded')",
            [$bookingId]
        );

        return self::success([
            'order_id'          => $orderData['id'],
            'amount'            => (int) $orderData['amount'],
            'currency'          => $orderData['currency'],
            'key_id'            => $razorpay->getKeyId(),
            'booking_id'        => $bookingId,
            'booking_reference' => $booking['booking_reference'],
            'gateway'           => 'razorpay',
            'mode'              => $razorpay->getMode(),
        ], 'Payment initiated successfully');
    }

    /**
     * Verify Razorpay payment signature and confirm booking
     * POST /api/v1/payments/verify
     */
    private static function verifyPayment(array $data)
    {
        $required = ['razorpay_order_id', 'razorpay_payment_id', 'razorpay_signature', 'booking_id'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                return self::error("Missing required field: $field", 400, 'PAYMENT_007');
            }
        }

        $orderId    = $data['razorpay_order_id'];
        $paymentId  = $data['razorpay_payment_id'];
        $signature  = $data['razorpay_signature'];
        $bookingId  = (int) $data['booking_id'];
        $userId     = (int) self::$user['id'];

        $booking = self::$db->fetch(
            "SELECT * FROM bookings WHERE id = ? AND user_id = ?",
            [$bookingId, $userId]
        );

        if (!$booking) {
            return self::error('Booking not found', 404, 'BOOKING_001');
        }

        if ($booking['payment_status'] === 'paid') {
            return self::success([
                'booking_id'        => $bookingId,
                'booking_reference' => $booking['booking_reference'],
                'payment_id'        => $booking['payment_id'],
                'payment_status'    => 'paid',
                'booking_status'    => $booking['status'],
            ], 'Booking already paid');
        }

        require_once __DIR__ . '/../../admin/app/Services/RazorpayService.php';
        $razorpay = new RazorpayService();

        if (!$razorpay->verifyPaymentSignature([
            'razorpay_order_id'   => $orderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature'  => $signature,
        ])) {
            return self::error('Invalid payment signature', 400, 'PAYMENT_001');
        }

        try {
            $paymentDetails = $razorpay->getPayment($paymentId);
        } catch (\Exception $e) {
            return self::error('Could not fetch payment details', 502, 'PAYMENT_008');
        }

        self::$db->beginTransaction();

        try {
            self::$db->execute(
                "UPDATE payments
                 SET razorpay_payment_id = ?,
                     razorpay_signature = ?,
                     payment_method = ?,
                     gateway_txn_id = ?,
                     status = 'paid',
                     paid_at = NOW(),
                     updated_at = NOW()
                 WHERE razorpay_order_id = ? AND user_id = ?",
                [
                    $paymentId,
                    $signature,
                    $paymentDetails['method'] ?? null,
                    $paymentId,
                    $orderId,
                    $userId,
                ]
            );

            self::$db->execute(
                "UPDATE bookings
                 SET payment_status = 'paid',
                     payment_id = ?,
                     status = CASE
                         WHEN status IN ('completed', 'cancelled') THEN status
                         ELSE 'confirmed'
                     END,
                     updated_at = NOW()
                 WHERE id = ? AND user_id = ?",
                [$paymentId, $bookingId, $userId]
            );

            self::$db->commit();
        } catch (\Exception $e) {
            self::$db->rollback();
            return self::error('Failed to update payment records', 500, 'PAYMENT_009');
        }

        $bookingDetails = self::$db->fetch(
            "SELECT b.*, v.name AS venue_name, v.address AS venue_address,
                    u.name AS user_name, u.email AS user_email, u.phone AS user_phone, u.whatsapp_number,
                    s.name AS sport_name
             FROM bookings b
             JOIN venues v ON b.venue_id = v.id
             JOIN users u ON b.user_id = u.id
             LEFT JOIN sports s ON b.sport_id = s.id
             WHERE b.id = ?",
            [$bookingId]
        );

        try {
            require_once __DIR__ . '/../../admin/app/Services/WhatsAppService.php';
            $whatsapp = new WhatsAppService();

            if ($whatsapp->isConfigured() && !empty($bookingDetails['whatsapp_number'])) {
                $whatsapp->sendBookingConfirmation($bookingDetails);

                $payment = self::$db->fetch(
                    "SELECT * FROM payments WHERE razorpay_payment_id = ?",
                    [$paymentId]
                );
                if ($payment) {
                    $whatsapp->sendPaymentConfirmation($bookingDetails, $payment);
                }
            }
        } catch (\Exception $e) {
            error_log('WhatsApp payment notification failed: ' . $e->getMessage());
        }

        return self::success([
            'booking_id'        => $bookingId,
            'booking_reference' => $bookingDetails['booking_reference'] ?? $booking['booking_reference'],
            'payment_id'        => $paymentId,
            'payment_status'    => 'paid',
            'booking_status'    => $bookingDetails['status'] ?? 'confirmed',
        ], 'Payment verified successfully');
    }

    /**
     * Razorpay webhook (no auth — signature verified)
     * POST /api/v1/payments/webhook
     */
    private static function handleWebhook(array $data)
    {
        $payload   = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

        require_once __DIR__ . '/../../admin/app/Services/RazorpayService.php';
        $razorpay = new RazorpayService();

        if (!$razorpay->verifyWebhookSignature($payload, $signature)) {
            return self::error('Invalid webhook signature', 401, 'PAYMENT_010');
        }

        $event = json_decode($payload, true);
        $eventType = $event['event'] ?? '';

        if ($eventType === 'payment.captured') {
            $paymentEntity = $event['payload']['payment']['entity'] ?? [];
            $orderId = $paymentEntity['order_id'] ?? null;

            if ($orderId) {
                self::$db->execute(
                    "UPDATE payments SET status = 'paid', paid_at = NOW(), updated_at = NOW()
                     WHERE razorpay_order_id = ? AND status = 'pending'",
                    [$orderId]
                );

                $payment = self::$db->fetch(
                    "SELECT subject_id FROM payments WHERE razorpay_order_id = ? LIMIT 1",
                    [$orderId]
                );
                if ($payment && !empty($payment['subject_id'])) {
                    self::$db->execute(
                        "UPDATE bookings
                         SET payment_status = 'paid',
                             status = CASE
                                 WHEN status IN ('completed', 'cancelled') THEN status
                                 ELSE 'confirmed'
                             END,
                             updated_at = NOW()
                         WHERE id = ? AND payment_status != 'paid'",
                        [(int) $payment['subject_id']]
                    );
                }
            }
        }

        return self::success(['message' => 'Webhook processed'], 'Webhook received');
    }
}
