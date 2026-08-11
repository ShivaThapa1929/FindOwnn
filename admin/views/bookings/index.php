<?php
/**
 * @var array  $stats   Booking stats from BookingController::index()
 * @var array  $result  Paginated bookings { data, total, page, pages, perPage }
 * @var string $filter  Status filter (all, pending, confirmed, etc.)
 * @var string $search  Search query string
 * @var array  $myVenues Owner venues for offline booking dropdown
 */
$stats    = $stats ?? [];
$result   = $result ?? ['data' => [], 'total' => 0, 'page' => 1, 'pages' => 1, 'perPage' => 20];
$filter   = $filter ?? 'all';
$search   = $search ?? '';
$myVenues = $myVenues ?? [];
$role     = auth()['role'] ?? '';
?><!-- Stats Bar -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card stat-card--blue">
      <div class="stat-card__icon"><i class="bi bi-calendar-check-fill"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= number_format($stats['total'] ?? 0) ?></div>
        <div class="stat-card__label">Total Bookings</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card stat-card--green">
      <div class="stat-card__icon"><i class="bi bi-check-circle-fill"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= number_format($stats['confirmed'] ?? 0) ?></div>
        <div class="stat-card__label">Confirmed</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card stat-card--orange">
      <div class="stat-card__icon"><i class="bi bi-currency-rupee"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value">₹<?= number_format($stats['total_revenue'] ?? 0) ?></div>
        <div class="stat-card__label">Revenue</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card stat-card--red">
      <div class="stat-card__icon"><i class="bi bi-x-circle-fill"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= number_format($stats['cancelled'] ?? 0) ?></div>
        <div class="stat-card__label">Cancelled</div>
      </div>
    </div>
  </div>
</div>

<!-- Filter Bar -->
<div class="d-flex flex-wrap gap-2 mb-4 align-items-center justify-content-between">
  <div class="d-flex gap-2 flex-wrap align-items-center">
    <form action="<?= url('/bookings') ?>" method="GET" class="d-flex gap-2">
      <input type="text" name="search" value="<?= e($search) ?>"
             class="form-control form-control-sm bookings-search-input" placeholder="Search ref, venue, player...">
      <input type="hidden" name="status" value="<?= e($filter) ?>">
      <button class="btn btn-sm btn-primary">Search</button>
      <?php if ($search): ?>
        <a href="<?= url('/bookings?status='.$filter) ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
      <?php endif; ?>
    </form>
    <?php foreach (['all'=>'All','pending'=>'Pending','confirmed'=>'Confirmed','completed'=>'Completed','cancelled'=>'Cancelled'] as $k=>$label): ?>
    <a href="<?= url('/bookings?status='.$k.($search?'&search='.urlencode($search):'')) ?>"
       class="btn btn-sm <?= $filter===$k ? 'btn-primary' : 'btn-outline-secondary' ?>">
      <?= $label ?>
    </a>
    <?php endforeach; ?>
  </div>
  <?php if ($role === 'venue_owner'): ?>
  <a href="<?= url('/bookings/offline/create') ?>" class="btn btn-sm btn-success">
    <i class="bi bi-plus-circle me-1"></i>Add Offline Booking
  </a>
  <?php endif; ?>
</div>

<!-- Table -->
<div class="panel">
  <div class="panel-head">
    <h6 class="panel-title">
      Bookings <span class="badge bg-secondary ms-1"><?= $result['total'] ?></span>
    </h6>
  </div>
  <div class="panel-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>Reference</th>
            <th>Venue</th>
            <th>Player</th>
            <th>Date & Time</th>
            <th>Hrs</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Payment</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($result['data'] as $b): ?>
          <tr>
            <td>
              <a href="<?= url('/bookings/'.$b['id']) ?>" class="fw-600 font-monospace small text-decoration-none text-primary">
                <?= e($b['booking_reference']) ?>
              </a>
              <?php if (str_starts_with($b['booking_reference'], 'OFL-')): ?>
                <span class="badge bg-warning text-dark ms-1" style="font-size:.6rem;">Offline</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="small fw-500"><?= e($b['venue_name']) ?></div>
              <?php if (!empty($b['sport_name'])): ?>
              <span class="badge bg-dark" style="font-size:.6rem;"><?= e($b['sport_name']) ?></span>
              <?php endif; ?>
              <?php if (!empty($b['court_name'])): ?>
              <span class="badge bg-secondary" style="font-size:.6rem;"><?= e($b['court_name']) ?></span>
              <?php endif; ?>
            </td>
            <td>
              <div class="small"><?= e($b['user_name'] ?? 'Walk-in') ?></div>
              <?php if (!empty($b['user_phone'])): ?>
              <div class="text-muted" style="font-size:.72rem;"><?= e($b['user_phone']) ?></div>
              <?php endif; ?>
            </td>
            <td>
              <div class="small fw-500"><?= date('M j, Y', strtotime($b['booking_date'])) ?></div>
              <div class="text-muted" style="font-size:.72rem;">
                <?= date('g:i A', strtotime($b['start_time'])) ?> – <?= date('g:i A', strtotime($b['end_time'])) ?>
                · <?= number_format((float) $b['total_hours'], 1) ?>h
              </div>
            </td>
            <td class="text-muted small"><?= number_format((float) $b['total_hours'], 1) ?>h</td>
            <td class="fw-600 small">₹<?= number_format($b['amount']) ?></td>
            <td><?= statusBadge($b['status']) ?></td>
            <td><?= statusBadge($b['payment_status']) ?></td>
            <td>
              <a href="<?= url('/bookings/'.$b['id']) ?>" class="btn btn-xs btn-outline-secondary">
                <i class="bi bi-eye"></i>
              </a>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($result['data'])): ?>
          <tr>
            <td colspan="9" class="text-center py-5 text-muted">
              <i class="bi bi-calendar-x d-block mb-2" style="font-size:2rem;"></i>
              No bookings found
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="panel-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
    <small class="text-muted">Showing <?= count($result['data']) ?> of <?= $result['total'] ?></small>
    <?= paginate_links($result['page'], $result['pages'], url('/bookings?status='.$filter.($search?'&search='.urlencode($search):''))) ?>
  </div>
</div>
