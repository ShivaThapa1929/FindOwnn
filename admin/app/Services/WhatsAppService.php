<?php

namespace App\Services;

use App\Core\Database;
use Exception;

class WhatsAppService
{
    private Database $db;
    private string $provider;
    private array $config;
    private ?bool $usesNewSchema = null;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->loadSettings();
    }
    
    private function loadSettings(): void
    {
        $settings = $this->db->fetchAll("SELECT * FROM settings WHERE `group` = 'whatsapp'");
        
        $this->config = [];
        foreach ($settings as $setting) {
            $this->config[$setting['key']] = $setting['value'];
        }
        
        $this->provider = $this->config['whatsapp_provider'] ?? 'twilio';
    }
    
    /**
     * Send WhatsApp Message
     */
    public function sendMessage(string $to, string $message, array $params = []): array
    {
        // Normalize phone number
        $to = $this->normalizePhoneNumber($to);
        
        // Log message attempt
        $messageId = $this->logMessage($to, 'text', $message, 'pending');
        
        try {
            if ($this->provider === 'twilio') {
                $result = $this->sendViaTwilio($to, $message);
            } elseif ($this->provider === 'meta') {
                $result = $this->sendViaMeta($to, $message, $params);
            } elseif ($this->provider === 'openwa') {
                $result = $this->sendViaOpenWA($to, $message, $params);
            } else {
                throw new Exception("Invalid WhatsApp provider: {$this->provider}");
            }
            
            // Update message status
            $this->updateMessageStatus($messageId, 'sent', $result['sid'] ?? $result['message_id'] ?? null);
            
            return [
                'success' => true,
                'message_id' => $messageId,
                'provider_id' => $result['sid'] ?? $result['message_id'] ?? null,
                'status' => 'sent'
            ];
            
        } catch (Exception $e) {
            // Update message status as failed
            $this->updateMessageStatus($messageId, 'failed', null, $e->getMessage());
            
            return [
                'success' => false,
                'message_id' => $messageId,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Send via Twilio
     */
    private function sendViaTwilio(string $to, string $message): array
    {
        $accountSid = $this->config['twilio_account_sid'] ?? '';
        $authToken = $this->config['twilio_auth_token'] ?? '';
        $fromNumber = $this->config['twilio_whatsapp_number'] ?? '';
        
        if (empty($accountSid) || empty($authToken) || empty($fromNumber)) {
            throw new Exception('Twilio credentials not configured');
        }
        
        $url = "https://api.twilio.com/2010-04-01/Accounts/$accountSid/Messages.json";
        
        $data = [
            'From' => 'whatsapp:' . $fromNumber,
            'To' => 'whatsapp:' . $to,
            'Body' => $message
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_USERPWD, "$accountSid:$authToken");
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("Twilio API Error: $error");
        }
        
        $result = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $errorMsg = $result['message'] ?? 'Unknown error';
            throw new Exception("Twilio Error: $errorMsg");
        }
        
        return $result;
    }
    
    /**
     * Send via Meta WhatsApp Business API
     */
    private function sendViaMeta(string $to, string $message, array $params = []): array
    {
        $accessToken = $this->config['meta_access_token'] ?? '';
        $phoneNumberId = $this->config['meta_phone_number_id'] ?? '';
        
        if (empty($accessToken) || empty($phoneNumberId)) {
            throw new Exception('Meta WhatsApp credentials not configured');
        }
        
        $url = "https://graph.facebook.com/v18.0/$phoneNumberId/messages";
        
        // If template specified, use template message
        if (!empty($params['template_name'])) {
            $data = [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'template',
                'template' => [
                    'name' => $params['template_name'],
                    'language' => ['code' => $params['language'] ?? 'en'],
                    'components' => $params['components'] ?? []
                ]
            ];
        } else {
            // Send text message
            $data = [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => ['body' => $message]
            ];
        }
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "Authorization: Bearer $accessToken"
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("Meta API Error: $error");
        }
        
        $result = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $errorMsg = $result['error']['message'] ?? 'Unknown error';
            throw new Exception("Meta Error: $errorMsg");
        }
        
        return [
            'message_id' => $result['messages'][0]['id'] ?? null
        ];
    }

    /**
     * Send via OpenWA self-hosted gateway
     */
    private function sendViaOpenWA(string $to, string $message, array $params = []): array
    {
        $openwa = new OpenWAService($this->config);

        if (!$openwa->isConfigured()) {
            throw new Exception('OpenWA credentials not configured (base URL + API key required)');
        }

        if (!empty($params['media_url'])) {
            $result = $openwa->sendMedia(
                $to,
                $params['media_url'],
                $message,
                $params['media_type'] ?? 'image'
            );
        } elseif (!empty($params['bulk']) && is_array($params['bulk'])) {
            $result = $openwa->sendBulk($params['bulk'], $message);
            return ['message_id' => $result['jobId'] ?? null];
        } else {
            $result = $openwa->sendText($to, $message);
        }

        return ['message_id' => $result['message_id'] ?? null];
    }
    
    /**
     * Send Booking Confirmation (Growth plan+ for venue owner)
     */
    public function sendBookingConfirmation(array $booking): array
    {
        if (!$this->ownerAllowsWhatsApp($booking)) {
            return $this->skippedPlanResponse('booking confirmation');
        }
        $message = $this->formatBookingMessage($booking);
        return $this->sendMessage($booking['user_phone'], $message, ['skip_plan_check' => true]);
    }
    
    /**
     * Send Payment Confirmation
     */
    public function sendPaymentConfirmation(array $booking, array $payment): array
    {
        if (!$this->ownerAllowsWhatsApp($booking)) {
            return $this->skippedPlanResponse('payment confirmation');
        }
        $message = $this->formatPaymentMessage($booking, $payment);
        return $this->sendMessage($booking['user_phone'], $message, ['skip_plan_check' => true]);
    }
    
    /**
     * Send Booking Reminder
     */
    public function sendBookingReminder(array $booking): array
    {
        if (!$this->ownerAllowsWhatsApp($booking)) {
            return $this->skippedPlanResponse('booking reminder');
        }
        $message = $this->formatReminderMessage($booking);
        return $this->sendMessage($booking['user_phone'], $message, ['skip_plan_check' => true]);
    }
    
    /**
     * Send Cancellation Notification
     */
    public function sendCancellationNotification(array $booking): array
    {
        $message = $this->formatCancellationMessage($booking);
        return $this->sendMessage($booking['user_phone'], $message);
    }
    
    /**
     * Format Booking Confirmation Message
     */
    private function formatBookingMessage(array $booking): string
    {
        return "🎉 *Booking Confirmed!*\n\n" .
               "Hello {$booking['user_name']},\n\n" .
               "Your booking at *{$booking['venue_name']}* is confirmed!\n\n" .
               "📅 *Date:* " . date('d M Y', strtotime($booking['booking_date'])) . "\n" .
               "⏰ *Time:* {$booking['start_time']} - {$booking['end_time']}\n" .
               "🎯 *Sport:* {$booking['sport_name']}\n" .
               "💰 *Amount:* ₹" . number_format($booking['amount'], 2) . "\n" .
               "🔖 *Booking ID:* {$booking['booking_reference']}\n\n" .
               "📍 *Venue Address:*\n{$booking['venue_address']}\n\n" .
               "Thank you for choosing Findownn! 🏆";
    }
    
    /**
     * Format Payment Confirmation Message
     */
    private function formatPaymentMessage(array $booking, array $payment): string
    {
        return "✅ *Payment Successful!*\n\n" .
               "Hello {$booking['user_name']},\n\n" .
               "Your payment has been received successfully.\n\n" .
               "💳 *Payment ID:* {$payment['gateway_txn_id']}\n" .
               "💰 *Amount Paid:* ₹" . number_format($payment['amount'], 2) . "\n" .
               "🔖 *Booking ID:* {$booking['booking_reference']}\n" .
               "📅 *Date:* " . date('d M Y', strtotime($booking['booking_date'])) . "\n\n" .
               "Your booking is now confirmed! See you at the venue! 🎉";
    }
    
    /**
     * Format Reminder Message
     */
    private function formatReminderMessage(array $booking): string
    {
        return "⏰ *Booking Reminder*\n\n" .
               "Hello {$booking['user_name']},\n\n" .
               "This is a reminder for your upcoming booking:\n\n" .
               "🏟️ *Venue:* {$booking['venue_name']}\n" .
               "📅 *Date:* " . date('d M Y', strtotime($booking['booking_date'])) . "\n" .
               "⏰ *Time:* {$booking['start_time']} - {$booking['end_time']}\n" .
               "🔖 *Booking ID:* {$booking['booking_reference']}\n\n" .
               "See you soon! 🏆";
    }
    
    /**
     * Format Cancellation Message
     */
    private function formatCancellationMessage(array $booking): string
    {
        return "❌ *Booking Cancelled*\n\n" .
               "Hello {$booking['user_name']},\n\n" .
               "Your booking has been cancelled.\n\n" .
               "🔖 *Booking ID:* {$booking['booking_reference']}\n" .
               "🏟️ *Venue:* {$booking['venue_name']}\n" .
               "📅 *Date:* " . date('d M Y', strtotime($booking['booking_date'])) . "\n\n" .
               "If this was a mistake, please contact us.\n\n" .
               "Thank you!";
    }
    
    /**
     * Normalize Phone Number to International Format
     */
    private function normalizePhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Add country code if not present
        if (strlen($phone) === 10) {
            $phone = '91' . $phone; // India
        }
        
        // Ensure + prefix
        if ($phone[0] !== '+') {
            $phone = '+' . $phone;
        }
        
        return $phone;
    }
    
    /**
     * Log WhatsApp Message
     */
    private function logMessage(string $to, string $type, string $message, string $status): int
    {
        $messageType = $this->normalizeMessageType($type);

        if ($this->usesNewSchema()) {
            return $this->db->insert(
                "INSERT INTO whatsapp_messages 
                (user_id, recipient_number, message_type, message_content, status, created_at)
                VALUES (?, ?, ?, ?, ?, NOW())",
                [null, $to, $messageType, $message, $status]
            );
        }

        return $this->db->insert(
            "INSERT INTO whatsapp_messages 
            (user_id, phone_number, message_type, message, status, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())",
            [$this->resolveUserIdForPhone($to), $to, $messageType, $message, $status]
        );
    }
    
    /**
     * Update Message Status
     */
    private function updateMessageStatus(int $messageId, string $status, ?string $providerId = null, ?string $error = null): void
    {
        if ($this->usesNewSchema()) {
            $sql = "UPDATE whatsapp_messages SET status = ?, provider_message_id = ?, error_message = ?, updated_at = NOW() WHERE id = ?";
            $this->db->execute($sql, [$status, $providerId, $error, $messageId]);
            return;
        }

        $sql = "UPDATE whatsapp_messages SET status = ?, error_message = ?, sent_at = IF(? IN ('sent','delivered'), NOW(), sent_at) WHERE id = ?";
        $this->db->execute($sql, [$status, $error, $status, $messageId]);
    }

    private function usesNewSchema(): bool
    {
        if ($this->usesNewSchema !== null) {
            return $this->usesNewSchema;
        }

        try {
            $columns = $this->db->fetchAll('SHOW COLUMNS FROM whatsapp_messages');
            $names   = array_column($columns, 'Field');
            $this->usesNewSchema = in_array('recipient_number', $names, true);
        } catch (\Throwable) {
            $this->usesNewSchema = false;
        }

        return $this->usesNewSchema;
    }

    private function normalizeMessageType(string $type): string
    {
        if ($this->usesNewSchema()) {
            $map = [
                'text'                 => 'custom',
                'reminder'             => 'reminder',
                'booking_confirmation' => 'booking_confirmation',
                'payment_confirmation' => 'payment_confirmation',
                'cancellation'       => 'cancellation',
                'promotion'            => 'custom',
            ];
            return $map[$type] ?? 'custom';
        }

        $map = [
            'text'                 => 'promotion',
            'custom'               => 'promotion',
            'payment_confirmation' => 'booking_confirmation',
            'reminder'             => 'reminder',
            'booking_confirmation' => 'booking_confirmation',
            'cancellation'         => 'cancellation',
            'promotion'            => 'promotion',
        ];

        return $map[$type] ?? 'promotion';
    }

    private function resolveUserIdForPhone(string $phone): int
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) >= 10) {
            $last10 = substr($digits, -10);
            $user   = $this->db->fetch(
                "SELECT id FROM users
                 WHERE REPLACE(REPLACE(COALESCE(phone, ''), '+', ''), ' ', '') LIKE ?
                    OR REPLACE(REPLACE(COALESCE(whatsapp_number, ''), '+', ''), ' ', '') LIKE ?
                 LIMIT 1",
                ['%' . $last10, '%' . $last10]
            );
            if ($user) {
                return (int) $user['id'];
            }
        }

        $fallback = $this->db->fetchColumn(
            "SELECT id FROM users WHERE role IN ('super_admin', 'admin') ORDER BY id ASC LIMIT 1"
        );

        return (int) ($fallback ?: 1);
    }
    
    /**
     * Check if WhatsApp is configured
     */
    public function isConfigured(): bool
    {
        if ($this->provider === 'twilio') {
            return !empty($this->config['twilio_account_sid']) && 
                   !empty($this->config['twilio_auth_token']);
        } elseif ($this->provider === 'meta') {
            return !empty($this->config['meta_access_token']) && 
                   !empty($this->config['meta_phone_number_id']);
        } elseif ($this->provider === 'openwa') {
            $openwa = new OpenWAService($this->config);
            return $openwa->isConfigured();
        }
        
        return false;
    }
    
    /**
     * Get Provider Name
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    private function ownerAllowsWhatsApp(array $booking): bool
    {
        require_once __DIR__ . '/PlatformFeeService.php';

        $ownerId = (int) ($booking['owner_id'] ?? 0);
        if ($ownerId <= 0 && !empty($booking['venue_id'])) {
            $row = $this->db->fetch('SELECT owner_id FROM venues WHERE id = ?', [(int) $booking['venue_id']]);
            $ownerId = (int) ($row['owner_id'] ?? 0);
        }

        if ($ownerId <= 0) {
            return false;
        }

        return (new PlatformFeeService())->hasWhatsAppAccess($ownerId);
    }

    /** @return array{success: false, skipped: true, error: string} */
    private function skippedPlanResponse(string $type): array
    {
        return [
            'success' => false,
            'skipped' => true,
            'error'   => "WhatsApp {$type} requires Growth plan or higher for this venue owner.",
        ];
    }
}
