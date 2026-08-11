<div class="row justify-content-center">
<div class="col-lg-7">
<div class="panel">
  <div class="panel-head">
    <h6 class="panel-title">Edit User: <?= e($userItem['name']) ?></h6>
    <a href="<?= url('/users') ?>" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Back</a>
  </div>
  <div class="panel-body">
    <form action="<?= url('/users/'.$userItem['id'].'/update') ?>" method="POST" novalidate>
      <?= csrf_field() ?>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label-sm">Full Name *</label>
          <input type="text" name="name" class="form-control" value="<?= e($userItem['name']) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label-sm">Email</label>
          <input type="email" class="form-control" value="<?= e($userItem['email']) ?>" disabled>
          <small class="text-muted">Email cannot be changed</small>
        </div>
        <div class="col-md-6">
          <label class="form-label-sm">Phone</label>
          <input type="text" name="phone" class="form-control" value="<?= e($userItem['phone'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label-sm">Role</label>
          <select name="role" class="form-select">
            <option value="venue_owner" <?= $userItem['role']==='venue_owner'?'selected':'' ?>>Venue Owner</option>
            <?php if (isRole('super_admin')): ?>
            <option value="admin" <?= $userItem['role']==='admin'?'selected':'' ?>>Admin</option>
            <option value="super_admin" <?= $userItem['role']==='super_admin'?'selected':'' ?>>Super Admin</option>
            <?php endif; ?>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label-sm">New Password <small class="text-muted">(leave blank to keep current)</small></label>
          <input type="password" name="password" class="form-control" placeholder="New password..." minlength="8">
        </div>
      </div>
      <div class="d-flex gap-2 mt-4">
        <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-lg me-1"></i>Update User</button>
        <a href="<?= url('/users') ?>" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>
</div>
</div>
