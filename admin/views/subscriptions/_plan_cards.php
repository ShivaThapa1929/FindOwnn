<?php
/**
 * Subscription plan cards — shared partial
 * @var array  $plans
 * @var array|null $mySub
 * @var bool   $allowUpgrade
 * @var string $upgradeAction
 */
$plans         = $plans ?? [];
$mySub         = $mySub ?? null;
$allowUpgrade  = $allowUpgrade ?? false;
$upgradeAction = $upgradeAction ?? url('/subscriptions/upgrade');
$currentPlanId = (int) ($mySub['plan_id'] ?? 0);

if (!function_exists('plan_price_label')) {
    function plan_price_label(array $p): string
    {
        $slug  = $p['slug'] ?? '';
        $price = (float) ($p['price'] ?? 0);
        if ($slug === 'enterprise') {
            return 'Custom';
        }
        if ($price <= 0) {
            return '₹0';
        }
        return '₹' . number_format($price);
    }
}

if (!function_exists('plan_platform_fee_label')) {
    function plan_platform_fee_label(array $p): string
    {
        $slug = $p['slug'] ?? '';
        if ($slug === 'enterprise') {
            return 'Negotiable';
        }
        $fee = $p['platform_fee_percent'] ?? null;
        if ($fee === null || $fee === '') {
            return '';
        }
        return rtrim(rtrim(number_format((float) $fee, 2), '0'), '.') . '% per booking';
    }
}
?>

<div id="subscriptionPlans" class="row g-3">
  <?php if (empty($plans)): ?>
    <div class="col-12">
      <div class="alert alert-warning mb-0">
        <i class="bi bi-exclamation-triangle me-1"></i>No subscription plans configured yet. Contact <?= e(site_contact_email()) ?>.
      </div>
    </div>
  <?php else: ?>
    <?php foreach ($plans as $p): ?>
      <?php
        $planId      = (int) $p['id'];
        $slug        = $p['slug'] ?? '';
        $isCurrent   = $planId === $currentPlanId;
        $isFeatured  = !empty($p['is_featured']);
        $price       = (float) ($p['price'] ?? 0);
        $priceLabel  = plan_price_label($p);
        $feeLabel    = plan_platform_fee_label($p);
        $featuresArr = array_filter(array_map('trim', explode("\n", $p['features'] ?? '')));
        $venueTxt    = ((int) ($p['max_venues'] ?? 1) >= 999) ? 'Unlimited venues' : (int) $p['max_venues'] . ' venue' . ((int) $p['max_venues'] > 1 ? 's' : '');
        $isStarter   = in_array($slug, ['starter', 'free'], true);
        $isEnterprise = $slug === 'enterprise';
      ?>
      <div class="col-md-6 col-xl-3">
        <div class="panel h-100 <?= $isFeatured ? 'border border-success' : '' ?>" style="<?= $isFeatured ? 'box-shadow:0 0 20px rgba(34,197,94,0.15);' : '' ?>">
          <div class="panel-body d-flex flex-column h-100">
            <?php if ($isFeatured): ?>
              <span class="badge bg-success align-self-start mb-2">Most Popular</span>
            <?php endif; ?>
            <?php if ($isCurrent): ?>
              <span class="badge bg-primary align-self-start mb-2">Current Plan</span>
            <?php endif; ?>

            <h6 class="fw-800 mb-1"><?= e($p['name']) ?></h6>
            <div class="mb-1">
              <span class="fs-4 fw-800 text-success"><?= $priceLabel ?></span>
              <?php if ($price > 0): ?>
                <span class="text-muted small">/<?= e($p['billing_cycle'] ?? 'month') ?></span>
              <?php elseif ($isStarter): ?>
                <span class="text-muted small">/month</span>
              <?php endif; ?>
            </div>
            <?php if ($feeLabel !== ''): ?>
              <div class="small text-muted mb-2">
                <i class="bi bi-percent me-1"></i>Platform fee: <strong><?= e($feeLabel) ?></strong>
              </div>
            <?php endif; ?>
            <?php if (!empty($p['description'])): ?>
              <p class="text-muted small mb-3"><?= e($p['description']) ?></p>
            <?php endif; ?>

            <ul class="list-unstyled small mb-3 flex-grow-1">
              <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i><?= e($venueTxt) ?></li>
              <?php foreach (array_slice($featuresArr, 0, 6) as $feat): ?>
                <li class="mb-1"><i class="bi bi-check2 text-success me-1"></i><?= e($feat) ?></li>
              <?php endforeach; ?>
              <?php if (count($featuresArr) > 6): ?>
                <li class="mb-1 text-muted">+ <?= count($featuresArr) - 6 ?> more features</li>
              <?php endif; ?>
            </ul>

            <?php if ($allowUpgrade && !$isCurrent): ?>
              <form action="<?= e($upgradeAction) ?>" method="POST"
                    onsubmit="return confirm('<?= $isEnterprise ? 'Send Enterprise enquiry?' : 'Switch to ' . e($p['name']) . ' plan?' ?>')">
                <?= csrf_field() ?>
                <input type="hidden" name="plan_id" value="<?= $planId ?>">
                <button type="submit" class="btn btn-sm w-100 <?= $isFeatured ? 'btn-success' : 'btn-outline-success' ?>">
                  <?php if ($isEnterprise): ?>
                    <i class="bi bi-envelope me-1"></i>Contact Sales
                  <?php elseif ($isStarter): ?>
                    <i class="bi bi-arrow-up-circle me-1"></i>Switch to Starter
                  <?php elseif ($price <= 0): ?>
                    <i class="bi bi-arrow-up-circle me-1"></i>Switch Plan
                  <?php else: ?>
                    <i class="bi bi-send me-1"></i>Request Upgrade
                  <?php endif; ?>
                </button>
              </form>
            <?php elseif ($isCurrent): ?>
              <button type="button" class="btn btn-sm btn-secondary w-100" disabled>Active Plan</button>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
