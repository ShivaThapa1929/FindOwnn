<?php
/**
 * @var string $search
 * @var string $filter
 * @var array  $result
 */
$search = $search ?? '';
$filter = $filter ?? 'all';
$result = $result ?? ['data' => [], 'total' => 0, 'page' => 1, 'pages' => 1];
?>
<!-- Search + Filter -->
<div class="d-flex flex-wrap gap-2 mb-4 align-items-center justify-content-between">
  <div class="d-flex gap-2 flex-wrap">
    <form action="<?= url('/players') ?>" method="GET" class="d-flex gap-2">
      <input type="text" name="search" value="<?= e($search) ?>" class="form-control form-control-sm" placeholder="Search name, phone, email..." style="width:240px;">
      <button class="btn btn-sm btn-primary">Search</button>
      <?php if ($search): ?>
        <a href="<?= url('/players') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
      <?php endif; ?>
    </form>
    <?php foreach (['all'=>'All','registered'=>'App Users','walkin'=>'Walk-ins','active'=>'Active'] as $k=>$label): ?>
    <a href="<?= url('/players?filter='.$k.($search ? '&search='.urlencode($search) : '')) ?>"
      class="btn btn-sm <?= $filter===$k ? 'btn-primary' : 'btn-outline-secondary' ?>">
      <?= $label ?>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<div class="panel">
  <div class="panel-head">
    <h6 class="panel-title"><i class="bi bi-person-badge me-2"></i>Players <span class="badge bg-secondary ms-1"><?= $result['total'] ?></span></h6>
  </div>
  <div class="panel-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>Player</th>
            <th>Phone / WhatsApp</th>
            <th>Type</th>
            <th>Bookings</th>
            <th>Total Spent</th>
            <th>Upcoming</th>
            <th>Last Booking</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($result['data'] as $p): ?>
          <?php
            $isWalkin = str_contains($p['email'] ?? '', '@offline.findownn');
            $phone    = $p['whatsapp_number'] ?: ($p['phone'] ?? '—');
          ?>
          <tr>
            <td class="text-muted small"><?= $p['id'] ?></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="avatar-sm"><?= strtoupper(substr($p['name'], 0, 1)) ?></div>
                <div>
                  <div class="fw-500"><?= e($p['name']) ?></div>
                  <div class="text-muted small"><?= e($isWalkin ? 'Walk-in customer' : $p['email']) ?></div>
                </div>
              </div>
            </td>
            <td class="small">
              <?= e($phone) ?>
              <?php if (!empty($p['whatsapp_opt_in'])): ?>
                <span class="badge bg-success ms-1" style="font-size:.6rem;">WA</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($isWalkin): ?>
                <span class="badge bg-warning text-dark">Walk-in</span>
              <?php else: ?>
                <span class="badge bg-info">Registered</span>
              <?php endif; ?>
            </td>
            <td class="fw-600"><?= (int) $p['total_bookings'] ?></td>
            <td class="small">₹<?= number_format((float) $p['total_spent']) ?></td>
            <td>
              <?php if ((int) $p['upcoming_bookings'] > 0): ?>
                <span class="badge bg-primary"><?= (int) $p['upcoming_bookings'] ?></span>
              <?php else: ?>
                <span class="text-muted">—</span>
              <?php endif; ?>
            </td>
            <td class="text-muted small">
              <?= !empty($p['last_booking_date']) ? date('M j, Y', strtotime($p['last_booking_date'])) : '—' ?>
            </td>
            <td>
              <div class="d-flex gap-1">
                <a href="<?= url('/players/'.$p['id']) ?>" class="btn btn-xs btn-outline-secondary" title="View details">
                  <i class="bi bi-eye"></i>
                </a>
                <?php if ((int) $p['upcoming_bookings'] > 0): ?>
                <form action="<?= url('/players/'.$p['id'].'/reminder') ?>" method="POST" class="d-inline"
                      onsubmit="return confirm('Send WhatsApp booking reminder to <?= e($p['name']) ?>?')">
                  <?= csrf_field() ?>
                  <button class="btn btn-xs btn-outline-success" title="Send reminder">
                    <i class="bi bi-whatsapp"></i>
                  </button>
                </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($result['data'])): ?>
            <tr><td colspan="9" class="text-center py-5 text-muted">No players found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="panel-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
    <small class="text-muted">Showing <?= count($result['data']) ?> of <?= $result['total'] ?></small>
    <?= paginate_links($result['page'], $result['pages'], url('/players')) ?>
  </div>
</div>
