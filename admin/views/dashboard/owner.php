<?php
/** Venue Owner Dashboard */
/** @var array $myVenues @var array $venueStats @var array $bookStats @var array|null $mySub */
/** @var array $recentBookings @var array $ownerMonthlyRev @var array $activity */
/** @var array $bestPerforming @var array $mostLoved */

$myVenues        = $myVenues ?? [];
$venueStats      = $venueStats ?? ['total' => 0, 'active' => 0, 'pending' => 0, 'verified' => 0];
$bookStats       = $bookStats ?? [];
$mySub           = $mySub ?? null;
$recentBookings  = $recentBookings ?? [];
$ownerMonthlyRev = $ownerMonthlyRev ?? [];
$activity        = $activity ?? [];
$bestPerforming  = $bestPerforming ?? [];
$mostLoved       = $mostLoved ?? [];
$recommendations = $recommendations ?? [];
$allPlans = $allPlans ?? [];
$hasRevenueData  = !empty($ownerMonthlyRev) && is_array($ownerMonthlyRev) && count($ownerMonthlyRev) > 0;
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
      <?= date('l, F j, Y') ?>
    </p>
  </div>
  <a href="<?= url('/venues/create') ?>" class="btn btn-sm btn-primary">
    <i class="bi bi-plus-lg me-1"></i>Add New Venue
  </a>
</div>

<?php if ($successMsg = flash('success')): ?>
<div class="alert alert-success d-flex align-items-center gap-2 mb-3 py-2 px-3 small border-0" style="background:rgba(56,135,198,0.15);color:#3887C6;border-left:3px solid #3887C6!important;">
  <i class="bi bi-check-circle-fill fs-5"></i>
  <div class="fw-600"><?= e($successMsg) ?></div>
</div>
<?php endif; ?>

<?php if ($errorMsg = flash('error')): ?>
<div class="alert alert-danger d-flex align-items-center gap-2 mb-3 py-2 px-3 small border-0" style="background:rgba(239,68,68,0.15);color:#ef4444;border-left:3px solid #ef4444!important;">
  <i class="bi bi-exclamation-circle-fill fs-5"></i>
  <div class="fw-600"><?= e($errorMsg) ?></div>
</div>
<?php endif; ?>

<!-- ── Subscription Alert ─────────────────────────────────────── -->
<?php if (!$mySub): ?>
<div class="alert alert-danger d-flex align-items-start gap-3 mb-4 border-0" style="background:rgba(239,68,68,0.1);border-left:3px solid #ef4444!important;">
  <i class="bi bi-shield-exclamation fs-4 flex-shrink-0 mt-1 text-danger"></i>
  <div class="flex-grow-1">
    <div class="fw-700 mb-1">No active subscription</div>
    <div class="small text-muted mb-2">Choose a subscription plan to start accepting bookings and grow your business.</div>
    <button class="btn btn-sm btn-danger" onclick="document.getElementById('subscriptionPlans')?.scrollIntoView({behavior:'smooth'})">
      <i class="bi bi-cart-plus me-1"></i>View Plans
    </button>
  </div>
</div>
<?php elseif (!empty($mySub['expires_at']) && strtotime($mySub['expires_at']) < strtotime('+7 days')): ?>
<div class="alert d-flex align-items-start gap-3 mb-4 border-0" style="background:rgba(234,179,8,0.1);border-left:3px solid #eab308!important;">
  <i class="bi bi-clock-history fs-4 flex-shrink-0 mt-1 text-warning"></i>
  <div class="flex-grow-1">
    <div class="fw-700 mb-1 text-warning">Subscription expiring soon</div>
    <div class="small text-muted mb-2">
      Your <strong><?= e($mySub['plan_name']) ?></strong> plan expires on
      <strong><?= date('M j, Y', strtotime($mySub['expires_at'])) ?></strong>.
    </div>
    <button class="btn btn-sm btn-warning" onclick="document.getElementById('subscriptionPlans')?.scrollIntoView({behavior:'smooth'})">
      <i class="bi bi-arrow-repeat me-1"></i>Renew Now
    </button>
  </div>
