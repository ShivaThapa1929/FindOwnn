<?php

namespace App\Helpers;

/**
 * Email normalization for login/register (Gmail dots, aliases, case).
 */
class EmailHelper
{
    public static function normalize(string $email): string
    {
        $email = strtolower(trim($email));
        if ($email === '' || !str_contains($email, '@')) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);
        $domain = match ($domain) {
            'googlemail.com' => 'gmail.com',
            default          => $domain,
        };

        if ($domain === 'gmail.com') {
            $local = preg_replace('/\+.*/', '', $local) ?? $local;
            $local = str_replace('.', '', $local);
        }

        return $local . '@' . $domain;
    }

    /** Gmail local part without dots/plus (for matching legacy stored emails). */
    public static function gmailLocalKey(string $email): ?string
    {
        $email = self::normalize($email);
        if (!str_ends_with($email, '@gmail.com')) {
            return null;
        }

        return strstr($email, '@', true) ?: null;
    }
}
