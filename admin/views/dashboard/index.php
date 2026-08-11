<?php
/** Admin / Super Admin Dashboard */
/** @var array $stats @var array $bookStats @var array $subStats @var array $monthlyRev */
/** @var array $recentUsers @var array $recentActivity @var array $venueStats */
/** @var array $pendingVenues @var array $recentBookings @var array $planDist */
/** @var array $sportStats @var array $topVenues @var array $whatsappStats */
/** @var array $recentWhatsApp @var array $upcomingBookings @var array $notifications */

$stats            = $stats ?? [];
$bookStats        = $bookStats ?? [];
$subStats         = $subStats ?? [];
$monthlyRev       = $monthlyRev ?? [];
$recentUsers      = $recentUsers ?? [];
$recentActivity   = $recentActivity ?? [];
$venueStats       = $venueStats ?? [];
$pendingVenues    = $pendingVenues ?? [];
$recentBookings   = $recentBookings ?? [];
$planDist         = $planDist ?? [];
$sportStats       = $sportStats ?? [];
$topVenues        = $topVenues ?? [];
$whatsappStats    = $whatsappStats ?? ['total_messages' => 0, 'messages_today' => 0, 'sent_messages' => 0];
$recentWhatsApp   = $recentWhatsApp ?? [];
$upcomingBookings = $upcomingBookings ?? [];
$notifications    = $notifications ?? [];

$isSuper = (auth()['role'] ?? '') === 'super_admin';

if (!function_exists('roleBadge')) {
    function roleBadge(string $role): string {
        $map = ['super_admin'=>'danger','admin'=>'primary','venue_owner'=>'success'];
        return '<span class="badge bg-'.($map[$role]??'secondary').'">'.ucwords(str_replace('_',' ',$role)).'</span>';
    }
}
if (!function_exists('actIcon')) {
    function actIcon(string $type): string {
        return ['auth'=>'person-check-fill','venue'=>'building-fill','user'=>'person-fill',
                'subscription'=>'credit-card-fill','booking'=>'calendar-check-fill','info'=>'info-circle-fill'][$type] ?? 'dot';
    }
}
if (!function_exists('actColor')) {
    function actColor(string $type): string {
        return ['auth'=>'green','venue'=>'blue','user'=>'purple',
                'subscription'=>'orange','booking'=>'teal','info'=>'yellow'][$type] ?? 'yellow';
    }
}
?>

<!-- ── Page Header ──────────────────────────────────────────────── -->
<div class="dashboard-page">
<div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
  <div>
    <h5 class="fw-800 mb-1 dashboard-greeting" style="font-family:var(--font-h);">
      Good <?= date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening') ?>,
      <?= e(explode(' ', auth()['name'] ?? 'User')[0]) ?> 👋
    </h5>
    <p class="text-muted mb-0" style="font-size:.84rem;">
      <?= date('l, F j, Y') ?> &mdash; Here&rsquo;s what&rsquo;s happening today.
    </p>
  </div>
  <div class="d-flex gap-2 dashboard-header-actions">
    <button class="btn btn-sm btn-outline-secondary" id="notificationBtn">
      <i class="bi bi-bell-fill"></i>
      <?php if (!empty($notifications)): ?>
      <span class="badge bg-danger rounded-pill" style="font-size:.65rem;padding:.2rem .4rem;"><?= count($notifications) ?></span>
      <?php endif; ?>
    </button>
    <?php if ($stats['pending_venues'] > 0): ?>
    <a href="<?= url('/venues?status=pending') ?>" class="btn btn-sm btn-warning">
      <i class="bi bi-hourglass-split me-1"></i>
      <?= $stats['pending_venues'] ?> Pending
    </a>
    <?php endif; ?>
    <?php if ($isSuper): ?>
    <a href="<?= url('/users/create') ?>" class="btn btn-sm btn-primary">
      <i class="bi bi-person-plus me-1"></i>Add User
    </a>
    <?php endif; ?>
  </div>
</div>

