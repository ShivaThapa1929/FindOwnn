<div class="d-flex justify-content-between align-items-center mb-4">
  <div></div>
  <?php if (isRole('super_admin')): ?>
  <a href="<?= url('/subscriptions/plans/create') ?>" class="btn btn-sm btn-primary">
    <i class="bi bi-plus-lg me-1"></i>Create Plan
  </a>
  <?php endif; ?>
</div>

<div class="row g-4">
  <?php foreach ($plans as $plan): ?>
  <div class="col-md-6 col-lg-3">
    <div class="panel h-100 <?= $plan['is_featured'] ? 'panel--featured' : '' ?>">
      <?php if ($plan['is_featured']): ?>
        <div class="plan-badge">Most Popular</div>
      <?php endif; ?>
      <div class="panel-body text-center">
        <h5 class="fw-800 mb-1"><?= e($plan['name']) ?></h5>
        <div class="plan-price">
          <?php
            $slug = $plan['slug'] ?? '';
            if ($slug === 'enterprise'): ?>
            <span class="plan-amount">Custom</span>
          <?php elseif ($plan['price'] == 0): ?>
            <span class="plan-amount">₹0</span>
            <span class="plan-cycle">/month</span>
          <?php else: ?>
            <span class="plan-currency">₹</span>
            <span class="plan-amount"><?= number_format($plan['price']) ?></span>
            <span class="plan-cycle">/<?= e($plan['billing_cycle']) ?></span>
          <?php endif; ?>
        </div>
        <?php
          $fee = $plan['platform_fee_percent'] ?? null;
          if ($slug === 'enterprise'): ?>
          <p class="small text-success mb-2">Platform fee: Negotiable</p>
        <?php elseif ($fee !== null && $fee !== ''): ?>
          <p class="small text-success mb-2">Platform fee: <?= e(rtrim(rtrim(number_format((float) $fee, 2), '0'), '.')) ?>% per booking</p>
        <?php endif; ?>
        <p class="text-muted small mb-3"><?= e($plan['description']) ?></p>
        <div class="plan-limits mb-3">
          <div class="plan-limit-item"><i class="bi bi-building text-success me-1"></i><?= $plan['max_venues'] == 999 ? 'Unlimited' : $plan['max_venues'] ?> Venues</div>
          <div class="plan-limit-item"><i class="bi bi-calendar3 text-success me-1"></i>Billing: <?= ucfirst(e($plan['billing_cycle'] ?? 'monthly')) ?></div>
        </div>
        <?php if ($plan['features']): ?>
        <ul class="plan-features text-start small">
          <?php foreach (explode("\n", $plan['features']) as $f): ?>
            <?php if (trim($f) && !preg_match('/\b(unlimited\s+)?(images?|time\s*slots?)\b/i', trim($f))): ?>
            <li><i class="bi bi-check-lg text-success me-1"></i><?= e(trim($f)) ?></li>
            <?php endif; ?>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <div class="d-flex gap-2 justify-content-center mt-3">
          <?= statusBadge($plan['is_active'] ? 'active' : 'inactive') ?>
        </div>
        <?php if (isRole('super_admin')): ?>
        <a href="<?= url('/subscriptions/plans/'.$plan['id'].'/edit') ?>" class="btn btn-sm btn-outline-primary w-100 mt-3">
          <i class="bi bi-pencil me-1"></i>Edit Plan
        </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  <?php if (empty($plans)): ?>
    <div class="col-12 text-center py-5 text-muted">No plans created yet.</div>
  <?php endif; ?>
</div>
