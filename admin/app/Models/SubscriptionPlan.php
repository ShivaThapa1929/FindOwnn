<?php

namespace App\Models;

use App\Core\Model;

class SubscriptionPlan extends Model
{
    protected string $table    = 'subscription_plans';
    protected array  $fillable = [
        'name', 'slug', 'price', 'platform_fee_percent', 'billing_cycle', 'description',
        'features', 'max_venues', 'max_images', 'max_slots',
        'is_active', 'is_featured', 'sort_order', 'created_at', 'updated_at',
    ];

    public function getActivePlans(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY sort_order ASC"
        );
    }
}
