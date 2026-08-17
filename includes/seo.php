<?php
/**
 * Per-page SEO metadata for the public website.
 */

function site_canonical_url(): string
{
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri      = $_SERVER['REQUEST_URI'] ?? '/';
    $path     = parse_url($uri, PHP_URL_PATH) ?: '/';
    $path     = preg_replace('#/index\.php$#', '', $path) ?: '/';

    return rtrim($protocol . '://' . $host, '/') . $path;
}

function site_seo_meta(string $routeName = 'index'): array
{
    $defaults = [
        'title'       => 'Findownn — Book Sports Playgrounds in Bhuj',
        'description' => 'Discover and book Box Cricket, Pickleball, and sports playgrounds across Bhuj instantly. No calls, no waiting — just book and play.',
        'keywords'    => 'Findownn, sports booking Bhuj, box cricket Bhuj, pickleball Bhuj, book playground Gujarat',
        'robots'      => 'index, follow',
        'og_type'     => 'website',
    ];

    $pages = [
        'index' => [
            'title'       => 'Findownn — Bhuj\'s Sports Playground Booking Platform',
            'description' => 'Book Box Cricket & Pickleball playgrounds across Bhuj in seconds. Verified venues, live slots, and secure online payments.',
        ],
        'venues' => [
            'title'       => 'Browse Playgrounds — Findownn Bhuj',
            'description' => 'Explore verified sports playgrounds in Bhuj. Filter by sport, compare prices, and book your court online.',
        ],
        'venue-details' => [
            'title'       => 'Playground Details & Booking — Findownn',
            'description' => 'View playground photos, courts, pricing, and available time slots. Book instantly on Findownn.',
        ],
        'sports' => [
            'title'       => 'Sports & Activities — Findownn Bhuj',
            'description' => 'Box Cricket, Pickleball, Football, Badminton and more — find playgrounds for every sport in Bhuj.',
        ],
        'partner' => [
            'title'       => 'List Your Playground — Findownn Partner Program',
            'description' => 'Grow your sports venue business with Findownn. Online bookings, payments, and owner dashboard.',
        ],
        'about' => [
            'title'       => 'About Findownn — Sports Booking Made Simple',
            'description' => 'Learn how Findownn helps players book playgrounds and helps venue owners manage bookings in Bhuj.',
        ],
        'contact' => [
            'title'       => 'Contact & Support — Findownn',
            'description' => 'Get help with bookings, playground listings, or partnerships. Reach the Findownn team in Bhuj.',
        ],
        'privacy' => [
            'title'       => 'Privacy Policy — Findownn',
            'description' => 'How Findownn collects, uses, and protects your personal information.',
        ],
        'terms' => [
            'title'       => 'Terms of Service — Findownn',
            'description' => 'Terms and conditions for using Findownn as a player or venue partner.',
        ],
        'login' => [
            'title'       => 'Sign In — Findownn',
            'description' => 'Choose your portal and sign in as a player, venue owner, or admin on Findownn.',
            'robots'      => 'noindex, follow',
        ],
        'register' => [
            'title'       => 'Create Player Account — Findownn',
            'description' => 'Register as a player to book sports playgrounds and manage your bookings on Findownn.',
            'robots'      => 'noindex, follow',
        ],
        'dashboard' => [
            'title'       => 'My Dashboard — Findownn',
            'description' => 'View your bookings, stats, and account on Findownn.',
            'robots'      => 'noindex, nofollow',
        ],
        'account' => [
            'title'       => 'My Account — Findownn',
            'description' => 'Manage your Findownn player profile and account settings.',
            'robots'      => 'noindex, nofollow',
        ],
        'booking-payment' => [
            'title'       => 'Complete Payment — Findownn',
            'description' => 'Secure checkout for your playground booking on Findownn.',
            'robots'      => 'noindex, nofollow',
        ],
        '404' => [
            'title'       => 'Page Not Found — Findownn',
            'description' => 'The page you requested could not be found on Findownn.',
            'robots'      => 'noindex, nofollow',
        ],
    ];

    return array_merge($defaults, $pages[$routeName] ?? []);
}

function site_json_ld_organization(string $assetBase): string
{
    require_once __DIR__ . '/site-contact.php';

    $base = rtrim($assetBase, '/');
    $logo = $base . '/assets/images/logo.png';

    $data = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Organization',
        'name'        => 'Findownn',
        'url'         => site_canonical_url(),
        'logo'        => $logo,
        'email'       => $site_contact_email ?? 'findownn@gmail.com',
        'telephone'   => $site_phone_tel ?? '',
        'address'     => [
            '@type'           => 'PostalAddress',
            'addressLocality' => 'Bhuj',
            'addressRegion'   => 'Gujarat',
            'postalCode'      => '370001',
            'addressCountry'  => 'IN',
        ],
    ];

    return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
