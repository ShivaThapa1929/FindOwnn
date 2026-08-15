<?php
/**
 * End-to-end subscription + notification test harness
 *
 * CLI:
 *   php admin/test-subscription-flow.php
 *   php admin/test-subscription-flow.php --notify --phone=9876543210
 *   php admin/test-subscription-flow.php --whatsapp-test --phone=9876543210
 *
 * Web:
 *   /admin/public/test-subscription-flow.php?key=CRON_SECRET
 *   &notify=1&phone=9876543210&whatsapp_test=1
 */
declare(strict_types=1);

define('ROOT_PATH', __DIR__);

$composerAutoload = ROOT_PATH . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require $composerAutoload;
} else {
    spl_autoload_register(function (string $class): void {
        $prefixes = [
            'App\\'      => ROOT_PATH . '/app/',
            'Database\\' => ROOT_PATH . '/database/',
        ];
        foreach ($prefixes as $prefix => $base) {
            if (str_starts_with($class, $prefix)) {
                $file = $base . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
                if (is_file($file)) {
                    require $file;
                    return;
                }
            }
        }
    });
}

require_once ROOT_PATH . '/app/Core/Config.php';
require_once ROOT_PATH . '/app/Core/Logger.php';
require_once ROOT_PATH . '/app/Core/Database.php';
require_once ROOT_PATH . '/app/Helpers/functions.php';

use App\Core\Config;
use App\Core\Database;
use App\Models\User;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Services\SubscriptionNotificationService;
use App\Services\WhatsAppService;

$isCli = PHP_SAPI === 'cli';
Config::load(ROOT_PATH . '/.env');

if (!$isCli) {
    $secret = Config::get('CRON_SECRET', '');
    if ($secret === '' || ($_GET['key'] ?? '') !== $secret) {
        http_response_code(403);
        echo '403 — add ?key=CRON_SECRET';
        exit;
    }
    header('Content-Type: text/plain; charset=utf-8');
}

function arg(string $name, $default = null)
{
    global $isCli;
    if ($isCli) {
        foreach ($GLOBALS['argv'] ?? [] as $a) {
            if (str_starts_with($a, "--{$name}=")) {
                return substr($a, strlen($name) + 3);
            }
            if ($a === "--{$name}") {
                return true;
            }
        }
        return $default;
    }
    return $_GET[$name] ?? $default;
}

function out(string $line): void
{
    echo $line . PHP_EOL;
}

function hr(): void
{
    out(str_repeat('─', 60));
}

