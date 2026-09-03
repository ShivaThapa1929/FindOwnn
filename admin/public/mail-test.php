<?php
/**
 * Quick Email Diagnostic Tool
 * GET /admin/public/mail-test.php?to=your_email@gmail.com
 */
ini_set('display_errors', '1');
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

try {
    define('ROOT_PATH', dirname(__DIR__));

    require_once ROOT_PATH . '/app/Core/Config.php';
    if (file_exists(ROOT_PATH . '/.env')) {
        \App\Core\Config::load(ROOT_PATH . '/.env');
    }
    $composerAutoload = ROOT_PATH . '/vendor/autoload.php';
    if (file_exists($composerAutoload)) {
        require_once $composerAutoload;
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
                        require_once $file;
                        return;
                    }
                }
            }
        });
    }

    $to = trim((string)($_GET['to'] ?? ''));

    if ($to === '') {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Please pass recipient email in query parameter, e.g. /admin/public/mail-test.php?to=your_email@gmail.com'
        ], JSON_PRETTY_PRINT);
        exit;
    }

$host = $_SERVER['HTTP_HOST'] ?? 'findownn.com';
$host = preg_replace('/:\d+$/', '', $host);

// Test 1: Direct native mail()
$headers = [
    'MIME-Version: 1.0',
    'Content-type: text/html; charset=utf-8',
    'From: FindOwnn <no-reply@' . $host . '>',
    'Reply-To: support@' . $host,
    'X-Mailer: PHP/' . phpversion()
];

$testSubject = 'FindOwnn Mail Diagnostic Test — ' . date('H:i:s');
$testBody = '<h3>FindOwnn Mail Diagnostic Test</h3><p>If you see this, PHP mail() works on your Hostinger server!</p>';

error_clear_last();
$mailSuccess = @mail($to, $testSubject, $testBody, implode("\r\n", $headers));
$lastError = error_get_last();

// Test 2: MailService
$mailService = new \App\Services\MailService();
$serviceResult = $mailService->send($to, $testSubject, 'Test plain text', ['html' => $testBody]);

echo json_encode([
    'timestamp'           => date('Y-m-d H:i:s'),
    'recipient'           => $to,
    'host_domain'         => $host,
    'phpmailer_installed' => class_exists('PHPMailer\\PHPMailer\\PHPMailer'),
    'native_mail'         => [
        'sent'            => $mailSuccess,
        'last_error'      => $lastError['message'] ?? null
    ],
    'mail_service'        => $serviceResult,
    'smtp_configured'     => $mailService->isSmtpConfigured()
], JSON_PRETTY_PRINT);
} catch (\Throwable $e) {
    http_response_code(200);
    echo json_encode([
        'status'    => 'error',
        'exception' => get_class($e),
        'message'   => $e->getMessage(),
        'file'      => $e->getFile(),
        'line'      => $e->getLine()
    ], JSON_PRETTY_PRINT);
}
