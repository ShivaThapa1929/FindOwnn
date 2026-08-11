<?php
/**
 * @var array  $config
 * @var array  $health
 * @var array  $session
 * @var array  $features
 * @var string $webhookUrl
 * @var string $swaggerUrl
 * @var string $dashboardUrl
 */
$config      = $config ?? [];
$health      = $health ?? ['ok' => false];
$session     = $session ?? [];
$features    = $features ?? [];
$issues      = $issues ?? [];
$hasApiKey   = $hasApiKey ?? false;
$webhookUrl  = $webhookUrl ?? '';
$swaggerUrl  = $swaggerUrl ?? '';
$dashboardUrl= $dashboardUrl ?? '';

$isConnected   = !empty($health['ok']);
$sessionStatus = $session['status'] ?? ($session['state'] ?? 'unknown');
$baseUrl       = $config['openwa_base_url'] ?? '';
$isLocalOpenWa = $baseUrl !== '' && str_contains($baseUrl, 'localhost');
$isLiveSite    = $isLiveSite ?? is_live_site_host();

$groupLabels = [
    'core'           => 'Core Features',
    'messaging'      => 'Messaging',
    'advanced'       => 'Advanced',
    'infrastructure' => 'Infrastructure',
];
?>

<?php if (!empty($success)): ?>
<div class="alert alert-success alert-dismissible fade show">
  <i class="bi bi-check-circle me-1"></i><?= e($success) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="alert alert-danger alert-dismissible fade show">
  <i class="bi bi-x-circle me-1"></i><?= e($error) ?>
  <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if ($isLiveSite && $isLocalOpenWa): ?>
<div class="alert alert-danger mb-4">
  <strong><i class="bi bi-exclamation-octagon me-1"></i>Live site — localhost URL kaam nahi karegi</strong>
  <p class="mb-2 small mt-2">Hostinger aapke PC ka OpenWA nahi dhoondh sakta. Pehle cloud par OpenWA deploy karo, phir neeche <strong>public HTTPS URL</strong> save karo.</p>
  <a class="btn btn-sm btn-danger" href="#liveOpenWaSetup"><i class="bi bi-cloud-upload me-1"></i>Live setup steps</a>
</div>
<?php endif; ?>

<!-- Live deployment guide (Hostinger + Render/Railway) -->
<div class="panel mb-4" id="liveOpenWaSetup">
  <div class="panel-head d-flex justify-content-between align-items-center flex-wrap gap-2">
    <h6 class="panel-title"><i class="bi bi-cloud-check me-2"></i><?= $isLiveSite ? 'Live OpenWA Setup (required on Hostinger)' : 'Live / Production OpenWA' ?></h6>
    <?php if ($isLiveSite): ?>
      <span class="badge bg-warning text-dark">You are on live hosting</span>
    <?php endif; ?>
  </div>
  <div class="panel-body">
    <p class="small text-muted mb-3">
      Findownn PHP Hostinger par chalega; OpenWA alag <strong>cloud server</strong> par chalega (Docker image).
      Dono HTTPS se connect honge.
    </p>
    <div class="row g-3">
      <div class="col-lg-6">
        <div class="p-3 rounded h-100" style="background:rgba(34,197,94,0.06);border:1px solid rgba(34,197,94,0.2);">
          <div class="fw-700 mb-2"><i class="bi bi-1-circle text-success me-1"></i> Render.com (recommended, free tier)</div>
          <ol class="small mb-3 ps-3">
            <li>GitHub par project push karo</li>
            <li><a href="https://dashboard.render.com/" target="_blank" rel="noopener">Render</a> → New → Blueprint → repo connect</li>
            <li>File: <code>admin/deploy/openwa/render.yaml</code></li>
            <li>Deploy URL copy karo → logs se API key</li>
          </ol>
          <a href="https://dashboard.render.com/" target="_blank" rel="noopener" class="btn btn-sm btn-success">Open Render Dashboard</a>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="p-3 rounded h-100" style="background:rgba(59,130,246,0.06);border:1px solid rgba(59,130,246,0.2);">
          <div class="fw-700 mb-2"><i class="bi bi-2-circle text-primary me-1"></i> Railway / VPS (alternative)</div>
          <ul class="small mb-3 ps-3">
            <li><strong>Railway:</strong> folder <code>admin/deploy/openwa</code> deploy → public domain</li>
            <li><strong>VPS:</strong> <code>docker compose up -d</code> in that folder + nginx HTTPS</li>
          </ul>
          <span class="small text-muted">Full guide: <code>admin/deploy/openwa/README-LIVE.md</code></span>
        </div>
      </div>
    </div>
    <div class="alert alert-info small mt-3 mb-0">
      <strong>Step 3 — Findownn admin (live):</strong>
      Base URL = <code>https://YOUR-OPENWA.onrender.com</code> · API Key = logs · Session = <code>findownn</code> ·
      <strong>Save</strong> → <strong>Register Webhook</strong> → OpenWA dashboard → QR scan → <strong>Send Test</strong>.
      <br>Webhook (auto): <code class="user-select-all"><?= e($webhookUrl) ?></code>
    </div>
  </div>
