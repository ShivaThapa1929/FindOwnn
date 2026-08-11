<?php
/**
 * @var array               $userItem  User row from UserController::show
 * @var \App\Core\Database  $db
 */
$userItem = $userItem ?? [];
$userId   = (int) ($userItem['id'] ?? 0);

$canSuper = isRole('super_admin');
$canAdmin = in_array(auth()['role'], ['super_admin','admin']);

// Fetch user's subscription + plans for assignment
$activeSub = $db->fetch(
    "SELECT s.*, p.name AS plan_name FROM subscriptions s
    JOIN subscription_plans p ON s.plan_id = p.id
    WHERE s.user_id = ? AND s.status = 'active' AND s.expires_at > NOW()
    ORDER BY s.created_at DESC LIMIT 1",
    [$userId]
);

$allPlans = $db->fetchAll(
    "SELECT id, name, price, billing_cycle FROM subscription_plans WHERE is_active = 1 ORDER BY sort_order"
);

$subHistory = $db->fetchAll(
    "SELECT s.*, p.name AS plan_name FROM subscriptions s
    JOIN subscription_plans p ON s.plan_id = p.id
    WHERE s.user_id = ? ORDER BY s.created_at DESC LIMIT 5",
    [$userId]
);

$venueCount = (int) $db->fetchColumn(
    "SELECT COUNT(*) FROM venues WHERE owner_id = ? AND deleted_at IS NULL",
    [$userId]
);
?>

