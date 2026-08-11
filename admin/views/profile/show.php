<?php
$u    = $userItem;
$role = $u['role'];
$sub  = !empty($u['sub_status']) ? $u : null;
?>

<div class="row g-4 justify-content-center">
  <div class="col-lg-4">
    <!-- Avatar Card -->
    <div class="panel text-center mb-4">
      <div class="panel-body py-4">
        <div class="avatar-xxl mx-auto mb-3"><?= strtoupper(substr($u['name'],0,1)) ?></div>
        <h5 class="fw-800 mb-1"><?= e($u['name']) ?></h5>
        <p class="text-muted small mb-2"><?= e($u['email']) ?></p>
        <?php
          $map = ['super_admin'=>'danger','admin'=>'primary','venue_owner'=>'success'];
          $c   = $map[$role] ?? 'secondary';
          echo '<span class="badge bg-'.$c.' px-3 py-1">'.ucwords(str_replace('_',' ',$role)).'</span>';
        ?>
        <div class="mt-3"><?= statusBadge($u['status']) ?></div>
      </div>
    </div>

    <!-- Subscription Card -->
    <div class="panel">
      <div class="panel-head"><h6 class="panel-title"><i class="bi bi-credit-card-fill me-2"></i>Subscription</h6></div>
      <div class="panel-body">
        <?php if (!empty($u['plan_name'])): ?>
        <div class="d-flex align-items-center gap-2 p-2 rounded mb-3" style="background:rgba(34,197,94,0.08);">
          <i class="bi bi-patch-check-fill text-success fs-4"></i>
          <div>
            <div class="fw-700 text-success"><?= e($u['plan_name']) ?></div>
            <div class="text-muted" style="font-size:.74rem;">
              <?= !empty($u['expires_at']) ? 'Expires '.date('M j, Y', strtotime($u['expires_at'])) : 'Active' ?>
            </div>
          </div>
        </div>
        <?php else: ?>
        <div class="text-muted small text-center py-2">
          <i class="bi bi-x-circle-fill text-danger d-block mb-1 fs-4"></i>
          No active subscription
        </div>
        <?php endif; ?>
        <a href="<?= url('/subscriptions') ?>" class="btn btn-sm btn-outline-primary w-100">
          View Subscriptions
        </a>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <!-- Edit Profile Form -->
    <div class="panel mb-4">
      <div class="panel-head"><h6 class="panel-title"><i class="bi bi-person-gear me-2"></i>Edit Profile</h6></div>
      <div class="panel-body">
        <?php if ($f = flash('success')): ?>
          <div class="alert alert-success py-2 small mb-3"><i class="bi bi-check-circle me-1"></i><?= e($f) ?></div>
        <?php endif; ?>
        <?php if ($f = flash('error')): ?>
          <div class="alert alert-danger py-2 small mb-3"><i class="bi bi-exclamation-circle me-1"></i><?= e($f) ?></div>
        <?php endif; ?>

        <form action="<?= url('/profile/update') ?>" method="POST" novalidate>
          <?= csrf_field() ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label-sm">Full Name *</label>
              <input type="text" name="name" class="form-control"
                     value="<?= e($u['name']) ?>" required minlength="2" maxlength="120">
            </div>
            <div class="col-md-6">
              <label class="form-label-sm">Email</label>
              <input type="email" class="form-control" value="<?= e($u['email']) ?>" disabled>
              <small class="text-muted">Email cannot be changed</small>
            </div>
            <div class="col-md-6">
              <label class="form-label-sm">Phone</label>
              <input type="text" name="phone" class="form-control"
                     value="<?= e($u['phone'] ?? '') ?>" placeholder="+91 98765 43210" maxlength="15">
            </div>
            <div class="col-md-6">
              <label class="form-label-sm">Role</label>
              <input class="form-control" value="<?= ucwords(str_replace('_',' ',$role)) ?>" disabled>
            </div>
            <div class="col-12"><hr class="border-secondary border-opacity-25 my-1"></div>
            <div class="col-md-6">
              <label class="form-label-sm">New Password <small class="text-muted">(leave blank to keep current)</small></label>
              <input type="password" name="password" class="form-control"
                     placeholder="Min 8 characters" minlength="8" autocomplete="new-password">
            </div>
            <div class="col-md-6">
              <label class="form-label-sm">Confirm New Password</label>
              <input type="password" name="password_confirm" class="form-control"
                     placeholder="Repeat new password" autocomplete="new-password">
            </div>
          </div>
          <div class="mt-4">
            <button type="submit" class="btn btn-primary px-4">
              <i class="bi bi-check-lg me-1"></i>Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Account Details -->
    <div class="panel">
      <div class="panel-head"><h6 class="panel-title"><i class="bi bi-info-circle me-2"></i>Account Info</h6></div>
      <div class="panel-body">
        <div class="row g-3">
          <div class="col-sm-6">
            <div class="info-group">
              <span class="info-label">Account Created</span>
              <span><?= date('M j, Y', strtotime($u['created_at'])) ?></span>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="info-group">
              <span class="info-label">Last Login</span>
              <span><?= !empty($u['last_login_at']) ? timeAgo($u['last_login_at']) : 'Never' ?></span>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="info-group">
              <span class="info-label">Email Verified</span>
              <?= !empty($u['email_verified_at'])
                ? '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Yes</span>'
                : '<span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>No</span>' ?>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="info-group">
              <span class="info-label">Account Status</span>
              <?= statusBadge($u['status']) ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Password match validation
document.querySelector('form').addEventListener('submit', function(e){
  const p1 = this.querySelector('[name="password"]').value;
  const p2 = this.querySelector('[name="password_confirm"]').value;
  if (p1 && p1 !== p2) {
    e.preventDefault();
    showToast('Passwords do not match.', 'danger');
    this.querySelector('[name="password_confirm"]').focus();
  }
});
</script>
