<?php
/** @var array $freePlan */
$freePlan = $freePlan ?? null;
$plans    = $plans ?? [];
?>
<div class="row justify-content-center">
<div class="col-lg-7">
<div class="panel">
  <div class="panel-head">
    <h6 class="panel-title">Create User</h6>
    <a href="<?= url('/users') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
  </div>
  <div class="panel-body">
    <form action="<?= url('/users/store') ?>" method="POST" novalidate id="createUserForm">
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label-sm">Full Name *</label>
          <input type="text" name="name" class="form-control" placeholder="John Doe" required>
        </div>
        <div class="col-md-6">
          <label class="form-label-sm">Email *</label>
          <input type="email" name="email" class="form-control" placeholder="user@example.com" required>
        </div>
        <div class="col-md-6">
          <label class="form-label-sm">Phone *</label>
          <input type="text" name="phone" id="userPhone" class="form-control" placeholder="9876543210" maxlength="10" pattern="[6-9][0-9]{9}" required>
        </div>
        <div class="col-md-6">
          <label class="form-label-sm">Role *</label>
          <select name="role" id="userRole" class="form-select" required>
            <option value="venue_owner">Venue Owner</option>
            <?php if (isRole('super_admin')): ?>
            <option value="admin">Admin</option>
            <option value="super_admin">Super Admin</option>
            <?php endif; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label-sm">Password *</label>
          <input type="password" name="password" class="form-control" placeholder="Min 8 characters" required minlength="8">
        </div>
        <div class="col-md-6">
          <label class="form-label-sm">Confirm Password *</label>
          <input type="password" name="password_confirm" class="form-control" placeholder="Repeat password" required>
        </div>
      </div>
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary px-4" id="createUserBtn"><i class="bi bi-person-plus me-1"></i>Create User</button>
        <a href="<?= url('/users') ?>" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
</div>
</div>

<!-- Starter plan confirmation for venue owners -->
<div class="modal fade" id="freePlanModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background:#0f1612;border:1px solid rgba(34,197,94,0.25);">
      <div class="modal-header border-secondary border-opacity-25">
        <h6 class="modal-title text-success"><i class="bi bi-gift me-2"></i>Continue with Starter Plan?</h6>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="small text-muted mb-3">This venue owner will start on the <strong class="text-white">Starter Plan (₹0/month, 5% platform fee)</strong> automatically so they can log in right away.</p>
        <?php if ($freePlan): ?>
        <div class="p-3 rounded" style="background:rgba(34,197,94,0.08);border:1px solid rgba(34,197,94,0.15);">
          <div class="fw-700"><?= e($freePlan['name']) ?> — ₹0/month</div>
          <?php if (!empty($freePlan['platform_fee_percent'])): ?>
          <div class="small text-success mt-1">Platform fee: <?= e(rtrim(rtrim(number_format((float) $freePlan['platform_fee_percent'], 2), '0'), '.')) ?>% per booking</div>
          <?php endif; ?>
          <div class="small text-muted mt-1"><?= e($freePlan['description'] ?? '') ?></div>
          <ul class="small mb-0 mt-2 ps-3">
            <li><?= (int) ($freePlan['max_venues'] ?? 1) ?> venue(s)</li>
            <li>Billing: <?= ucfirst(e($freePlan['billing_cycle'] ?? 'monthly')) ?></li>
          </ul>
        </div>
        <?php endif; ?>
        <p class="small text-muted mt-3 mb-0">You can assign a paid plan later from <strong>Users → View User → Assign Subscription</strong>.</p>
      </div>
      <div class="modal-footer border-secondary border-opacity-25">
        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success btn-sm" id="confirmFreePlanBtn">
          <i class="bi bi-check-lg me-1"></i>Yes, Create with Starter Plan
        </button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const form = document.getElementById('createUserForm');
  const roleSelect = document.getElementById('userRole');
  let freePlanConfirmed = false;
  const freePlanModal = document.getElementById('freePlanModal') ? new bootstrap.Modal(document.getElementById('freePlanModal')) : null;

  form?.addEventListener('submit', function (e) {
    if (roleSelect.value === 'venue_owner' && !freePlanConfirmed) {
      e.preventDefault();
      freePlanModal?.show();
    }
  });

  document.getElementById('confirmFreePlanBtn')?.addEventListener('click', function () {
    freePlanConfirmed = true;
    freePlanModal?.hide();
    form.submit();
  });
})();
</script>