</div>
<?php else: ?>
<div class="alert d-flex align-items-center gap-3 mb-4 border-0" style="background:rgba(56,135,198,0.08);border-left:3px solid #3887C6!important;">
  <i class="bi bi-patch-check-fill text-success fs-5 flex-shrink-0"></i>
  <div class="small flex-grow-1">
    <strong class="text-success"><?= e($mySub['plan_name'] ?? 'Subscription') ?> Plan</strong> active
    <?php if (!empty($mySub['platform_fee_percent']) || ($mySub['plan_slug'] ?? '') === 'enterprise'): ?>
      — platform fee: <strong><?= ($mySub['plan_slug'] ?? '') === 'enterprise' ? 'Negotiable' : e(rtrim(rtrim(number_format((float) $mySub['platform_fee_percent'], 2), '0'), '.') . '%') ?></strong>
    <?php endif; ?>
    — expires <strong><?= !empty($mySub['expires_at']) ? date('M j, Y', strtotime($mySub['expires_at'])) : '—' ?></strong>
  </div>
  <a href="<?= url('/subscriptions/my-plans') ?>" class="btn btn-xs btn-outline-success">
    <i class="bi bi-arrow-up-circle me-1"></i>Upgrade
  </a>
</div>
<?php endif; ?>

<!-- ── KPI Cards ─────────────────────────────────────────────────── -->
<div class="row g-2 mb-3 dashboard-kpi-row">
  <div class="col-6 col-sm-6 col-xl-3">
    <div class="stat-card stat-card--green">
      <div class="stat-card__icon"><i class="bi bi-building-fill"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= $venueStats['total'] ?></div>
        <div class="stat-card__label">My Venues</div>
        <div class="stat-card__trend trend-neutral">
          <i class="bi bi-check-circle-fill text-success"></i>
          <?= $venueStats['active'] ?> live
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-sm-6 col-xl-3">
    <div class="stat-card stat-card--blue">
      <div class="stat-card__icon"><i class="bi bi-calendar-check-fill"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= number_format($bookStats['total'] ?? 0) ?></div>
        <div class="stat-card__label">Total Bookings</div>
        <div class="stat-card__trend trend-neutral">
          <i class="bi bi-check2-circle"></i>
          <?= number_format($bookStats['confirmed'] ?? 0) ?> confirmed
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-sm-6 col-xl-3">
    <div class="stat-card stat-card--orange">
      <div class="stat-card__icon"><i class="bi bi-currency-rupee"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value">₹<?= number_format($bookStats['total_revenue'] ?? 0) ?></div>
        <div class="stat-card__label">Total Revenue</div>
        <div class="stat-card__trend trend-up">
          <i class="bi bi-graph-up-arrow"></i>
          ₹<?= number_format($bookStats['monthly_revenue'] ?? 0) ?> this month
        </div>
      </div>
    </div>
  </div>
  <div class="col-6 col-sm-6 col-xl-3">
    <div class="stat-card <?= $venueStats['verified'] > 0 ? 'stat-card--teal' : 'stat-card--yellow' ?>">
      <div class="stat-card__icon"><i class="bi bi-patch-check-fill"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= $venueStats['verified'] ?></div>
        <div class="stat-card__label">Verified Venues</div>
        <?php if ($venueStats['pending'] > 0): ?>
        <div class="stat-card__trend trend-down">
          <i class="bi bi-hourglass-split"></i>
          <?= $venueStats['pending'] ?> pending
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ── Smart Recommendations ─────────────────────────────────────── -->
<?php include __DIR__ . '/_recommendations.php'; ?>