<!-- ── Notification Panel (Hidden by default) ───────────────────── -->
<div id="notificationPanel" class="notification-panel" style="display:none;">
  <div class="notification-panel-header">
    <h6 class="mb-0"><i class="bi bi-bell-fill me-2"></i>Notifications</h6>
    <button class="btn btn-sm btn-link text-decoration-none" id="closeNotifications">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>
  <div class="notification-panel-body">
    <?php if (!empty($notifications)): ?>
      <?php foreach ($notifications as $notif): ?>
      <a href="<?= url($notif['link']) ?>" class="notification-item notification-<?= $notif['type'] ?>">
        <div class="notification-icon">
          <i class="bi bi-<?= $notif['icon'] ?>"></i>
        </div>
        <div class="notification-content">
          <div class="notification-title"><?= e($notif['title']) ?></div>
          <div class="notification-message"><?= e($notif['message']) ?></div>
          <div class="notification-time"><?= $notif['time'] ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="text-center py-4 text-muted">
        <i class="bi bi-check-circle d-block mb-2" style="font-size:2rem;opacity:0.3;"></i>
        <p class="mb-0">All caught up! 🎉</p>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- ── KPI Cards ──────────────────────────────────────────── -->
<div class="dashboard-kpi-grid mb-3">
<div class="row g-4 mb-0">
  <div class="col-6 col-xl-3 d-flex">
    <a href="<?= url('/users') ?>" class="stat-card stat-card--blue text-decoration-none">
      <div class="stat-card__icon"><i class="bi bi-people-fill"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= number_format($stats['total_users']) ?></div>
        <div class="stat-card__label">Total Users</div>
        <?php if (isset($stats['user_growth'])): ?>
        <div class="stat-card__trend <?= $stats['user_growth'] >= 0 ? 'trend-up' : 'trend-down' ?>">
          <i class="bi bi-arrow-<?= $stats['user_growth'] >= 0 ? 'up' : 'down' ?>-short"></i>
          <?= abs($stats['user_growth']) ?>% this month
        </div>
        <?php else: ?>
        <div class="stat-card__trend trend-neutral">
          <i class="bi bi-person-badge"></i>
          <a href="<?= url('/players') ?>" class="text-decoration-none"><?= number_format($stats['total_players'] ?? 0) ?> players</a>
        </div>
        <?php endif; ?>
      </div>
    </a>
  </div>
  <div class="col-6 col-xl-3 d-flex">
    <a href="<?= url('/venues') ?>" class="stat-card stat-card--green text-decoration-none">
      <div class="stat-card__icon"><i class="bi bi-building-fill"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= number_format($stats['total_venues']) ?></div>
        <div class="stat-card__label">Total Venues</div>
        <div class="stat-card__trend trend-neutral">
          <i class="bi bi-patch-check-fill text-success"></i>
          <?= $stats['verified_venues'] ?> verified
        </div>
      </div>
    </a>
  </div>
  <div class="col-6 col-xl-3 d-flex">
    <a href="<?= url('/bookings') ?>" class="stat-card stat-card--orange text-decoration-none">
      <div class="stat-card__icon"><i class="bi bi-calendar-check-fill"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= number_format($stats['total_bookings']) ?></div>
        <div class="stat-card__label">Total Bookings</div>
        <div class="stat-card__trend <?= $stats['bookings_today'] > 0 ? 'trend-up' : 'trend-neutral' ?>">
          <i class="bi bi-<?= $stats['bookings_today'] > 0 ? 'graph-up-arrow' : 'check-circle-fill' ?>"></i>
          <?= number_format($stats['bookings_today']) ?> today
        </div>
      </div>
    </a>
  </div>
  <div class="col-6 col-xl-3 d-flex">
    <div class="stat-card stat-card--purple">
      <div class="stat-card__icon"><i class="bi bi-currency-rupee"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value">₹<?= number_format($stats['total_revenue'] ?? 0) ?></div>
        <div class="stat-card__label">Total Revenue</div>
        <div class="stat-card__trend trend-up">
          <i class="bi bi-graph-up-arrow"></i>
          ₹<?= number_format($stats['revenue_today'] ?? 0) ?> today
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row g-4 mb-0">
  <div class="col-6 col-xl-3 d-flex">
    <div class="stat-card stat-card--teal">
      <div class="stat-card__icon"><i class="bi bi-grid-3x3-gap-fill"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= number_format($stats['total_courts']) ?></div>
        <div class="stat-card__label">Total Courts</div>
        <div class="stat-card__trend trend-neutral">
          <i class="bi bi-check-circle-fill text-success"></i>
          <?= $stats['active_courts'] ?> active
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-xl-3 d-flex">
    <a href="<?= url('/venues?status=pending') ?>" class="stat-card <?= $stats['pending_venues'] > 0 ? 'stat-card--yellow' : 'stat-card--green' ?> text-decoration-none">
      <div class="stat-card__icon"><i class="bi bi-hourglass-split"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= number_format($stats['pending_venues']) ?></div>
        <div class="stat-card__label">Pending Review</div>
        <?php if ($stats['pending_venues'] > 0): ?>
        <div class="stat-card__trend trend-down">
          <i class="bi bi-exclamation-circle"></i> Needs action
        </div>
        <?php else: ?>
        <div class="stat-card__trend trend-neutral">
          <i class="bi bi-check-circle-fill"></i> All clear
        </div>
        <?php endif; ?>
      </div>
    </a>
  </div>
  <div class="col-6 col-xl-3 d-flex">
    <a href="<?= url('/subscriptions?status=active') ?>" class="stat-card stat-card--green text-decoration-none">
      <div class="stat-card__icon"><i class="bi bi-credit-card-fill"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= number_format($stats['active_subs']) ?></div>
        <div class="stat-card__label">Active Subs</div>
        <div class="stat-card__trend trend-neutral">
          <i class="bi bi-check-circle-fill"></i>
          Live subscriptions
        </div>
      </div>
    </a>
  </div>
  <div class="col-6 col-xl-3 d-flex">
    <div class="stat-card stat-card--indigo">
      <div class="stat-card__icon"><i class="bi bi-whatsapp"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= number_format($whatsappStats['total_messages']) ?></div>
        <div class="stat-card__label">WhatsApp Messages</div>
        <div class="stat-card__trend trend-neutral">
          <i class="bi bi-send-fill"></i>
          <?= $whatsappStats['messages_today'] ?> today
        </div>
      </div>
    </div>
  </div>
