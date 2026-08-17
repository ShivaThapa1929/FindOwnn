<?php
require_once __DIR__ . '/../includes/site-contact.php';
include __DIR__ . '/../includes/header.php';
?>

<header class="page-header page-header--compact">
    <div class="container text-center position-relative z-1 animate-on-scroll">
        <span class="badge-premium mb-3">Legal</span>
        <h1 class="display-5 fw-bold text-white mb-2">Terms of Service</h1>
        <p class="text-secondary mx-auto mb-0" style="max-width: 560px;">Rules for using Findownn as a player or venue partner.</p>
    </div>
</header>

<section class="py-5">
    <div class="container" style="max-width: 820px;">
        <div class="glass-card p-4 p-md-5 legal-content">
            <p class="text-secondary small mb-4">Last updated: <?= date('F j, Y') ?></p>

            <h2 class="text-white h5 mb-3">1. Using Findownn</h2>
            <p class="text-secondary">By using our website or app, you agree to these terms. You must be at least 15 years old to register as a player.</p>

            <h2 class="text-white h5 mb-3 mt-4">2. Bookings</h2>
            <p class="text-secondary">Confirmed bookings are subject to venue rules and availability. Cancellation and refund policies are set by individual venues and displayed at checkout where applicable.</p>

            <h2 class="text-white h5 mb-3 mt-4">3. Payments</h2>
            <p class="text-secondary">Online payments are processed through Razorpay. Findownn is not responsible for bank or gateway delays outside our control.</p>

            <h2 class="text-white h5 mb-3 mt-4">4. Venue partners</h2>
            <p class="text-secondary">Venue owners must provide accurate listing information, honour confirmed bookings, and comply with applicable local laws and subscription plan terms.</p>

            <h2 class="text-white h5 mb-3 mt-4">5. Limitation of liability</h2>
            <p class="text-secondary">Findownn facilitates bookings between players and venues. We are not liable for injuries, property damage, or disputes at physical venues beyond our platform obligations.</p>

            <h2 class="text-white h5 mb-3 mt-4">6. Changes</h2>
            <p class="text-secondary mb-0">We may update these terms. Continued use after changes constitutes acceptance. Contact <a href="mailto:<?= e($site_contact_email) ?>" class="text-success"><?= e($site_contact_email) ?></a> with questions.</p>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
