<?php

namespace App\Models;

use App\Core\Model;

class Subscription extends Model
{
    protected string $table    = 'subscriptions';
    protected array  $fillable = [
        'user_id', 'plan_id', 'status', 'starts_at', 'expires_at',
        'auto_renew', 'payment_id', 'amount_paid', 'invoice_number',
        'created_at', 'updated_at',
    ];

    public function getActiveByUser(int $userId): array|false
    {
        return $this->db->fetch(
            "SELECT s.*, p.name AS plan_name, p.price, p.features, p.max_venues
             FROM subscriptions s
             LEFT JOIN subscription_plans p ON s.plan_id = p.id
             WHERE s.user_id = ? AND s.status = 'active' AND s.expires_at > NOW()
             ORDER BY s.created_at DESC LIMIT 1",
            [$userId]
        );
    }

    public function getAllWithDetails(int $page = 1, int $perPage = 20, string $status = 'all'): array
    {
        $where  = '1=1';
        $params = [];
        if ($status !== 'all') {
            $where  .= ' AND s.status = ?';
            $params[] = $status;
        }

        $total  = (int) $this->db->fetchColumn(
            "SELECT COUNT(*) FROM subscriptions s WHERE {$where}", $params
        );
        $offset = ($page - 1) * $perPage;
        $pages  = (int) ceil($total / $perPage);

        $data = $this->db->fetchAll(
            "SELECT s.*, u.name AS user_name, u.email AS user_email,
                    p.name AS plan_name, p.price
             FROM subscriptions s
             LEFT JOIN users u ON s.user_id = u.id
             LEFT JOIN subscription_plans p ON s.plan_id = p.id
             WHERE {$where}
             ORDER BY s.created_at DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        return compact('data', 'total', 'page', 'perPage', 'pages');
    }

    public function expireOld(): int
    {
        return $this->db->execute(
            "UPDATE subscriptions SET status = 'expired', updated_at = ?
             WHERE status = 'active' AND expires_at < NOW()",
            [now()]
        );
    }

    public function getStats(): array
    {
        $row = $this->db->fetch(
            "SELECT
                COUNT(*) AS total,
                SUM(status = 'active') AS active,
                SUM(status = 'expired') AS expired,
                SUM(status = 'pending') AS pending,
                SUM(amount_paid) AS total_revenue
             FROM subscriptions"
        );
        return $row ?: [];
    }

    public function generateInvoiceNumber(): string
    {
        $count = $this->count() + 1;
        return 'INV-' . date('Ymd') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    /** Cancel other active subs and assign a new plan */
    public function replaceActiveSubscription(
        int $userId,
        int $planId,
        int $months = 1,
        float $amountPaid = 0,
        string $status = 'active'
    ): ?int {
        $plan = $this->db->fetch(
            'SELECT id, name, billing_cycle, price FROM subscription_plans WHERE id = ? AND is_active = 1',
            [$planId]
        );
        if (!$plan) {
            return null;
        }

        $this->db->execute(
            "UPDATE subscriptions SET status = 'cancelled', updated_at = ?
             WHERE user_id = ? AND status = 'active'",
            [now(), $userId]
        );

        if (($plan['billing_cycle'] ?? '') === 'lifetime') {
            $expiresAt = date('Y-m-d H:i:s', strtotime('+10 years'));
        } elseif (($plan['billing_cycle'] ?? '') === 'yearly') {
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$months} years"));
        } else {
            $expiresAt = date('Y-m-d H:i:s', strtotime("+{$months} months"));
        }

        return $this->create([
            'user_id'        => $userId,
            'plan_id'        => $planId,
            'status'         => $status,
            'starts_at'      => now(),
            'expires_at'     => $expiresAt,
            'amount_paid'    => $amountPaid,
            'invoice_number' => $this->generateInvoiceNumber(),
        ]);
    }

    /** Assign a plan so venue owners can log in immediately */
    public function assignPlanToUser(int $userId, string $planSlug = 'starter', int $months = 12): bool
    {
        $plan = null;
        foreach (array_unique([$planSlug, 'starter', 'free']) as $slug) {
            $plan = $this->db->fetch(
                "SELECT id, billing_cycle FROM subscription_plans WHERE slug = ? AND is_active = 1 LIMIT 1",
                [$slug]
            );
            if ($plan) {
                break;
            }
        }

        if (!$plan) {
            $plan = $this->db->fetch(
                "SELECT id, billing_cycle FROM subscription_plans WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 1"
            );
        }

        if (!$plan) {
            return false;
        }

        $expiresAt = ($plan['billing_cycle'] ?? '') === 'lifetime'
            ? date('Y-m-d H:i:s', strtotime('+10 years'))
            : date('Y-m-d H:i:s', strtotime("+{$months} months"));

        $this->create([
            'user_id'        => $userId,
            'plan_id'        => (int) $plan['id'],
            'status'         => 'active',
            'starts_at'      => now(),
            'expires_at'     => $expiresAt,
            'amount_paid'    => 0,
            'invoice_number' => $this->generateInvoiceNumber(),
        ]);

        return true;
    }
}
