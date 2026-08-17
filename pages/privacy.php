<?php
require_once __DIR__ . '/../includes/site-contact.php';
include __DIR__ . '/../includes/header.php';
?>

<header class="page-header page-header--compact">
    <div class="container text-center position-relative z-1 animate-on-scroll">
        <span class="badge-premium mb-3">Legal</span>
        <h1 class="display-5 fw-bold text-white mb-2">Privacy Policy</h1>
        <p class="text-secondary mx-auto mb-0" style="max-width: 560px;">How Findownn collects, uses, and protects your information.</p>
    </div>
</header>

<section class="py-5">
    <div class="container" style="max-width: 820px;">
        <div class="glass-card p-4 p-md-5 legal-content">
            <p class="text-secondary small mb-4">Last updated: <?= date('F j, Y') ?></p>

            <h2 class="text-white h5 mb-3">1. Information we collect</h2>
            <p class="text-secondary">When you register, book a playground, or contact us, we may collect your name, email, phone number, booking details, and payment references (processed securely via Razorpay).</p>

            <h2 class="text-white h5 mb-3 mt-4">2. How we use information</h2>
            <p class="text-secondary">We use your data to process bookings, send confirmations, improve our platform, and respond to support requests. Venue owners receive only the information needed to fulfil bookings.</p>

            <h2 class="text-white h5 mb-3 mt-4">3. Data sharing</h2>
            <p class="text-secondary">We do not sell personal data. We share information only with venue partners for confirmed bookings, payment processors, and when required by law.</p>

            <h2 class="text-white h5 mb-3 mt-4">4. Security</h2>
            <p class="text-secondary">We use industry-standard measures including HTTPS, secure sessions, and access controls. No method of transmission over the internet is 100% secure.</p>

            <h2 class="text-white h5 mb-3 mt-4">5. Your rights</h2>
            <p class="text-secondary">You may request access, correction, or deletion of your account data by emailing <a href="mailto:<?= e($site_contact_email) ?>" class="text-success"><?= e($site_contact_email) ?></a>.</p>

            <h2 class="text-white h5 mb-3 mt-4">6. Contact</h2>
            <p class="text-secondary mb-0">Questions about this policy? Reach us at <a href="contact" class="text-success">Support</a> or <?= e($site_contact_email) ?>.</p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