try {
    $db = Database::getInstance();
    out('Findownn — Subscription & Notification Test Harness');
    out('Time: ' . date('c'));
    hr();

    // 1. Plans
    out('[1] Subscription plans');
    if (is_file(ROOT_PATH . '/setup-subscription-plans.php')) {
        ob_start();
        require ROOT_PATH . '/setup-subscription-plans.php';
        ob_end_clean();
        out('  ✓ Plans upserted via setup-subscription-plans.php');
    }

    $planModel = new SubscriptionPlan();
    $plans     = $planModel->getActivePlans();
    $bySlug    = [];
    foreach ($plans as $p) {
        $bySlug[$p['slug']] = $p;
        out("  · {$p['name']} ({$p['slug']}) — ₹{$p['price']}");
    }

    if (count($bySlug) < 4) {
        out('  ⚠ Expected 4 marketplace plans. Run setup-subscription-plans.php');
    }
    hr();

    // 2. WhatsApp gateway (Twilio/Meta — optional)
    out('[2] WhatsApp gateway (optional)');
    $wa       = new WhatsAppService();
    $provider = $wa->getProvider();
    out("  Provider: {$provider}");
    out('  WhatsApp configured: ' . ($wa->isConfigured() ? 'yes' : 'no'));

    $testPhone = (string) arg('phone', '');
    if (arg('whatsapp_test') || arg('whatsapp-test')) {
        if ($testPhone === '') {
            out('  ⚠ Pass --phone=10digit for WhatsApp send test');
        } else {
            $notifier = new SubscriptionNotificationService();
            $testWa   = $notifier->testWhatsApp($testPhone, 'Findownn WhatsApp test — subscription notifications ready.');
            out('  WhatsApp test send: ' . ($testWa['success'] ? 'SUCCESS' : 'FAILED'));
            if (!empty($testWa['error'])) {
                out('  Error: ' . $testWa['error']);
            }
        }
    }
    hr();

    // 3. Create 4 test users
    out('[3] Test users (4 plans + edge cases)');
    $userModel = new User();
    $subModel  = new Subscription();
    $password  = password_hash('Test@1234', PASSWORD_BCRYPT, ['cost' => 12]);

    $testUsers = [
        [
            'name'  => 'Test Owner Starter',
            'email' => 'test.starter@findownn.test',
            'phone' => '9876500001',
            'slug'  => 'starter',
            'months'=> 12,
            'note'  => 'Active Starter — full term',
        ],
        [
            'name'  => 'Test Owner Growth',
            'email' => 'test.growth@findownn.test',
            'phone' => '9876500002',
            'slug'  => 'growth',
            'months'=> 12,
            'note'  => 'Active Growth — WhatsApp tier',
        ],
        [
            'name'  => 'Test Owner Pro Expiring',
            'email' => 'test.pro.expiring@findownn.test',
            'phone' => '9876500003',
            'slug'  => 'professional',
            'months'=> 0,
            'note'  => 'Expiring in 3 days — expiry warning test',
            'expires_override' => date('Y-m-d H:i:s', strtotime('+3 days')),
        ],
        [
            'name'  => 'Test Owner Expired',
            'email' => 'test.expired@findownn.test',
            'phone' => '9876500004',
            'slug'  => 'starter',
            'months'=> 0,
            'note'  => 'Already expired — edge case',
            'expires_override' => date('Y-m-d H:i:s', strtotime('-2 days')),
            'status_override' => 'expired',
        ],
    ];

    $created = [];

    foreach ($testUsers as $spec) {
        $email = User::normalizeEmail($spec['email']);
        $existing = $userModel->findByEmail($email);

        if ($existing) {
            $userId = (int) $existing['id'];
            out("  · Reusing {$spec['name']} (#{$userId})");
        } else {
            $userId = (int) $userModel->create([
                'name'     => $spec['name'],
                'email'    => $email,
                'password' => $password,
                'phone'    => '+91 ' . substr($spec['phone'], 0, 5) . ' ' . substr($spec['phone'], 5),
                'role'     => 'venue_owner',
                'status'   => 'active',
            ]);
            out("  ✓ Created {$spec['name']} (#{$userId}) — {$spec['note']}");
        }

        // Cancel prior active subs for clean test state
        $db->execute(
            "UPDATE subscriptions SET status = 'cancelled', updated_at = NOW()
             WHERE user_id = ? AND status IN ('active','pending')",
            [$userId]
        );

        $plan = $bySlug[$spec['slug']] ?? null;
        if (!$plan) {
            out("  ⚠ Plan {$spec['slug']} missing — skip sub for user #{$userId}");
            continue;
        }

        $expires = $spec['expires_override']
            ?? date('Y-m-d H:i:s', strtotime('+' . max(1, (int) $spec['months']) . ' months'));
        $status  = $spec['status_override'] ?? 'active';

        $subId = (int) $subModel->create([
            'user_id'        => $userId,
            'plan_id'        => (int) $plan['id'],
            'status'         => $status,
            'starts_at'      => date('Y-m-d H:i:s', strtotime('-1 month')),
            'expires_at'     => $expires,
            'amount_paid'    => 0,
            'invoice_number' => $subModel->generateInvoiceNumber(),
        ]);

        $active = $subModel->getActiveByUser($userId);
        $created[] = [
            'user_id'  => $userId,
            'email'    => $email,
            'phone'    => $spec['phone'],
            'sub_id'   => $subId,
            'plan'     => $spec['slug'],
            'status'   => $status,
            'expires'  => $expires,
            'active'   => $active ? ($active['plan_name'] ?? 'yes') : 'none',
            'note'     => $spec['note'],
        ];

        out("    Sub #{$subId} — {$spec['slug']} — {$status} — expires {$expires}");
        out("    Active check: " . ($active ? $active['plan_name'] : 'NO ACTIVE SUB'));
    }
    hr();

    // 4. Notifications
    $runNotify = (bool) arg('notify', false);
    out('[4] Notifications' . ($runNotify ? ' (LIVE send)' : ' (dry-run — pass --notify to send)'));

    $notifier = new SubscriptionNotificationService();

    if ($runNotify) {
        foreach ($created as $row) {
            if ($row['status'] !== 'active') {
                continue;
            }
            // Clear prior plan_start notifications so we can re-test
            $db->execute(
                "DELETE FROM notifications WHERE user_id = ? AND subject_id = ? AND type = 'subscription_plan_start'",
                [$row['user_id'], $row['sub_id']]
            );
            $r = $notifier->sendPlanStart($row['user_id'], $row['sub_id']);
            $wa = $r['whatsapp']['success'] ?? false ? 'WA:ok' : ('WA:' . ($r['whatsapp']['error'] ?? 'skip'));
            out("  Plan start → {$row['email']}: in-app=" . (!empty($r['success']) ? 'yes' : 'no') . " {$wa}");
        }

        $warn = $notifier->processExpiryWarnings();
        out("  Expiry warnings: sent={$warn['sent']} skipped={$warn['skipped']}");
        if (!empty($warn['errors'])) {
            foreach ($warn['errors'] as $e) {
                out("    · {$e}");
            }
        }

        $exp = $notifier->expireAndNotify();
        out("  Expired processed: {$exp['expired']}");
    } else {
        out('  Skipped live notifications. Use --notify to trigger plan start + expiry cron.');
        out('  Expiring-soon user: test.pro.expiring@findownn.test');
        out('  Expired user: test.expired@findownn.test (set status=expired manually)');
    }
    hr();

    // 5. Summary table
    out('[5] Test credentials (password: Test@1234)');
    foreach ($created as $row) {
        out(sprintf(
            '  %-32s %-28s %s',
            $row['email'],
            $row['plan'] . ' / ' . $row['status'],
            'active: ' . $row['active']
        ));
    }
    hr();

    out('[6] Manual checks');
    out('  Admin → Users → assign/change plan → verify plan start notification');
    out('  Owner login → Dashboard → subscription banner');
    out('  Admin → Settings → configure Twilio/Meta WhatsApp (optional)');
    out('  Cron: php admin/cron/send-subscription-notifications.php --expire');
    out('  Web cron: /admin/cron/send-subscription-notifications.php?key=CRON_SECRET&expire=1');
    out('');
    out('Done.');

} catch (Throwable $e) {
    out('ERROR: ' . $e->getMessage());
    if ($isCli) {
        exit(1);
    }
    http_response_code(500);
}
