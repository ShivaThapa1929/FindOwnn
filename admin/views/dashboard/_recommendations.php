<?php
/** @var array $recommendations */
$recommendations = $recommendations ?? [];
if ($recommendations === []) {
    return;
}

$priorityClass = [
    'high'   => 'owner-rec-card--high',
    'medium' => 'owner-rec-card--medium',
    'low'    => 'owner-rec-card--low',
];
?>
<div class="panel mb-3 owner-recommendations-panel" id="ownerRecommendations">
  <div class="panel-head d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h6 class="panel-title mb-0">
      <i class="bi bi-lightbulb-fill me-2 text-warning"></i>
      Recommendations for You
    </h6>
    <span class="badge bg-success-soft"><?= count($recommendations) ?> action<?= count($recommendations) === 1 ? '' : 's' ?></span>
  </div>
  <div class="panel-body p-3">
    <div class="row g-3">
      <?php foreach ($recommendations as $rec): ?>
      <div class="col-md-6 col-xl-4">
        <article class="owner-rec-card <?= e($priorityClass[$rec['priority']] ?? 'owner-rec-card--low') ?>">
          <div class="owner-rec-card__icon">
            <i class="bi bi-<?= e($rec['icon']) ?>"></i>
          </div>
          <div class="owner-rec-card__body">
            <?php if (!empty($rec['badge'])): ?>
            <span class="owner-rec-card__badge"><?= e($rec['badge']) ?></span>
            <?php endif; ?>
            <h6 class="owner-rec-card__title"><?= e($rec['title']) ?></h6>
            <p class="owner-rec-card__text"><?= e($rec['description']) ?></p>
            <a href="<?= e($rec['action_url']) ?>" class="btn btn-sm btn-outline-success owner-rec-card__btn">
              <?= e($rec['action_label']) ?>
              <i class="bi bi-arrow-right ms-1"></i>
            </a>
          </div>
        </article>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
