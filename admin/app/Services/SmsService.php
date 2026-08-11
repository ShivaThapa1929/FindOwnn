<?php
/**
 * SMS delivery for OTP (Fast2SMS, Twilio, MSG91).
 */
namespace App\Services;

use App\Core\Config;
use Exception;

class SmsService
{
    private ?array $twilioConfig = null;
    private string $provider;

    public function __construct()
    {
        $this->provider = strtolower(trim((string) Config::get('SMS_PROVIDER', 'fast2sms')));
    }

    public function isConfigured(): bool
    {
        return match ($this->provider) {
            'fast2sms' => trim((string) Config::get('FAST2SMS_API_KEY', '')) !== '',
            'twilio'   => $this->twilioCredentials()['ok'],
            'msg91'    => trim((string) Config::get('MSG91_AUTH_KEY', '')) !== '',
            default    => false,
        };
    }

    /** @return array{success:bool,message:string,channel?:string} */
    public function sendOtp(string $phone, string $otp): array
    {
        $digits = $this->digits10($phone);
        if (!preg_match('/^[6-9]\d{9}$/', $digits)) {
            return ['success' => false, 'message' => 'Enter a valid 10-digit Indian mobile number.'];
        }

        if (!$this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'SMS gateway not configured. Set FAST2SMS_API_KEY in admin/.env on the server.',
            ];
        }

        $text = "Your Findownn verification code is {$otp}. Valid for 10 minutes. Do not share this code.";

        try {
            $result = match ($this->provider) {
                'fast2sms' => $this->sendFast2Sms($digits, $otp, $text),
                'twilio'   => $this->sendTwilioSms($phone, $text),
                'msg91'    => $this->sendMsg91($digits, $otp, $text),
                default    => ['ok' => false, 'error' => 'Unknown SMS provider'],
            };
        } catch (Exception $e) {
            error_log('[Findownn SMS] ' . $e->getMessage());
            return ['success' => false, 'message' => $this->publicError($e->getMessage())];
        }

        if (empty($result['ok'])) {
            return ['success' => false, 'message' => $this->publicError($result['error'] ?? 'SMS failed')];
        }

