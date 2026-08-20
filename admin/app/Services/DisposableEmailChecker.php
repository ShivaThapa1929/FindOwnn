<?php

namespace App\Services;

/**
 * DisposableEmailChecker — Protection against temporary/disposable email services.
 */
class DisposableEmailChecker
{
    private static array $disposableDomains = [
        'mailinator.com', 'tempmail.com', 'temp-mail.org', '10minutemail.com',
        'guerrillamail.com', 'trashmail.com', 'yopmail.com', 'getnada.com',
        'dispostable.com', 'fakeinbox.com', 'sharklasers.com', 'generator.email',
        'tempmailo.com', 'mohmal.com', 'maildrop.cc', 'throwawaymail.com',
        'crazymailing.com', 'tempmailaddress.com', 'disposablemail.com',
        'mytemp.email', 'anonymbox.com', 'nada.ltd', 'emailondeck.com',
        'temp-mail.ru', 'tempail.com', 'bupkis.org', 'dropmail.me'
    ];

    /**
     * Check if an email uses a disposable/temporary domain.
     */
    public static function isDisposable(string $email): bool
    {
        $email = strtolower(trim($email));
        if (!str_contains($email, '@')) {
            return false;
        }

        $parts = explode('@', $email);
        $domain = end($parts);

        if (empty($domain)) {
            return false;
        }

        // Direct domain match or subdomain match
        foreach (self::$disposableDomains as $disposable) {
            if ($domain === $disposable || str_ends_with($domain, '.' . $disposable)) {
                return true;
            }
        }

        return false;
    }
}
