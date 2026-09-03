<?php
/**
 * Centralized Privacy Policy & Terms content for Findownn.
 * Update sections here — all pages and links use this single source.
 */

require_once __DIR__ . '/site-contact.php';

if (!function_exists('legal_last_updated')) {
    function legal_last_updated(): string
    {
        return 'September 1, 2026';
    }
}

if (!function_exists('legal_public_url')) {
    /** Public-site URL for legal pages (works from website and admin auth). */
    function legal_public_url(string $path = ''): string
    {
        global $asset_base;

        if (function_exists('site_home_url') && str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admin')) {
            $base = rtrim(site_home_url(), '/');
        } else {
            $base = rtrim($asset_base ?? '/', '/');
        }

        $path = ltrim($path, '/');
        if ($base === '' || $base === '/') {
            return '/' . $path;
        }

        return $base . '/' . $path;
    }
}

if (!function_exists('legal_privacy_url')) {
    function legal_privacy_url(): string
    {
        return legal_public_url('privacy');
    }
}

if (!function_exists('legal_terms_url')) {
    function legal_terms_url(): string
    {
        return legal_public_url('terms');
    }
}

if (!function_exists('legal_terms_accepted')) {
    function legal_terms_accepted(array $data): bool
    {
        return !empty($data['accept_terms']) || !empty($data['terms_accepted']);
    }
}

if (!function_exists('legal_privacy_sections')) {
    function legal_privacy_sections(): array
    {
        $email = site_contact_email();

        return [
            [
                'title' => 'Introduction',
                'paragraphs' => [
                    'Findownn (“we”, “us”, “our”) operates a sports playground and court booking platform for players and venue owners in India. This Privacy Policy explains how we collect, use, store, and protect your information when you use our website, mobile applications, and related services (collectively, the “Platform”).',
                    'By using Findownn, you agree to the practices described in this policy. If you do not agree, please do not use the Platform.',
                ],
            ],
            [
                'title' => 'Information We Collect',
                'paragraphs' => [
                    'We collect information that you provide directly, that is generated when you use the Platform, and that we receive from trusted third parties such as payment processors.',
                ],
            ],
            [
                'title' => 'Personal Information',
                'paragraphs' => [
                    'This may include your full name, email address, mobile phone number, WhatsApp number (if provided), profile details, and communications you send to our support team.',
                ],
            ],
            [
                'title' => 'Account Information',
                'paragraphs' => [
                    'When you register as a player or venue owner, we store account credentials (hashed passwords), role, account status, login timestamps, email verification status, and subscription plan details for venue partners.',
                ],
            ],
            [
                'title' => 'Venue & Booking Information',
                'paragraphs' => [
                    'When you book or manage a court, we collect venue name, sport, court, date, time slot, booking reference, booking status, amount, payment status, and notes related to walk-in or offline bookings entered by venue owners.',
                ],
            ],
            [
                'title' => 'How We Use Information',
                'paragraphs' => [
                    'We use your information to create and manage accounts; process and confirm bookings; facilitate payments; send booking confirmations and reminders (including WhatsApp where opted in); display booking history on your dashboard; support venue owners with listings, slots, and revenue tools; improve Platform performance and security; respond to support requests; and comply with legal obligations.',
                ],
            ],
            [
                'title' => 'Data Storage & Security',
                'paragraphs' => [
                    'We store data on secure servers with access controls, encrypted connections (HTTPS), and industry-standard safeguards. Passwords are stored using secure hashing. No method of transmission or storage is 100% secure, but we work continuously to protect your data.',
                ],
            ],
            [
                'title' => 'Cookies / Tracking Technologies',
                'paragraphs' => [
                    'We use cookies and similar technologies for session management, authentication, preferences, and analytics. You can control cookies through your browser settings. Disabling cookies may limit some Platform features.',
                ],
            ],
            [
                'title' => 'Third-Party Services',
                'paragraphs' => [
                    'We integrate with third parties including Razorpay (payments), email delivery providers, WhatsApp messaging (when configured), maps/location services, and hosting providers. These services process data according to their own policies and only as needed to provide Platform functionality.',
                ],
            ],
            [
                'title' => 'Payment Information',
                'paragraphs' => [
                    'Online payments are processed by Razorpay. We do not store full card, UPI PIN, or net-banking credentials on our servers. We may store payment references, transaction IDs, order IDs, payment status, and amounts for booking reconciliation and support.',
                ],
            ],
            [
                'title' => 'Location Information',
                'paragraphs' => [
                    'We may collect city or area information you enter when searching venues, or approximate location if you grant browser/device permission. Venue listings include address and map data supplied by venue owners.',
                ],
            ],
            [
                'title' => 'User Rights',
                'paragraphs' => [
                    'You may request access to, correction of, or deletion of your personal data, subject to legal and operational requirements. Contact us using the details below. Venue owners may update listing information through the owner dashboard.',
                ],
            ],
            [
                'title' => 'Data Retention',
                'paragraphs' => [
                    'We retain account and booking records for as long as your account is active and as required for legal, tax, audit, and dispute-resolution purposes. Anonymised or aggregated data may be kept longer for analytics.',
                ],
            ],
            [
                'title' => 'Account Deletion',
                'paragraphs' => [
                    'You may request account deletion by emailing us. We will delete or anonymise personal data where possible, except where retention is required by law or for completed booking records shared with venues.',
                ],
            ],
            [
                'title' => 'Children\'s Privacy',
                'paragraphs' => [
                    'Findownn is not intended for users under 15 years of age without parental consent. Players must be at least 15 to register. We do not knowingly collect personal information from children under 15.',
                ],
            ],
            [
                'title' => 'Changes to Privacy Policy',
                'paragraphs' => [
                    'We may update this Privacy Policy from time to time. The “Last updated” date at the top will change when we do. Continued use of the Platform after changes constitutes acceptance of the updated policy.',
                ],
            ],
            [
                'title' => 'Contact Us',
                'paragraphs' => [
                    'For privacy-related questions or requests, contact us at <a href="mailto:' . e($email) . '" class="text-success">' . e($email) . '</a> or through our <a href="' . e(legal_public_url('contact')) . '" class="text-success">Support page</a>.',
                ],
            ],
        ];
    }
}

