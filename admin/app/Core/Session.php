<?php

namespace App\Core;

/**
 * Session — Wraps PHP sessions with security hardening.
 */
class Session
{
    private static bool $started = false;

    public static function start(): void
    {
        if (self::$started || session_status() === PHP_SESSION_ACTIVE) {
            self::$started = true;
            return;
        }

        $name     = Config::get('SESSION_NAME', 'findownn_admin_session');
        $lifetime = (int) Config::get('SESSION_LIFETIME', 120) * 60;
        $path     = self::cookiePath();

        session_name($name);
        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path'     => $path,
            'domain'   => '',
            'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax',
        ]);

        session_start();
        self::$started = true;

        // Regenerate session ID periodically to prevent fixation
        if (!isset($_SESSION['_init'])) {
            session_regenerate_id(true);
            $_SESSION['_init'] = time();
        }
    }

    public static function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function flash(string $type, string $message): void
    {
        $_SESSION['flash'][$type] = $message;
    }

    public static function getFlash(string $type): string|null
    {
        $msg = $_SESSION['flash'][$type] ?? null;
        unset($_SESSION['flash'][$type]);
        return $msg;
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
        self::$started = false;
    }

    public static function regenerate(): void
    {
        session_regenerate_id(true);
    }

    /** CSRF token management */
    public static function generateCsrf(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function verifyCsrf(string $token): bool
    {
        return hash_equals($_SESSION['_csrf'] ?? '', $token);
    }

    /** Cookie path scoped to /admin install (fixes CSRF on subdirectory XAMPP/live) */
    private static function cookiePath(): string
    {
        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        if (str_contains($scriptName, '/admin')) {
            $pos  = strpos($scriptName, '/admin');
            $base = substr($scriptName, 0, $pos + 6);

            return rtrim($base, '/') . '/';
        }

        return '/';
    }
}
