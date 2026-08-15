<?php

namespace App\Services;

use App\Core\Database;

/**
 * Subscription lifecycle notifications — in-app + optional WhatsApp (Twilio/Meta).
 */
class SubscriptionNotificationService
{
    private Database $db;
    private WhatsAppService $whatsapp;

    public function __construct()
    {
        $this->db       = Database::getInstance();
        $this->whatsapp = new WhatsAppService();
    }

    /** Notify owner when a plan becomes active. */
    public function sendPlanStart(int $userId, int $subscriptionId): array
    {
        if (!$this->isEnabled('send_subscription_start')) {
            return $this->skipped('Plan start notifications disabled');
        }

        if ($this->alreadyNotified($userId, $subscriptionId, 'subscription_plan_start')) {
            return $this->skipped('Plan start already sent');
        }

        $sub = $this->getSubscriptionPayload($subscriptionId, $userId);
        if (!$sub) {
            return ['success' => false, 'error' => 'Subscription not found'];
        }

        $title   = 'Subscription activated';
        $message = $this->formatPlanStartMessage($sub);

        $this->createInAppNotification($userId, $title, $message, 'subscription_plan_start', $subscriptionId);

        $wa = $this->sendWhatsAppToOwner($sub, $message);

        return [
            'success'    => true,
            'type'       => 'plan_start',
            'user_id'    => $userId,
            'sub_id'     => $subscriptionId,
            'whatsapp'   => $wa,
            'in_app'     => true,
        ];
    }

    /** Warn owner before subscription expires (default: 7 days). */
    public function sendExpiryWarning(array $sub): array
    {
        if (!$this->isEnabled('send_subscription_expiry_warning')) {
            return $this->skipped('Expiry warning notifications disabled');
        }

        $userId = (int) $sub['user_id'];
        $subId  = (int) $sub['id'];

        if ($this->alreadyNotified($userId, $subId, 'subscription_expiry_warning')) {
            return $this->skipped('Expiry warning already sent for this subscription');
        }

        $title   = 'Subscription expiring soon';
        $message = $this->formatExpiryWarningMessage($sub);

        $this->createInAppNotification($userId, $title, $message, 'subscription_expiry_warning', $subId);

        $wa = $this->sendWhatsAppToOwner($sub, $message);

        return [
            'success'  => true,
            'type'     => 'expiry_warning',
            'user_id'  => $userId,
            'sub_id'   => $subId,
            'whatsapp' => $wa,
            'in_app'   => true,
        ];
    }

    /** Notify owner when subscription has expired. */
    public function sendPlanExpired(array $sub): array
    {
        if (!$this->isEnabled('send_subscription_expired')) {
            return $this->skipped('Expired notifications disabled');
        }

        $userId = (int) $sub['user_id'];
        $subId  = (int) $sub['id'];

        if ($this->alreadyNotified($userId, $subId, 'subscription_expired')) {
            return $this->skipped('Expired notification already sent');
        }

        $title   = 'Subscription expired';
        $message = $this->formatExpiredMessage($sub);

        $this->createInAppNotification($userId, $title, $message, 'subscription_expired', $subId);

        $wa = $this->sendWhatsAppToOwner($sub, $message);

        return [
            'success'  => true,
            'type'     => 'expired',
            'user_id'  => $userId,
            'sub_id'   => $subId,
            'whatsapp' => $wa,
            'in_app'   => true,
        ];
    }

    /** Cron: expiry warnings for active subs expiring within N days. */
    public function processExpiryWarnings(): array
    {
        $days = max(1, (int) ($this->getSetting('subscription_warning_days') ?: 7));

        $rows = $this->db->fetchAll(
            "SELECT s.*, p.name AS plan_name, p.slug AS plan_slug, p.platform_fee_percent,
                    u.name AS user_name, u.email AS user_email, u.phone, u.whatsapp_number
             FROM subscriptions s
             JOIN subscription_plans p ON s.plan_id = p.id
             JOIN users u ON s.user_id = u.id
             WHERE s.status = 'active'
               AND s.expires_at > NOW()
               AND s.expires_at <= DATE_ADD(NOW(), INTERVAL ? DAY)
             ORDER BY s.expires_at ASC",
            [$days]
        );

        $sent = 0;
        $skipped = 0;
        $errors = [];

        foreach ($rows as $row) {
            $result = $this->sendExpiryWarning($row);
            if (!empty($result['success'])) {
                $sent++;
            } elseif (!empty($result['skipped'])) {
                $skipped++;
            } else {
                $errors[] = ($result['error'] ?? 'Unknown error') . " (sub #{$row['id']})";
            }
        }

        return compact('sent', 'skipped', 'errors');
    }

    /** Expire old subs and notify owners. Returns [expired_count, notifications]. */
    public function expireAndNotify(): array
    {
        $expiring = $this->db->fetchAll(
            "SELECT s.*, p.name AS plan_name, p.slug AS plan_slug, p.platform_fee_percent,
                    u.name AS user_name, u.email AS user_email, u.phone, u.whatsapp_number
             FROM subscriptions s
             JOIN subscription_plans p ON s.plan_id = p.id
             JOIN users u ON s.user_id = u.id
             WHERE s.status = 'active' AND s.expires_at < NOW()"
        );

        $notified = [];
        foreach ($expiring as $sub) {
            $notified[] = $this->sendPlanExpired($sub);
        }

        $count = $this->db->execute(
            "UPDATE subscriptions SET status = 'expired', updated_at = ?
             WHERE status = 'active' AND expires_at < NOW()",
            [now()]
        );

        return ['expired' => $count, 'notifications' => $notified];
    }

