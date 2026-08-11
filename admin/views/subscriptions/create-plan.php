<div class="row justify-content-center">
<div class="col-lg-8">
<div class="panel">
  <div class="panel-head">
    <h6 class="panel-title">Create Subscription Plan</h6>
    <a href="<?= url('/subscriptions/plans') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
  </div>
  <div class="panel-body">
    <form action="<?= url('/subscriptions/plans/store') ?>" method="POST" novalidate>
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label-sm">Plan Name *</label>
          <input type="text" name="name" class="form-control" placeholder="e.g. Premium" required>
        </div>
        <div class="col-md-3">
          <label class="form-label-sm">Price (₹) *</label>
          <input type="number" name="price" class="form-control" placeholder="999" min="0" step="1" required>
        </div>
        <div class="col-md-3">
          <label class="form-label-sm">Platform Fee (%)</label>
          <input type="number" name="platform_fee_percent" class="form-control" placeholder="5" min="0" max="100" step="0.01">
          <small class="text-muted">Per successful booking (leave empty for Enterprise/custom)</small>
        </div>
        <div class="col-md-6">
          <label class="form-label-sm">Max Venues</label>
          <input type="number" name="max_venues" class="form-control" value="1" min="1">
        </div>
        <div class="col-md-6">
          <label class="form-label-sm">Billing Cycle *</label>
          <select name="billing_cycle" class="form-select">
            <option value="monthly">Monthly</option>
            <option value="quarterly">Quarterly</option>
            <option value="yearly">Yearly</option>
            <option value="lifetime">Lifetime</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label-sm">Short Description</label>
          <input type="text" name="description" class="form-control" placeholder="Great for growing businesses">
        </div>
        <div class="col-12">
          <label class="form-label-sm">Features <small class="text-muted">(one per line)</small></label>
          <textarea name="features" class="form-control" rows="5" placeholder="Unlimited Venues&#10;Priority Support&#10;Analytics Dashboard"></textarea>
        </div>
        <div class="col-md-3">
          <label class="form-label-sm">Sort Order</label>
          <input type="number" name="sort_order" class="form-control" value="0" min="0">
        </div>
        <div class="col-md-3">
          <label class="form-label-sm">Active</label>
          <select name="is_active" class="form-select">
            <option value="1">Yes</option>
            <option value="0">No</option>
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label-sm">Featured</label>
          <select name="is_featured" class="form-select">
            <option value="0">No</option>
            <option value="1">Yes</option>
          </select>
        </div>
      </div>
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Create Plan</button>
        <a href="<?= url('/subscriptions/plans') ?>" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
</div>
</div>
