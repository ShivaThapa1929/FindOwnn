<?php
/** @var string $asset_base Base path for assets — set by index.php */
if (!isset($asset_base)) {
    $script_dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $asset_base = ($script_dir === '') ? '/' : $script_dir . '/';
}
require_once __DIR__ . '/site-contact.php';
if (!isset($site_user) && function_exists('site_user')) {
    $site_user = site_user();
}
?>
<!-- Footer -->
<footer class="footer">
    <div class="container">
        <div class="row g-4 g-lg-5 mb-5">

            <!-- Brand Column -->
            <div class="col-12 col-lg-4">
                <a class="navbar-brand footer-brand mb-4" href="./">
                    <div class="navbar-brand-logo">
                        <img src="<?= $asset_base ?>assets/images/logo.png" alt="Findownn">
                    </div>
                    <span class="navbar-brand-text">FIND<span class="brand-accent">OWNN</span></span>
                </a>
                <p
                    style="font-size: 0.92rem; line-height: 1.75; color: var(--text-secondary); max-width: 320px; margin-bottom: 24px;">
                    Bhuj's first sports playground booking platform. Find a slot, book your time, and just show up. It
                    really is that simple.
                </p>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="#" class="social-icon" aria-label="Follow us on Instagram"><i
                            class="bi bi-instagram"></i></a>
                    <a href="#" class="social-icon" aria-label="Follow us on Facebook"><i
                            class="bi bi-facebook"></i></a>
                    <a href="#" class="social-icon" aria-label="Follow us on Twitter / X"><i
                            class="bi bi-twitter-x"></i></a>
                    <a href="#" class="social-icon" aria-label="Connect on LinkedIn"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="col-6 col-md-4 col-lg-2 ms-lg-auto">
                <h6 class="footer-heading">Explore</h6>
                <nav class="d-flex flex-column gap-1">
                    <a href="./" class="footer-link">Home</a>
                    <a href="venues" class="footer-link">Playgrounds</a>
                    <a href="sports" class="footer-link">Sports</a>
                    <a href="partner" class="footer-link">List Your Playground</a>
                    <a href="about" class="footer-link">About</a>
                    <a href="contact" class="footer-link">Support</a>
                </nav>
            </div>

            <!-- Contact -->
            <div class="col-6 col-md-4 col-lg-3">
                <h6 class="footer-heading">Contact</h6>
                <div class="d-flex flex-column gap-3" style="font-size: 0.88rem; color: var(--text-secondary);">
                    <div class="d-flex align-items-start gap-2">
                        <i class="bi bi-geo-alt-fill text-success mt-1 flex-shrink-0" style="font-size: 0.85rem;"></i>
                        <span>New Station Road, Bhuj<br>Gujarat — 370001</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-envelope-fill text-success flex-shrink-0" style="font-size: 0.82rem;"></i>
                        <a href="mailto:<?= e($site_contact_email) ?>" class="footer-link"
                            style="padding: 0;"><?= e($site_contact_email) ?></a>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-telephone-fill text-success flex-shrink-0" style="font-size: 0.82rem;"></i>
                        <a href="tel:<?= e($site_phone_tel) ?>" class="footer-link"
                            style="padding: 0;"><?= e($site_whatsapp_display) ?></a>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-whatsapp text-success flex-shrink-0" style="font-size: 0.9rem;"></i>
                        <a href="<?= e($site_whatsapp_url) ?>" class="footer-link" style="padding: 0;" target="_blank"
                            rel="noopener">Chat on WhatsApp</a>
                    </div>
                </div>
            </div>

            <!-- Newsletter -->
            <div class="col-12 col-md-4 col-lg-3">
                <h6 class="footer-heading">Stay in the loop</h6>
                <p style="font-size: 0.88rem; color: var(--text-secondary); margin-bottom: 14px; line-height: 1.6;">
                    New playgrounds, new sports, and early access — straight to your inbox.
                </p>
                <form
                    onsubmit="event.preventDefault(); this.querySelector('button').textContent = '✓ Subscribed!'; this.querySelector('input').disabled = true;"
                    class="d-flex flex-column gap-2">
                    <input type="email" class="glass-input" placeholder="your@email.com" required
                        style="font-size: 0.88rem;">
                    <button type="submit" class="btn btn-premium w-100"
                        style="font-size: 0.88rem; padding: 12px;">Subscribe</button>
                </form>
            </div>

        </div>

        <!-- Bottom Bar -->
        <div class="pt-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3"
            style="border-top: 1px solid var(--border-glass);">
            <p style="font-size: 0.82rem; color: var(--text-muted); margin: 0;">
                &copy; <?php echo date('Y'); ?> Findownn. Made with <span style="color: var(--primary);">♥</span> in
                Bhuj, Gujarat.
            </p>
            <div class="d-flex gap-4">
                <a href="privacy" class="footer-link" style="font-size: 0.82rem; padding: 0;">Privacy Policy</a>
                <a href="terms" class="footer-link" style="font-size: 0.82rem; padding: 0;">Terms &amp; Conditions</a>
                <a href="contact" class="footer-link" style="font-size: 0.82rem; padding: 0;">Support</a>
            </div>
        </div>
    </div>