<!-- ── Charts + Venues ───────────────────────────────────────────── -->
<div class="row g-3 mb-3">
  <!-- Revenue Chart -->
  <div class="col-lg-7">
    <div class="panel">
      <div class="panel-head">
        <h6 class="panel-title"><i class="bi bi-bar-chart-fill me-2 text-success"></i>My Revenue (Last 6 months)</h6>
      </div>
      
      <?php if (!$hasRevenueData): ?>
      <!-- Empty State -->
      <div class="panel-body" style="min-height: 320px;">
        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 280px; text-align: center; padding: 2rem;">
          <!-- SVG Chart Placeholder -->
          <svg width="140" height="100" viewBox="0 0 140 100" xmlns="http://www.w3.org/2000/svg" style="margin-bottom: 1.5rem; opacity: 0.5;">
            <defs>
              <linearGradient id="emptyChartGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                <stop offset="0%" style="stop-color:#3887C6;stop-opacity:0.4" />
                <stop offset="100%" style="stop-color:#10b981;stop-opacity:0.2" />
              </linearGradient>
            </defs>
            <!-- Line Chart Shape -->
            <path d="M 10 80 L 30 60 L 50 70 L 70 45 L 90 55 L 110 30 L 130 40" 
                  stroke="url(#emptyChartGrad)" 
                  stroke-width="3" 
                  fill="none" 
                  stroke-linecap="round"
                  stroke-linejoin="round"/>
            <!-- Data Points -->
            <circle cx="10" cy="80" r="4" fill="#3887C6" opacity="0.5"/>
            <circle cx="30" cy="60" r="4" fill="#3887C6" opacity="0.5"/>
            <circle cx="50" cy="70" r="4" fill="#3887C6" opacity="0.5"/>
            <circle cx="70" cy="45" r="5" fill="#3887C6" opacity="0.6"/>
            <circle cx="90" cy="55" r="4" fill="#3887C6" opacity="0.5"/>
            <circle cx="110" cy="30" r="5" fill="#3887C6" opacity="0.6"/>
            <circle cx="130" cy="40" r="4" fill="#3887C6" opacity="0.5"/>
            <!-- Axis Lines -->
            <line x1="10" y1="85" x2="130" y2="85" stroke="#86a892" stroke-width="1" opacity="0.2"/>
          </svg>
          
          <h6 style="color: #86a892; font-weight: 600; font-size: 1.1rem; margin: 0 0 0.5rem 0;">
            No Revenue Data Yet
          </h6>
          <p style="color: #6b7c75; font-size: 0.9rem; margin: 0 0 1.5rem 0; max-width: 320px;">
            Your revenue chart will appear here once you start receiving paid bookings.
          </p>
          <a href="<?= url('/bookings/offline/create') ?>" class="btn btn-sm btn-success">
            <i class="bi bi-plus-lg me-1"></i>Create First Booking
          </a>
        </div>
      </div>
      
      <?php else: ?>
      <!-- Chart Display -->
      <div class="panel-body" style="padding: 1.25rem 1rem 0.5rem;">
        <div style="height: 280px; position: relative;">
          <canvas id="ownerRevChart"></canvas>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <!-- Venue Quick Status -->
  <div class="col-lg-5">
    <div class="panel h-100">
      <div class="panel-head">
        <h6 class="panel-title"><i class="bi bi-building me-2"></i>My Venues</h6>
        <a href="<?= url('/venues/create') ?>" class="btn btn-xs btn-primary"><i class="bi bi-plus-lg"></i></a>
      </div>
      <div class="panel-body p-0" style="max-height:260px;overflow-y:auto;">
        <?php if (empty($myVenues)): ?>
          <div class="p-4 text-center text-muted">
            <i class="bi bi-building d-block mb-2" style="font-size:2rem;"></i>
            <p class="small mb-0">No venues yet.</p>
            <a href="<?= url('/venues/create') ?>" class="btn btn-sm btn-primary mt-2">Add First Venue</a>
          </div>
        <?php else: ?>
          <?php foreach ($myVenues as $v): ?>
          <div class="d-flex align-items-center gap-2 px-3 py-2 border-bottom border-opacity-10">
            <div class="flex-shrink-0" style="width:8px;height:8px;border-radius:50%;background:<?= $v['status']==='active' ? '#3887C6' : ($v['verification_status']==='pending' ? '#f59e0b' : '#ef4444') ?>;"></div>
            <div class="flex-grow-1 min-w-0">
              <div class="fw-500 small text-truncate"><?= e($v['name']) ?></div>
              <div class="text-muted d-flex align-items-center gap-2" style="font-size:.72rem;">
                <span><?= ucwords($v['verification_status'] ?? 'pending') ?></span>
                <span>·</span>
                <span>₹<?= number_format($v['price_per_hour']) ?>/hr</span>
                <?php if ($v['is_verified']): ?>
                  <i class="bi bi-patch-check-fill text-success"></i>
                <?php endif; ?>
              </div>
            </div>
            <div class="d-flex gap-1 flex-shrink-0">
              <?= statusBadge($v['verification_status']) ?>
            </div>
            <a href="<?= url('/venues/'.$v['id'].'/edit') ?>" class="btn btn-xs btn-outline-secondary flex-shrink-0">
              <i class="bi bi-pencil"></i>
            </a>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ── Best Performing & Most Loved Venues ──────────────────────── -->
