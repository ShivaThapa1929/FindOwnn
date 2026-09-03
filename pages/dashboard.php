<?php
require_once __DIR__ . '/../includes/user-auth.php';
site_send_no_cache_headers();
site_require_user();

$user = site_user();
$stats = site_user_stats($user['id']);
$venues = site_user_venues($user['id']);
$bookingGroups = site_user_bookings_split($user['id']);
$upcoming = $bookingGroups['upcoming'];
$past = $bookingGroups['past'];

if (!empty($_GET['paid'])) {
    site_flash('success', 'Payment successful! Your latest booking is shown below.');
}

include __DIR__ . '/../includes/header.php';
?>

<header class="page-header">
    <div class="glow-orb glow-orb-bottom-left"></div>
    <div class="container position-relative z-1 animate-on-scroll">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
            <div>
                <span class="badge-premium mb-3"><i class="bi bi-speedometer2 me-1"></i> My Dashboard</span>
                <h1 class="display-5 fw-bold text-white mb-1">Welcome, <?= e($user['name']) ?></h1>
                <p class="text-secondary mb-0"><?= e($user['email']) ?> · <?= e($user['phone']) ?></p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= e($asset_base) ?>venues" class="btn btn-premium btn-sm">
                    <i class="bi bi-calendar-plus me-1"></i>Book Court
                </a>
                <a href="<?= e($asset_base) ?>logout" class="btn btn-premium-outline btn-sm">
                    <i class="bi bi-box-arrow-right me-1"></i>Sign Out
                </a>
            </div>
        </div>
    </div>
</header>

