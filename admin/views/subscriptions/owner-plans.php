<?php
/** @var array $plans @var array|null $mySub */
$plans = $plans ?? [];
$mySub = $mySub ?? null;
?>

<div class="panel mb-4">
  <div class="panel-head d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h6 class="panel-title">Subscription Plans</h6>
    <a href="<?= url('/dashboard') ?>" class="btn btn-xs btn-outline-secondary">Back</a>
  </div>
  <div class="panel-body">
    <?php if ($mySub): ?>
      <p class="small text-muted mb-3">
        Active: <strong><?= e($mySub['plan_name'] ?? '—') ?></strong>
        <?php if (!empty($mySub['expires_at'])): ?>
          · Renews <?= date('M j, Y', strtotime($mySub['expires_at'])) ?>
        <?php endif; ?>
      </p>
    <?php endif; ?>

    <?php
      $showPlanNote = true;
      include __DIR__ . '/_plan_fee_model.php';
      $allowUpgrade = true;
      include __DIR__ . '/_plan_cards.php';
    ?>
  </div>
</div>