<?php if (!empty($bestPerforming) || !empty($mostLoved)): ?>
<div class="row g-3 mb-3">
  <!-- Best Performing Venues -->
  <?php if (!empty($bestPerforming)): ?>
  <div class="col-lg-6">
    <div class="panel">
      <div class="panel-head">
        <h6 class="panel-title">
          <i class="bi bi-trophy-fill me-2" style="color:#f59e0b;"></i>
          Best Performing Venues
        </h6>
        <span class="badge bg-orange-soft">By Revenue</span>
      </div>
      <div class="panel-body p-0">
        <?php foreach ($bestPerforming as $idx => $venue): ?>
        <div class="featured-venue-card <?= $idx === 0 ? 'featured-venue-card--top' : '' ?>">
          <div class="featured-venue-rank">#<?= $idx + 1 ?></div>
          
          <div class="featured-venue-image">
            <?php if ($venue['featured_image']): ?>
              <img src="<?= url('/public/uploads/' . $venue['featured_image']) ?>" alt="<?= e($venue['name']) ?>">
            <?php else: ?>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 150" width="200" height="150">
                <rect width="200" height="150" fill="#0d1510"/>
                <circle cx="100" cy="75" r="30" fill="#3887C6" opacity="0.2"/>
                <path d="M70 75 L85 60 L115 60 L130 75 L115 90 L85 90 Z" fill="#3887C6" opacity="0.3"/>
                <text x="100" y="120" font-family="Arial" font-size="11" fill="#86a892" text-anchor="middle">No Image</text>
              </svg>
            <?php endif; ?>
            <?php if ($idx === 0): ?>
              <div class="featured-venue-badge">
                <i class="bi bi-star-fill"></i> Top Performer
              </div>
            <?php endif; ?>
          </div>
          
          <div class="featured-venue-body">
            <div class="featured-venue-header">
              <div>
                <h6 class="featured-venue-name"><?= e($venue['name']) ?></h6>
                <div class="featured-venue-location">
                  <i class="bi bi-geo-alt-fill me-1"></i><?= e($venue['city']) ?>
                </div>
              </div>
              <a href="<?= url('/venues/' . $venue['id']) ?>" class="btn btn-xs btn-outline-success">
                <i class="bi bi-eye"></i>
              </a>
            </div>
            
            <div class="featured-venue-stats">
              <div class="featured-venue-stat">
                <div class="featured-venue-stat-value">₹<?= number_format($venue['total_revenue']) ?></div>
                <div class="featured-venue-stat-label">Total Revenue</div>
              </div>
              <div class="featured-venue-stat">
                <div class="featured-venue-stat-value"><?= number_format($venue['total_bookings']) ?></div>
                <div class="featured-venue-stat-label">Bookings</div>
              </div>
              <div class="featured-venue-stat">
                <div class="featured-venue-stat-value">₹<?= number_format($venue['monthly_revenue']) ?></div>
                <div class="featured-venue-stat-label">This Month</div>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
  
  <!-- Most Loved Venues -->
  <?php if (!empty($mostLoved)): ?>
  <div class="col-lg-6">
    <div class="panel">
      <div class="panel-head">
        <h6 class="panel-title">
          <i class="bi bi-heart-fill me-2 text-danger"></i>
          Most Loved Venues
        </h6>
        <span class="badge bg-danger-soft">By Rating</span>
      </div>
      <div class="panel-body p-0">
        <?php foreach ($mostLoved as $idx => $venue): ?>
        <div class="featured-venue-card <?= $idx === 0 ? 'featured-venue-card--top' : '' ?>">
          <div class="featured-venue-rank">#<?= $idx + 1 ?></div>
          
          <div class="featured-venue-image">
            <?php if ($venue['featured_image']): ?>
              <img src="<?= url('/public/uploads/' . $venue['featured_image']) ?>" alt="<?= e($venue['name']) ?>">
            <?php else: ?>
              <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 150" width="200" height="150">
                <rect width="200" height="150" fill="#0d1510"/>
                <circle cx="100" cy="75" r="30" fill="#ef4444" opacity="0.2"/>
                <path d="M100 90 L80 70 Q80 50 100 60 Q120 50 120 70 Z" fill="#ef4444" opacity="0.3"/>
                <text x="100" y="120" font-family="Arial" font-size="11" fill="#86a892" text-anchor="middle">No Image</text>
              </svg>
            <?php endif; ?>
            <?php if ($idx === 0): ?>
              <div class="featured-venue-badge featured-venue-badge--loved">
                <i class="bi bi-heart-fill"></i> Most Loved
              </div>
            <?php endif; ?>
          </div>
          
          <div class="featured-venue-body">
            <div class="featured-venue-header">
              <div>
                <h6 class="featured-venue-name"><?= e($venue['name']) ?></h6>
                <div class="featured-venue-location">
                  <i class="bi bi-geo-alt-fill me-1"></i><?= e($venue['city']) ?>
                </div>
              </div>
              <a href="<?= url('/venues/' . $venue['id']) ?>" class="btn btn-xs btn-outline-danger">
                <i class="bi bi-eye"></i>
              </a>
            </div>
            
            <div class="featured-venue-stats">
              <div class="featured-venue-stat">
                <div class="featured-venue-stat-value">
                  <i class="bi bi-star-fill text-warning"></i>
                  <?= number_format($venue['rating'], 1) ?>
                </div>
                <div class="featured-venue-stat-label">Rating</div>
              </div>
              <div class="featured-venue-stat">
                <div class="featured-venue-stat-value"><?= number_format($venue['total_bookings']) ?></div>
                <div class="featured-venue-stat-label">Total Bookings</div>
              </div>
              <div class="featured-venue-stat">
                <div class="featured-venue-stat-value"><?= $venue['total_reviews'] ?></div>
                <div class="featured-venue-stat-label">Reviews</div>
              </div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- ── Recent Bookings + Activity ───────────────────────────────── -->
