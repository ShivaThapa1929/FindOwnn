<?php

namespace App\Services;

use App\Models\Subscription;

/**
 * Marketplace platform fee — per owner subscription plan.
 */
class PlatformFeeService
{
    /** @var array<string, int> */
    private const TIER = [
        'starter'      => 1,
        'free'         => 1,
        'growth'       => 2,
        'basic'        => 2,
        'professional' => 3,
        'premium'      => 3,
        'enterprise'   => 4,
    ];

    public function getOwnerPlan(int $ownerId): array|false
    {
        return (new Subscription())->getActiveByUser($ownerId);
    }

    public function getPlatformFeePercent(int $ownerId): ?float
    {
        $plan = $this->getOwnerPlan($ownerId);
        if (!$plan) {
            return 5.0;
        }

        $slug = (string) ($plan['plan_slug'] ?? $plan['slug'] ?? 'starter');
        if ($slug === 'enterprise') {
            return null;
        }

        if (isset($plan['platform_fee_percent']) && $plan['platform_fee_percent'] !== '') {
            return (float) $plan['platform_fee_percent'];
        }

        return match ($slug) {
            'growth', 'basic'        => 3.0,
            'professional', 'premium' => 1.0,
            default                  => 5.0,
        };
    }

    public function getPlatformFeeLabel(int $ownerId): string
    {
        $fee = $this->getPlatformFeePercent($ownerId);
        if ($fee === null) {
            return 'Negotiable';
        }

        return rtrim(rtrim(number_format($fee, 2), '0'), '.') . '%';
    }

    /** Growth plan and above */
    public function hasWhatsAppAccess(int $ownerId): bool
    {
        return $this->planTier($ownerId) >= 2;
    }

    public function planTier(int $ownerId): int
    {
        $plan = $this->getOwnerPlan($ownerId);
        if (!$plan) {
            return 1;
        }

        $slug = (string) ($plan['plan_slug'] ?? $plan['slug'] ?? 'starter');

        return self::TIER[$slug] ?? 1;
    }

    /** @return array{platform_fee: float, owner_payout: float, fee_percent: float|null} */
    public function calculateSettlement(float $bookingAmount, int $ownerId): array
    {
        $feePercent = $this->getPlatformFeePercent($ownerId);
        if ($feePercent === null) {
            return [
                'platform_fee' => 0.0,
                'owner_payout' => round($bookingAmount, 2),
                'fee_percent'  => null,
            ];
        }

        $platformFee = round($bookingAmount * $feePercent / 100, 2);

        return [
            'platform_fee' => $platformFee,
            'owner_payout' => round($bookingAmount - $platformFee, 2),
            'fee_percent'  => $feePercent,
        ];
    }

    /** Example savings vs Starter for UI calculator */
    public function monthlyFeeSavings(float $monthlyBookings, string $fromSlug, string $toSlug): array
    {
        $fees = [
            'starter'      => 5.0,
            'free'         => 5.0,
            'growth'       => 3.0,
            'basic'        => 3.0,
            'professional' => 1.0,
            'premium'      => 1.0,
        ];
        $prices = [
            'starter'      => 0,
            'growth'       => 999,
            'professional' => 2499,
        ];

        $fromFee = $fees[$fromSlug] ?? 5.0;
        $toFee   = $fees[$toSlug] ?? 5.0;
        $fromCost = $monthlyBookings * $fromFee / 100;
        $toCost   = $monthlyBookings * $toFee / 100 + ($prices[$toSlug] ?? 0);
        $saved    = max(0, $fromCost - $toCost);

        return [
            'from_fee'     => $fromFee,
            'to_fee'       => $toFee,
            'fee_saved'    => round($saved, 0),
            'monthly_cost' => round($toCost, 0),
        ];
    }
}