</footer>

<!-- Floating WhatsApp -->
<a href="<?= e($site_whatsapp_url) ?>" class="whatsapp-float" target="_blank" rel="noopener noreferrer"
    aria-label="Chat on WhatsApp — <?= e($site_whatsapp_display) ?>" title="Chat on WhatsApp">
    <i class="bi bi-whatsapp"></i>
    <span class="whatsapp-float__label">WhatsApp</span>
</a>

<!-- Ensure page is visible if deferred scripts fail (auth pages: immediate; others: fallback) -->
<script>
(function () {
  function showPage() {
    document.documentElement.classList.remove('splash-active');
    document.body.classList.remove('splash-active');
    document.body.classList.add('page-ready');
    var splash = document.getElementById('splash-screen');
    if (splash) splash.remove();
  }
  var skipSplash = document.body.dataset.skipSplash === '1';
  if (skipSplash) {
    showPage();
  } else {
    setTimeout(function () {
      if (!document.body.classList.contains('page-ready')) showPage();
    }, 4000);
  }
})();
</script>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>

<!-- Error toasts & offline support -->
<script src="<?= $asset_base ?>js/errors.js?v=1.0" defer></script>
<script src="<?= $asset_base ?>js/offline.js" defer></script>

<!-- API Service -->
<script src="<?= $asset_base ?>js/api.js?v=1.5" defer></script>

<!-- Custom Scripts -->
<script src="<?= $asset_base ?>js/script.js?v=5.2" defer></script>

<?php if (empty($site_user)): ?>
    <script src="<?= $asset_base ?>js/auth-modal.js?v=1.4" defer></script>
<?php endif; ?>

<!-- Page-specific Scripts -->
<?php
$page_name = $route_name ?? basename($_SERVER['PHP_SELF'], '.php');
$page_scripts = [
    'index' => 'js/home-api.js',
    'venues' => 'js/venues.js',
    'venue-details' => 'js/venue-details.js',
    'contact' => 'js/contact.js',
];

if (isset($page_scripts[$page_name])) {
    $pageScriptVersion = match ($page_name) {
        'index' => '?v=4.0',
        'venues' => '?v=1.5',
        'venue-details' => '?v=1.5',
        default => '',
    };
    echo '<script src="' . $asset_base . $page_scripts[$page_name] . $pageScriptVersion . '" defer></script>';
}
?>

<?php if (!empty($invalidate_booking_cache)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof FindownnAPI !== 'undefined') {
                FindownnAPI.invalidateBookingCache();
            }
        });
    </script>
<?php endif; ?>

</body>

</html>