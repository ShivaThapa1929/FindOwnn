<?php
/**
 * @var array $venue  Venue record from VenueController::edit()
 * @var array $old    Previous input after validation errors
 * @var array $errors Field validation errors
 */
$venue  = $venue ?? [];
$old    = $old ?? [];
$errors = $errors ?? [];
?>
<div class="row justify-content-center">
<div class="col-lg-9">
<div class="panel">
  <div class="panel-head">
    <h6 class="panel-title">Edit Venue: <?= e($venue['name']) ?></h6>
    <a href="<?= url('/venues/'.$venue['id']) ?>" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Back
    </a>
  </div>
  <div class="panel-body">
    <form action="<?= url('/venues/'.$venue['id'].'/update') ?>" method="POST" novalidate>
      <?= csrf_field() ?>

      <div class="row g-3">
        <div class="col-md-12">
          <label class="form-label-sm">Venue Name *</label>
          <input type="text" name="name" class="form-control" value="<?= e($venue['name']) ?>" required>
        </div>
        <div class="col-12">
          <label class="form-label-sm">Description</label>
          <textarea name="description" class="form-control" rows="3"><?= e($venue['description'] ?? '') ?></textarea>
        </div>
        <div class="col-12">
          <label class="form-label-sm">Full Address</label>
          <input type="text" name="address" class="form-control" value="<?= e($venue['address'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label-sm">City</label>
          <input type="text" name="city" class="form-control" value="<?= e($venue['city'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label-sm">State</label>
          <input type="text" name="state" class="form-control" value="<?= e($venue['state'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label-sm">Pincode</label>
          <input type="text" name="pincode" class="form-control" value="<?= e($venue['pincode'] ?? '') ?>" maxlength="6">
        </div>
        <div class="col-md-8">
          <label class="form-label-sm">Google Maps Link</label>
          <input type="url" name="google_map_link" class="form-control" value="<?= e($venue['google_map_link'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label-sm">Price per Hour (₹)</label>
          <input type="number" name="price_per_hour" class="form-control"
                 value="<?= $venue['price_per_hour'] ?>" min="1" step="1">
        </div>
        <div class="col-12">
          <label class="form-label-sm">Amenities</label>
          <input type="text" name="amenities" class="form-control"
                 data-amenity-input
                 value="<?= is_string($venue['amenities']) ? e(implode(', ', json_decode($venue['amenities'], true) ?? [])) : '' ?>">
          <small class="text-muted">Type amenity and press Enter or comma to add</small>
        </div>
      </div>

      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary px-4">
          <i class="bi bi-check-lg me-1"></i>Save Changes
        </button>
        <a href="<?= url('/venues/'.$venue['id']) ?>" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
</div>
</div>