</div>
</div>

<!-- ── Charts Row ───────────────────────────────────────────────── -->
<div class="row g-3 mb-3">
  <!-- Revenue Bar Chart -->
  <div class="col-lg-8">
    <div class="panel h-100">
      <div class="panel-head">
        <h6 class="panel-title"><i class="bi bi-bar-chart-fill me-2 text-success"></i>Revenue & Bookings (Last 6 months)</h6>
        <a href="<?= url('/reports') ?>" class="btn btn-xs btn-outline-secondary">Full Report</a>
      </div>
      <div class="panel-body">
        <canvas id="revChart" height="95"></canvas>
      </div>
    </div>
  </div>
  <!-- Venue Doughnut -->
  <div class="col-lg-4">
    <div class="panel h-100">
      <div class="panel-head">
        <h6 class="panel-title"><i class="bi bi-building me-2 text-primary"></i>Venue Status</h6>
      </div>
      <div class="panel-body d-flex flex-column align-items-center">
        <canvas id="venueChart" style="max-width:180px;" height="180"></canvas>
        <div class="d-flex gap-3 mt-3">
          <div class="text-center">
            <div class="fw-800 text-success" style="font-size:1.2rem;"><?= $venueStats['approved'] ?></div>
            <div class="text-muted" style="font-size:.7rem;letter-spacing:.04em;text-transform:uppercase;">Approved</div>
          </div>
          <div class="text-center">
            <div class="fw-800 text-warning" style="font-size:1.2rem;"><?= $venueStats['pending'] ?></div>
            <div class="text-muted" style="font-size:.7rem;letter-spacing:.04em;text-transform:uppercase;">Pending</div>
          </div>
          <div class="text-center">
            <div class="fw-800 text-danger" style="font-size:1.2rem;"><?= $venueStats['rejected'] ?></div>
            <div class="text-muted" style="font-size:.7rem;letter-spacing:.04em;text-transform:uppercase;">Rejected</div>
          </div>
          <div class="text-center">
            <div class="fw-800 text-info" style="font-size:1.2rem;"><?= $venueStats['verified'] ?></div>
            <div class="text-muted" style="font-size:.7rem;letter-spacing:.04em;text-transform:uppercase;">Verified</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── NEW: Sport Performance & Top Venues Row ──────────────────── -->
