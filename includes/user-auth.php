<?php
/**
 * Public site — player login / register (session-based)
 */

use App\Core\Config;
use App\Core\Database;

require_once __DIR__ . '/../admin/app/Core/Config.php';
require_once __DIR__ . '/../admin/app/Core/Database.php';
require_once __DIR__ . '/../admin/app/Core/Logger.php';
require_once __DIR__ . '/../admin/app/Helpers/EmailHelper.php';

if (session_status() === PHP_SESSION_NONE) {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
    $cookiePath = '/';
    if (preg_match('#^(/.+?)/#', $scriptName, $m)) {
        $cookiePath = rtrim($m[1], '/') . '/';
    }
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => $cookiePath,
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

Config::load(__DIR__ . '/../admin/.env');

function site_db(): Database
{
    return Database::getInstance();
}

function site_user(): ?array
{
    return $_SESSION['site_user'] ?? null;
}

function site_login(array $user, string $token): void
{
    $_SESSION['site_user'] = [
        'id'    => (int) $user['id'],
        'name'  => $user['name'],
        'email' => $user['email'],
        'phone' => $user['phone'] ?? '',
        'role'  => $user['role'],
        'token' => $token,
    ];
}

function site_logout(): void
{
    $user = site_user();
    if ($user) {
        try {
            site_db()->execute(
                'UPDATE users SET api_token = NULL, updated_at = NOW() WHERE id = ?',
                [$user['id']]
            );
        } catch (\Throwable) {
            // ignore DB errors on logout
        }
    }
    unset($_SESSION['site_user']);
}

function site_require_user(): void
{
    if (!site_user()) {
        site_flash('error', 'Please sign in to continue.');
        site_redirect_auth('login');
    }
}

function site_flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['site_flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['site_flash'][$key] ?? null;
    unset($_SESSION['site_flash'][$key]);
    return $value;
}

function site_csrf_token(): string
{
    if (empty($_SESSION['site_csrf'])) {
        $_SESSION['site_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['site_csrf'];
}

function site_csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(site_csrf_token()) . '">';
}

function site_verify_csrf(): bool
{
    $token = $_POST['_csrf'] ?? '';
    return $token !== '' && hash_equals(site_csrf_token(), $token);
}

function site_redirect(string $page): never
{
    global $asset_base;
    $base = $asset_base ?? '/';
    header('Location: ' . rtrim($base, '/') . '/' . ltrim($page, '/'));
    exit;
}

function site_redirect_auth(string $tab = 'login'): never
{
    global $asset_base;
    $base = rtrim($asset_base ?? '/', '/');
    header('Location: ' . ($base === '' ? '/' : $base . '/') . '?auth=' . urlencode($tab));
    exit;
}

function site_resolve_image_url(?string $path): string
{
    global $asset_base;
    $base = rtrim($asset_base ?? '/', '/');
    $prefix = $base === '' ? '' : $base;
    $fallback = ($prefix ?: '') . '/assets/images/logo.png';

    if (!$path) {
        return $fallback;
    }
    if (preg_match('/^https?:\/\//i', $path)) {
        return $path;
    }
    if ($path[0] === '/') {
        return $path;
    }
    if (str_starts_with($path, 'admin/public/uploads/') || str_starts_with($path, 'assets/')) {
        return $prefix . '/' . $path;
    }
    if (str_starts_with($path, 'public/uploads/')) {
        return $prefix . '/admin/' . $path;
    }
    if (str_starts_with($path, 'venues/') || str_starts_with($path, 'courts/')) {
        return $prefix . '/admin/public/uploads/' . $path;
    }

    return $prefix . '/' . ltrim($path, '/');
}

function site_normalize_email(string $email): string
{
    return \App\Helpers\EmailHelper::normalize($email);
}

function site_find_user_by_email(Database $db, string $email): array|false
{
    $email = site_normalize_email($email);
    if ($email === '') {
        return false;
    }

    $user = $db->fetch(
        'SELECT * FROM users WHERE LOWER(TRIM(email)) = ? AND deleted_at IS NULL LIMIT 1',
        [$email]
    );

    if ($user) {
        return $user;
    }

    $gmailLocal = \App\Helpers\EmailHelper::gmailLocalKey($email);
    if ($gmailLocal === null) {
        return false;
    }

    return $db->fetch(
        "SELECT * FROM users
         WHERE deleted_at IS NULL
           AND LOWER(TRIM(SUBSTRING_INDEX(email, '@', -1))) IN ('gmail.com', 'googlemail.com')
           AND REPLACE(
                 SUBSTRING_INDEX(SUBSTRING_INDEX(LOWER(TRIM(email)), '@', 1), '+', 1),
                 '.', ''
               ) = ?
         LIMIT 1",
        [$gmailLocal]
    ) ?: false;
}

function site_portal_url(string $portal): string
{
    global $asset_base;
    $base = rtrim($asset_base ?? '/', '/') . '/admin/';
    return match ($portal) {
        'owner' => $base . 'owner/login',
        'admin' => $base . 'login',
        default => rtrim($asset_base ?? '/', '/') . '/?auth=login',
    };
}

function site_auth_login(string $email, string $password, string $loginAs = 'player'): array
{
    $db = site_db();
    $email = site_normalize_email($email);
    $loginAs = trim(strtolower($loginAs));
    if ($loginAs === '') {
        $loginAs = 'player';
    }

    if ($email === '') {
        return ['ok' => false, 'error' => 'Enter your email address.'];
    }

    try {
        $user = site_find_user_by_email($db, $email);
    } catch (\Throwable $e) {
        return ['ok' => false, 'error' => 'Unable to connect. Please try again later.'];
    }

    if (!$user) {
        return ['ok' => false, 'error' => 'No account found for this email. Please register first.'];
    }

    if (!password_verify($password, $user['password'])) {
        return ['ok' => false, 'error' => 'Incorrect password. Please try again.'];
    }

    if ($user['status'] !== 'active') {
        return ['ok' => false, 'error' => 'Your account is inactive. Contact support.'];
    }

    $role = $user['role'] ?? 'player';

    if ($loginAs === 'venue_owner') {
        if ($role !== 'venue_owner') {
            if ($role === 'player') {
                return [
                    'ok'     => false,
                    'error'  => 'This is a player account. Sign in as Player on the website or register as venue owner.',
                    'portal' => 'player',
                ];
            }

            return [
                'ok'           => false,
                'error'        => 'Use the admin panel for staff accounts.',
                'portal'       => 'admin',
                'redirect_url' => site_portal_url('admin'),
            ];
        }

        return [
            'ok'           => false,
            'error'        => 'Venue owner account detected. Opening owner dashboard…',
            'portal'       => 'owner',
            'redirect_url' => site_portal_url('owner') . '?email=' . urlencode($email),
        ];
    }

    if ($role === 'venue_owner') {
        return [
            'ok'           => false,
            'error'        => 'Venue owner account — use the owner dashboard to sign in.',
            'portal'       => 'owner',
            'redirect_url' => site_portal_url('owner') . '?email=' . urlencode($email),
        ];
    }

    if (in_array($role, ['admin', 'super_admin'], true)) {
        return [
            'ok'           => false,
            'error'        => 'Admin account — use the admin login page.',
            'portal'       => 'admin',
            'redirect_url' => site_portal_url('admin'),
        ];
    }

    if ($role !== 'player') {
        return ['ok' => false, 'error' => 'This account cannot sign in here.'];
    }

    $token = bin2hex(random_bytes(32));
    try {
        $db->execute(
            'UPDATE users SET api_token = ?, updated_at = NOW() WHERE id = ?',
            [$token, $user['id']]
        );
    } catch (\Throwable) {
        try {
            $db->execute('UPDATE users SET updated_at = NOW() WHERE id = ?', [$user['id']]);
        } catch (\Throwable) {
            // continue — login can still work without api_token column
        }
        $token = '';
    }

    site_login($user, $token);

    return ['ok' => true, 'redirect' => 'dashboard'];
}

function site_auth_register(array $data): array
{
    $name     = trim($data['name'] ?? '');
    $email    = site_normalize_email($data['email'] ?? '');
    $phone    = preg_replace('/\D/', '', $data['phone'] ?? '');
    $password = $data['password'] ?? '';
    $confirm  = $data['password_confirmation'] ?? ($data['password_confirm'] ?? '');

    if ($name === '') {
        return ['ok' => false, 'error' => 'Name is required.'];
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'Enter a valid email address.'];
    }
    if (!preg_match('/^[6-9][0-9]{9}$/', $phone)) {
        return ['ok' => false, 'error' => 'Enter a valid 10-digit mobile number.'];
    }
    if (strlen($password) < 8) {
        return ['ok' => false, 'error' => 'Password must be at least 8 characters.'];
    }
    if ($password !== $confirm) {
        return ['ok' => false, 'error' => 'Passwords do not match.'];
    }

    $db = site_db();
    if (site_find_user_by_email($db, $email)) {
        return ['ok' => false, 'error' => 'This email is already registered. Try signing in.'];
    }

    $token = bin2hex(random_bytes(32));
    $whatsappOptIn = !empty($data['whatsapp_opt_in']) ? 1 : 0;
    $whatsapp = $data['whatsapp_number'] ?? null;
    if ($whatsappOptIn && !$whatsapp) {
        $whatsapp = $phone;
    }

    $passwordHash = password_hash($password, PASSWORD_BCRYPT);
    $userId = null;

    try {
        $userId = $db->insert(
            "INSERT INTO users (name, email, phone, password, whatsapp_number, whatsapp_opt_in, api_token, role, status, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'player', 'active', NOW(), NOW())",
            [$name, $email, $phone, $passwordHash, $whatsapp, $whatsappOptIn, $token]
        );
    } catch (\Throwable) {
        try {
            $userId = $db->insert(
                "INSERT INTO users (name, email, phone, password, role, status, created_at, updated_at)
                 VALUES (?, ?, ?, ?, 'player', 'active', NOW(), NOW())",
                [$name, $email, $phone, $passwordHash]
            );
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => 'Registration failed. Please try again or contact support.'];
        }
    }

    if (!$userId) {
        return ['ok' => false, 'error' => 'Registration failed. Please try again.'];
    }

    $user = $db->fetch(
        'SELECT id, name, email, phone, role FROM users WHERE id = ?',
        [$userId]
    );

    site_login($user, $token);

    return ['ok' => true];
}

