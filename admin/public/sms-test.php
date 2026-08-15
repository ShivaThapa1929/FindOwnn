<?php
/**
 * Live SMS diagnostic — DELETE after testing
 *
 * Check API key only (no SMS sent):
 *   /admin/public/sms-test.php?key=CRON_SECRET&mode=check
 *
 * Send test OTP:
 *   /admin/public/sms-test.php?key=CRON_SECRET&phone=9876543210
 */
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

$composerAutoload = ROOT_PATH . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require $composerAutoload;
} else {
    spl_autoload_register(function (string $class): void {
        $prefixes = [
            'App\\'      => ROOT_PATH . '/app/',
            'Database\\' => ROOT_PATH . '/database/',
        ];
        foreach ($prefixes as $prefix => $base) {
            if (str_starts_with($class, $prefix)) {
                $relative = substr($class, strlen($prefix));
                $file     = $base . str_replace('\\', '/', $relative) . '.php';
                if (file_exists($file)) {
                    require $file;
                    return;
                }
            }
        }
    });
}

use App\Core\Config;
use App\Core\Database;
use App\Services\SmsService;

header('Content-Type: application/json; charset=utf-8');

try {
    Config::load(ROOT_PATH . '/.env');

    $secret = Config::get('CRON_SECRET', '');
    if ($secret === '' || ($_GET['key'] ?? '') !== $secret) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Forbidden — set CRON_SECRET in .env and pass ?key=']);
        exit;
    }

    $provider = strtolower(trim((string) Config::get('SMS_PROVIDER', 'fast2sms')));
    $apiKey   = match ($provider) {
        'smsalert', 'sms_alert' => trim((string) Config::get('SMSALERT_API_KEY', '')),
        default                 => trim((string) Config::get('FAST2SMS_API_KEY', '')),
    };
    $mode = strtolower(trim((string) ($_GET['mode'] ?? 'send')));

    // Provider wallet / credit check — validates API key without sending SMS
    $wallet = null;
    if ($apiKey !== '') {
        if (in_array($provider, ['smsalert', 'sms_alert'], true)) {
            $url = 'https://www.smsalert.co.in/api/creditstatus.json?' . http_build_query(['apikey' => $apiKey]);
            $ch  = curl_init($url);
            curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15]);
            $walletRaw  = curl_exec($ch);
            $walletCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $wallet = ['http' => $walletCode, 'body' => json_decode($walletRaw ?: '{}', true)];
        } else {
            $ch = curl_init('https://www.fast2sms.com/dev/wallet');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => ['Authorization: ' . $apiKey, 'Accept: application/json'],
                CURLOPT_TIMEOUT        => 15,
            ]);
            $walletRaw = curl_exec($ch);
            $walletCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $wallet = [
                'http' => $walletCode,
                'body' => json_decode($walletRaw ?: '{}', true),
            ];
        }
    }

    if ($mode === 'check') {
        $valid = false;
        if (in_array($provider, ['smsalert', 'sms_alert'], true)) {
            $valid = is_array($wallet['body'] ?? null)
                && strtolower((string) ($wallet['body']['status'] ?? '')) === 'success';
        } else {
            $valid = is_array($wallet['body'] ?? null) && !empty($wallet['body']['return']);
        }
        echo json_encode([
            'ok'           => $valid,
            'provider'     => $provider,
            'api_key_set'  => $apiKey !== '',
            'sender_id'    => Config::get('SMSALERT_SENDER_ID', ''),
            'wallet'       => $wallet,
            'hint'         => $valid
                ? 'API key valid. Test OTP with ?phone=10digit'
                : 'Check SMSALERT_API_KEY and SMSALERT_SENDER_ID in admin/.env',
        ], JSON_PRETTY_PRINT);
        exit;
    }

    $phone = preg_replace('/\D/', '', $_GET['phone'] ?? '');
    if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
        echo json_encode(['ok' => false, 'error' => 'Pass ?phone=10digitIndianMobile or ?mode=check']);
        exit;
    }

    $sms = new SmsService();
    $testOtp = (string) random_int(100000, 999999);
    $result  = $sms->sendOtp('+91' . $phone, $testOtp);

    $dbOk = false;
    try {
        Database::getInstance();
        $dbOk = true;
    } catch (Throwable) {
        $dbOk = false;
    }

    echo json_encode([
        'ok'          => $result['success'],
        'message'     => $result['message'] ?? '',
        'provider'    => $provider,
        'route'       => Config::get('SMSALERT_ROUTE', Config::get('FAST2SMS_ROUTE', 'auto')),
        'configured'  => $sms->isConfigured(),
        'has_api_key' => $apiKey !== '',
        'sender_id'   => Config::get('SMSALERT_SENDER_ID', ''),
        'wallet'      => $wallet,
        'db_ok'       => $dbOk,
        'curl'        => function_exists('curl_init'),
    ], JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