<div class="row g-3 mb-3">
  <!-- Sport Performance -->
  <div class="col-lg-6">
    <div class="panel h-100">
      <div class="panel-head">
        <h6 class="panel-title"><i class="bi bi-trophy-fill me-2 text-warning"></i>Sport Performance</h6>
      </div>
      <div class="panel-body">
        <?php if (!empty($sportStats)): ?>
          <?php foreach ($sportStats as $sport): ?>
          <div class="sport-stat-item">
            <div class="d-flex align-items-center gap-2 mb-2">
              <div class="sport-icon"><i class="bi <?= $sport['icon'] ?>"></i></div>
              <div class="flex-grow-1">
                <div class="fw-600 small"><?= e($sport['name']) ?></div>
                <div class="text-muted" style="font-size:.72rem;">
                  <?= number_format($sport['total_bookings']) ?> bookings
                  <span class="text-success ms-2">₹<?= number_format($sport['revenue']) ?></span>
                </div>
              </div>
              <div class="text-end">
                <span class="badge bg-success" style="font-size:.7rem;">
                  <?= $sport['bookings_this_month'] ?> this month
                </span>
              </div>
            </div>
            <div class="progress" style="height:6px;">
              <div class="progress-bar bg-success" style="width:<?= $sport['total_bookings'] > 0 ? min(100, ($sport['total_bookings'] / max(1, $sportStats[0]['total_bookings'] ?? 1)) * 100) : 0 ?>%"></div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="text-center py-4 text-muted">
            <i class="bi bi-trophy d-block mb-2" style="font-size:2rem;opacity:0.3;"></i>
            <p class="mb-0">No sport data yet</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Top Performing Venues -->
  <div class="col-lg-6">
    <div class="panel h-100">
      <div class="panel-head">
        <h6 class="panel-title"><i class="bi bi-star-fill me-2 text-warning"></i>Top Performing Venues</h6>
        <a href="<?= url('/reports') ?>" class="btn btn-xs btn-outline-secondary">View All</a>
      </div>
      <div class="panel-body">
        <?php if (!empty($topVenues)): ?>
          <?php $maxRevenue = max(array_column($topVenues, 'revenue')); ?>
          <?php foreach ($topVenues as $idx => $venue): ?>
          <div class="top-venue-item">
            <div class="d-flex align-items-center gap-3">
              <div class="rank-badge rank-<?= $idx + 1 ?>"><?= $idx + 1 ?></div>
              <div class="flex-grow-1">
                <div class="fw-600 small"><?= e($venue['name']) ?></div>
                <div class="text-muted" style="font-size:.72rem;">
                  <i class="bi bi-geo-alt-fill"></i> <?= e($venue['city']) ?>
                  <span class="ms-2">
                    <i class="bi bi-calendar-check"></i> <?= number_format($venue['total_bookings']) ?> bookings
                  </span>
                </div>
              </div>
              <div class="text-end">
                <div class="fw-700 text-success">₹<?= number_format($venue['revenue']) ?></div>
              </div>
            </div>
            <div class="progress mt-2" style="height:4px;">
              <div class="progress-bar bg-warning" style="width:<?= $maxRevenue > 0 ? ($venue['revenue'] / $maxRevenue * 100) : 0 ?>%"></div>
            </div>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="text-center py-4 text-muted">
            <i class="bi bi-star d-block mb-2" style="font-size:2rem;opacity:0.3;"></i>
            <p class="mb-0">No venue data yet</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ── Tables Row ───────────────────────────────────────────────── -->
