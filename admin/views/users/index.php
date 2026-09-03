<!-- Search + Filter -->
<div class="d-flex flex-wrap gap-2 mb-4 align-items-center justify-content-between">
  <div class="d-flex gap-2 flex-wrap">
    <form action="<?= url('/users') ?>" method="GET" class="d-flex gap-2">
      <input type="text" name="search" value="<?= e($search) ?>" class="form-control form-control-sm" placeholder="Search name, email..." style="width:220px;">
      <button class="btn btn-sm btn-primary">Search</button>
      <?php if ($search): ?>
        <a href="<?= url('/users') ?>" class="btn btn-sm btn-outline-secondary">Clear</a>
      <?php endif; ?>
    </form>
    <?php foreach (['all'=>'All','super_admin'=>'Super Admin','admin'=>'Admin','venue_owner'=>'Owners'] as $k=>$label): ?>
    <a href="<?= url('/users?role='.$k) ?>"
       class="btn btn-sm <?= $role===$k ? 'btn-primary' : 'btn-outline-secondary' ?>">
      <?= $label ?>
    </a>
    <?php endforeach; ?>
  </div>
  <a href="<?= url('/users/create') ?>" class="btn btn-sm btn-primary">
    <i class="bi bi-person-plus me-1"></i>Create User
  </a>
</div>

<div class="panel">
  <div class="panel-head">
    <h6 class="panel-title">Users <span class="badge bg-secondary ms-1"><?= $result['total'] ?></span></h6>
  </div>
  <div class="panel-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>#</th><th>User</th><th>Role</th><th>Phone</th><th>Status</th><th>Last Login</th><th>Joined</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($result['data'] as $u): ?>
          <tr>
            <td class="text-muted small"><?= $u['id'] ?></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="avatar-sm"><?= strtoupper(substr($u['name'],0,1)) ?></div>
                <div>
                  <div class="fw-500"><?= e($u['name']) ?></div>
                  <div class="text-muted small"><?= e($u['email']) ?></div>
                </div>
              </div>
            </td>
            <td><?php
              $map = ['super_admin'=>'danger','admin'=>'primary','venue_owner'=>'success'];
              $c   = $map[$u['role']] ?? 'secondary';
              echo '<span class="badge bg-'.$c.'">'.ucwords(str_replace('_',' ',$u['role'])).'</span>';
            ?></td>
            <td class="text-muted small"><?= e($u['phone'] ?? '—') ?></td>
            <td><?= statusBadge($u['status']) ?></td>
            <td class="text-muted small"><?= $u['last_login_at'] ? timeAgo($u['last_login_at']) : 'Never' ?></td>
            <td class="text-muted small"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
            <td>
              <div class="d-flex gap-1">
                <a href="<?= url('/users/'.$u['id']) ?>" class="btn btn-xs btn-outline-secondary">
                  <i class="bi bi-eye"></i>
                </a>
                <a href="<?= url('/users/'.$u['id'].'/edit') ?>" class="btn btn-xs btn-outline-primary">
                  <i class="bi bi-pencil"></i>
                </a>
                <form action="<?= url('/users/'.$u['id'].'/toggle') ?>" method="POST" class="d-inline">
                  <?= csrf_field() ?>
                  <button class="btn btn-xs btn-outline-<?= $u['status']==='active' ? 'warning' : 'success' ?>"
                          title="<?= $u['status']==='active' ? 'Deactivate' : 'Activate' ?>">
                    <i class="bi bi-<?= $u['status']==='active' ? 'pause' : 'play' ?>"></i>
                  </button>
                </form>
                <?php if (isRole('super_admin') && $u['id'] != auth()['id']): ?>
                <form action="<?= url('/users/'.$u['id'].'/delete') ?>" method="POST" class="d-inline"
                      onsubmit="return confirm('Delete user permanently?')">
                  <?= csrf_field() ?>
                  <button class="btn btn-xs btn-outline-danger"><i class="bi bi-trash"></i></button>
                </form>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($result['data'])): ?>
            <tr><td colspan="8" class="text-center py-5 text-muted">No users found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="panel-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
    <small class="text-muted">Showing <?= count($result['data']) ?> of <?= $result['total'] ?></small>
    <?php
      $userParams = array_filter([
        'role'   => (isset($role) && $role !== 'all') ? $role : null,
        'search' => !empty($search) ? $search : null,
      ]);
      $userPaginationUrl = url('/users') . ($userParams ? '?' . http_build_query($userParams) : '');
    ?>
    <?= paginate_links($result['page'], $result['pages'], $userPaginationUrl) ?>
  </div>
</div>