<div class="row g-4">

  <!-- ── Left: Profile Summary ──────────────────────────────────── -->
  <div class="col-lg-4">
    <div class="panel text-center mb-3">
      <div class="panel-body py-4">
        <div class="avatar-xxl mx-auto mb-3"><?= strtoupper(substr($userItem['name'],0,1)) ?></div>
        <h5 class="fw-800 mb-1"><?= e($userItem['name']) ?></h5>
        <p class="text-muted small mb-2"><?= e($userItem['email']) ?></p>
        <?php
          $map = ['super_admin'=>'danger','admin'=>'primary','venue_owner'=>'success'];
          $c   = $map[$userItem['role']] ?? 'secondary';
          echo '<span class="badge bg-'.$c.' px-3 py-1 mb-2">'.ucwords(str_replace('_',' ',$userItem['role'])).'</span>';
        ?>
        <div class="mt-1"><?= statusBadge($userItem['status']) ?></div>
      </div>
    </div>

    <!-- Key Metrics -->
    <div class="panel mb-3">
      <div class="panel-body">
        <div class="row g-3 text-center">
          <div class="col-6">
            <div class="fw-800 text-primary" style="font-size:1.4rem;"><?= $venueCount ?></div>
            <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;">Venues</div>
          </div>
          <div class="col-6">
            <div class="fw-800 text-orange" style="font-size:1.4rem;">
              <?= $activeSub ? '<i class="bi bi-check-circle-fill text-success" style="font-size:1.2rem;"></i>' : '<i class="bi bi-x-circle-fill text-danger" style="font-size:1.2rem;"></i>' ?>
            </div>
            <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;">Subscription</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="panel">
      <div class="panel-head"><h6 class="panel-title">Actions</h6></div>
      <div class="panel-body d-grid gap-2">
        <a href="<?= url('/users/'.$userItem['id'].'/edit') ?>" class="btn btn-outline-primary btn-sm">
          <i class="bi bi-pencil me-1"></i>Edit User
        </a>
        <form action="<?= url('/users/'.$userItem['id'].'/toggle') ?>" method="POST">
          <?= csrf_field() ?>
          <button class="btn btn-sm w-100 btn-outline-<?= $userItem['status']==='active'?'warning':'success' ?>">
            <i class="bi bi-<?= $userItem['status']==='active'?'pause-circle':'play-circle' ?> me-1"></i>
            <?= $userItem['status']==='active' ? 'Deactivate' : 'Activate' ?>
          </button>
        </form>
        <?php if ($canSuper && $userItem['id'] != auth()['id']): ?>
        <form action="<?= url('/users/'.$userItem['id'].'/delete') ?>" method="POST"
              onsubmit="return confirm('Permanently delete this user? All their data will be soft-deleted.')">
          <?= csrf_field() ?>
          <button class="btn btn-sm btn-outline-danger w-100">
            <i class="bi bi-trash me-1"></i>Delete User
          </button>
        </form>
        <?php endif; ?>
        <a href="<?= url('/users') ?>" class="btn btn-sm btn-outline-secondary">
          <i class="bi bi-arrow-left me-1"></i>Back to Users
        </a>
      </div>
    </div>
  </div>

  <!-- ── Right: Details + Subscription ──────────────────────────── -->
  <div class="col-lg-8">

    <!-- Account Details -->
    <div class="panel mb-4">
      <div class="panel-head"><h6 class="panel-title"><i class="bi bi-person-fill me-2"></i>Account Details</h6></div>
      <div class="panel-body">
        <div class="row g-3">
          <div class="col-sm-6">
            <div class="info-group">
              <span class="info-label">Phone</span>
              <span><?= e($userItem['phone'] ?? '—') ?></span>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="info-group">
              <span class="info-label">Registered</span>
              <span><?= date('M j, Y H:i', strtotime($userItem['created_at'])) ?></span>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="info-group">
              <span class="info-label">Last Login</span>
              <span><?= !empty($userItem['last_login_at']) ? timeAgo($userItem['last_login_at']) : '<span class="text-muted">Never</span>' ?></span>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="info-group">
              <span class="info-label">Email Verified</span>
              <?= !empty($userItem['email_verified_at'])
                ? '<span class="text-success"><i class="bi bi-check-circle-fill me-1"></i>Yes</span>'
                : '<span class="text-danger"><i class="bi bi-x-circle-fill me-1"></i>No</span>' ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Current Subscription -->
    <div class="panel mb-4">
      <div class="panel-head">
        <h6 class="panel-title"><i class="bi bi-credit-card-fill me-2"></i>Subscription</h6>
      </div>
      <div class="panel-body">
        <?php if ($activeSub): ?>
        <div class="d-flex align-items-center gap-3 p-3 rounded mb-3" style="background:rgba(34,197,94,0.07);border:1px solid rgba(34,197,94,0.15);">
          <i class="bi bi-patch-check-fill text-success" style="font-size:2rem;"></i>
          <div>
            <div class="fw-700"><?= e($activeSub['plan_name']) ?> Plan</div>
            <div class="text-muted small">
              Expires: <?= date('M j, Y', strtotime($activeSub['expires_at'])) ?>
              &nbsp;·&nbsp; Paid: ₹<?= number_format($activeSub['amount_paid']) ?>
            </div>
            <div class="text-muted" style="font-size:.72rem;">
              Invoice: <?= e($activeSub['invoice_number'] ?? 'N/A') ?>
            </div>
          </div>
          <?php if ($canAdmin): ?>
          <form action="<?= url('/subscriptions/'.$activeSub['id'].'/cancel') ?>" method="POST"
                class="ms-auto" onsubmit="return confirm('Cancel this subscription?')">
            <?= csrf_field() ?>
            <button class="btn btn-xs btn-outline-danger">Cancel Sub</button>
          </form>
          <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="text-center py-3 text-muted">
          <i class="bi bi-credit-card d-block mb-2" style="font-size:2rem;"></i>
          <p class="small mb-0">No active subscription</p>
        </div>
        <?php endif; ?>

        <!-- Assign Subscription (Super Admin only) -->
        <?php if ($canSuper && $userItem['role'] === 'venue_owner'): ?>
        <hr class="border-secondary border-opacity-25">
        <p class="fw-600 small mb-2"><i class="bi bi-plus-circle me-1 text-success"></i>Assign New Subscription</p>
        <?php if (empty($allPlans)): ?>
          <div class="alert alert-warning small mb-0">
            No active plans in database. Run <code>php admin/database/seeders/DatabaseSeeder.php</code> or create plans under Subscriptions → Plans.
          </div>
        <?php else: ?>
        <form action="<?= url('/users/'.$userItem['id'].'/assign-sub') ?>" method="POST">
          <?= csrf_field() ?>
          <div class="row g-2 align-items-end">
            <div class="col-md-5">
              <label class="form-label-sm">Plan *</label>
              <select name="plan_id" class="form-select" required>
                <option value="">— Select Plan —</option>
                <?php foreach ($allPlans as $p): ?>
                <option value="<?= $p['id'] ?>">
                  <?= e($p['name']) ?> — <?= $p['price'] > 0 ? '₹'.number_format($p['price']) : 'Free' ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-md-3">
              <label class="form-label-sm">Duration (months)</label>
              <input type="number" name="months" class="form-control" value="12" min="1" max="24" required>
            </div>
            <div class="col-md-4">
              <button type="submit" class="btn btn-success w-100">
                <i class="bi bi-check-circle me-1"></i>Assign Plan
              </button>
            </div>
          </div>
          <small class="text-muted">Replaces current active subscription. Assigned at ₹0 (admin grant).</small>
        </form>
        <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Subscription History -->
    <?php if (!empty($subHistory)): ?>
    <div class="panel mb-4">
      <div class="panel-head"><h6 class="panel-title"><i class="bi bi-clock-history me-2"></i>Subscription History</h6></div>
      <div class="panel-body p-0">
        <table class="table table-hover mb-0">
          <thead><tr><th>Plan</th><th>Status</th><th>Starts</th><th>Expires</th><th>Amount</th><th>Invoice</th></tr></thead>
          <tbody>
            <?php foreach ($subHistory as $s): ?>
            <tr>
              <td class="fw-500 small"><?= e($s['plan_name']) ?></td>
              <td><?= statusBadge($s['status']) ?></td>
              <td class="text-muted small"><?= $s['starts_at'] ? date('M j, Y', strtotime($s['starts_at'])) : '—' ?></td>
              <td class="text-muted small <?= !empty($s['expires_at']) && strtotime($s['expires_at']) < time() ? 'text-danger' : '' ?>">
                <?= !empty($s['expires_at']) ? date('M j, Y', strtotime($s['expires_at'])) : '—' ?>
              </td>
              <td class="small">₹<?= number_format($s['amount_paid']) ?></td>
              <td class="font-monospace text-muted" style="font-size:.72rem;"><?= e($s['invoice_number'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

    <!-- Venues owned -->
    <?php if ($userItem['role'] === 'venue_owner' && $venueCount > 0): ?>
    <?php $venues = $db->fetchAll("SELECT id, name, type, city, verification_status, is_verified FROM venues WHERE owner_id = ? AND deleted_at IS NULL ORDER BY created_at DESC LIMIT 5", [$userItem['id']]); ?>
    <div class="panel">
      <div class="panel-head">
        <h6 class="panel-title"><i class="bi bi-building me-2"></i>Venues (<?= $venueCount ?>)</h6>
        <a href="<?= url('/venues?search='.urlencode($userItem['email'])) ?>" class="btn btn-xs btn-outline-secondary">View All</a>
      </div>
      <div class="panel-body p-0">
        <table class="table table-hover mb-0">
          <thead><tr><th>Name</th><th>Type</th><th>City</th><th>Status</th><th>Badge</th></tr></thead>
          <tbody>
            <?php foreach ($venues as $v): ?>
            <tr>
              <td><a href="<?= url('/venues/'.$v['id']) ?>" class="fw-500 small text-decoration-none"><?= e($v['name']) ?></a></td>
              <td><span class="badge bg-dark" style="font-size:.62rem;"><?= ucwords(str_replace('_',' ',$v['type'])) ?></span></td>
              <td class="text-muted small"><?= e($v['city']) ?></td>
              <td><?= statusBadge($v['verification_status']) ?></td>
              <td>
                <?php if ($v['is_verified']): ?>
                  <i class="bi bi-patch-check-fill text-success"></i>
                <?php else: ?>
                  <span class="text-muted small">—</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>
