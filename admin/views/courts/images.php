<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h4 class="mb-1"><?= e($court['name']) ?> - Image Gallery</h4>
      <p class="text-muted mb-0">
        <a href="<?= url('/courts?venue_id=' . $court['venue_id']) ?>" class="text-decoration-none">
          <i class="bi bi-arrow-left me-1"></i>Back to <?= e($court['venue_name']) ?> Courts
        </a>
      </p>
    </div>
  </div>
</div>

<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show">
  <?= e($success) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger alert-dismissible fade show">
  <?= e($error) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Court Info Card -->
<div class="panel mb-4">
  <div class="panel-body">
    <div class="row align-items-center">
      <div class="col-md-8">
        <h6 class="mb-2"><?= e($court['name']) ?></h6>
        <div class="d-flex flex-wrap gap-2">
          <span class="badge bg-primary"><?= e($court['sport_name']) ?></span>
          <?php if ($court['court_number']): ?>
          <span class="badge bg-secondary"><?= e($court['court_number']) ?></span>
          <?php endif; ?>
          <?php if ($court['surface_type']): ?>
          <span class="badge bg-info"><?= e($court['surface_type']) ?></span>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <div class="fw-700 text-success" style="font-size:1.2rem;">₹<?= number_format($court['price_per_hour']) ?>/hr</div>
        <div class="text-muted small">
          <?= count($images) ?> image<?= count($images) !== 1 ? 's' : '' ?> uploaded
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Image Gallery Component -->
<?php
  require_once __DIR__ . '/../components/image-gallery.php';
  renderImageGallery('court', $court['id'], $images ?? []);
?>

