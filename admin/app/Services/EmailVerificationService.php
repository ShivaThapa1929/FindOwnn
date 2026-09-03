<?php

namespace App\Services;

use App\Core\Database;
use App\Models\ActivityLog;
use App\Models\AuditLog;
use App\Models\User;

/**
 * EmailVerificationService — Secure, token-based single-use email verification.
 */
class EmailVerificationService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Generate a cryptographically secure random raw token (64 hex characters).
     */
    public function generateRawToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Hash token for secure database storage (sha256).
     */
    public function hashToken(string $rawToken): string
    {
        return hash('sha256', $rawToken);
    }

    /**
     * Create or refresh verification token hash and expiration (24 hours).
     */
    public function createVerificationToken(int $userId): string
    {
        $rawToken = $this->generateRawToken();
        $tokenHash = $this->hashToken($rawToken);
        $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 60 minutes in PHP time

        $this->db->execute(
            "UPDATE users 
             SET email_verification_token_hash = ?,
                 email_verification_expires_at = ?,
                 email_verification_attempts = 0,
                 updated_at = NOW()
             WHERE id = ?",
            [$tokenHash, $expiresAt, $userId]
        );

        return $rawToken;
    }

    /**
     * Send email verification link to user.
     */
    public function sendVerificationEmail(array $user, string $rawToken): bool
    {
        $verificationUrl = url('/owner/verify-email?token=' . urlencode($rawToken) . '&email=' . urlencode($user['email']));

        $to = $user['email'];
        $subject = 'Verify Your FindOwnn Venue Owner Account';

        $htmlContent = $this->buildEmailHtml($user['name'], $verificationUrl);

        // System activity log for local dev / testing
        try {
            ActivityLog::record(
                "Sent email verification link to {$user['email']}: {$verificationUrl}",
                'auth', 'User', (int)$user['id']
            );
        } catch (\Throwable $e) {
            // Ignore logging error if log table unavailable
        }

        $mailService = new MailService();
        $plainText = "Hello {$user['name']},\n\nPlease verify your email address by clicking the link below:\n\n{$verificationUrl}\n\nThis link will expire in 24 hours.\n\n- FindOwnn Team";
        $res = $mailService->send($to, $subject, $plainText, ['html' => $htmlContent]);

        if (!empty($res['success'])) {
            return true;
        }

        // Direct host mail fallback
        $host = $_SERVER['HTTP_HOST'] ?? 'findownn.com';
        $host = preg_replace('/:\d+$/', '', $host);
        if ($host === 'localhost' || $host === '127.0.0.1') {
            $host = 'findownn.com';
        }

        $fromAddr = 'no-reply@' . $host;
        $msgId = '<' . time() . '.' . bin2hex(random_bytes(8)) . '@' . $host . '>';

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            'From: FindOwnn <' . $fromAddr . '>',
            'Reply-To: support@' . $host,
            'Date: ' . date('r'),
            'Message-ID: ' . $msgId,
            'X-Mailer: PHP/' . phpversion()
        ];

        return (bool) @mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=', $htmlContent, implode("\r\n", $headers), "-f {$fromAddr}");
    }

    /**
     * Verify a raw token and activate the account if valid.
     * 
     * @return array{success: bool, code: string, message: string, user?: array}
     */
    public function verifyEmailToken(string $rawToken): array
    {
        $rawToken = trim($rawToken);
        if ($rawToken === '') {
            return [
                'success' => false,
                'code'    => 'INVALID_TOKEN',
                'message' => 'Invalid verification link. Please request a new verification email.'
            ];
        }

        $tokenHash = $this->hashToken($rawToken);

        $user = $this->db->fetch(
            "SELECT * FROM users 
             WHERE email_verification_token_hash = ? AND deleted_at IS NULL",
            [$tokenHash]
        );

        if (!$user) {
            // Check if user email in session or unverified email is already active
            $sessionEmail = $_SESSION['unverified_email'] ?? '';
            if ($sessionEmail !== '') {
                $checkUser = $this->db->fetch(
                    "SELECT * FROM users WHERE LOWER(TRIM(email)) = ? AND deleted_at IS NULL",
                    [strtolower(trim($sessionEmail))]
                );
                if ($checkUser && (!empty($checkUser['email_verified_at']) || $checkUser['status'] === 'active')) {
                    return [
                        'success' => true,
                        'code'    => 'SUCCESS',
                        'message' => 'Your Venue Owner account is already verified! Welcome to your dashboard.',
                        'user'    => $checkUser
                    ];
                }
            }

            return [
                'success' => false,
                'code'    => 'INVALID_TOKEN',
                'message' => 'Invalid or expired verification link. If your account is already active, please try signing in.'
            ];
        }

        // If user is ALREADY verified and active (e.g. pre-fetched by Gmail link scanner or double-tapped on mobile)
        if (!empty($user['email_verified_at']) && $user['status'] === 'active') {
            $this->db->execute(
                "UPDATE users 
                 SET email_verification_token_hash = NULL,
                     email_verification_expires_at = NULL,
                     email_verification_attempts = 0,
                     updated_at = NOW()
                 WHERE id = ?",
                [$user['id']]
            );

            return [
                'success' => true,
                'code'    => 'SUCCESS',
                'message' => 'Your Venue Owner account is already verified! Welcome to your dashboard.',
                'user'    => $user
            ];
        }

        // Check if token has expired (60m PHP timestamp)
        $expiresAt = !empty($user['email_verification_expires_at']) ? strtotime($user['email_verification_expires_at']) : 0;
        if ($expiresAt > 0 && $expiresAt < time()) {
            return [
                'success' => false,
                'code'    => 'EXPIRED_TOKEN',
                'message' => 'Your verification link has expired. Please request a new verification email.',
                'user'    => $user
            ];
        }

        // Single-use token: Mark email as verified and set status active
        $this->db->execute(
            "UPDATE users 
             SET email_verified_at = NOW(),
                 status = 'active',
                 email_verification_token_hash = NULL,
                 email_verification_expires_at = NULL,
                 email_verification_attempts = 0,
                 updated_at = NOW()
             WHERE id = ?",
            [$user['id']]
        );

        try {
            AuditLog::log('EMAIL_VERIFIED', 'User', (int)$user['id'], ['status' => $user['status']], ['status' => 'active']);
            ActivityLog::record("Email verified for venue owner: {$user['email']}", 'auth', 'User', (int)$user['id']);
        } catch (\Throwable $e) {}

        $user['status'] = 'active';
        $user['email_verified_at'] = date('Y-m-d H:i:s');

        return [
            'success' => true,
            'code'    => 'SUCCESS',
            'message' => 'Your Venue Owner account has been verified successfully.',
            'user'    => $user
        ];
    }

    /**
     * Resend verification email with rate limiting (60s cooldown).
     * 
     * @return array{success: bool, message: string}
     */
    public function resendVerification(string $email): array
    {
        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            return ['success' => false, 'message' => 'No account found for this email address.'];
        }

        if (!empty($user['email_verified_at']) && $user['status'] === 'active') {
            return ['success' => false, 'message' => 'This account is already verified. Please sign in to your dashboard.'];
        }

        // 60-second rate limiting cooldown check
        $lastUpdated = !empty($user['updated_at']) ? strtotime($user['updated_at']) : 0;
        if ($lastUpdated > 0 && (time() - $lastUpdated) < 60) {
            $remaining = 60 - (time() - $lastUpdated);
            return [
                'success' => false,
                'message' => "Please wait {$remaining} seconds before requesting another verification email."
            ];
        }

        // Create new token and send email immediately
        $rawToken = $this->createVerificationToken((int)$user['id']);
        $sent = $this->sendVerificationEmail($user, $rawToken);

        if (!$sent) {
            return ['success' => false, 'message' => 'Failed to deliver verification email. Please verify mail settings or try again.'];
        }

        return ['success' => true, 'message' => 'A new verification link has been sent to your email address. Please check your inbox and spam folder.'];
    }

    /**
     * Change unverified email address and send verification to new email.
     * 
     * @return array{success: bool, message: string}
     */
    public function changeUnverifiedEmail(int $userId, string $newEmail): array
    {
        $newEmail = User::normalizeEmail($newEmail);

        if ($newEmail === '' || !filter_var($newEmail, FILTER_VALIDATE_EMAIL) || str_contains($newEmail, ' ')) {
            return ['success' => false, 'message' => 'Please enter a valid email address with no spaces.'];
        }

        if (DisposableEmailChecker::isDisposable($newEmail)) {
            return ['success' => false, 'message' => 'Please use a valid permanent email address that you can access.'];
        }

        // Check if email already registered by another account (global case-insensitive check because UNIQUE constraint is on email)
        $existing = $this->db->fetch(
            "SELECT id FROM users WHERE LOWER(TRIM(email)) = ? AND id != ? LIMIT 1",
            [strtolower(trim($newEmail)), $userId]
        );

        if ($existing) {
            return ['success' => false, 'message' => 'This email address is already registered.'];
        }

        $user = $this->db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);

        if (!$user) {
            return ['success' => false, 'message' => 'User account not found.'];
        }

        if (!empty($user['email_verified_at']) && $user['status'] === 'active') {
            return ['success' => false, 'message' => 'Your email is already verified and cannot be changed here.'];
        }

        // Update email and generate new token
        try {
            $this->db->execute(
                "UPDATE users 
                 SET email = ?,
                     email_verified_at = NULL,
                     status = 'pending_email_verification',
                     updated_at = NOW()
                 WHERE id = ?",
                [$newEmail, $userId]
            );
        } catch (\Throwable $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), '1062')) {
                return ['success' => false, 'message' => 'This email address is already registered.'];
            }
            throw $e;
        }

        $user['email'] = $newEmail;
        $rawToken = $this->createVerificationToken($userId);
        $this->sendVerificationEmail($user, $rawToken);

        return ['success' => true, 'message' => "Your email address has been updated to {$newEmail}. A new verification link has been sent."];
    }

    /**
     * HTML template for email verification.
     */
    private function buildEmailHtml(string $name, string $verificationUrl): string
    {
        $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $url = htmlspecialchars($verificationUrl, ENT_QUOTES, 'UTF-8');

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Verify Email Address — FindOwnn</title>
<style>
    body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #0b0f19; color: #e2e8f0; margin: 0; padding: 20px; }
    .container { max-width: 560px; margin: 0 auto; background: #131b2e; border-radius: 12px; border: 1px solid #1e293b; padding: 32px; box-shadow: 0 10px 25px rgba(0,0,0,0.5); }
    .logo { text-align: center; margin-bottom: 24px; }
    .logo h1 { color: #10b981; font-size: 26px; margin: 0; letter-spacing: -0.5px; }
    h2 { font-size: 20px; color: #f8fafc; margin-top: 0; }
    p { font-size: 15px; line-height: 1.6; color: #94a3b8; }
    .btn-container { text-align: center; margin: 30px 0; }
    .btn { display: inline-block; background-color: #10b981; color: #042f2e; text-decoration: none; font-weight: 700; font-size: 16px; padding: 14px 32px; border-radius: 8px; box-shadow: 0 4px 12px rgba(16,185,129,0.3); }
    .expiry { font-size: 13px; color: #64748b; text-align: center; margin-top: 20px; }
    .url-box { background: #0f172a; padding: 12px; border-radius: 6px; font-size: 12px; word-break: break-all; color: #38bdf8; margin-top: 20px; }
    .footer { text-align: center; font-size: 12px; color: #475569; margin-top: 32px; border-top: 1px solid #1e293b; padding-top: 16px; }
</style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>FindOwnn</h1>
        </div>
        <h2>Verify Your Email Address</h2>
        <p>Hello <strong>{$name}</strong>,</p>
        <p>Thank you for registering as a Venue Owner on FindOwnn. To activate your account and access your Venue Owner Dashboard, please verify that you have access to this email address.</p>
        
        <div class="btn-container">
            <a href="{$url}" class="btn" target="_blank">Verify Email Address</a>
        </div>

        <p class="expiry">This verification link will expire in <strong>60 minutes</strong>. If you did not create a FindOwnn account, please ignore this email.</p>
        
        <div class="url-box">
            If the button doesn't work, copy and paste this link into your browser:<br>
            {$url}
        </div>

        <div class="footer">
            &copy; 2026 FindOwnn. All rights reserved.
        </div>
    </div>
</body>
</html>
HTML;
    }
}