</div>

<?php if (!$isLiveSite && (!$hasApiKey || $baseUrl === '')): ?>
<div class="alert alert-warning mb-4">
  <strong><i class="bi bi-laptop me-1"></i>Local XAMPP testing</strong>
  <ol class="small mb-0 mt-2 ps-3">
    <li class="mb-2">
      <strong>OpenWA PC par start karo</strong> (Docker Desktop ya Node.js):
      <pre class="bg-dark text-light p-2 rounded small mb-1 mt-1">docker run -d --name openwa -p 2785:2785 rmyndharis/openwa</pre>
      <span class="text-muted">Docker nahi hai? → <code>admin/deploy/openwa/README-LIVE.md</code> ya Node: <code>npm run start</code> in OpenWA repo</span>
    </li>
    <li>Logs se API key → Base URL <code>http://localhost:2785</code> → Save → QR scan → Test</li>
  </ol>
</div>
<?php endif; ?>

<!-- Connection Status -->
<div class="row g-4 mb-4">
  <div class="col-lg-8">
    <div class="panel">
      <div class="panel-head d-flex justify-content-between align-items-center">
        <h6 class="panel-title"><i class="bi bi-whatsapp me-2"></i>OpenWA Gateway</h6>
        <span class="badge bg-<?= $isConnected ? 'success' : 'danger' ?>">
          <?= $isConnected ? 'Connected' : 'Disconnected' ?>
        </span>
      </div>
      <div class="panel-body">
        <div class="row g-3">
          <div class="col-md-4">
            <div class="info-group">
              <span class="info-label">Session</span>
              <span class="font-monospace small"><?= e($config['openwa_session_id'] ?? 'findownn') ?></span>
            </div>
          </div>
          <div class="col-md-4">
            <div class="info-group">
              <span class="info-label">Session Status</span>
              <?= statusBadge($sessionStatus) ?>
            </div>
          </div>
          <div class="col-md-4">
            <div class="info-group">
              <span class="info-label">Provider</span>
              <span class="badge bg-success">OpenWA</span>
            </div>
          </div>
        </div>
        <?php if (!$isConnected): ?>
          <div class="alert alert-warning small mt-3 mb-0">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <?php if (!empty($issues)): ?>
              <ul class="mb-0 ps-3">
                <?php foreach ($issues as $issue): ?>
                  <li><?= e($issue) ?></li>
                <?php endforeach; ?>
              </ul>
            <?php else: ?>
              <?= e($health['error'] ?? 'OpenWA not connected') ?>
            <?php endif; ?>
          </div>
        <?php endif; ?>
        <div class="d-flex flex-wrap gap-2 mt-3">
          <?php if ($swaggerUrl): ?>
          <a href="<?= e($swaggerUrl) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-book me-1"></i>Swagger Docs
          </a>
          <?php endif; ?>
          <?php if ($dashboardUrl): ?>
          <a href="<?= e($dashboardUrl) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-window me-1"></i>Web Dashboard
          </a>
          <?php endif; ?>
          <form action="<?= url('/openwa/test') ?>" method="POST" class="d-inline">
            <?= csrf_field() ?>
            <button class="btn btn-sm btn-outline-primary"><i class="bi bi-plug me-1"></i>Test Connection</button>
          </form>
          <form action="<?= url('/openwa/webhook') ?>" method="POST" class="d-inline">
            <?= csrf_field() ?>
            <button class="btn btn-sm btn-outline-info"><i class="bi bi-link-45deg me-1"></i>Register Webhook</button>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="panel h-100">
      <div class="panel-head"><h6 class="panel-title"><i class="bi bi-send me-2"></i>Send Test Message</h6></div>
      <div class="panel-body">
        <form action="<?= url('/openwa/test-message') ?>" method="POST">
          <?= csrf_field() ?>
          <div class="mb-2">
            <label class="form-label-sm">Phone (with country code)</label>
            <input type="text" name="test_phone" class="form-control form-control-sm" placeholder="+919558346768" required>
          </div>
          <div class="mb-3">
            <label class="form-label-sm">Message</label>
            <textarea name="test_message" class="form-control form-control-sm" rows="2">Hello from Findownn OpenWA! 🏆</textarea>
          </div>
          <button type="submit" class="btn btn-success btn-sm w-100">
            <i class="bi bi-whatsapp me-1"></i>Send Test
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php if (!$hasApiKey || $baseUrl === ''): ?>
<div class="alert alert-danger mb-4" id="openwaConfig">
  <strong><i class="bi bi-exclamation-octagon me-1"></i>OpenWA abhi configure nahi hai</strong>
  <p class="small mb-2 mt-2">Pehle <strong>neeche Configuration</strong> mein Base URL + API Key save karo. Live Hostinger par OpenWA alag cloud server par chalega (localhost kaam nahi karega).</p>
  <?php if ($isLiveSite): ?>
  <p class="small mb-2">DB rows missing hon to ek baar setup chalao (CRON_SECRET se):</p>
  <code class="user-select-all small d-block mb-2"><?= e(url('/public/openwa-setup.php') . '?key=YOUR_CRON_SECRET') ?></code>
  <?php endif; ?>
  <a href="#openwaConfiguration" class="btn btn-sm btn-danger"><i class="bi bi-gear me-1"></i>Go to Configuration</a>
