<?php
/** Shared error page — 404 / 500 */
$code = (int) ($error_code ?? 500);
$route_name = $route_name ?? ($code === 404 ? '404' : '500');

$titles = [
    404 => 'Page Not Found',
    500 => 'Something Went Wrong',
];
$messages = [
    404 => 'The page you are looking for does not exist or may have been moved.',
    500 => 'We\'re unavailable right now. Please try again in a few minutes.',
];

$title   = $titles[$code] ?? 'Error';
$message = $messages[$code] ?? $messages[500];

if (!headers_sent() && http_response_code() < 400) {
    http_response_code($code);
}

include __DIR__ . '/header.php';
?>
<section class="py-5 text-center min-vh-100 d-flex align-items-center justify-content-center position-relative overflow-hidden" style="margin-top:75px;">
    <div class="glow-orb glow-orb-bottom-left"></div>
    <div class="container position-relative z-1 animate-on-scroll">
        <span class="badge-premium mb-3">Error <?= $code ?></span>
        <h1 class="display-4 fw-bold text-white mb-2"><?= e($title) ?></h1>
        <p class="text-secondary lead mx-auto mb-4" style="max-width: 520px;"><?= e($message) ?></p>
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="<?= e($asset_base ?? './') ?>" class="btn btn-premium btn-shimmer">
                <i class="bi bi-house-door me-2"></i>Back to Home
            </a>
            <button type="button" class="btn btn-premium-outline" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise me-2"></i>Try Again
            </button>
            <a href="<?= e(rtrim($asset_base ?? '', '/') . '/contact') ?>" class="btn btn-outline-light">
                <i class="bi bi-headset me-2"></i>Contact Support
            </a>
        </div>
    </div>
</section>
<?php include __DIR__ . '/footer.php'; ?>