        return [
            'success' => true,
            'message' => 'OTP sent to your mobile via SMS.',
            'channel' => 'sms',
        ];
    }

    private function publicError(string $raw): string
    {
        $msg = trim($raw);
        if ($msg === '') {
            return 'Could not send SMS. Please try again.';
        }

        return $msg;
    }

    private function digits10(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return substr($digits, 2);
        }

        return $digits;
    }

    /** @return array{ok:bool,error?:string} */
    private function sendFast2Sms(string $digits10, string $otp, string $text): array
    {
        $apiKey = trim((string) Config::get('FAST2SMS_API_KEY', ''));
        if ($apiKey === '') {
            return ['ok' => false, 'error' => 'FAST2SMS_API_KEY is empty'];
        }

        $preferred = strtolower(trim((string) Config::get('FAST2SMS_ROUTE', 'auto')));

        $attempts = match ($preferred) {
            'q', 'quick' => [
                fn () => $this->fast2SmsQuick($apiKey, $digits10, $text),
            ],
            'otp' => [
                fn () => $this->fast2SmsOtp($apiKey, $digits10, $otp),
            ],
            default => [
                fn () => $this->fast2SmsOtp($apiKey, $digits10, $otp),
                fn () => $this->fast2SmsQuick($apiKey, $digits10, $text),
                fn () => $this->fast2SmsOtpGet($apiKey, $digits10, $otp),
            ],
        };

        $lastError = 'Fast2SMS request failed';
        foreach ($attempts as $attempt) {
            $res = $attempt();
            if (!empty($res['ok'])) {
                return $res;
            }
            $lastError = $res['error'] ?? $lastError;
        }

        error_log('[Findownn SMS Fast2SMS] ' . $lastError);
        return ['ok' => false, 'error' => $lastError];
    }

    /** @return array{ok:bool,error?:string} */
    private function fast2SmsOtp(string $apiKey, string $digits10, string $otp): array
    {
        return $this->fast2SmsRequest($apiKey, [
            'route'            => 'otp',
            'variables_values' => $otp,
            'numbers'          => $digits10,
        ]);
    }

    /** @return array{ok:bool,error?:string} */
    private function fast2SmsQuick(string $apiKey, string $digits10, string $text): array
    {
        $sender = trim((string) Config::get('FAST2SMS_SENDER_ID', ''));
        $payload = [
            'route'     => 'q',
            'message'   => $text,
            'language'  => 'english',
            'numbers'   => $digits10,
        ];
        if ($sender !== '') {
            $payload['sender_id'] = $sender;
        }

        return $this->fast2SmsRequest($apiKey, $payload);
    }

    /** GET fallback — some hosts block outbound POST JSON */
    private function fast2SmsOtpGet(string $apiKey, string $digits10, string $otp): array
    {
        $url = 'https://www.fast2sms.com/dev/bulkV2?' . http_build_query([
            'authorization'    => $apiKey,
            'route'            => 'otp',
            'variables_values'   => $otp,
            'numbers'          => $digits10,
        ]);

        return $this->fast2SmsHttp('GET', $url, $apiKey, null);
    }

    /** @param array<string,mixed> $payload */
    private function fast2SmsRequest(string $apiKey, array $payload): array
    {
        return $this->fast2SmsHttp(
            'POST',
            'https://www.fast2sms.com/dev/bulkV2',
            $apiKey,
            json_encode($payload)
        );
    }

    private function fast2SmsHttp(string $method, string $url, string $apiKey, ?string $jsonBody): array
    {
        $ch = curl_init($url);
        $headers = [
            'Authorization: ' . $apiKey,
            'Accept: application/json',
        ];
        if ($jsonBody !== null) {
            $headers[] = 'Content-Type: application/json';
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => 25,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        if ($method === 'POST' && $jsonBody !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
        }

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($curlErr) {
            return ['ok' => false, 'error' => 'SMS network error: ' . $curlErr];
        }

        $body = json_decode($response ?: '{}', true);
        if (!is_array($body)) {
            return ['ok' => false, 'error' => 'Invalid response from SMS gateway'];
        }

        if ($httpCode >= 400) {
            return ['ok' => false, 'error' => $this->extractFast2SmsMessage($body, $httpCode)];
        }

        if (!empty($body['return']) || ($body['status'] ?? '') === 'OK') {
            return ['ok' => true];
        }

        return ['ok' => false, 'error' => $this->extractFast2SmsMessage($body, $httpCode)];
    }

    private function extractFast2SmsMessage(array $body, int $httpCode): string
    {
        if (!empty($body['message'])) {
            return is_array($body['message'])
                ? implode(' ', $body['message'])
                : (string) $body['message'];
        }
        if (!empty($body['errors'])) {
            return is_array($body['errors']) ? implode(' ', $body['errors']) : (string) $body['errors'];
        }

        return 'Fast2SMS error (HTTP ' . $httpCode . ')';
    }

    /** @return array{ok:bool,sid?:string,token?:string} */
    private function twilioCredentials(): array
    {
        $cfg = $this->loadTwilioConfig();
        $from = trim((string) Config::get('TWILIO_SMS_FROM', '')) ?: ($cfg['twilio_sms_from'] ?? '');

        return [
            'ok'    => !empty($cfg['twilio_account_sid']) && !empty($cfg['twilio_auth_token']) && $from !== '',
            'sid'   => $cfg['twilio_account_sid'] ?? '',
            'token' => $cfg['twilio_auth_token'] ?? '',
            'from'  => $from,
        ];
    }

    private function loadTwilioConfig(): array
    {
        if ($this->twilioConfig !== null) {
            return $this->twilioConfig;
        }

        $this->twilioConfig = [];
        try {
            $db   = \App\Core\Database::getInstance();
            $rows = $db->fetchAll("SELECT `key`, value FROM settings WHERE `group` = 'whatsapp'");
            foreach ($rows as $row) {
                $this->twilioConfig[$row['key']] = $row['value'];
            }
        } catch (Exception) {
            // optional
        }

        return $this->twilioConfig;
    }

    /** @return array{ok:bool,error?:string} */
    private function sendTwilioSms(string $phoneE164, string $text): array
    {
        $c = $this->twilioCredentials();
        if (!$c['ok']) {
            return ['ok' => false, 'error' => 'Twilio SMS not configured'];
        }

        $to = str_starts_with($phoneE164, '+') ? $phoneE164 : '+' . ltrim($phoneE164, '+');
        $url  = 'https://api.twilio.com/2010-04-01/Accounts/' . $c['sid'] . '/Messages.json';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query(['From' => $c['from'], 'To' => $to, 'Body' => $text]),
            CURLOPT_USERPWD        => $c['sid'] . ':' . $c['token'],
            CURLOPT_TIMEOUT        => 20,
        ]);
        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            $r = json_decode($response ?: '{}', true);
            return ['ok' => false, 'error' => $r['message'] ?? 'Twilio SMS failed'];
        }

        return ['ok' => true];
    }

    /** @return array{ok:bool,error?:string} */
    private function sendMsg91(string $digits10, string $otp, string $text): array
    {
        $authKey = trim((string) Config::get('MSG91_AUTH_KEY', ''));
        $url = 'https://control.msg91.com/api/sendotp.php?' . http_build_query([
            'authkey'    => $authKey,
            'mobile'     => '91' . $digits10,
            'otp'        => $otp,
            'sender'     => Config::get('MSG91_SENDER_ID', 'FINDO'),
            'message'    => $text,
            'otp_expiry' => 10,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20]);
        $response = curl_exec($ch);
        curl_close($ch);

        $body = json_decode($response ?: '{}', true);
        if (is_array($body) && ($body['type'] ?? '') === 'success') {
            return ['ok' => true];
        }

        return ['ok' => false, 'error' => is_string($response) ? $response : 'MSG91 failed'];
    }
}