<div class="row g-3 mb-3">
  <!-- Pending venues -->
  <?php if (!empty($pendingVenues)): ?>
  <div class="col-lg-6">
    <div class="panel">
      <div class="panel-head">
        <h6 class="panel-title">
          <i class="bi bi-hourglass-split me-2 text-warning"></i>
          Awaiting Approval
          <span class="badge bg-warning text-dark ms-1"><?= count($pendingVenues) ?></span>
        </h6>
        <a href="<?= url('/venues?status=pending') ?>" class="btn btn-xs btn-outline-warning">View All</a>
      </div>
      <div class="panel-body p-0">
        <table class="table table-hover mb-0">
          <thead><tr><th>Venue</th><th>Owner</th><th>City</th><th>Submitted</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($pendingVenues as $v): ?>
            <tr>
              <td>
                <div class="fw-500 small"><?= e($v['name']) ?></div>
                <span class="badge bg-dark" style="font-size:.62rem;"><?= ucwords(str_replace('_',' ',$v['type'])) ?></span>
              </td>
              <td class="text-muted small"><?= e($v['owner_name']) ?></td>
              <td class="text-muted small"><?= e($v['city']) ?></td>
              <td class="text-muted small"><?= timeAgo($v['created_at']) ?></td>
              <td>
                <div class="d-flex gap-1">
                  <form action="<?= url('/venues/'.$v['id'].'/approve') ?>" method="POST" class="d-inline">
                    <?= csrf_field() ?>
                    <button class="btn btn-xs btn-success" title="Approve"><i class="bi bi-check-lg"></i></button>
                  </form>
                  <a href="<?= url('/venues/'.$v['id']) ?>" class="btn btn-xs btn-outline-secondary"><i class="bi bi-eye"></i></a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Recent Bookings -->
  <div class="col-lg-<?= !empty($pendingVenues) ? '6' : '8' ?>">
    <div class="panel">
      <div class="panel-head">
        <h6 class="panel-title"><i class="bi bi-calendar-check-fill me-2 text-orange"></i>Recent Bookings</h6>
        <a href="<?= url('/bookings') ?>" class="btn btn-xs btn-outline-secondary">View All</a>
      </div>
      <div class="panel-body p-0">
        <table class="table table-hover mb-0">
          <thead><tr><th>Ref</th><th>Venue</th><th>User</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach ($recentBookings as $b): ?>
            <tr>
              <td class="font-monospace" style="font-size:.74rem;"><?= e($b['booking_reference']) ?></td>
              <td class="small"><?= e($b['venue_name']) ?></td>
              <td class="text-muted small"><?= e($b['user_name']) ?></td>
              <td class="text-muted small"><?= date('M j', strtotime($b['booking_date'])) ?></td>
              <td class="fw-500 small">₹<?= number_format($b['amount']) ?></td>
              <td><?= statusBadge($b['status']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($recentBookings)): ?>
              <tr><td colspan="6" class="text-center py-4 text-muted">No bookings yet</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- ── Bottom Row ───────────────────────────────────────────────── -->
