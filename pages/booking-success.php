<?php
require_once __DIR__ . '/../includes/user-auth.php';
site_send_no_cache_headers();

$ref       = trim($_GET['ref'] ?? '');
$bookingId = (int) ($_GET['booking_id'] ?? 0);
$user      = site_user();

include __DIR__ . '/../includes/header.php';
?>

<header class="page-header page-header--compact">
    <div class="glow-orb glow-orb-bottom-left"></div>
    <div class="container text-center position-relative z-1 animate-on-scroll">
        <span class="badge-premium mb-3"><i class="bi bi-check-circle-fill me-1"></i> Payment Successful</span>
        <h1 class="display-5 fw-bold text-white mb-2">Booking Confirmed!</h1>
        <p class="text-secondary mx-auto mb-0" style="max-width: 520px;">
            Your payment was verified on our server. Your booking is now confirmed.
        </p>
    </div>
</header>

<section class="py-5 position-relative">
    <div class="container" style="max-width: 640px;">
        <div class="glass-card p-4 p-md-5 text-center animate-on-scroll">
            <div class="mb-4">
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center"
                     style="width:72px;height:72px;background:rgba(34,197,94,0.15);border:2px solid rgba(34,197,94,0.4);">
                    <i class="bi bi-check-lg text-success" style="font-size:2rem;"></i>
                </div>
            </div>

            <?php if ($ref !== ''): ?>
            <p class="text-secondary small mb-1">Booking Reference</p>
            <p class="text-white fw-bold fs-5 font-monospace mb-4"><?= e($ref) ?></p>
            <?php endif; ?>

            <p class="text-secondary mb-4">
                A confirmation has been sent. You can view your booking details on your dashboard.
            </p>

            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <?php if ($user): ?>
                <a href="<?= e($asset_base) ?>dashboard?paid=1<?= $ref ? '&ref=' . urlencode($ref) : '' ?>"
                   class="btn btn-premium" id="go-dashboard-btn">
                    <i class="bi bi-speedometer2 me-1"></i> View My Dashboard
                </a>
                <?php else: ?>
                <a href="<?= e($asset_base) ?>?auth=login" class="btn btn-premium">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to View Bookings
                </a>
                <?php endif; ?>
                <a href="<?= e($asset_base) ?>venues" class="btn btn-outline-light">
                    <i class="bi bi-calendar-plus me-1"></i> Book Another Court
                </a>
            </div>
        </div>
    </div>
</section>

<script src="<?= e($asset_base) ?>js/api.js?v=1.4"></script>
<script>
(function () {
    if (typeof FindownnAPI !== 'undefined') {
        FindownnAPI.invalidateBookingCache(<?= $bookingId ?: 'null' ?>);
    }
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
