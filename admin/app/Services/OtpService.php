<?php

namespace App\Services;

use App\Contracts\RecordsPhoneVerification;
use App\Core\Database;
use Exception;

class OtpService implements RecordsPhoneVerification
{
    private Database $db;
    private int $ttlMinutes = 10;
    private int $maxAttempts = 5;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureTable();
    }

    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) === 10) {
            return '+91' . $digits;
        }
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return '+' . $digits;
        }

        return $phone;
    }

    /** @return array{success:bool,message:string} */
    public function send(string $phone, string $purpose = 'registration'): array
    {
        $phone = $this->normalizePhone($phone);
        if (!preg_match('/^\+91[6-9]\d{9}$/', $phone)) {
            return ['success' => false, 'message' => 'Enter a valid 10-digit Indian mobile number.'];
        }

        $this->purgeExpired();

        $recent = $this->db->fetch(
            "SELECT id FROM phone_otps
             WHERE phone = ? AND purpose = ? AND verified_at IS NULL AND expires_at > NOW()
               AND created_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
             LIMIT 1",
            [$phone, $purpose]
        );
        if ($recent) {
            return ['success' => false, 'message' => 'Please wait 1 minute before requesting another OTP.'];
        }

        $otp  = (string) random_int(100000, 999999);
        $hash = password_hash($otp, PASSWORD_DEFAULT);

        $otpId = $this->db->insert(
            "INSERT INTO phone_otps (phone, otp_hash, purpose, expires_at, created_at)
             VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE), NOW())",
            [$phone, $hash, $purpose, $this->ttlMinutes]
        );

        $delivery = $this->deliverOtp($phone, $otp);

        if (!$delivery['success']) {
            $this->db->execute('DELETE FROM phone_otps WHERE id = ?', [(int) $otpId]);

            return [
                'success' => false,
                'message' => $delivery['message'],
            ];
        }

        return [
            'success' => true,
            'message' => $delivery['message'],
        ];
    }

    /** @return array{success:bool,message:string} */
    public function verify(string $phone, string $code, string $purpose = 'registration'): array
    {
        $phone = $this->normalizePhone($phone);
        $code  = preg_replace('/\D/', '', $code);

        if (strlen($code) !== 6) {
            return ['success' => false, 'message' => 'Enter the 6-digit OTP.'];
        }

        $row = $this->db->fetch(
            "SELECT * FROM phone_otps
             WHERE phone = ? AND purpose = ? AND verified_at IS NULL AND expires_at > NOW()
             ORDER BY id DESC LIMIT 1",
            [$phone, $purpose]
        );

        if (!$row) {
            return ['success' => false, 'message' => 'OTP expired or not found. Request a new one.'];
        }

        if ((int) $row['attempts'] >= $this->maxAttempts) {
            return ['success' => false, 'message' => 'Too many attempts. Request a new OTP.'];
        }

        $this->db->execute(
            'UPDATE phone_otps SET attempts = attempts + 1 WHERE id = ?',
            [(int) $row['id']]
        );

        if (!password_verify($code, $row['otp_hash'])) {
            return ['success' => false, 'message' => 'Invalid OTP. Please try again.'];
        }

        $this->db->execute(
            'UPDATE phone_otps SET verified_at = NOW() WHERE id = ?',
            [(int) $row['id']]
        );

        return ['success' => true, 'message' => 'Phone number verified.'];
    }

    public function isVerifiedRecently(string $phone, string $purpose = 'registration'): bool
    {
        $phone = $this->normalizePhone($phone);

        $row = $this->db->fetch(
            "SELECT id FROM phone_otps
             WHERE phone = ? AND purpose = ? AND verified_at IS NOT NULL
               AND verified_at > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
             ORDER BY id DESC LIMIT 1",
            [$phone, $purpose]
        );

        return (bool) $row;
    }

    /** Mark phone verified after external auth (Firebase Phone Auth, etc.). */
    public function recordExternalVerification(string $phone, string $purpose = 'registration', string $source = 'external'): array
    {
        $phone = $this->normalizePhone($phone);

        $this->db->insert(
            "INSERT INTO phone_otps (phone, otp_hash, purpose, verified_at, expires_at, created_at)
             VALUES (?, ?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 30 MINUTE), NOW())",
            [$phone, 'verified:' . $source, $purpose]
        );

        return [
            'success' => true,
            'message' => 'Mobile number verified successfully.',
            'phone'   => $phone,
        ];
    }

    /** @return array{success:bool,message:string} */
    private function deliverOtp(string $phone, string $otp): array
    {
        $sms = new SmsService();
        $smsError = '';

        if ($sms->isConfigured()) {
            $result = $sms->sendOtp($phone, $otp);
            if ($result['success']) {
                return $result;
            }
            $smsError = $result['message'] ?? 'SMS delivery failed';
            error_log('[Findownn OTP] SMS failed for ' . $phone . ': ' . $smsError);
        }

        // WhatsApp fallback when SMS is not configured or delivery failed
        try {
            $wa = new WhatsAppService();
            if ($wa->isConfigured()) {
                $message = "Your Findownn verification code is: {$otp}. Valid for {$this->ttlMinutes} minutes. Do not share this code.";
                $result  = $wa->sendMessage($phone, $message);
                if (!empty($result['success'])) {
                    return ['success' => true, 'message' => 'OTP sent to your WhatsApp number.'];
                }
                error_log('[Findownn OTP WhatsApp fallback] ' . ($result['error'] ?? 'failed'));
            }
        } catch (Exception $e) {
            error_log('[Findownn OTP WhatsApp fallback] ' . $e->getMessage());
        }

        if ($smsError !== '') {
            return ['success' => false, 'message' => $smsError];
        }

        return [
            'success' => false,
            'message' => 'SMS is not set up. Add a valid FAST2SMS_API_KEY in admin/.env (Fast2SMS → Developer → API Key).',
        ];
    }

    private function purgeExpired(): void
    {
        try {
            $this->db->execute('DELETE FROM phone_otps WHERE expires_at < DATE_SUB(NOW(), INTERVAL 1 DAY)');
        } catch (Exception) {
            // table may not exist yet
        }
    }

    private function ensureTable(): void
    {
        try {
            $this->db->fetchColumn('SELECT 1 FROM phone_otps LIMIT 1');
            return;
        } catch (Exception) {
            // create table
        }

        try {
            $this->db->execute(
                "CREATE TABLE IF NOT EXISTS `phone_otps` (
                    `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `phone`       VARCHAR(20)  NOT NULL,
                    `otp_hash`    VARCHAR(255) NOT NULL,
                    `purpose`     VARCHAR(40)  NOT NULL DEFAULT 'registration',
                    `attempts`    TINYINT UNSIGNED NOT NULL DEFAULT 0,
                    `verified_at` DATETIME     NULL DEFAULT NULL,
                    `expires_at`  DATETIME     NOT NULL,
                    `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_phone_otps_phone` (`phone`),
                    KEY `idx_phone_otps_expires` (`expires_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );
        } catch (Exception) {
            // ignore
        }

        try {
            $this->db->fetchColumn("SELECT phone_verified_at FROM users LIMIT 1");
        } catch (Exception) {
            try {
                $this->db->execute('ALTER TABLE `users` ADD COLUMN `phone_verified_at` DATETIME NULL DEFAULT NULL AFTER `phone`');
            } catch (Exception) {
                // column may already exist
            }
        }
    }
}
