<?php
/**
 * Quick Email Diagnostic Tool
 * GET /admin/public/mail-test.php?to=your_email@gmail.com
 */
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

require_once ROOT_PATH . '/app/Core/Config.php';
if (file_exists(ROOT_PATH . '/.env')) {
    \App\Core\Config::load(ROOT_PATH . '/.env');
}
require_once ROOT_PATH . '/vendor/autoload.php';

header('Content-Type: application/json; charset=utf-8');

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
    'timestamp'       => date('Y-m-d H:i:s'),
    'recipient'       => $to,
    'host_domain'     => $host,
    'native_mail'     => [
        'sent'        => $mailSuccess,
        'last_error'  => $lastError['message'] ?? null
    ],
    'mail_service'    => $serviceResult,
    'smtp_configured' => $mailService->isSmtpConfigured()
], JSON_PRETTY_PRINT);