<div class="row g-3 mb-3">
  <!-- Upcoming Bookings -->
  <div class="col-lg-6">
    <div class="panel">
      <div class="panel-head">
        <h6 class="panel-title"><i class="bi bi-calendar-event me-2 text-info"></i>Upcoming Bookings (Next 7 Days)</h6>
        <a href="<?= url('/bookings') ?>" class="btn btn-xs btn-outline-secondary">View All</a>
      </div>
      <div class="panel-body p-0" style="max-height:320px;overflow-y:auto;">
        <?php if (!empty($upcomingBookings)): ?>
          <table class="table table-hover mb-0">
            <thead><tr><th>Date</th><th>Venue / Court</th><th>Sport</th><th>Player</th><th></th></tr></thead>
            <tbody>
              <?php foreach ($upcomingBookings as $ub): ?>
              <tr>
                <td class="small">
                  <div class="fw-600"><?= date('M j', strtotime($ub['booking_date'])) ?></div>
                  <div class="text-muted" style="font-size:.7rem;"><?= date('g:i A', strtotime($ub['start_time'])) ?></div>
                </td>
                <td class="small">
                  <div class="fw-500"><?= e($ub['venue_name']) ?></div>
                  <div class="text-muted" style="font-size:.7rem;"><?= e($ub['court_name']) ?></div>
                </td>
                <td><span class="badge bg-primary" style="font-size:.7rem;"><?= e($ub['sport_name']) ?></span></td>
                <td class="small text-muted"><?= e($ub['user_name']) ?></td>
                <td>
                  <?php if (!empty($ub['whatsapp_number'])): ?>
                  <a href="https://wa.me/<?= preg_replace('/\D/', '', $ub['whatsapp_number']) ?>" 
                     target="_blank" 
                     class="btn btn-xs btn-success" 
                     title="Message on WhatsApp">
                    <i class="bi bi-whatsapp"></i>
                  </a>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php else: ?>
          <div class="text-center py-4 text-muted">
            <i class="bi bi-calendar-x d-block mb-2" style="font-size:2rem;opacity:0.3;"></i>
            <p class="mb-0">No upcoming bookings</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- WhatsApp Messages -->
  <div class="col-lg-6">
    <div class="panel">
      <div class="panel-head">
        <h6 class="panel-title"><i class="bi bi-whatsapp me-2 text-success"></i>Recent WhatsApp Messages</h6>
      </div>
      <div class="panel-body p-0" style="max-height:320px;overflow-y:auto;">
        <?php if (!empty($recentWhatsApp)): ?>
          <ul class="list-group list-group-flush">
            <?php foreach ($recentWhatsApp as $wm): ?>
            <li class="list-group-item">
              <div class="d-flex align-items-center gap-2">
                <div class="flex-shrink-0">
                  <div class="whatsapp-icon">
                    <i class="bi bi-whatsapp"></i>
                  </div>
                </div>
                <div class="flex-grow-1">
                  <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="fw-600 small"><?= e($wm['user_name']) ?></span>
                    <span class="badge bg-<?= $wm['status'] === 'sent' ? 'success' : ($wm['status'] === 'failed' ? 'danger' : 'warning') ?>" style="font-size:.65rem;">
                      <?= ucfirst($wm['status']) ?>
                    </span>
                  </div>
                  <div class="text-muted" style="font-size:.72rem;">
                    <span class="badge bg-secondary me-1"><?= ucfirst(str_replace('_', ' ', $wm['message_type'])) ?></span>
                    <?= e($wm['user_phone']) ?>
                  </div>
                  <div class="text-muted" style="font-size:.7rem;margin-top:.25rem;">
                    <i class="bi bi-clock"></i> <?= timeAgo($wm['created_at']) ?>
                  </div>
                </div>
              </div>
            </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <div class="text-center py-4 text-muted">
            <i class="bi bi-chat-dots d-block mb-2" style="font-size:2rem;opacity:0.3;"></i>
            <p class="mb-0">No messages sent yet</p>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ── Recent Activity Row ──────────────────────────────────────── -->
<div class="row g-3">
  <!-- Recent Users -->
  <div class="col-lg-7">
    <div class="panel">
      <div class="panel-head">
        <h6 class="panel-title"><i class="bi bi-people-fill me-2 text-blue"></i>Recent Users</h6>
        <a href="<?= url('/users') ?>" class="btn btn-xs btn-outline-secondary">View All</a>
      </div>
      <div class="panel-body p-0">
        <table class="table table-hover mb-0">
          <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Joined</th></tr></thead>
          <tbody>
            <?php foreach ($recentUsers as $u): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="avatar-sm"><?= strtoupper(substr($u['name'],0,1)) ?></div>
                  <div>
                    <div class="fw-500 small"><?= e($u['name']) ?></div>
                    <div class="text-muted" style="font-size:.74rem;"><?= e($u['email']) ?></div>
                  </div>
                </div>
              </td>
              <td><?= roleBadge($u['role']) ?></td>
              <td><?= statusBadge($u['status']) ?></td>
              <td class="text-muted small"><?= timeAgo($u['created_at']) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($recentUsers)): ?>
              <tr><td colspan="4" class="text-center py-4 text-muted">No users yet</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Activity Feed -->
  <div class="col-lg-5">
    <div class="panel h-100">
      <div class="panel-head">
        <h6 class="panel-title"><i class="bi bi-activity me-2 text-purple"></i>Activity Feed</h6>
        <?php if ($isSuper): ?>
        <a href="<?= url('/reports/audit-logs') ?>" class="btn btn-xs btn-outline-secondary">Audit Logs</a>
        <?php endif; ?>
      </div>
      <div class="panel-body p-0" style="max-height:320px;overflow-y:auto;">
        <ul class="activity-list">
          <?php foreach ($recentActivity as $a): ?>
          <li class="activity-item">
            <div class="activity-icon activity-icon--<?= actColor($a['type']) ?>">
              <i class="bi bi-<?= actIcon($a['type']) ?>"></i>
            </div>
            <div class="activity-body">
              <p class="activity-text">
                <?php if (!empty($a['user_name'])): ?>
                  <span class="fw-600"><?= e($a['user_name']) ?></span> —
                <?php endif; ?>
                <?= e($a['description']) ?>
              </p>
              <span class="activity-time"><?= timeAgo($a['created_at']) ?></span>
            </div>
          </li>
          <?php endforeach; ?>
          <?php if (empty($recentActivity)): ?>
            <li class="p-4 text-center text-muted">
              <i class="bi bi-clock-history d-block mb-2" style="font-size:1.5rem;"></i>No activity yet
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>
</div>
</div><!-- /.dashboard-page -->

