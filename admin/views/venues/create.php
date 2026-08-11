<?php
$old    = $old    ?? [];
$errors = $errors ?? [];
$old_v  = fn(string $k, mixed $d = '') => $old[$k] ?? $d;
$err    = fn(string $k) => isset($errors[$k])
    ? '<div class="invalid-feedback d-block mt-1">'.e($errors[$k]).'</div>' : '';
?>

<div class="row justify-content-center">
<div class="col-lg-9">
<div class="panel">
  <div class="panel-head">
    <div>
      <h6 class="panel-title">Add New Venue</h6>
      <p class="text-muted small mb-0 mt-1">Fill in the details below. An admin will review and approve within 24 hours.</p>
    </div>
    <a href="<?= url('/venues') ?>" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Back
    </a>
  </div>
  <div class="panel-body">

    <?php if ($f = flash('error')): ?>
      <div class="alert alert-danger py-2 small mb-3"><i class="bi bi-exclamation-circle me-1"></i><?= $f ?></div>
    <?php endif; ?>

    <form action="<?= url('/venues/store') ?>" method="POST" id="venueForm" novalidate>
      <?= csrf_field() ?>

      <!-- ── Basic Info ─────────────────────────────────────────── -->
      <div class="section-divider mb-3"><span class="section-divider-label">Basic Information</span></div>
      <div class="row g-3 mb-4">
        <div class="col-md-8">
          <label class="form-label-sm">Venue Name *</label>
          <input type="text" name="name"
                 class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                 value="<?= e($old_v('name')) ?>"
                 placeholder="e.g. Bhuj Box Arena" maxlength="200" required>
          <?= $err('name') ?>
        </div>
        <div class="col-md-4">
          <label class="form-label-sm">Venue Type *</label>
          <select name="type" class="form-select <?= isset($errors['type']) ? 'is-invalid' : '' ?>" required>
            <option value="">— Select Type —</option>
            <?php foreach (['box_cricket'=>'Box Cricket','pickleball'=>'Pickleball','football'=>'Football','badminton'=>'Badminton','tennis'=>'Tennis','other'=>'Other'] as $val=>$label): ?>
            <option value="<?= $val ?>" <?= $old_v('type')===$val ? 'selected':'' ?>><?= $label ?></option>
            <?php endforeach; ?>
          </select>
          <?= $err('type') ?>
        </div>
        <div class="col-md-4">
          <label class="form-label-sm">Price per Hour (₹) *</label>
          <div class="input-group">
            <span class="input-group-text">₹</span>
            <input type="number" name="price_per_hour"
                   class="form-control <?= isset($errors['price_per_hour']) ? 'is-invalid' : '' ?>"
                   value="<?= e($old_v('price_per_hour')) ?>"
                   placeholder="1000" min="1" max="100000" step="50" required>
          </div>
          <?= $err('price_per_hour') ?>
        </div>
        <div class="col-md-8">
          <label class="form-label-sm">Amenities <small class="text-muted fw-400">(comma-separated)</small></label>
          <input type="text" name="amenities" class="form-control"
                 value="<?= e($old_v('amenities')) ?>"
                 placeholder="Parking, Floodlights, Changing Room, Water, Café">
          <small class="text-muted">Separate each amenity with a comma</small>
        </div>
        <div class="col-12">
          <label class="form-label-sm">Description</label>
          <textarea name="description" class="form-control" rows="3"
                    maxlength="2000"
                    placeholder="Describe your venue — facilities, rules, special features..."><?= e($old_v('description')) ?></textarea>
          <small class="text-muted">Max 2000 characters</small>
        </div>
      </div>

      <!-- ── Location ───────────────────────────────────────────── -->
      <div class="section-divider mb-3"><span class="section-divider-label">Location</span></div>
      <div class="row g-3 mb-4">
        <div class="col-12">
          <label class="form-label-sm">Full Address *</label>
          <input type="text" name="address"
                 class="form-control <?= isset($errors['address']) ? 'is-invalid' : '' ?>"
                 value="<?= e($old_v('address')) ?>"
                 placeholder="Street / Area / Landmark" required>
          <?= $err('address') ?>
        </div>
        <div class="col-md-4">
          <label class="form-label-sm">City *</label>
          <input type="text" name="city"
                 class="form-control <?= isset($errors['city']) ? 'is-invalid' : '' ?>"
                 value="<?= e($old_v('city', 'Bhuj')) ?>"
                 placeholder="Bhuj" required>
          <?= $err('city') ?>
        </div>
        <div class="col-md-4">
          <label class="form-label-sm">State *</label>
          <input type="text" name="state"
                 class="form-control <?= isset($errors['state']) ? 'is-invalid' : '' ?>"
                 value="<?= e($old_v('state', 'Gujarat')) ?>"
                 placeholder="Gujarat" required>
          <?= $err('state') ?>
        </div>
        <div class="col-md-4">
          <label class="form-label-sm">Pincode</label>
          <input type="text" name="pincode"
                 class="form-control <?= isset($errors['pincode']) ? 'is-invalid' : '' ?>"
                 value="<?= e($old_v('pincode')) ?>"
                 placeholder="370001" maxlength="6" pattern="\d{6}">
          <?= $err('pincode') ?>
        </div>
        <div class="col-12">
          <label class="form-label-sm">Google Maps Link <small class="text-muted fw-400">(optional)</small></label>
          <input type="url" name="google_map_link"
                 class="form-control <?= isset($errors['google_map_link']) ? 'is-invalid' : '' ?>"
                 value="<?= e($old_v('google_map_link')) ?>"
                 placeholder="https://maps.google.com/...">
          <?= $err('google_map_link') ?>
          <small class="text-muted">Paste the share link from Google Maps</small>
        </div>
      </div>

      <!-- ── Submit ─────────────────────────────────────────────── -->
      <div class="d-flex gap-3 pt-2">
        <button type="submit" class="btn btn-primary px-5">
          <i class="bi bi-send me-2"></i>Submit for Review
        </button>
        <a href="<?= url('/venues') ?>" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
</div>
</div>