function site_user_bookings(int $userId, int $limit = 50): array
{
    return site_db()->fetchAll(
        "SELECT b.*,
                v.name AS venue_name, v.city AS venue_city,
                s.name AS sport_name,
                c.name AS court_name
         FROM bookings b
         JOIN venues v ON b.venue_id = v.id
         LEFT JOIN sports s ON b.sport_id = s.id
         LEFT JOIN courts c ON b.court_id = c.id
         WHERE b.user_id = ?
         ORDER BY b.booking_date DESC, b.start_time DESC
         LIMIT ?",
        [$userId, $limit]
    );
}

function site_user_stats(int $userId): array
{
    $stats = site_db()->fetch(
        "SELECT
            COUNT(*) AS total_bookings,
            SUM(CASE WHEN status IN ('confirmed','pending') AND booking_date >= CURDATE() THEN 1 ELSE 0 END) AS upcoming,
            SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) AS total_spent
         FROM bookings
         WHERE user_id = ?",
        [$userId]
    );

    return [
        'total_bookings' => (int) ($stats['total_bookings'] ?? 0),
        'upcoming'       => (int) ($stats['upcoming'] ?? 0),
        'total_spent'    => (int) ($stats['total_spent'] ?? 0),
    ];
}

function site_booking_status_badge(string $status): string
{
    return match ($status) {
        'confirmed' => 'success',
        'pending'   => 'warning',
        'completed' => 'info',
        'cancelled' => 'danger',
        default     => 'secondary',
    };
}

