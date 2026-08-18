<?php
/**
 * Public site email helper — uses admin MailService + .env SMTP settings.
 */

require_once __DIR__ . '/../admin/app/Core/Config.php';
require_once __DIR__ . '/../admin/vendor/autoload.php';
require_once __DIR__ . '/../admin/app/Services/MailService.php';

use App\Core\Config;
use App\Services\MailService;

if (!function_exists('site_send_email')) {
    /**
     * @param array{reply_to?:string,reply_name?:string,html?:string} $options
     * @return array{success:bool,message:string,transport?:string}
     */
    function site_send_email(string $to, string $subject, string $bodyText, array $options = []): array
    {
        static $configLoaded = false;
        if (!$configLoaded) {
            Config::load(__DIR__ . '/../admin/.env');
            $configLoaded = true;
        }

        return (new MailService())->send($to, $subject, $bodyText, $options);
    }
}

if (!function_exists('site_contact_notify')) {
    /** Notify support inbox about a form submission. */
    function site_contact_notify(string $subject, string $bodyText, ?string $replyTo = null, ?string $replyName = null): array
    {
        if (!function_exists('site_contact_email')) {
            require_once __DIR__ . '/site-contact.php';
        }

        $options = [];
        if ($replyTo) {
            $options['reply_to']   = $replyTo;
            $options['reply_name'] = $replyName ?? '';
        }

        return site_send_email(
            site_contact_email(),
            $subject,
            $bodyText,
            $options
        );
    }
}
