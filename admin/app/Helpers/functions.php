<?php

use App\Core\Config;
use App\Core\Session;

if (!function_exists('url')) {
    function url(string $path = ''): string
    {
        // Dynamically compute the base URL to prevent hardcoding to localhost
        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        $normalizedScript = str_replace('\\', '/', $scriptName);
        
        if (str_contains($normalizedScript, '/admin')) {
            // Find position of '/admin' and take the base path up to '/admin'
            $pos = strpos($normalizedScript, '/admin');
            $basePath = substr($normalizedScript, 0, $pos + 6); // 6 is strlen('/admin')
        } else {
            // Delegated from root index.php
            $dir = dirname($normalizedScript);
            $basePath = ($dir === '/' || $dir === '\\') ? '/admin' : rtrim($dir, '/\\') . '/admin';
        }
        $basePath = '/' . ltrim($basePath, '/');
        
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $base = $protocol . $host . rtrim($basePath, '/');

        // Normalise: ensure path starts with /
        $path = '/' . ltrim($path, '/');
        // Remove double /public/public if asset() is called via url()
        return $base . $path;
    }
}

if (!function_exists('site_home_url')) {
    /** Public website home URL (parent of /admin) */
    function site_home_url(): string
    {
        return preg_replace('#/admin/?$#', '/', url('/')) ?: '/';
    }
}

if (!function_exists('is_live_site_host')) {
    /** True when app runs on public hosting (not local XAMPP) */
    function is_live_site_host(): bool
    {
        $host = strtolower($_SERVER['HTTP_HOST'] ?? 'localhost');

        return !str_contains($host, 'localhost')
            && !str_contains($host, '127.0.0.1')
            && !str_starts_with($host, '192.168.')
            && !str_starts_with($host, '10.');
    }
}

if (!function_exists('openwa_webhook_url')) {
    /** Public webhook URL OpenWA must call (works on local + live) */
    function openwa_webhook_url(): string
    {
        return rtrim(site_home_url(), '/') . '/api/v1/openwa/webhook';
    }
}

if (!function_exists('site_logo_url')) {
    /** Same logo asset path as the public website navbar */
    function site_logo_url(): string
    {
        return rtrim(site_home_url(), '/') . '/assets/images/logo.png';
    }
}

if (!function_exists('site_favicon_url')) {
    /** Tab icon — square crop of logo.png (same as footer 32px display) */
    function site_favicon_url(): string
    {
        return rtrim(site_home_url(), '/') . '/assets/images/favicon-32x32.png?v=6';
    }
}

if (!function_exists('site_contact_email')) {
    /** Public support / contact email (same as website) */
    function site_contact_email(): string
    {
        static $email = null;
        if ($email !== null) {
            return $email;
        }

        $contactFile = dirname(__DIR__, 3) . '/includes/site-contact.php';
        if (is_file($contactFile)) {
            require $contactFile;
            $email = $site_contact_email ?? 'findownn@gmail.com';
        } else {
            $email = 'findownn@gmail.com';
        }

        return $email;
    }
}

if (!function_exists('asset')) {
    function asset(string $path = ''): string
    {
        return url('public/assets/' . ltrim($path, '/'));
    }
}

if (!function_exists('uploads_url')) {
    function uploads_url(string $path = ''): string
    {
        return url('public/uploads/' . ltrim($path, '/'));
    }
}

if (!function_exists('now')) {
    function now(): string
    {
        return date('Y-m-d H:i:s');
    }
}

if (!function_exists('flash')) {
    function flash(string $type = 'success'): string|null
    {
        return Session::getFlash($type);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return Session::generateCsrf();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . csrf_token() . '">';
    }
}

if (!function_exists('e')) {
    /** Escape HTML output */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}

if (!function_exists('auth')) {
    function auth(): array|null
    {
        return Session::get('user');
    }
}

if (!function_exists('isRole')) {
    function isRole(string $role): bool
    {
        return (Session::get('user')['role'] ?? '') === $role;
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): void
    {
        http_response_code(302);
        header("Location: {$url}");
        exit;
    }
}

if (!function_exists('formatCurrency')) {
    function formatCurrency(float $amount, string $symbol = '₹'): string
    {
        return $symbol . number_format($amount, 2, '.', ',');
    }
}

if (!function_exists('timeAgo')) {
    function timeAgo(string $datetime): string
    {
        $time = time() - strtotime($datetime);
        if ($time < 60)       return 'just now';
        if ($time < 3600)     return floor($time / 60) . 'm ago';
        if ($time < 86400)    return floor($time / 3600) . 'h ago';
        if ($time < 2592000)  return floor($time / 86400) . 'd ago';
        return date('M j, Y', strtotime($datetime));
    }
}

if (!function_exists('slugify')) {
    function slugify(string $text): string
    {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        return trim($text, '-');
    }
}

if (!function_exists('paginate_links')) {
    function paginate_links(int $currentPage, int $totalPages, string $baseUrl): string
    {
        if ($totalPages <= 1) return '';
        $html = '<nav><ul class="pagination pagination-sm mb-0">';
        for ($i = 1; $i <= $totalPages; $i++) {
            $active = $i === $currentPage ? 'active' : '';
            $url    = $baseUrl . '?page=' . $i;
            $html  .= "<li class=\"page-item {$active}\"><a class=\"page-link\" href=\"{$url}\">{$i}</a></li>";
        }
        $html .= '</ul></nav>';
        return $html;
    }
}

if (!function_exists('statusBadge')) {
    function statusBadge(string $status): string
    {
        $map = [
            // Booking statuses
            'pending'     => 'warning',
            'confirmed'   => 'success',
            'completed'   => 'info',
            'cancelled'   => 'danger',
            
            // Payment statuses
            'paid'        => 'success',
            'unpaid'      => 'danger',
            'failed'      => 'danger',
            'refunded'    => 'info',
            
            // Venue verification statuses
            'active'      => 'success',
            'inactive'    => 'secondary',
            'approved'    => 'success',
            'rejected'    => 'danger',
            'expired'     => 'danger',
            'suspended'   => 'dark',
        ];
        $color = $map[strtolower($status)] ?? 'secondary';
        return '<span class="badge bg-' . $color . '">' . e(ucfirst($status)) . '</span>';
    }
}
