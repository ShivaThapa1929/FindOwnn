<?php
/**
 * Upsert Findownn Marketplace subscription plans (Venue Owner model)
 * Run: php admin/setup-subscription-plans.php
 */
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', __DIR__);
}

require_once __DIR__ . '/app/Core/Config.php';
require_once __DIR__ . '/app/Core/Logger.php';
require_once __DIR__ . '/app/Core/Database.php';

use App\Core\Config;
use App\Core\Database;

try {
    Config::load(__DIR__ . '/.env');
    $db = Database::getInstance();

    echo "Updating subscription plans (Marketplace Model)...\n\n";

    try {
        $db->execute(
            'ALTER TABLE subscription_plans ADD COLUMN platform_fee_percent DECIMAL(5,2) NULL DEFAULT NULL AFTER price'
        );
        echo "  ✓ Added platform_fee_percent column\n";
    } catch (Throwable) {
        echo "  · platform_fee_percent column already exists\n";
    }

    $plans = [
        [
            'name'                  => 'Starter',
            'slug'                  => 'starter',
            'price'                 => 0,
            'platform_fee_percent'  => 5.00,
            'billing_cycle'         => 'monthly',
            'description'           => 'Best for new venue owners starting with Findownn',
            'max_venues'            => 1,
            'is_featured'           => 0,
            'sort_order'            => 1,
            'features'              => implode("\n", [
                '1 Venue Listing',
                'Online Booking Management',
                'Online Payment Collection',
                'Booking Calendar',
                'Venue Profile',
                'Booking History',
                'Customer Reviews & Ratings',
                'Basic Dashboard',
                'Basic Revenue Summary',
                'Settlement History',
                'Email Support',
            ]),
        ],
        [
            'name'                  => 'Growth',
            'slug'                  => 'growth',
            'price'                 => 999,
            'platform_fee_percent'  => 3.00,
            'billing_cycle'         => 'monthly',
            'description'           => 'Best for growing sports venues',
            'max_venues'            => 3,
            'is_featured'           => 0,
            'sort_order'            => 2,
            'features'              => implode("\n", [
                'Everything in Starter',
                'WhatsApp Booking Confirmation',
                'WhatsApp Payment Confirmation',
                'Booking Reminder Notifications',
                'Weekday & Weekend Pricing',
                'Customer Database',
                'Booking Reports',
                'Cancellation & Refund Management',
                'Basic Analytics Dashboard',
                'Download Reports (PDF / Excel)',
                'Email & Chat Support',
            ]),
        ],
        [
            'name'                  => 'Professional',
            'slug'                  => 'professional',
            'price'                 => 2499,
            'platform_fee_percent'  => 1.00,
            'billing_cycle'         => 'monthly',
            'description'           => 'Best for professional venue owners who want to automate and grow',
            'max_venues'            => 10,
            'is_featured'           => 1,
            'sort_order'            => 3,
            'features'              => implode("\n", [
                'Everything in Growth',
                'Partial Payment Support',
                'QR Code Check-in',
                'Additional Billing (Snacks, Drinks, Equipment)',
                'Staff Accounts',
                'Advanced Revenue Analytics',
                'Peak Hour Analytics',
                'Customer Insights',
                'Verified Venue Badge',
                'Featured Venue Listing',
                'Priority Customer Support',
                'Custom Booking Rules',
                'Advanced Settlement Reports',
            ]),
        ],
        [
            'name'                  => 'Enterprise',
            'slug'                  => 'enterprise',
            'price'                 => 0,
            'platform_fee_percent'  => null,
            'billing_cycle'         => 'monthly',
            'description'           => 'Best for sports clubs, academies, schools & multi-location businesses',
            'max_venues'            => 999,
            'is_featured'           => 0,
            'sort_order'            => 4,
            'features'              => implode("\n", [
                'Everything in Professional',
                'Unlimited Venue Management',
                'Multi-location Dashboard',
                'Unlimited Staff Accounts',
                'Branch-wise Reports',
                'Dedicated Account Manager',
                'Custom Reports',
                'Personalized Onboarding & Training',
                'Priority Support',
                'Custom Business Solutions',
                'Custom Pricing & Negotiable Platform Fee',
            ]),
        ],
    ];

    // Migrate legacy slugs → new slugs (keep subscription FKs valid)
    $legacyMap = ['free' => 'starter', 'basic' => 'growth', 'premium' => 'professional'];
    foreach ($legacyMap as $old => $new) {
        $oldRow = $db->fetch('SELECT id FROM subscription_plans WHERE slug = ?', [$old]);
        $newRow = $db->fetch('SELECT id FROM subscription_plans WHERE slug = ?', [$new]);
        if ($oldRow && !$newRow) {
            $db->execute('UPDATE subscription_plans SET slug = ? WHERE id = ?', [$new, $oldRow['id']]);
            echo "  ✓ Renamed slug {$old} → {$new}\n";
        }
    }

    foreach ($plans as $p) {
        $existing = $db->fetch('SELECT id FROM subscription_plans WHERE slug = ?', [$p['slug']]);
        if ($existing) {
            $db->execute(
                'UPDATE subscription_plans SET name=?, price=?, platform_fee_percent=?, billing_cycle=?, description=?, features=?, max_venues=?, is_active=1, is_featured=?, sort_order=?, updated_at=NOW() WHERE slug=?',
                [$p['name'], $p['price'], $p['platform_fee_percent'], $p['billing_cycle'], $p['description'], $p['features'], $p['max_venues'], $p['is_featured'], $p['sort_order'], $p['slug']]
            );
            echo "  ✓ Updated {$p['name']}\n";
        } else {
            $db->execute(
                'INSERT INTO subscription_plans (name, slug, price, platform_fee_percent, billing_cycle, description, features, max_venues, is_active, is_featured, sort_order, created_at, updated_at) VALUES (?,?,?,?,?,?,?,?,1,?,?,NOW(),NOW())',
                [$p['name'], $p['slug'], $p['price'], $p['platform_fee_percent'], $p['billing_cycle'], $p['description'], $p['features'], $p['max_venues'], $p['is_featured'], $p['sort_order']]
            );
            echo "  ✓ Inserted {$p['name']}\n";
        }
    }

    // Deactivate old duplicate legacy plans if both exist
    foreach (array_keys($legacyMap) as $legacySlug) {
        $db->execute(
            "UPDATE subscription_plans SET is_active = 0, updated_at = NOW() WHERE slug = ? AND slug NOT IN ('starter','growth','professional','enterprise')",
            [$legacySlug]
        );
    }

    echo "\n✅ Marketplace subscription plans ready.\n";
    echo "   Platform fees: Starter 5% | Growth 3% | Professional 1% | Enterprise negotiable\n";
} catch (Throwable $e) {
    echo '❌ Error: ' . $e->getMessage() . "\n";
    exit(1);
}
