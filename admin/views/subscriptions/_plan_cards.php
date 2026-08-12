<?php
/**
 * Subscription plan cards — minimal shared partial
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
            return 'Negotiable platform fee';
        }
        $fee = $p['platform_fee_percent'] ?? null;
        if ($fee === null || $fee === '') {
            return '';
        }
        return rtrim(rtrim(number_format((float) $fee, 2), '0'), '.') . '% platform fee';
    }
}

if (!function_exists('plan_highlight_features')) {
    function plan_highlight_features(array $p, int $limit = 5): array
    {
        $features = array_filter(array_map('trim', explode("\n", $p['features'] ?? '')));
        $out = [];
        foreach ($features as $feat) {
            if (preg_match('/^everything in /i', $feat)) {
                continue;
            }
            $out[] = $feat;
            if (count($out) >= $limit) {
                break;
            }
        }
        return $out;
    }
}
?>

<div id="subscriptionPlans" class="plan-grid">
  <?php if (empty($plans)): ?>
    <div class="alert alert-warning mb-0">
      No plans configured yet. Contact <?= e(site_contact_email()) ?>.
    </div>
  <?php else: ?>
    <?php foreach ($plans as $p): ?>
      <?php
        $planId     = (int) $p['id'];
        $slug       = $p['slug'] ?? '';
        $isCurrent  = $planId === $currentPlanId;
        $isFeatured = !empty($p['is_featured']);
        $price      = (float) ($p['price'] ?? 0);
        $priceLabel = plan_price_label($p);
        $feeLabel   = plan_platform_fee_label($p);
        $highlights = plan_highlight_features($p);
        $isEnterprise = $slug === 'enterprise';
        $cardClass  = 'plan-card';
        if ($isCurrent) {
            $cardClass .= ' plan-card--current';
        } elseif ($isFeatured) {
            $cardClass .= ' plan-card--featured';
        }
      ?>
      <div class="<?= $cardClass ?>">
        <?php if ($isCurrent): ?>
          <div class="plan-card__tag">Current plan</div>
        <?php elseif ($isFeatured): ?>
          <div class="plan-card__tag">Most popular</div>
        <?php endif; ?>

        <h3 class="plan-card__name"><?= e($p['name']) ?></h3>

        <div class="plan-card__price">
          <?= e($priceLabel) ?>
          <?php if (!$isEnterprise): ?>
            <small>/month</small>
          <?php endif; ?>
        </div>

        <?php if ($feeLabel !== ''): ?>
          <div class="plan-card__fee"><?= e($feeLabel) ?></div>
        <?php endif; ?>

        <?php if (!empty($highlights)): ?>
          <ul class="plan-card__features">
            <?php foreach ($highlights as $feat): ?>
              <li><?= e($feat) ?></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>

        <div class="plan-card__btn">
          <?php if ($allowUpgrade && !$isCurrent): ?>
            <form action="<?= e($upgradeAction) ?>" method="POST"
                  onsubmit="return confirm('<?= $isEnterprise ? 'Send Enterprise enquiry?' : 'Switch to ' . e($p['name']) . '?' ?>')">
              <?= csrf_field() ?>
              <input type="hidden" name="plan_id" value="<?= $planId ?>">
              <button type="submit" class="btn btn-sm w-100 <?= $isFeatured ? 'btn-success' : 'btn-outline-secondary' ?>">
                <?= $isEnterprise ? 'Contact sales' : 'Choose plan' ?>
              </button>
            </form>
          <?php elseif ($isCurrent): ?>
            <button type="button" class="btn btn-sm btn-secondary w-100" disabled>Active</button>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