<!-- ── Chart Scripts ─────────────────────────────────────────────── -->
<script>
(function(){
  const gridColor = 'rgba(255,255,255,0.05)';
  const tickColor = '#86a892';

  // Revenue & Bookings
  const revCtx = document.getElementById('revChart');
  if (revCtx) {
    const labels   = <?= json_encode(array_column($monthlyRev,'month')) ?>;
    const revenue  = <?= json_encode(array_column($monthlyRev,'revenue')) ?>;
    const bookings = <?= json_encode(array_column($monthlyRev,'total_bookings')) ?>;

    new Chart(revCtx, {
      data: {
        labels,
        datasets: [
          {
            type: 'bar', label: 'Revenue (₹)', data: revenue,
            backgroundColor: 'rgba(34,197,94,0.75)', borderColor: '#22c55e',
            borderWidth: 1, borderRadius: 5, yAxisID: 'yRev',
          },
          {
            type: 'line', label: 'Bookings', data: bookings,
            borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)',
            tension: 0.4, pointRadius: 4, pointBackgroundColor: '#3b82f6',
            fill: true, yAxisID: 'yBook',
          }
        ]
      },
      options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { labels: { color: tickColor, boxWidth: 11, padding: 16, font: { size: 11 } } },
          tooltip: {
            backgroundColor: 'rgba(10,17,12,0.95)',
            borderColor: 'rgba(34,197,94,0.2)', borderWidth: 1,
            titleColor: '#f0fdf4', bodyColor: '#86a892',
            callbacks: {
              label: ctx => ctx.dataset.label === 'Revenue (₹)'
                ? ' ₹' + Number(ctx.raw).toLocaleString('en-IN')
                : ' ' + ctx.raw + ' bookings'
            }
          }
        },
        scales: {
          yRev:  { position: 'left',  beginAtZero: true, ticks: { color: tickColor, callback: v => '₹' + (v/1000).toFixed(0)+'k' }, grid: { color: gridColor } },
          yBook: { position: 'right', beginAtZero: true, ticks: { color: tickColor }, grid: { display: false } },
          x:     { ticks: { color: tickColor }, grid: { display: false } }
        }
      }
    });
  }

  // Venue doughnut
  const venCtx = document.getElementById('venueChart');
  if (venCtx) {
    new Chart(venCtx, {
      type: 'doughnut',
      data: {
        labels: ['Approved','Pending','Rejected'],
        datasets: [{
          data: [<?= $venueStats['approved'] ?>, <?= $venueStats['pending'] ?>, <?= $venueStats['rejected'] ?>],
          backgroundColor: ['#22c55e','#f59e0b','#ef4444'],
          borderWidth: 0, hoverOffset: 8,
        }]
      },
      options: {
        cutout: '74%',
        plugins: { legend: { display: false } }
      }
    });
  }
})();
</script>