</div>
<?php endif; ?>

<!-- Configuration -->
<div class="panel mb-4" id="openwaConfiguration">
  <div class="panel-head"><h6 class="panel-title"><i class="bi bi-gear me-2"></i>OpenWA Configuration</h6></div>
  <div class="panel-body">
    <form action="<?= url('/openwa/save') ?>" method="POST">
      <?= csrf_field() ?>
      <input type="hidden" name="whatsapp_provider" value="openwa">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label-sm">OpenWA Base URL *</label>
          <input type="url" name="openwa_base_url" class="form-control"
                 value="<?= e($config['openwa_base_url'] ?? '') ?>"
                 placeholder="<?= $isLiveSite ? 'https://findownn-openwa.onrender.com' : 'http://localhost:2785' ?>" required>
          <small class="text-muted">
            <?php if ($isLiveSite): ?>
              Live: Render/Railway OpenWA URL only — <strong>not</strong> setup URL, not hostingersite.com
            <?php else: ?>
              Local XAMPP: <code>http://localhost:2785</code> — save before Test Connection
            <?php endif; ?>
          </small>
        </div>
        <div class="col-md-6">
          <label class="form-label-sm">API Key * <?= $hasApiKey ? '<span class="badge bg-success ms-1">Saved</span>' : '<span class="badge bg-danger ms-1">Missing</span>' ?></label>
          <input type="password" name="openwa_api_key" class="form-control"
                 value=""
                 placeholder="<?= $hasApiKey ? '•••••••• (leave blank to keep current key)' : 'owa_k1_... paste from docker logs' ?>"
                 autocomplete="new-password">
          <?php if ($hasApiKey): ?>
          <small class="text-muted">Key is saved. Leave blank unless you want to replace it.</small>
          <?php endif; ?>
        </div>
        <div class="col-md-4">
          <label class="form-label-sm">Session ID</label>
          <input type="text" name="openwa_session_id" class="form-control"
                 value="<?= e($config['openwa_session_id'] ?? 'findownn') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label-sm">Webhook Secret (HMAC)</label>
          <input type="text" name="openwa_webhook_secret" class="form-control"
                 value="<?= e($config['openwa_webhook_secret'] ?? '') ?>"
                 autocomplete="off">
        </div>
        <div class="col-md-4">
          <label class="form-label-sm">Reminder Hours Before</label>
          <input type="number" name="reminder_hours_before" class="form-control"
                 value="<?= e($config['reminder_hours_before'] ?? '24') ?>" min="1" max="72">
        </div>
        <div class="col-12">
          <label class="form-label-sm">Webhook URL (register this on OpenWA)</label>
          <input type="text" class="form-control font-monospace small" readonly value="<?= e($webhookUrl) ?>">
        </div>
        <div class="col-md-4">
          <div class="form-check mt-2">
            <input type="hidden" name="send_booking_confirmation" value="0">
            <input class="form-check-input" type="checkbox" name="send_booking_confirmation" value="1"
                   id="sendConfirm" <?= ($config['send_booking_confirmation'] ?? '1') === '1' ? 'checked' : '' ?>>
            <label class="form-check-label small" for="sendConfirm">Booking Confirmation</label>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-check mt-2">
            <input type="hidden" name="send_payment_confirmation" value="0">
            <input class="form-check-input" type="checkbox" name="send_payment_confirmation" value="1"
                   id="sendPayment" <?= ($config['send_payment_confirmation'] ?? '1') === '1' ? 'checked' : '' ?>>
            <label class="form-check-label small" for="sendPayment">Payment Confirmation</label>
          </div>
        </div>
        <div class="col-md-4">
          <div class="form-check mt-2">
            <input type="hidden" name="send_reminder" value="0">
            <input class="form-check-input" type="checkbox" name="send_reminder" value="1"
                   id="sendReminder" <?= ($config['send_reminder'] ?? '1') === '1' ? 'checked' : '' ?>>
            <label class="form-check-label small" for="sendReminder">Auto Booking Reminders</label>
          </div>
        </div>
      </div>
      <button type="submit" class="btn btn-primary mt-3">
        <i class="bi bi-check-lg me-1"></i>Save OpenWA Settings
      </button>
    </form>
  </div>
</div>

<!-- Feature Matrix -->
<?php foreach ($features as $groupKey => $items): ?>
<div class="panel mb-4">
  <div class="panel-head">
    <h6 class="panel-title"><?= e($groupLabels[$groupKey] ?? ucfirst($groupKey)) ?></h6>
  </div>
  <div class="panel-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead>
          <tr><th>Feature</th><th>Description</th><th class="text-center" style="width:100px;">Status</th></tr>
        </thead>
        <tbody>
          <?php foreach ($items as $f): ?>
          <tr>
            <td class="fw-600 small"><?= e($f['name']) ?></td>
            <td class="text-muted small"><?= e($f['desc']) ?></td>
            <td class="text-center">
              <?php if ($f['status'] === 'active'): ?>
                <i class="bi bi-check-circle-fill text-success" title="Active"></i>
              <?php elseif ($f['status'] === 'offline'): ?>
                <i class="bi bi-x-circle-fill text-danger" title="Server offline"></i>
              <?php else: ?>
                <i class="bi bi-dash-circle text-muted" title="Configure OpenWA"></i>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endforeach; ?>