    public function isWhatsAppConfigured(): bool
    {
        return $this->whatsapp->isConfigured();
    }

    public function testWhatsApp(string $phone, string $message = ''): array
    {
        if (!$this->whatsapp->isConfigured()) {
            return ['success' => false, 'error' => 'WhatsApp is not configured'];
        }

        $message = $message !== '' ? $message : 'Findownn test — WhatsApp gateway is working.';

        return $this->whatsapp->sendMessage($phone, $message, ['skip_plan_check' => true]);
    }

    private function getSubscriptionPayload(int $subscriptionId, int $userId): array|false
    {
        return $this->db->fetch(
            "SELECT s.*, p.name AS plan_name, p.slug AS plan_slug, p.platform_fee_percent,
                    u.name AS user_name, u.email AS user_email, u.phone, u.whatsapp_number
             FROM subscriptions s
             JOIN subscription_plans p ON s.plan_id = p.id
             JOIN users u ON s.user_id = u.id
             WHERE s.id = ? AND s.user_id = ?
             LIMIT 1",
            [$subscriptionId, $userId]
        );
    }

    private function sendWhatsAppToOwner(array $sub, string $message): array
    {
        $phone = $this->resolveOwnerPhone($sub);
        if ($phone === '') {
            return ['success' => false, 'skipped' => true, 'error' => 'No phone on file'];
        }

        if (!$this->whatsapp->isConfigured()) {
            return ['success' => false, 'skipped' => true, 'error' => 'WhatsApp not configured'];
        }

        return $this->whatsapp->sendMessage($phone, $message, ['skip_plan_check' => true]);
    }

    private function resolveOwnerPhone(array $sub): string
    {
        $raw = trim((string) ($sub['whatsapp_number'] ?? $sub['phone'] ?? ''));
        if ($raw === '') {
            return '';
        }

        $digits = preg_replace('/\D/', '', $raw);
        if (strlen($digits) === 10) {
            return '+91' . $digits;
        }
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return '+' . $digits;
        }

        return str_starts_with($raw, '+') ? $raw : '+' . $digits;
    }

    private function formatPlanStartMessage(array $sub): string
    {
        $fee = ($sub['plan_slug'] ?? '') === 'enterprise'
            ? 'Negotiable'
            : rtrim(rtrim(number_format((float) ($sub['platform_fee_percent'] ?? 0), 2), '0'), '.') . '%';

        return "🎉 *Findownn Plan Activated*\n\n"
            . "Hello {$sub['user_name']},\n\n"
            . "Your *{$sub['plan_name']}* subscription is now active.\n\n"
            . "📅 Valid until: " . date('d M Y', strtotime($sub['expires_at'])) . "\n"
            . "💳 Platform fee: {$fee} per booking\n\n"
            . "Manage your plan in the owner dashboard.\n"
            . "— Team Findownn";
    }

    private function formatExpiryWarningMessage(array $sub): string
    {
        $daysLeft = max(0, (int) ceil((strtotime($sub['expires_at']) - time()) / 86400));

        return "⏰ *Subscription Expiring Soon*\n\n"
            . "Hello {$sub['user_name']},\n\n"
            . "Your *{$sub['plan_name']}* plan expires in *{$daysLeft} day(s)* "
            . '(' . date('d M Y', strtotime($sub['expires_at'])) . ").\n\n"
            . "Renew or upgrade in the Findownn owner dashboard to avoid interruption.\n"
            . "— Team Findownn";
    }

    private function formatExpiredMessage(array $sub): string
    {
        return "⚠️ *Subscription Expired*\n\n"
            . "Hello {$sub['user_name']},\n\n"
            . "Your *{$sub['plan_name']}* plan has expired.\n\n"
            . "Booking features may be limited until you renew. "
            . "Visit the owner dashboard to choose a plan.\n"
            . "— Team Findownn";
    }

    private function createInAppNotification(
        int $userId,
        string $title,
        string $message,
        string $type,
        int $subjectId
    ): void {
        $this->db->insert(
            "INSERT INTO notifications (user_id, title, message, type, subject_type, subject_id, is_read, created_at)
             VALUES (?, ?, ?, ?, 'subscription', ?, 0, NOW())",
            [$userId, $title, $message, $type, $subjectId]
        );
    }

    private function alreadyNotified(int $userId, int $subjectId, string $type): bool
    {
        $exists = $this->db->fetchColumn(
            "SELECT COUNT(*) FROM notifications
             WHERE user_id = ? AND subject_type = 'subscription' AND subject_id = ? AND type = ?",
            [$userId, $subjectId, $type]
        );

        return (int) $exists > 0;
    }

    private function isEnabled(string $key): bool
    {
        return $this->getSetting($key) !== '0';
    }

    private function getSetting(string $key): string
    {
        $row = $this->db->fetch(
            "SELECT value FROM settings WHERE `key` = ? LIMIT 1",
            [$key]
        );

        return trim((string) ($row['value'] ?? '1'));
    }

    /** @return array{success: false, skipped: true, error: string} */
    private function skipped(string $reason): array
    {
        return ['success' => false, 'skipped' => true, 'error' => $reason];
    }
}