<div class="row g-3">
  <div class="col-lg-8">
    <div class="panel">
      <div class="panel-head">
        <h6 class="panel-title"><i class="bi bi-calendar-event me-2 text-blue"></i>Recent Bookings</h6>
        <a href="<?= url('/bookings') ?>" class="btn btn-xs btn-outline-secondary">View All</a>
      </div>
      <?php if (empty($recentBookings)): ?>
        <div class="panel-body text-center py-5 text-muted">
          <i class="bi bi-calendar-x d-block mb-2" style="font-size:2rem;"></i>
          <p class="small mb-0">No bookings yet. Approve your venues to start receiving bookings.</p>
        </div>
      <?php else: ?>
        <div class="panel-body p-0">
          <div class="table-responsive">
            <table class="table table-hover mb-0">
              <thead><tr><th>Ref</th><th>Venue</th><th>Player</th><th>Date</th><th>Amount</th><th>Status</th></tr></thead>
              <tbody>
                <?php foreach ($recentBookings as $b): ?>
                <tr>
                  <td class="font-monospace" style="font-size:.72rem;"><?= e($b['booking_reference']) ?></td>
                  <td class="small fw-500"><?= e($b['venue_name']) ?></td>
                  <td>
                    <div class="small"><?= e($b['user_name']) ?></div>
                    <?php if ($b['user_phone']): ?>
                    <div class="text-muted" style="font-size:.72rem;"><?= e($b['user_phone']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td class="text-muted small">
                    <?= date('M j', strtotime($b['booking_date'])) ?>
                    <div style="font-size:.7rem;"><?= substr($b['start_time'],0,5) ?>–<?= substr($b['end_time'],0,5) ?></div>
                  </td>
                  <td class="fw-600 small">₹<?= number_format($b['amount']) ?></td>
                  <td><?= statusBadge($b['status']) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Activity -->
  <div class="col-lg-4">
    <div class="panel" style="display: flex; flex-direction: column; height: 100%;">
      <div class="panel-head"><h6 class="panel-title"><i class="bi bi-activity me-2"></i>My Activity</h6></div>
      <?php if (empty($activity)): ?>
        <div class="panel-body" style="flex: 1; display: flex; align-items: center; justify-content: center; min-height: 200px;">
          <div style="text-align: center;">
            <i class="bi bi-clock-history d-block mb-2" style="font-size:2.5rem;opacity:0.3;color:#86a892;"></i>
            <p style="color:#86a892;font-size:0.9rem;margin:0;">No recent activity</p>
          </div>
        </div>
      <?php else: ?>
        <div class="panel-body p-0" style="flex: 1; overflow-y: auto; max-height: 520px;">
          <ul class="activity-list" style="margin: 0; padding: 0; list-style: none;">
            <?php foreach ($activity as $a): ?>
            <li class="activity-item">
              <div class="activity-icon activity-icon--<?= $a['type'] === 'venue' ? 'blue' : ($a['type'] === 'auth' ? 'green' : 'yellow') ?>">
                <i class="bi bi-<?= $a['type']==='venue'?'building-fill':($a['type']==='auth'?'person-check-fill':'info-circle-fill') ?>"></i>
              </div>
              <div class="activity-body">
                <p class="activity-text"><?= e($a['description']) ?></p>
                <span class="activity-time"><?= timeAgo($a['created_at']) ?></span>
              </div>
            </li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- ── Subscription Plans ─────────────────────────────────────── -->
<div class="panel mb-4">
  <div class="panel-head d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h6 class="panel-title">Plans</h6>
    <a href="<?= url('/subscriptions/my-plans') ?>" class="btn btn-xs btn-outline-secondary">View all</a>
  </div>
  <div class="panel-body">
    <?php
      $plans = $allPlans;
      $allowUpgrade = true;
      include __DIR__ . '/../subscriptions/_plan_cards.php';
    ?>
  </div>
</div>

</div><!-- /.dashboard-page -->

<script>
<?php if ($hasRevenueData): ?>
// Chart initialization
document.addEventListener('DOMContentLoaded', function() {
  const ctx = document.getElementById('ownerRevChart');
  if (!ctx) {
    console.error('Canvas element #ownerRevChart not found');
    return;
  }
  
  // Check if Chart.js is loaded
  if (typeof Chart === 'undefined') {
    console.error('Chart.js library not loaded');
    ctx.parentElement.innerHTML = '<div style="text-align:center;padding:2rem;color:#86a892;"><i class="bi bi-exclamation-triangle-fill" style="font-size:2rem;display:block;margin-bottom:1rem;opacity:0.5;"></i><p style="margin:0;">Chart library failed to load.<br><small>Please check your internet connection and refresh.</small></p></div>';
    return;
  }
  
  const labels   = <?= json_encode(array_column($ownerMonthlyRev,'month')) ?>;
  const revenue  = <?= json_encode(array_column($ownerMonthlyRev,'revenue')) ?>;
  const bookings = <?= json_encode(array_column($ownerMonthlyRev,'bookings')) ?>;
  
  console.log('✓ Chart Data:', { 
    labels: labels,
    revenue: revenue,
    bookings: bookings,
    months: labels.length, 
    totalRevenue: revenue.reduce((a,b) => parseFloat(a) + parseFloat(b), 0),
    totalBookings: bookings.reduce((a,b) => parseInt(a) + parseInt(b), 0),
    maxRevenue: Math.max(...revenue),
    maxBookings: Math.max(...bookings)
  });

  // Calculate dynamic Y-axis max values
  const maxRevenue = Math.max(...revenue);
  const maxBookings = Math.max(...bookings);
  const suggestedMaxRevenue = Math.ceil(maxRevenue * 1.15); // 15% padding
  const suggestedMaxBookings = Math.ceil(maxBookings * 1.2); // 20% padding
  
  console.log('✓ Y-axis ranges:', {
    revenueMax: suggestedMaxRevenue,
    bookingsMax: suggestedMaxBookings
  });


  try {
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Revenue (₹)',
            data: revenue,
            borderColor: '#3887C6',
            backgroundColor: 'rgba(56,135,198,0.15)',
            borderWidth: 3,
            tension: 0.4,
            pointRadius: 6,
            pointHoverRadius: 8,
            pointBackgroundColor: '#3887C6',
            pointBorderColor: '#E5EFFB',
            pointBorderWidth: 2,
            pointHoverBackgroundColor: '#3887C6',
            pointHoverBorderColor: '#fff',
            pointHoverBorderWidth: 3,
            fill: true,
            yAxisID: 'y'
          },
          {
            label: 'Bookings',
            data: bookings,
            borderColor: '#f59e0b',
            backgroundColor: 'rgba(245,158,11,0.1)',
            borderWidth: 2.5,
            tension: 0.4,
            pointRadius: 5,
            pointHoverRadius: 7,
            pointBackgroundColor: '#f59e0b',
            pointBorderColor: '#E5EFFB',
            pointBorderWidth: 2,
            pointHoverBackgroundColor: '#f59e0b',
            pointHoverBorderColor: '#fff',
            pointHoverBorderWidth: 2,
            fill: true,
            yAxisID: 'y1'
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { 
          mode: 'index', 
          intersect: false 
        },
        plugins: {
          legend: { 
            display: true,
            position: 'top',
            align: 'end',
            labels: { 
              color: '#86a892',
              boxWidth: 12,
              boxHeight: 12,
              padding: 15,
              font: { 
                size: 12,
                weight: '600'
              },
              usePointStyle: true,
              pointStyle: 'circle'
            }
          },
          tooltip: {
            enabled: true,
            backgroundColor: 'rgba(10,15,11,0.95)',
            borderColor: 'rgba(56,135,198,0.3)',
            borderWidth: 1,
            titleColor: '#f0fdf4',
            titleFont: { size: 13, weight: 'bold' },
            bodyColor: '#d1e7d9',
            bodyFont: { size: 12 },
            padding: 12,
            cornerRadius: 8,
            displayColors: true,
            boxPadding: 6,
            callbacks: {
              label: function(context) {
                let label = context.dataset.label || '';
                if (label) {
                  label += ': ';
                }
                if (context.parsed.y !== null) {
                  if (context.datasetIndex === 0) {
                    label += '₹' + context.parsed.y.toLocaleString('en-IN');
                  } else {
                    label += context.parsed.y;
                  }
                }
                return label;
              }
            }
          }
        },
        scales: {
          y: {
            type: 'linear',
            display: true,
            position: 'left',
            beginAtZero: true,
            suggestedMax: suggestedMaxRevenue,
            grid: {
              color: 'rgba(134,168,146,0.08)',
              drawBorder: false
            },
            border: {
              display: false
            },
            ticks: {
              color: '#86a892',
              font: { size: 11 },
              padding: 8,
              callback: function(value) {
                if (value >= 1000) {
                  return '₹' + (value / 1000).toFixed(0) + 'k';
                }
                return '₹' + value;
              }
            }
          },
          y1: {
            type: 'linear',
            display: true,
            position: 'right',
            beginAtZero: true,
            suggestedMax: suggestedMaxBookings,
            grid: {
              drawOnChartArea: false
            },
            border: {
              display: false
            },
            ticks: {
              color: '#86a892',
              font: { size: 11 },
              padding: 8,
              stepSize: 1
            }
          },
          x: {
            grid: {
              display: false,
              drawBorder: false
            },
            border: {
              display: false
            },
            ticks: {
              color: '#86a892',
              font: { size: 11 },
              padding: 8,
              callback: function(value, index) {
                const label = this.getLabelForValue(value);
                const parts = label.split('-');
                if (parts.length === 2) {
                  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                  return months[parseInt(parts[1]) - 1] + " '" + parts[0].slice(-2);
                }
                return label;
              }
            }
          }
        }
      }
    });
    
    console.log('✓ Revenue chart initialized successfully');
    
  } catch (error) {
    console.error('✗ Chart initialization failed:', error);
    ctx.parentElement.innerHTML = '<div style="text-align:center;padding:2rem;color:#ef4444;"><i class="bi bi-x-circle-fill" style="font-size:2rem;display:block;margin-bottom:1rem;"></i><p style="margin:0;">Failed to render chart.<br><small>' + error.message + '</small></p></div>';
  }
});
<?php endif; ?>
</script>

