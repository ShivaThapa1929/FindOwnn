<?php
/**
 * Public contact form — save message + notify support email.
 */

require_once __DIR__ . '/site-errors.php';
require_once __DIR__ . '/user-auth.php';
require_once __DIR__ . '/site-contact.php';
require_once __DIR__ . '/site-mail.php';

function contact_json(array $data, int $code = 200): never
{
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

function contact_ensure_table(): void
{
    site_db()->execute("
        CREATE TABLE IF NOT EXISTS contact_messages (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(120) NOT NULL,
            email VARCHAR(190) NOT NULL,
            phone VARCHAR(20) NULL,
            subject VARCHAR(120) NOT NULL,
            message TEXT NOT NULL,
            ip_address VARCHAR(45) NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_contact_created (created_at),
            INDEX idx_contact_read (is_read)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
}

function contact_handle_submit(): void
{
    if (!site_verify_csrf()) {
        contact_json(['ok' => false, 'error' => 'Session expired. Please refresh and try again.'], 403);
    }

    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = preg_replace('/\D/', '', $_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '' || strlen($name) > 120) {
        contact_json(['ok' => false, 'error' => 'Please enter your name.'], 422);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        contact_json(['ok' => false, 'error' => 'Please enter a valid email address.'], 422);
    }
    if ($subject === '') {
        contact_json(['ok' => false, 'error' => 'Please select a subject.'], 422);
    }
    if ($message === '' || strlen($message) < 10) {
        contact_json(['ok' => false, 'error' => 'Please enter a message (at least 10 characters).'], 422);
    }
    if ($phone !== '' && strlen($phone) !== 10) {
        contact_json(['ok' => false, 'error' => 'Phone number must be 10 digits.'], 422);
    }

    try {
        contact_ensure_table();

        site_db()->execute(
            'INSERT INTO contact_messages (name, email, phone, subject, message, ip_address, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())',
            [$name, $email, $phone ?: null, $subject, $message, $_SERVER['REMOTE_ADDR'] ?? null]
        );

        $body = "New contact message from Findownn website\r\n\r\n"
            . "Name: {$name}\r\n"
            . "Email: {$email}\r\n"
            . "Phone: " . ($phone ?: '—') . "\r\n"
            . "Subject: {$subject}\r\n\r\n"
            . $message;

        $mailResult = site_contact_notify(
            '[Findownn Contact] ' . $subject,
            $body,
            $email,
            $name
        );

        if (!$mailResult['success']) {
            site_log_error('Contact email failed: ' . ($mailResult['message'] ?? 'unknown'));
        }
    } catch (\Throwable $e) {
        site_log_error('Contact form error: ' . $e->getMessage());
        contact_json([
            'ok'    => false,
            'error' => 'We\'re unavailable right now. Please try again in a few minutes.',
        ], 503);
    }

    contact_json([
        'ok'      => true,
        'message' => 'Thank you! Our team will reply to your email shortly.',
    ]);
}