<script>
// Notification Panel Toggle
(function() {
  const notifBtn = document.getElementById('notificationBtn');
  const notifPanel = document.getElementById('notificationPanel');
  const closeNotif = document.getElementById('closeNotifications');

  if (notifBtn && notifPanel) {
    notifBtn.addEventListener('click', function(e) {
      e.stopPropagation();
      notifPanel.style.display = notifPanel.style.display === 'none' ? 'block' : 'none';
    });

    if (closeNotif) {
      closeNotif.addEventListener('click', function(e) {
        e.stopPropagation();
        notifPanel.style.display = 'none';
      });
    }

    // Close when clicking outside
    document.addEventListener('click', function(e) {
      if (!notifPanel.contains(e.target) && !notifBtn.contains(e.target)) {
        notifPanel.style.display = 'none';
      }
    });
  }
})();
</script>

<style>
/* Notification Panel Styles */
.notification-panel {
  position: fixed;
  top: 70px;
  right: 20px;
  width: 380px;
  max-width: calc(100vw - 40px);
  background: rgba(13,22,15,0.98);
  border: 1px solid rgba(34,197,94,0.2);
  border-radius: 12px;
  box-shadow: 0 8px 32px rgba(0,0,0,0.5);
  z-index: 1050;
  max-height: 500px;
  display: flex;
  flex-direction: column;
}

.notification-panel-header {
  padding: 1rem 1.25rem;
  border-bottom: 1px solid rgba(134,168,146,0.15);
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.notification-panel-header h6 {
  font-family: var(--font-h);
  font-weight: 700;
  color: #f0fdf4;
  margin: 0;
}

.notification-panel-body {
  overflow-y: auto;
  flex: 1;
}

.notification-item {
  display: flex;
  gap: 1rem;
  padding: 1rem 1.25rem;
  border-bottom: 1px solid rgba(134,168,146,0.1);
  transition: background 0.2s;
  text-decoration: none;
  color: inherit;
}

.notification-item:hover {
  background: rgba(34,197,94,0.05);
}

.notification-item:last-child {
  border-bottom: none;
}

.notification-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 1.1rem;
}

.notification-success .notification-icon {
  background: rgba(34,197,94,0.15);
  color: #22c55e;
}

.notification-warning .notification-icon {
  background: rgba(245,158,11,0.15);
  color: #f59e0b;
}

.notification-info .notification-icon {
  background: rgba(59,130,246,0.15);
  color: #3b82f6;
}

.notification-danger .notification-icon {
  background: rgba(239,68,68,0.15);
  color: #ef4444;
}

.notification-content {
  flex: 1;
  min-width: 0;
}

.notification-title {
  font-weight: 600;
  font-size: 0.875rem;
  color: #f0fdf4;
  margin-bottom: 0.25rem;
}

.notification-message {
  font-size: 0.8rem;
  color: #86a892;
  margin-bottom: 0.25rem;
}

.notification-time {
  font-size: 0.7rem;
  color: #4a5d4f;
}

/* Sport/Venue Stats */
.sport-stat-item, .top-venue-item {
  padding: 0.75rem 0;
  border-bottom: 1px solid rgba(134,168,146,0.1);
}

.sport-stat-item:last-child, .top-venue-item:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.sport-icon {
  font-size: 1.5rem;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(34,197,94,0.1);
  border-radius: 8px;
}

.rank-badge {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 0.9rem;
  flex-shrink: 0;
}

.rank-1 {
  background: linear-gradient(135deg, #ffd700, #ffed4e);
  color: #854d0e;
}

.rank-2 {
  background: linear-gradient(135deg, #c0c0c0, #e8e8e8);
  color: #3f3f46;
}

.rank-3 {
  background: linear-gradient(135deg, #cd7f32, #dda15e);
  color: #451a03;
}

.rank-4, .rank-5 {
  background: rgba(34,197,94,0.15);
  color: #22c55e;
}

.whatsapp-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: rgba(37,211,102,0.15);
  color: #25d366;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.2rem;
}

/* Indigo color for WhatsApp stats card */
.stat-card--indigo .stat-card__icon {
  background: rgba(99,102,241,0.15);
  color: #6366f1;
}

.stat-card--indigo .stat-card__value {
  color: #818cf8;
}
</style>