<section class="py-5 position-relative">
    <div class="container">

        <?php if ($success = site_flash('success')): ?>
            <div class="alert alert-success py-2 small mb-4"><i class="bi bi-check-circle me-1"></i><?= e($success) ?></div>
        <?php endif; ?>
        <?php if ($error = site_flash('error')): ?>
            <div class="alert alert-danger py-2 small mb-4"><i class="bi bi-exclamation-circle me-1"></i><?= e($error) ?></div>
        <?php endif; ?>

        <div class="row g-4 mb-5 animate-on-scroll">
            <div class="col-md-4">
                <div class="glass-card p-4 text-center h-100 user-dash-stat">
                    <i class="bi bi-calendar-check text-success fs-4 mb-2 d-block"></i>
                    <div class="display-6 fw-bold text-white"><?= $stats['total_bookings'] ?></div>
                    <div class="text-secondary small">Total Bookings</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card p-4 text-center h-100 user-dash-stat">
                    <i class="bi bi-clock-history text-success fs-4 mb-2 d-block"></i>
                    <div class="display-6 fw-bold text-success"><?= $stats['upcoming'] ?></div>
                    <div class="text-secondary small">Upcoming</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card p-4 text-center h-100 user-dash-stat">
                    <i class="bi bi-currency-rupee text-success fs-4 mb-2 d-block"></i>
                    <div class="display-6 fw-bold text-white">₹<?= number_format($stats['total_spent']) ?></div>
                    <div class="text-secondary small">Total Spent</div>
                </div>
            </div>
        </div>

        <?php if (!empty($upcoming)): ?>
        <div class="glass-card p-4 mb-4 animate-on-scroll">
            <h2 class="text-white fw-bold h5 mb-4"><i class="bi bi-lightning-charge text-success me-2"></i>Upcoming Bookings</h2>
            <div class="row g-3">
                <?php foreach ($upcoming as $b): ?>
                    <?php $badge = site_booking_status_badge($b['status']); ?>
                    <div class="col-md-6">
                        <div class="user-booking-card">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <div>
                                    <div class="text-white fw-600"><?= e($b['venue_name']) ?></div>
                                    <div class="text-secondary small"><?= e($b['venue_city'] ?? '') ?></div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-<?= e($badge) ?>"><?= e(ucfirst($b['status'])) ?></span>
                                    <?php if (($b['payment_status'] ?? '') === 'paid'): ?>
                                    <span class="badge bg-success ms-1">Paid</span>
                                    <?php elseif (($b['payment_status'] ?? '') === 'pending'): ?>
                                    <span class="badge bg-warning text-dark ms-1">Payment Pending</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="text-secondary small mb-1">
                                <i class="bi bi-calendar3 me-1"></i><?= e(date('D, M j, Y', strtotime($b['booking_date']))) ?>
                                · <?= e(substr($b['start_time'] ?? '', 0, 5)) ?>–<?= e(substr($b['end_time'] ?? '', 0, 5)) ?>
                            </div>
                            <div class="text-secondary small">
                                <?= e($b['sport_name'] ?? 'Sport') ?> · <?= e($b['court_name'] ?? 'Court') ?>
                                · <span class="text-white">₹<?= number_format((int) ($b['amount'] ?? 0)) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="glass-card p-4 mb-4 animate-on-scroll">
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                <h2 class="text-white fw-bold h5 mb-0"><i class="bi bi-geo-alt text-success me-2"></i>My Playgrounds</h2>
                <span class="text-secondary small"><?= count($venues) ?> venue<?= count($venues) === 1 ? '' : 's' ?></span>
            </div>

            <?php if (empty($venues)): ?>
                <div class="text-center py-4">
                    <i class="bi bi-building display-4 text-secondary"></i>
                    <p class="text-secondary mt-3 mb-4">You haven't booked any playground yet.</p>
                    <a href="<?= e($asset_base) ?>venues" class="btn btn-premium">Browse Playgrounds</a>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($venues as $v): ?>
                        <div class="col-md-6 col-lg-4">
                            <a href="<?= e($asset_base) ?>venue-details?id=<?= (int) $v['id'] ?>" class="user-venue-card text-decoration-none">
                                <div class="user-venue-img">
                                    <img src="<?= e(site_resolve_image_url($v['featured_image'] ?? null)) ?>" alt="<?= e($v['name']) ?>">
                                </div>
                                <div class="user-venue-body">
                                    <div class="text-white fw-600 mb-1"><?= e($v['name']) ?></div>
                                    <div class="text-secondary small mb-2">
                                        <i class="bi bi-geo-alt me-1"></i><?= e($v['city'] ?? 'Bhuj') ?>
                                        · <?= e(site_venue_type_label($v['type'] ?? '')) ?>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-success small fw-600"><?= (int) $v['booking_count'] ?> booking<?= (int) $v['booking_count'] === 1 ? '' : 's' ?></span>
                                        <span class="text-secondary small">Last: <?= e(date('M j, Y', strtotime($v['last_booked']))) ?></span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="glass-card p-4 animate-on-scroll">
            <h2 class="text-white fw-bold h5 mb-4"><i class="bi bi-list-check text-success me-2"></i>Booking History</h2>

            <?php if (empty($past) && empty($upcoming)): ?>
                <p class="text-secondary mb-0">No bookings to show.</p>
            <?php elseif (empty($past)): ?>
                <p class="text-secondary mb-0">No past bookings yet.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover align-middle mb-0 user-dash-table">
                        <thead>
                            <tr class="text-secondary small">
                                <th>Date</th>
                                <th>Playground</th>
                                <th>Sport / Court</th>
                                <th>Time</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($past as $b): ?>
                                <?php $badge = site_booking_status_badge($b['status']); ?>
                                <tr>
                                    <td class="text-white-50"><?= e(date('M j, Y', strtotime($b['booking_date']))) ?></td>
                                    <td>
                                        <a href="<?= e($asset_base) ?>venue-details?id=<?= (int) $b['venue_id'] ?>" class="text-white fw-600 text-decoration-none"><?= e($b['venue_name']) ?></a>
                                        <div class="text-secondary small"><?= e($b['venue_city'] ?? '') ?></div>
                                    </td>
                                    <td class="text-secondary small">
                                        <?= e($b['sport_name'] ?? '—') ?><br>
                                        <?= e($b['court_name'] ?? '') ?>
                                    </td>
                                    <td class="text-secondary small">
                                        <?= e(substr($b['start_time'] ?? '', 0, 5)) ?> – <?= e(substr($b['end_time'] ?? '', 0, 5)) ?>
                                    </td>
                                    <td class="text-white">₹<?= number_format((int) ($b['amount'] ?? 0)) ?></td>
                                    <td>
                                        <span class="badge bg-<?= e($badge) ?>"><?= e(ucfirst($b['status'])) ?></span>
                                        <?php if (($b['payment_status'] ?? '') === 'paid'): ?>
                                        <span class="badge bg-success ms-1">Paid</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Legal -->
        <div class="glass-card p-4 animate-on-scroll mt-4">
            <h2 class="text-white fw-bold h6 mb-3"><i class="bi bi-shield-check text-success me-2"></i>Legal &amp; Privacy</h2>
            <div class="d-flex flex-wrap gap-3">
                <a href="<?= e($asset_base) ?>privacy" class="btn btn-sm btn-premium-outline">
                    <i class="bi bi-file-earmark-lock me-1"></i> Privacy Policy
                </a>
                <a href="<?= e($asset_base) ?>terms" class="btn btn-sm btn-premium-outline">
                    <i class="bi bi-file-earmark-text me-1"></i> Terms &amp; Conditions
                </a>
            </div>
            <p class="text-secondary small mb-0 mt-3">Last updated: <?= e(legal_last_updated()) ?></p>
        </div>
    </div>
</section>

<script>
(function () {
    var user = <?= json_encode($user, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    if (user && user.token) {
        try {
            localStorage.setItem('findownn_token', user.token);
            localStorage.setItem('findownn_user', JSON.stringify({
                id: user.id, name: user.name, email: user.email, phone: user.phone
            }));
        } catch (e) {}
    }
})();
</script>

<?php
$invalidate_booking_cache = true;
include __DIR__ . '/../includes/footer.php';
?>
