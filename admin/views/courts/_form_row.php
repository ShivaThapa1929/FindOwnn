<?php
/**
 * Single court row for venue create/edit forms.
 *
 * @var int         $index
 * @var array       $sports
 * @var array|null  $court
 * @var array       $errors
 * @var bool        $removable
 */
$index     = $index ?? 0;
$sports    = $sports ?? [];
$court     = $court ?? [];
$errors    = $errors ?? [];
$removable = $removable ?? ($index > 0);
$prefix    = "courts[{$index}]";
$cid       = 'court-row-' . $index;
$val       = fn(string $k, mixed $d = '') => $court[$k] ?? $d;
$err       = fn(string $k) => isset($errors["courts.{$index}.{$k}"])
    ? '<div class="invalid-feedback d-block mt-1">' . e($errors["courts.{$index}.{$k}"]) . '</div>' : '';

$amenitiesRaw = '';
if (!empty($court['amenities'])) {
    $decoded = is_string($court['amenities']) ? json_decode($court['amenities'], true) : $court['amenities'];
    $amenitiesRaw = is_array($decoded) ? implode(', ', $decoded) : '';
}
$equipmentRaw = '';
if (!empty($court['equipment_provided'])) {
    $decoded = is_string($court['equipment_provided']) ? json_decode($court['equipment_provided'], true) : $court['equipment_provided'];
    $equipmentRaw = is_array($decoded) ? implode(', ', $decoded) : '';
}
?>
<div class="court-form-row border rounded-3 p-3 mb-3 bg-light bg-opacity-10" id="<?= e($cid) ?>" data-court-index="<?= (int) $index ?>">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h6 class="mb-0 text-white-50">
      <i class="bi bi-grid-3x3-gap me-1 text-success"></i>
      Court <?= (int) $index + 1 ?>
      <?php if (!empty($court['id'])): ?>
        <span class="badge bg-secondary ms-1">Existing</span>
      <?php endif; ?>
    </h6>
    <?php if ($removable): ?>
    <button type="button" class="btn btn-sm btn-outline-danger court-row-remove" data-target="<?= e($cid) ?>">
      <i class="bi bi-trash"></i> Remove
    </button>
    <?php endif; ?>
  </div>

  <?php if (!empty($court['id'])): ?>
  <input type="hidden" name="<?= e($prefix) ?>[id]" value="<?= (int) $court['id'] ?>">
  <?php endif; ?>

  <div class="row g-3">
    <div class="col-md-8">
      <label class="form-label-sm">Court Name *</label>
      <input type="text" name="<?= e($prefix) ?>[name]"
             class="form-control <?= isset($errors["courts.{$index}.name"]) ? 'is-invalid' : '' ?>"
             value="<?= e($val('name')) ?>"
             placeholder="e.g. Court A, Main Court" required>
      <?= $err('name') ?>
    </div>
    <div class="col-md-4">
      <label class="form-label-sm">Court Number</label>
      <input type="text" name="<?= e($prefix) ?>[court_number]" class="form-control"
             value="<?= e($val('court_number')) ?>" placeholder="C1, A1">
    </div>

    <div class="col-md-6">
      <label class="form-label-sm">Sport *</label>
      <select name="<?= e($prefix) ?>[sport_id]"
              class="form-select court-sport-select <?= isset($errors["courts.{$index}.sport_id"]) ? 'is-invalid' : '' ?>"
              required>
        <option value="">— Select Sport —</option>
        <?php foreach ($sports as $sport): ?>
        <option value="<?= (int) $sport['id'] ?>"
                data-slug="<?= e($sport['slug']) ?>"
                <?= (string) $val('sport_id') === (string) $sport['id'] ? 'selected' : '' ?>>
          <?= e($sport['name']) ?>
        </option>
        <?php endforeach; ?>
      </select>
      <?= $err('sport_id') ?>
    </div>

    <div class="col-md-6">
      <label class="form-label-sm">Price per Hour (₹) *</label>
      <div class="input-group">
        <span class="input-group-text">₹</span>
        <input type="number" name="<?= e($prefix) ?>[price_per_hour]"
               class="form-control court-price-input <?= isset($errors["courts.{$index}.price_per_hour"]) ? 'is-invalid' : '' ?>"
               value="<?= e($val('price_per_hour')) ?>"
               min="1" max="100000" step="1" required>
      </div>
      <?= $err('price_per_hour') ?>
    </div>

    <div class="col-12">
      <label class="form-label-sm">Description</label>
      <textarea name="<?= e($prefix) ?>[description]" class="form-control" rows="2"
                placeholder="Brief description of the court"><?= e($val('description')) ?></textarea>
    </div>

    <div class="col-md-4">
      <label class="form-label-sm">Surface Type</label>
      <input type="text" name="<?= e($prefix) ?>[surface_type]" class="form-control"
             value="<?= e($val('surface_type')) ?>" placeholder="Artificial Turf, Concrete">
    </div>
    <div class="col-md-4">
      <label class="form-label-sm">Dimensions</label>
      <input type="text" name="<?= e($prefix) ?>[dimensions]" class="form-control"
             value="<?= e($val('dimensions')) ?>" placeholder="30x20 feet">
    </div>
    <div class="col-md-4">
      <label class="form-label-sm">Capacity (Max Players)</label>
      <input type="number" name="<?= e($prefix) ?>[capacity]" class="form-control"
             value="<?= e($val('capacity')) ?>" min="1" placeholder="12">
    </div>

    <div class="col-md-6">
      <label class="form-label-sm d-block">Features</label>
      <div class="form-check">
        <input type="checkbox" name="<?= e($prefix) ?>[is_indoor]" value="1"
               class="form-check-input" id="<?= e($cid) ?>-indoor"
               <?= !empty($court['is_indoor']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="<?= e($cid) ?>-indoor">Indoor Court</label>
      </div>
      <div class="form-check">
        <input type="checkbox" name="<?= e($prefix) ?>[has_lighting]" value="1"
               class="form-check-input" id="<?= e($cid) ?>-lighting"
               <?= !isset($court['has_lighting']) || !empty($court['has_lighting']) ? 'checked' : '' ?>>
        <label class="form-check-label" for="<?= e($cid) ?>-lighting">Has Lighting</label>
      </div>
    </div>

    <div class="col-md-6">
      <label class="form-label-sm">Court Amenities</label>
      <input type="text" name="<?= e($prefix) ?>[amenities]" class="form-control"
             value="<?= e($amenitiesRaw) ?>" placeholder="Seating, Water, Changing Room">
      <small class="text-muted">Separate with commas</small>
    </div>

    <div class="col-md-6">
      <label class="form-label-sm">Equipment Provided</label>
      <input type="text" name="<?= e($prefix) ?>[equipment]" class="form-control"
             value="<?= e($equipmentRaw) ?>" placeholder="Balls, Nets, Scoreboard">
      <small class="text-muted">Separate with commas</small>
    </div>
  </div>
</div>
