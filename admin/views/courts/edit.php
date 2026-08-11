<div class="mb-4">
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h4 class="mb-1">Edit Court</h4>
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

<div class="panel">
  <div class="panel-head">
    <h6 class="panel-title">Court Information</h6>
  </div>
  <div class="panel-body">
    <form action="<?= url('/courts/' . $court['id'] . '/update') ?>" method="POST">
      <?= csrf_field() ?>
      
      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label">Court Name *</label>
          <input type="text" name="name" class="form-control" value="<?= e($court['name']) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Court Number</label>
          <input type="text" name="court_number" class="form-control" value="<?= e($court['court_number'] ?? '') ?>" placeholder="e.g., C1, A1">
        </div>
        
        <div class="col-md-6">
          <label class="form-label">Sport *</label>
          <select name="sport_id" class="form-select" required>
            <option value="">Select Sport</option>
            <?php foreach ($sports as $sport): ?>
            <option value="<?= $sport['id'] ?>" <?= $sport['id'] == $court['sport_id'] ? 'selected' : '' ?>>
              <?= e($sport['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        
        <div class="col-md-6">
          <label class="form-label">Price per Hour (₹) *</label>
          <input type="number" name="price_per_hour" class="form-control" value="<?= $court['price_per_hour'] ?>" min="1" step="50" required>
        </div>
        
        <div class="col-12">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="3"><?= e($court['description'] ?? '') ?></textarea>
        </div>
        
        <div class="col-md-6">
          <label class="form-label">Surface Type</label>
          <input type="text" name="surface_type" class="form-control" value="<?= e($court['surface_type'] ?? '') ?>" placeholder="e.g., Artificial Turf, Concrete">
        </div>
        
        <div class="col-md-6">
          <label class="form-label">Dimensions</label>
          <input type="text" name="dimensions" class="form-control" value="<?= e($court['dimensions'] ?? '') ?>" placeholder="e.g., 30x20 feet">
        </div>
        
        <div class="col-md-6">
          <label class="form-label">Capacity (Max Players)</label>
          <input type="number" name="capacity" class="form-control" value="<?= $court['capacity'] ?? 0 ?>" min="1" placeholder="e.g., 12">
        </div>
        
        <div class="col-md-6">
          <label class="form-label">Features</label>
          <div class="form-check">
            <input type="checkbox" name="is_indoor" value="1" class="form-check-input" id="is_indoor" <?= $court['is_indoor'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="is_indoor">Indoor Court</label>
          </div>
          <div class="form-check">
            <input type="checkbox" name="has_lighting" value="1" class="form-check-input" id="has_lighting" <?= $court['has_lighting'] ? 'checked' : '' ?>>
            <label class="form-check-label" for="has_lighting">Has Lighting</label>
          </div>
        </div>
        
        <div class="col-md-6">
          <label class="form-label">Amenities</label>
          <input type="text" name="amenities" class="form-control" value="<?= e($court['amenities_string'] ?? '') ?>" placeholder="Seating, Water, Changing Room">
          <small class="text-muted">Separate with commas</small>
        </div>
        
        <div class="col-md-6">
          <label class="form-label">Equipment Provided</label>
          <input type="text" name="equipment" class="form-control" value="<?= e($court['equipment_string'] ?? '') ?>" placeholder="Balls, Nets, Scoreboard">
          <small class="text-muted">Separate with commas</small>
        </div>
      </div>
      
      <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">
          <i class="bi bi-check-lg me-1"></i>Update Court
        </button>
        <a href="<?= url('/courts?venue_id=' . $court['venue_id']) ?>" class="btn btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
