<?php
/**
 * @var array $plan Subscription plan row from SubscriptionController::editPlan
 */
$plan = $plan ?? [];
?>
<div class="row justify-content-center">
<div class="col-lg-8">
<div class="panel">
  <div class="panel-head">
    <h6 class="panel-title">Edit Plan: <?= e($plan['name']) ?></h6>
    <a href="<?= url('/subscriptions/plans') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
  </div>
  <div class="panel-body">
    <form action="<?= url('/subscriptions/plans/'.$plan['id'].'/update') ?>" method="POST" novalidate>
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label-sm">Plan Name *</label>
          <input type="text" name="name" class="form-control" value="<?= e($plan['name']) ?>" required>
        </div>
        <div class="col-md-3">
          <label class="form-label-sm">Price (₹) *</label>
          <input type="number" name="price" class="form-control" value="<?= $plan['price'] ?>" min="0" step="1" required>
        </div>
        <div class="col-md-3">
          <label class="form-label-sm">Platform Fee (%)</label>
          <input type="number" name="platform_fee_percent" class="form-control"
                value="<?= e($plan['platform_fee_percent'] ?? '') ?>" min="0" max="100" step="0.01" placeholder="Negotiable if empty">
        </div>
        <div class="col-12">
          <label class="form-label-sm">Short Description</label>
          <input type="text" name="description" class="form-control" value="<?= e($plan['description'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label-sm">Max Venues</label>
          <input type="number" name="max_venues" class="form-control" value="<?= $plan['max_venues'] ?>" min="1">
        </div>
        <div class="col-md-6">
          <label class="form-label-sm">Billing Cycle *</label>
          <select name="billing_cycle" class="form-select">
            <?php foreach (['monthly','quarterly','yearly','lifetime'] as $bc): ?>
            <option value="<?= $bc ?>" <?= ($plan['billing_cycle'] ?? '')===$bc?'selected':'' ?>><?= ucfirst($bc) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label-sm">Features</label>
          <textarea name="features" class="form-control" rows="5"><?= e($plan['features'] ?? '') ?></textarea>
        </div>
        <div class="col-md-3">
          <label class="form-label-sm">Sort Order</label>
          <input type="number" name="sort_order" class="form-control" value="<?= $plan['sort_order'] ?>" min="0">
        </div>
        <div class="col-md-3">
          <label class="form-label-sm">Active</label>
          <select name="is_active" class="form-select">
            <option value="1" <?= $plan['is_active']?'selected':'' ?>>Yes</option>
            <option value="0" <?= !$plan['is_active']?'selected':'' ?>>No</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label-sm">Featured</label>
          <select name="is_featured" class="form-select">
            <option value="0" <?= !$plan['is_featured']?'selected':'' ?>>No</option>
            <option value="1" <?= $plan['is_featured']?'selected':'' ?>>Yes</option>
          </select>
        </div>
      </div>
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Save Changes</button>
        <a href="<?= url('/subscriptions/plans') ?>" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
</div>
</div>
