<?php

namespace App\Services;

use App\Core\Config;
use Exception;

/**
 * Firebase Phone Auth — Google sends SMS OTP (free tier on Spark, low cost on Blaze).
 * Setup: Firebase Console → Authentication → Phone → Enable
 *        Add your domain under Authorized domains
 */
class FirebaseAuthService
{
    public static function isConfigured(): bool
    {
        return trim((string) Config::get('FIREBASE_API_KEY', '')) !== ''
            && trim((string) Config::get('FIREBASE_AUTH_DOMAIN', '')) !== ''
            && trim((string) Config::get('FIREBASE_PROJECT_ID', '')) !== '';
    }

    /** Public web config for Firebase JS SDK */
    public static function clientConfig(): array
    {
        return [
            'apiKey'            => Config::get('FIREBASE_API_KEY', ''),
            'authDomain'        => Config::get('FIREBASE_AUTH_DOMAIN', ''),
            'projectId'         => Config::get('FIREBASE_PROJECT_ID', ''),
            'appId'             => Config::get('FIREBASE_APP_ID', ''),
            'messagingSenderId' => Config::get('FIREBASE_MESSAGING_SENDER_ID', ''),
        ];
    }

    /** Preferred OTP channel: firebase | sms | auto */
    public static function otpMode(): string
    {
        $mode = strtolower(trim((string) Config::get('OTP_PROVIDER', 'auto')));

        if ($mode === 'firebase' && self::isConfigured()) {
            return 'firebase';
        }
        if ($mode === 'sms') {
            return 'sms';
        }

        return self::isConfigured() ? 'firebase' : 'sms';
    }

    /**
     * Verify Firebase ID token and return normalized +91 phone.
     *
     * @return array{success:bool,phone?:string,message?:string}
     */
    public function verifyIdToken(string $idToken, string $expectedPhone = ''): array
    {
        $idToken = trim($idToken);
        if ($idToken === '') {
            return ['success' => false, 'message' => 'Missing verification token.'];
        }

        $apiKey = trim((string) Config::get('FIREBASE_API_KEY', ''));
        if ($apiKey === '') {
            return ['success' => false, 'message' => 'Firebase is not configured on the server.'];
        }

        $url = 'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=' . urlencode($apiKey);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode(['idToken' => $idToken]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 20,
        ]);
        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            return ['success' => false, 'message' => 'Could not verify OTP. Try again.'];
        }

        $body = json_decode($response ?: '{}', true);
        if ($httpCode >= 400 || empty($body['users'][0])) {
            $msg = $body['error']['message'] ?? 'Invalid or expired OTP session.';
            return ['success' => false, 'message' => self::friendlyError($msg)];
        }

        $user  = $body['users'][0];
        $phone = trim((string) ($user['phoneNumber'] ?? ''));
        if ($phone === '') {
            return ['success' => false, 'message' => 'Phone number not found in verification.'];
        }

        $normalized = (new OtpService())->normalizePhone($phone);

        if ($expectedPhone !== '') {
            $expected = (new OtpService())->normalizePhone($expectedPhone);
            if ($normalized !== $expected) {
                return ['success' => false, 'message' => 'Verified number does not match the mobile you entered.'];
            }
        }

        return ['success' => true, 'phone' => $normalized];
    }

    private static function friendlyError(string $raw): string
    {
        if (str_contains($raw, 'INVALID_ID_TOKEN') || str_contains($raw, 'expired')) {
            return 'OTP session expired. Send OTP again.';
        }

        return 'OTP verification failed. Please try again.';
    }
}