function site_user_venues(int $userId): array
{
    return site_db()->fetchAll(
        "SELECT v.id, v.name, v.slug, v.city, v.address, v.type, v.featured_image, v.price_per_hour,
                COUNT(b.id) AS booking_count,
                MAX(b.booking_date) AS last_booked
         FROM bookings b
         JOIN venues v ON b.venue_id = v.id
         WHERE b.user_id = ?
         GROUP BY v.id, v.name, v.slug, v.city, v.address, v.type, v.featured_image, v.price_per_hour
         ORDER BY last_booked DESC",
        [$userId]
    );
}

function site_user_bookings_split(int $userId, int $limit = 50): array
{
    $all = site_user_bookings($userId, $limit);
    $upcoming = [];
    $past = [];
    $today = date('Y-m-d');

    foreach ($all as $b) {
        if (in_array($b['status'], ['confirmed', 'pending'], true) && ($b['booking_date'] ?? '') >= $today) {
            $upcoming[] = $b;
        } else {
            $past[] = $b;
        }
    }

    return ['upcoming' => $upcoming, 'past' => $past];
}

function site_venue_type_label(?string $type): string
{
    return match ($type) {
        'box_cricket' => 'Box Cricket',
        'pickleball'  => 'Pickleball',
        'football'    => 'Football',
        'badminton'   => 'Badminton',
        'tennis'      => 'Tennis',
        default       => 'Sports',
    };
}
