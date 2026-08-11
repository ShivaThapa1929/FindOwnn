<?php
/**
 * Auth diagnostic — DELETE after use
 * /admin/public/auth-check.php?key=CRON_SECRET&email=vatsalpareshshah@gmail.com
 */
declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

$composerAutoload = ROOT_PATH . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require $composerAutoload;
} else {
    spl_autoload_register(function (string $class): void {
        if (str_starts_with($class, 'App\\')) {
            $file = ROOT_PATH . '/app/' . str_replace('\\', '/', substr($class, 4)) . '.php';
            if (is_file($file)) {
                require $file;
            }
        }
    });
}

use App\Core\Config;
use App\Helpers\EmailHelper;
use App\Models\User;

header('Content-Type: application/json; charset=utf-8');

try {
    Config::load(ROOT_PATH . '/.env');
    $secret = Config::get('CRON_SECRET', '');
    if ($secret === '' || ($_GET['key'] ?? '') !== $secret) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'Forbidden']);
        exit;
    }

    $email = EmailHelper::normalize((string) ($_GET['email'] ?? ''));
    if ($email === '') {
        echo json_encode(['ok' => false, 'error' => 'Pass ?email=']);
        exit;
    }

    $user = (new User())->findByEmail($email);
    if (!$user) {
        echo json_encode([
            'ok'      => false,
            'found'   => false,
            'email'   => $email,
            'message' => 'No user row for this email (after Gmail normalization).',
        ], JSON_PRETTY_PRINT);
        exit;
    }

    echo json_encode([
        'ok'           => true,
        'found'        => true,
        'id'           => (int) $user['id'],
        'email_db'     => $user['email'],
        'normalized'   => $email,
        'role'         => $user['role'] ?? null,
        'status'       => $user['status'] ?? null,
        'has_password' => !empty($user['password']),
        'login_url'    => match ($user['role'] ?? '') {
            'venue_owner'         => '/admin/owner/login',
            'player'              => '/?auth=login',
            'admin', 'super_admin'=> '/admin/login',
            default               => 'unknown',
        },
    ], JSON_PRETTY_PRINT);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
