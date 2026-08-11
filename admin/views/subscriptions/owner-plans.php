<?php
/** @var array $plans @var array|null $mySub */
$plans = $plans ?? [];
$mySub = $mySub ?? null;
?>

<div class="panel mb-4">
  <div class="panel-head d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h6 class="panel-title"><i class="bi bi-grid-3x3-gap me-2"></i>Subscription Plans</h6>
    <a href="<?= url('/dashboard') ?>" class="btn btn-xs btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Dashboard</a>
  </div>
  <div class="panel-body">
    <?php if ($mySub): ?>
      <div class="alert alert-success small mb-4">
        <i class="bi bi-patch-check-fill me-1"></i>
        Current plan: <strong><?= e($mySub['plan_name'] ?? 'Active') ?></strong>
        <?php if (!empty($mySub['expires_at'])): ?>
          — expires <?= date('M j, Y', strtotime($mySub['expires_at'])) ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <p class="text-muted small mb-3">Players pay through Findownn; settlements are released after the platform fee for your plan. Starter switches instantly; paid plans require admin activation after payment.</p>

    <?php
      $allowUpgrade = true;
      include __DIR__ . '/_plan_cards.php';
    ?>
  </div>
</div>