if (!function_exists('legal_terms_sections')) {
    function legal_terms_sections(): array
    {
        $email = site_contact_email();

        return [
            [
                'title' => 'Introduction',
                'paragraphs' => [
                    'These Terms & Conditions (“Terms”) govern your access to and use of the Findownn website, applications, and services. By creating an account, booking a court, or listing a venue, you agree to these Terms.',
                ],
            ],
            [
                'title' => 'User Eligibility',
                'paragraphs' => [
                    'Players must be at least 15 years old. Venue owners must be legally able to enter contracts and authorised to operate or represent the listed venue. You must provide accurate registration information and keep your account secure.',
                ],
            ],
            [
                'title' => 'Account Registration',
                'paragraphs' => [
                    'You are responsible for all activity under your account. Do not share login credentials. Notify us immediately if you suspect unauthorised access. We may suspend accounts with false or misleading registration details.',
                ],
            ],
            [
                'title' => 'User Responsibilities',
                'paragraphs' => [
                    'Players must arrive on time, follow venue rules, treat staff and facilities respectfully, and pay applicable booking fees. Misuse of the Platform, fraudulent bookings, or abusive behaviour may result in account suspension.',
                ],
            ],
            [
                'title' => 'Venue Owner Responsibilities',
                'paragraphs' => [
                    'Venue owners must provide accurate listings, maintain courts and facilities, honour confirmed bookings, keep availability up to date, comply with local laws, and respond to player enquiries in a reasonable timeframe.',
                ],
            ],
            [
                'title' => 'Venue Listing Rules',
                'paragraphs' => [
                    'Listings must include truthful names, addresses, sports offered, pricing, photos you have rights to use, and operating hours. Misleading, duplicate, or illegal listings may be removed. Admin approval may be required before public visibility.',
                ],
            ],
            [
                'title' => 'Venue Booking Rules',
                'paragraphs' => [
                    'Bookings are subject to venue availability and confirmation. Slot selection must reflect actual playable hours. Offline or walk-in bookings recorded by owners must be accurate. Double-booking the same slot is prohibited.',
                ],
            ],
            [
                'title' => 'Cancellation & Refund Policy',
                'paragraphs' => [
                    'Cancellation and refund rules may vary by venue and are displayed at checkout where applicable. Findownn facilitates bookings but final refund decisions may depend on venue policy and payment status. Contact support for disputed cancellations.',
                ],
            ],
            [
                'title' => 'Payments',
                'paragraphs' => [
                    'Online payments are processed through Razorpay. You agree to pay all charges associated with your booking. Failed or reversed payments may result in booking cancellation. Platform subscription fees for venue owners are billed according to the selected plan.',
                ],
            ],
            [
                'title' => 'Booking Confirmation',
                'paragraphs' => [
                    'A booking is confirmed only after successful payment verification on our servers (or manual confirmation for eligible offline bookings). Booking references (e.g. OFL-XXXXXXX) are proof of record on the Platform.',
                ],
            ],
            [
                'title' => 'Venue Availability',
                'paragraphs' => [
                    'Availability shown on the Platform is based on venue-supplied schedules minus confirmed bookings. We strive for real-time accuracy but are not liable for rare sync delays. Confirmed paid slots should be treated as unavailable.',
                ],
            ],
            [
                'title' => 'Reviews & Ratings',
                'paragraphs' => [
                    'If review features are enabled, content must be honest, relevant, and free of hate speech, spam, or personal attacks. We may remove reviews that violate these standards.',
                ],
            ],
            [
                'title' => 'Prohibited Activities',
                'paragraphs' => [
                    'You may not scrape, hack, reverse-engineer, or overload the Platform; create fake bookings; impersonate others; upload malware; circumvent payments; or use Findownn for unlawful purposes.',
                ],
            ],
            [
                'title' => 'Intellectual Property',
                'paragraphs' => [
                    'Findownn branding, software, design, and content are owned by us or our licensors. You may not copy or redistribute Platform materials without permission. Venue owners grant us a licence to display their listing content for booking purposes.',
                ],
            ],
            [
                'title' => 'Third-Party Services',
                'paragraphs' => [
                    'Third-party tools (payments, maps, messaging) are subject to their own terms. We are not responsible for third-party outages beyond our reasonable control.',
                ],
            ],
            [
                'title' => 'Account Suspension / Termination',
                'paragraphs' => [
                    'We may suspend or terminate accounts that violate these Terms, pose security risks, or remain inactive for extended periods. You may stop using the Platform at any time; certain data may be retained as described in our Privacy Policy.',
                ],
            ],
            [
                'title' => 'Limitation of Liability',
                'paragraphs' => [
                    'Findownn is a booking facilitator between players and venues. We are not liable for injuries, property damage, disputes at physical venues, weather cancellations, or indirect losses, to the maximum extent permitted by law.',
                ],
            ],
            [
                'title' => 'Disclaimer',
                'paragraphs' => [
                    'The Platform is provided “as is” without warranties of uninterrupted or error-free operation. Venue quality, safety, and amenities are the responsibility of venue owners.',
                ],
            ],
            [
                'title' => 'Changes to Terms',
                'paragraphs' => [
                    'We may update these Terms periodically. Material changes will be reflected by the “Last updated” date. Continued use after changes constitutes acceptance.',
                ],
            ],
            [
                'title' => 'Governing Law',
                'paragraphs' => [
                    'These Terms are governed by the laws of India. Disputes shall be subject to the jurisdiction of courts in Gujarat, India, unless otherwise required by applicable law.',
                ],
            ],
            [
                'title' => 'Contact Us',
                'paragraphs' => [
                    'Questions about these Terms? Email <a href="mailto:' . e($email) . '" class="text-success">' . e($email) . '</a> or visit our <a href="' . e(legal_public_url('contact')) . '" class="text-success">Support page</a>.',
                ],
            ],
        ];
    }
}
