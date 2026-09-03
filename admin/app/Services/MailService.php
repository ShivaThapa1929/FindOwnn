<?php

namespace App\Services;

use App\Core\Config;
use App\Models\Setting;
use PHPMailer\PHPMailer\Exception as MailException;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Outbound email — SMTP (Gmail/Hostinger) with PHP mail() fallback.
 */
class MailService
{
    private static function getConfig(string $key, mixed $default = null): mixed
    {
        $val = Config::get($key);
        if ($val !== null && $val !== '') {
            return $val;
        }
        try {
            $dbVal = Setting::getValue(strtolower($key));
            if ($dbVal !== null && $dbVal !== '') {
                return $dbVal;
            }
        } catch (\Throwable $e) {}
        return $default;
    }

    public function isSmtpConfigured(): bool
    {
        $host = trim((string) self::getConfig('MAIL_HOST', ''));
        $user = trim((string) self::getConfig('MAIL_USERNAME', ''))
            ?: trim((string) self::getConfig('MAIL_FROM', ''));
        $pass = trim((string) self::getConfig('MAIL_PASSWORD', ''));

        return $host !== '' && $user !== '' && $pass !== '';
    }

    private function smtpUsername(): string
    {
        return trim((string) self::getConfig('MAIL_USERNAME', ''))
            ?: trim((string) self::getConfig('MAIL_FROM', ''));
    }

    /**
     * @param array{reply_to?:string,reply_name?:string,html?:string} $options
     * @return array{success:bool,message:string,transport?:string}
     */
    public function send(string $to, string $subject, string $bodyText, array $options = []): array
    {
        $to = trim($to);
        if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid recipient email.'];
        }

        $smtpErr = null;
        if ($this->isSmtpConfigured()) {
            try {
                return $this->sendViaSmtp($to, $subject, $bodyText, $options);
            } catch (\Throwable $e) {
                $smtpErr = $e->getMessage();
                error_log('[Findownn Mail SMTP] ' . $e->getMessage());
            }
        }

        $res = $this->sendViaMailFunction($to, $subject, $bodyText, $options);
        if ($smtpErr) {
            $res['smtp_error'] = $smtpErr;
        }
        return $res;
    }

    /** @param array{reply_to?:string,reply_name?:string,html?:string} $options */
    private function sendViaSmtp(string $to, string $subject, string $bodyText, array $options): array
    {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = (string) self::getConfig('MAIL_HOST', 'smtp.gmail.com');
        $mail->Port       = (int) self::getConfig('MAIL_PORT', 587);
        $mail->SMTPAuth   = true;
        $mail->Username   = $this->smtpUsername();
        $mail->Password   = (string) self::getConfig('MAIL_PASSWORD', '');
        $mail->CharSet    = PHPMailer::CHARSET_UTF8;

        $encryption = strtolower((string) self::getConfig('MAIL_ENCRYPTION', 'tls'));
        if ($encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        } elseif ($encryption === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } else {
            $mail->SMTPSecure = false;
            $mail->SMTPAutoTLS = false;
        }

        $fromEmail = (string) self::getConfig('MAIL_FROM', self::getConfig('MAIL_USERNAME', ''));
        $fromName  = (string) self::getConfig('MAIL_FROM_NAME', 'Findownn');
        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);

        if (!empty($options['reply_to']) && filter_var($options['reply_to'], FILTER_VALIDATE_EMAIL)) {
            $mail->addReplyTo(
                $options['reply_to'],
                $options['reply_name'] ?? ''
            );
        }

        $mail->Subject = $subject;
        $mail->addCustomHeader('Auto-Submitted', 'auto-generated');

        if (!empty($options['html'])) {
            $mail->isHTML(true);
            $mail->Body    = $options['html'];
            $mail->AltBody = $bodyText;
        } else {
            $mail->isHTML(false);
            $mail->Body = $bodyText;
        }

        $mail->send();

        return [
            'success'   => true,
            'message'   => 'Email sent via SMTP.',
            'transport' => 'smtp',
        ];
    }

    /** @param array{reply_to?:string,reply_name?:string,html?:string} $options */
    private function sendViaMailFunction(string $to, string $subject, string $bodyText, array $options): array
    {
        $domain = $_SERVER['HTTP_HOST'] ?? 'findownn.com';
        $domain = preg_replace('/:\d+$/', '', $domain);
        if ($domain === 'localhost' || $domain === '127.0.0.1') {
            $domain = 'findownn.com';
        }

        $configFrom = trim((string) self::getConfig('MAIL_FROM', ''));
        if ($configFrom === '' || preg_match('/@(gmail|yahoo|hotmail|outlook)\.com$/i', $configFrom)) {
            $fromEmail = "no-reply@{$domain}";
        } else {
            $fromEmail = $configFrom;
        }

        $fromName  = (string) self::getConfig('MAIL_FROM_NAME', 'Findownn');

        $isHtml = !empty($options['html']);
        $body   = $isHtml ? $options['html'] : $bodyText;

        $msgId = '<' . time() . '.' . bin2hex(random_bytes(8)) . '@' . $domain . '>';

        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: ' . ($isHtml ? 'text/html; charset=UTF-8' : 'text/plain; charset=UTF-8'),
            'From: ' . $this->encodeAddress($fromEmail, $fromName),
            'Reply-To: support@' . $domain,
            'Date: ' . date('r'),
            'Message-ID: ' . $msgId,
            'Auto-Submitted: auto-generated',
            'X-Auto-Response-Suppress: All',
            'X-Mailer: PHP/' . phpversion()
        ];

        // Send via mail() with Return-Path (-f) for SPF compliance on Hostinger
        $ok = @mail($to, $this->encodeSubject($subject), $body, implode("\r\n", $headers), "-f {$fromEmail}");

        return [
            'success'   => (bool)$ok,
            'message'   => $ok ? 'Email sent successfully via PHP mail().' : 'Failed to send email via PHP mail().',
            'transport' => 'mail',
        ];
    }

    private function encodeAddress(string $email, string $name = ''): string
    {
        if ($name === '') {
            return $email;
        }

        return sprintf('%s <%s>', addslashes($name), $email);
    }

    private function encodeSubject(string $subject): string
    {
        return '=?UTF-8?B?' . base64_encode($subject) . '?=';
    }
}
