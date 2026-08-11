<div class="row">
  <div class="col-lg-8">

    <form action="<?= url('/settings/save') ?>" method="POST">
      <?= csrf_field() ?>

      <!-- General -->
      <div class="panel mb-4">
        <div class="panel-head"><h6 class="panel-title"><i class="bi bi-gear me-2"></i>General Settings</h6></div>
        <div class="panel-body">
          <div class="row g-3">
            <?php foreach ($settings['general'] ?? [] as $s): ?>
            <div class="col-md-6">
              <label class="form-label-sm"><?= e($s['label']) ?></label>
              <input type="text" name="<?= e($s['key']) ?>" class="form-control"
                     value="<?= e($s['value'] ?? '') ?>">
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Payment -->
      <div class="panel mb-4">
        <div class="panel-head"><h6 class="panel-title"><i class="bi bi-credit-card me-2"></i>Payment Settings</h6></div>
        <div class="panel-body">
          <?php if (!empty($setup_logs)): ?>
          <div class="alert alert-info small mb-3">
            <strong>Setup log:</strong>
            <ul class="mb-0 mt-1"><?php foreach ($setup_logs as $log): ?>
              <li><?= e($log) ?></li>
            <?php endforeach; ?></ul>
          </div>
          <?php endif; ?>
          <div class="alert alert-warning small mb-3">
            <strong>First time?</strong> Run database setup once before entering Razorpay keys.
            <form action="<?= url('/settings/setup-payment') ?>" method="POST" class="d-inline mt-2">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-database-add me-1"></i>Initialize Payment Gateway
              </button>
            </form>
          </div>
          <p class="text-muted small mb-3">
            Webhook URL (add in Razorpay Dashboard):<br>
            <code><?= e(rtrim(str_replace('/admin', '', url('/')), '/') . '/api/v1/payments/webhook') ?></code>
          </p>
          <div class="row g-3">
            <?php foreach ($settings['payment'] ?? [] as $s): ?>
            <div class="col-md-6">
              <label class="form-label-sm"><?= e($s['label']) ?></label>
              <input type="text" name="<?= e($s['key']) ?>" class="form-control"
                     value="<?= e($s['value'] ?? '') ?>"
                     autocomplete="off">
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Security -->
      <div class="panel mb-4">
        <div class="panel-head"><h6 class="panel-title"><i class="bi bi-shield-lock me-2"></i>Security Settings</h6></div>
        <div class="panel-body">
          <div class="row g-3">
            <?php foreach ($settings['security'] ?? [] as $s): ?>
            <div class="col-md-6">
              <label class="form-label-sm"><?= e($s['label']) ?></label>
              <input type="text" name="<?= e($s['key']) ?>" class="form-control"
                     value="<?= e($s['value'] ?? '') ?>">
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Mail -->
      <div class="panel mb-4">
        <div class="panel-head"><h6 class="panel-title"><i class="bi bi-envelope me-2"></i>Mail Settings</h6></div>
        <div class="panel-body">
          <div class="row g-3">
            <?php foreach ($settings['mail'] ?? [] as $s): ?>
            <div class="col-md-6">
              <label class="form-label-sm"><?= e($s['label']) ?></label>
              <input type="text" name="<?= e($s['key']) ?>" class="form-control"
                     value="<?= e($s['value'] ?? '') ?>">
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary px-5">
        <i class="bi bi-check-lg me-2"></i>Save All Settings
      </button>
    </form>

  </div>
  <div class="col-lg-4">

    <!-- DB Backup -->
    <div class="panel mb-4">
      <div class="panel-head"><h6 class="panel-title"><i class="bi bi-database me-2"></i>Database Backup</h6></div>
      <div class="panel-body">
        <p class="text-muted small mb-3">Create a full SQL dump of the database. The backup file is saved to <code>storage/backups/</code>.</p>
        <button class="btn btn-outline-warning w-100" id="backupBtn" onclick="triggerBackup()">
          <i class="bi bi-download me-2"></i>Create Backup
        </button>
        <div id="backupMsg" class="mt-2 small"></div>
      </div>
    </div>

    <!-- System Info -->
    <div class="panel">
      <div class="panel-head"><h6 class="panel-title"><i class="bi bi-info-circle me-2"></i>System Info</h6></div>
      <div class="panel-body">
        <div class="info-group mb-2">
          <span class="info-label">PHP Version</span>
          <span class="badge bg-secondary"><?= PHP_VERSION ?></span>
        </div>
        <div class="info-group mb-2">
          <span class="info-label">Platform</span>
          <span class="text-muted small"><?= PHP_OS ?></span>
        </div>
        <div class="info-group mb-2">
          <span class="info-label">App Version</span>
          <span class="badge bg-primary">v1.0.0</span>
        </div>
        <div class="info-group">
          <span class="info-label">Server Time</span>
          <span class="text-muted small"><?= date('Y-m-d H:i:s') ?></span>
        </div>
      </div>
    </div>

  </div>
</div>

<script>
function triggerBackup() {
  const btn = document.getElementById('backupBtn');
  const msg = document.getElementById('backupMsg');
  btn.disabled = true;
  btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Creating...';

  // Get fresh CSRF token from the page
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || 
                    document.querySelector('[name="_csrf"]')?.value || '';

  fetch('<?= url('/settings/backup') ?>', {
    method: 'POST',
    headers: { 
      'X-CSRF-Token': csrfToken,
      'X-Requested-With': 'XMLHttpRequest',
      'Content-Type': 'application/json'
    }
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      msg.innerHTML = '<span class="text-success"><i class="bi bi-check-circle me-1"></i>Backup created: ' + data.file + '</span>';
    } else {
      msg.innerHTML = '<span class="text-danger"><i class="bi bi-exclamation-circle me-1"></i>' + (data.error || 'Backup failed') + '</span>';
    }
  })
  .catch(err => { 
    console.error(err);
    msg.innerHTML = '<span class="text-danger">Request failed. Check console for details.</span>'; 
  })
  .finally(() => {
    btn.disabled = false;
    btn.innerHTML = '<i class="bi bi-download me-2"></i>Create Backup';
  });
}
</script>
