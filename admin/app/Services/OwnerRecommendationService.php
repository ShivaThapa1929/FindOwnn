<?php

namespace App\Services;

use App\Core\Database;

/**
 * Actionable setup & growth tips for venue owner dashboard.
 */
class OwnerRecommendationService
{
    private Database $db;

    public function __construct(?Database $db = null)
    {
        $this->db = $db ?? Database::getInstance();
    }

    /** @return list<array<string, mixed>> */
    public function forOwner(int $ownerId, array $context = []): array
    {
        $myVenues       = $context['myVenues'] ?? [];
        $mySub          = $context['mySub'] ?? null;
        $bookStats      = $context['bookStats'] ?? [];
        $venueStats     = $context['venueStats'] ?? [];
        $recommendations = [];

        $venueIds = array_map(fn($v) => (int) $v['id'], $myVenues);
        $courtCount = 0;
        $imageCount = 0;

        if ($venueIds !== []) {
            $placeholders = implode(',', array_fill(0, count($venueIds), '?'));
            $courtCount = (int) $this->db->fetchColumn(
                "SELECT COUNT(*) FROM courts WHERE venue_id IN ({$placeholders}) AND deleted_at IS NULL",
                $venueIds
            );
            $imageCount = (int) $this->db->fetchColumn(
                "SELECT COUNT(*) FROM venue_images WHERE venue_id IN ({$placeholders})",
                $venueIds
            );
        }

        $pendingBookings = (int) ($bookStats['pending'] ?? 0);

        if (!$mySub) {
            $recommendations[] = $this->item(
                'subscribe',
                'high',
                'shield-check',
                'Choose a subscription plan',
                'Pick Starter, Pro, or Enterprise to list venues and accept online bookings.',
                'View Plans',
                url('/subscriptions/my-plans'),
                'Required'
            );
        } elseif (!empty($mySub['expires_at']) && strtotime($mySub['expires_at']) < strtotime('+14 days')) {
            $recommendations[] = $this->item(
                'renew_sub',
                'high',
                'clock-history',
                'Renew your subscription',
                'Your ' . ($mySub['plan_name'] ?? 'plan') . ' expires on ' . date('M j, Y', strtotime($mySub['expires_at'])) . '. Renew to avoid booking interruptions.',
                'Renew Now',
                url('/subscriptions/my-plans'),
                'Expiring'
            );
        }

        if (empty($myVenues)) {
            $recommendations[] = $this->item(
                'add_venue',
                'high',
                'building-add',
                'Add your first venue',
                'Create a playground profile with location, pricing, and courts so players can find and book you.',
                'Add Venue',
                url('/venues/create'),
                'Start here'
            );
        } else {
            if ($courtCount === 0) {
                $firstVenueId = (int) $myVenues[0]['id'];
                $recommendations[] = $this->item(
                    'add_courts',
                    'high',
                    'grid-3x3-gap',
                    'Add courts to your venue',
                    'Bookings are made per court. Add at least one court or play area to start accepting reservations.',
                    'Add Courts',
                    url('/courts?venue_id=' . $firstVenueId),
                    'Important'
                );
            }

            if ($imageCount === 0) {
                $firstVenueId = (int) $myVenues[0]['id'];
                $recommendations[] = $this->item(
                    'upload_photos',
                    'medium',
                    'camera-fill',
                    'Upload venue photos',
                    'Listings with photos get more clicks. Add cover and gallery images to build trust with players.',
                    'Upload Photos',
                    url('/venues/' . $firstVenueId),
                    'Boost visibility'
                );
            }

            if (($venueStats['verified'] ?? 0) === 0) {
                $recommendations[] = $this->item(
                    'get_verified',
                    'medium',
                    'patch-check',
                    'Get verified badge',
                    'Complete your venue details and keep courts active. Verified venues rank higher on Findownn.',
                    'View Venues',
                    url('/venues'),
                    'Trust'
                );
            }

            if (($venueStats['pending'] ?? 0) > 0) {
                $recommendations[] = $this->item(
                    'venue_pending',
                    'low',
                    'hourglass-split',
                    'Venue under review',
                    'You have ' . (int) $venueStats['pending'] . ' venue(s) awaiting approval. We usually review within 24 hours.',
                    'Check Status',
                    url('/venues?status=pending'),
                    'In progress'
                );
            }
        }

        if ($pendingBookings > 0) {
            $recommendations[] = $this->item(
                'pending_bookings',
                'high',
                'calendar-event',
                'Review pending bookings',
                "You have {$pendingBookings} booking(s) awaiting payment or confirmation. Respond quickly to avoid losing players.",
                'View Pending',
                url('/bookings?status=pending'),
                (string) $pendingBookings . ' pending'
            );
        }

        if (!empty($myVenues) && $courtCount > 0 && (int) ($bookStats['total'] ?? 0) === 0) {
            $recommendations[] = $this->item(
                'first_booking',
                'medium',
                'calendar-plus',
                'Create your first booking',
                'Try an offline / walk-in booking to test slots, or share your venue link with local players.',
                'Add Offline Booking',
                url('/bookings/offline/create'),
                'Quick win'
            );
        }

        if ($mySub && !empty($mySub['max_venues']) && count($myVenues) >= (int) $mySub['max_venues']) {
            $recommendations[] = $this->item(
                'upgrade_plan',
                'medium',
                'arrow-up-circle',
                'Upgrade your plan',
                'You have reached the venue limit on your ' . ($mySub['plan_name'] ?? 'current') . ' plan. Upgrade to add more playgrounds.',
                'Upgrade Plan',
                url('/subscriptions/my-plans'),
                'Grow'
            );
        }

        if (!empty($myVenues) && ($bookStats['total'] ?? 0) > 0) {
            $recommendations[] = $this->item(
                'booking_slots',
                'low',
                'clock',
                'Manage booking slots',
                'Set court availability, block maintenance hours, and control peak-time pricing from Booking Slots.',
                'Open Slots',
                url('/bookings/slots'),
                'Pro tip'
            );
        }

        // Always show at least one helpful tip if list is empty (fully set up owners)
        if ($recommendations === []) {
            $recommendations[] = $this->item(
                'share_venue',
                'low',
                'share',
                'Share your venue link',
                'Promote your Findownn listing on WhatsApp and Instagram to drive more bookings this week.',
                'View Public Site',
                function_exists('site_home_url') ? rtrim(site_home_url(), '/') . '/venues' : url('/venues'),
                'Growth'
            );
        }

        usort($recommendations, fn($a, $b) => ($a['sort'] <=> $b['sort']));

        return array_slice($recommendations, 0, 6);
    }

    private function item(
        string $id,
        string $priority,
        string $icon,
        string $title,
        string $description,
        string $actionLabel,
        string $actionUrl,
        string $badge = ''
    ): array {
        $sort = match ($priority) {
            'high'   => 1,
            'medium' => 2,
            default  => 3,
        };

        return [
            'id'           => $id,
            'priority'     => $priority,
            'sort'         => $sort,
            'icon'         => $icon,
            'title'        => $title,
            'description'  => $description,
            'action_label' => $actionLabel,
            'action_url'   => $actionUrl,
            'badge'        => $badge,
        ];
    }
}
