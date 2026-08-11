<!-- Stats -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card stat-card--green">
      <div class="stat-card__icon"><i class="bi bi-check-circle-fill"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= number_format($stats['active'] ?? 0) ?></div>
        <div class="stat-card__label">Active</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card stat-card--red">
      <div class="stat-card__icon"><i class="bi bi-x-circle-fill"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= number_format($stats['expired'] ?? 0) ?></div>
        <div class="stat-card__label">Expired</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card stat-card--yellow">
      <div class="stat-card__icon"><i class="bi bi-hourglass-split"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value"><?= number_format($stats['pending'] ?? 0) ?></div>
        <div class="stat-card__label">Pending</div>
      </div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card stat-card--purple">
      <div class="stat-card__icon"><i class="bi bi-currency-rupee"></i></div>
      <div class="stat-card__body">
        <div class="stat-card__value">₹<?= number_format($stats['total_revenue'] ?? 0) ?></div>
        <div class="stat-card__label">Total Revenue</div>
      </div>
    </div>
  </div>
</div>

<!-- Filter -->
<div class="d-flex gap-2 mb-4 flex-wrap align-items-center justify-content-between">
  <div class="d-flex gap-2 flex-wrap">
    <?php foreach (['all'=>'All','active'=>'Active','expired'=>'Expired','pending'=>'Pending'] as $k=>$label): ?>
    <a href="<?= url('/subscriptions?status='.$k) ?>"
       class="btn btn-sm <?= $status===$k ? 'btn-primary' : 'btn-outline-secondary' ?>">
      <?= $label ?>
    </a>
    <?php endforeach; ?>
  </div>
  <a href="<?= url('/subscriptions/plans') ?>" class="btn btn-sm btn-outline-primary">
    <i class="bi bi-layers me-1"></i>Manage Plans
  </a>
</div>

<div class="panel">
  <div class="panel-head">
    <h6 class="panel-title">Subscriptions <span class="badge bg-secondary ms-1"><?= $result['total'] ?></span></h6>
  </div>
  <div class="panel-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>User</th><th>Plan</th><th>Amount</th><th>Status</th><th>Starts</th><th>Expires</th><th>Invoice</th></tr>
        </thead>
        <tbody>
          <?php foreach ($result['data'] as $s): ?>
          <tr>
            <td>
              <div class="fw-500"><?= e($s['user_name']) ?></div>
              <div class="text-muted small"><?= e($s['user_email']) ?></div>
            </td>
            <td><span class="badge bg-primary"><?= e($s['plan_name']) ?></span></td>
            <td class="fw-500">₹<?= number_format($s['amount_paid']) ?></td>
            <td><?= statusBadge($s['status']) ?></td>
            <td class="text-muted small"><?= $s['starts_at'] ? date('M j, Y', strtotime($s['starts_at'])) : '—' ?></td>
            <td class="<?= $s['expires_at'] && strtotime($s['expires_at']) < time() ? 'text-danger fw-500' : 'text-muted' ?> small">
              <?= $s['expires_at'] ? date('M j, Y', strtotime($s['expires_at'])) : '—' ?>
            </td>
            <td class="text-muted small font-monospace"><?= e($s['invoice_number'] ?? '—') ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($result['data'])): ?>
            <tr><td colspan="7" class="text-center py-5 text-muted">No subscriptions found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="panel-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
    <small class="text-muted">Showing <?= count($result['data']) ?> of <?= $result['total'] ?></small>
    <?= paginate_links($result['page'], $result['pages'], url('/subscriptions?status='.$status)) ?>
  </div>
</div>