<style>
/* ========== Activity Items ========== */
.activity-list {
  margin: 0;
  padding: 0;
  list-style: none;
}

.activity-item {
  display: flex;
  gap: 0.75rem;
  padding: 1rem;
  border-bottom: 1px solid rgba(134,168,146,0.08);
  transition: background 0.2s ease;
}

.activity-item:last-child {
  border-bottom: none;
}

.activity-item:hover {
  background: rgba(56,135,198,0.02);
}

.activity-icon {
  width: 36px;
  height: 36px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 0.95rem;
}

.activity-icon--blue {
  background: rgba(59,130,246,0.15);
  color: #3b82f6;
}

.activity-icon--green {
  background: rgba(56,135,198,0.15);
  color: #3887C6;
}

.activity-icon--yellow {
  background: rgba(245,158,11,0.15);
  color: #f59e0b;
}

.activity-body {
  flex: 1;
  min-width: 0;
  overflow: hidden;
}

.activity-text {
  margin: 0 0 0.25rem 0;
  font-size: 0.85rem;
  color: #d1e7d9;
  line-height: 1.4;
  word-wrap: break-word;
  overflow-wrap: break-word;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.activity-time {
  font-size: 0.72rem;
  color: #86a892;
  white-space: nowrap;
}

/* ========== Featured Venue Cards ========== */
.featured-venue-card {
  display: flex;
  gap: 1rem;
  padding: 1rem;
  border-bottom: 1px solid rgba(134,168,146,0.1);
  transition: all 0.3s ease;
  position: relative;
}

.featured-venue-card:last-child {
  border-bottom: none;
}

.featured-venue-card:hover {
  background: rgba(56,135,198,0.03);
}

.featured-venue-card--top {
  background: linear-gradient(135deg, rgba(56,135,198,0.05), rgba(56,135,198,0.03));
  border-left: 3px solid #3887C6;
}

.featured-venue-rank {
  position: absolute;
  top: 0.5rem;
  left: 0.5rem;
  width: 32px;
  height: 32px;
  background: rgba(10,15,11,0.9);
  border: 2px solid #3887C6;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 800;
  font-size: 0.85rem;
  color: #3887C6;
  z-index: 2;
}

.featured-venue-card--top .featured-venue-rank {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  border-color: #fbbf24;
  color: #fffbeb;
  box-shadow: 0 2px 8px rgba(245,158,11,0.3);
}

.featured-venue-image {
  position: relative;
  width: 140px;
  height: 105px;
  flex-shrink: 0;
  border-radius: 8px;
  overflow: hidden;
  background: rgba(0,0,0,0.3);
}

.featured-venue-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.featured-venue-image svg {
  width: 100%;
  height: 100%;
}

.featured-venue-badge {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: #fffbeb;
  padding: 0.25rem 0.6rem;
  border-radius: 6px;
  font-size: 0.7rem;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.3rem;
  box-shadow: 0 2px 6px rgba(0,0,0,0.3);
  z-index: 1;
}

.featured-venue-badge--loved {
  background: linear-gradient(135deg, #ef4444, #dc2626);
}

.featured-venue-body {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  min-width: 0;
}

.featured-venue-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 0.5rem;
}

.featured-venue-name {
  font-size: 1rem;
  font-weight: 700;
  color: #f0fdf4;
  margin: 0 0 0.25rem 0;
  line-height: 1.3;
}

.featured-venue-location {
  font-size: 0.8rem;
  color: #86a892;
  display: flex;
  align-items: center;
}

.featured-venue-stats {
  display: flex;
  gap: 1rem;
  margin-top: auto;
}

.featured-venue-stat {
  flex: 1;
}

.featured-venue-stat-value {
  font-size: 1rem;
  font-weight: 700;
  color: #3887C6;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.featured-venue-card--top .featured-venue-stat-value:first-child {
  color: #f59e0b;
}

.featured-venue-stat-label {
  font-size: 0.7rem;
  color: #86a892;
  text-transform: uppercase;
  letter-spacing: 0.03em;
  margin-top: 0.15rem;
}

.bg-success-soft {
  background-color: rgba(56,135,198,0.15);
  color: #3887C6;
  font-size: 0.7rem;
  font-weight: 600;
  padding: 0.25rem 0.6rem;
}

/* ========== Owner Recommendations ========== */
.owner-recommendations-panel {
  border: 1px solid rgba(245,158,11,0.2);
  background: linear-gradient(135deg, rgba(245,158,11,0.04), rgba(13,21,16,0.2));
}

.owner-rec-card {
  display: flex;
  gap: 0.85rem;
  height: 100%;
  padding: 1rem;
  border-radius: 12px;
  border: 1px solid rgba(134,168,146,0.14);
  background: rgba(10,15,11,0.55);
  transition: border-color 0.2s, transform 0.2s;
}

.owner-rec-card:hover {
  border-color: rgba(56,135,198,0.35);
  transform: translateY(-2px);
}

.owner-rec-card--high {
  border-left: 3px solid #ef4444;
}

.owner-rec-card--medium {
  border-left: 3px solid #f59e0b;
}

.owner-rec-card--low {
  border-left: 3px solid #3887C6;
}

.owner-rec-card__icon {
  flex-shrink: 0;
  width: 42px;
  height: 42px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.15rem;
  background: rgba(56,135,198,0.12);
  color: #5a9fd4;
}

.owner-rec-card--high .owner-rec-card__icon {
  background: rgba(239,68,68,0.12);
  color: #f87171;
}

.owner-rec-card--medium .owner-rec-card__icon {
  background: rgba(245,158,11,0.12);
  color: #fbbf24;
}

.owner-rec-card__body {
  flex: 1;
  min-width: 0;
}

.owner-rec-card__badge {
  display: inline-block;
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #86a892;
  margin-bottom: 0.35rem;
}

.owner-rec-card__title {
  font-size: 0.92rem;
  font-weight: 700;
  color: #f0fdf4;
  margin: 0 0 0.35rem 0;
  line-height: 1.3;
}

.owner-rec-card__text {
  font-size: 0.78rem;
  color: #86a892;
  line-height: 1.55;
  margin: 0 0 0.75rem 0;
}

.owner-rec-card__btn {
  font-size: 0.75rem;
  padding: 0.25rem 0.65rem;
}

/* Badge Styles */
.bg-orange-soft {
  background-color: rgba(245,158,11,0.15);
  color: #f59e0b;
  font-size: 0.7rem;
  font-weight: 600;
  padding: 0.25rem 0.6rem;
}

.bg-danger-soft {
  background-color: rgba(239,68,68,0.15);
  color: #ef4444;
  font-size: 0.7rem;
  font-weight: 600;
  padding: 0.25rem 0.6rem;
}

/* Responsive */
@media (max-width: 768px) {
  .featured-venue-card {
    flex-direction: column;
  }
  
  .featured-venue-image {
    width: 100%;
    height: 180px;
  }
  
  .featured-venue-stats {
    flex-wrap: wrap;
  }
  
  .featured-venue-stat {
    min-width: 30%;
  }
}
</style>
