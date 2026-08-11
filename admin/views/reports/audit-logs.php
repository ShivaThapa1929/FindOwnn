<div class="panel">
  <div class="panel-head">
    <h6 class="panel-title">Audit Logs <span class="badge bg-secondary ms-1"><?= $result['total'] ?></span></h6>
    <a href="<?= url('/reports') ?>" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Back to Reports
    </a>
  </div>
  <div class="panel-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0 table-sm">
        <thead>
          <tr>
            <th>Time</th><th>User</th><th>Role</th><th>Action</th><th>Model</th><th>Record ID</th><th>IP</th><th>Details</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($result['data'] as $log): ?>
          <tr>
            <td class="text-muted" style="font-size:.78rem;white-space:nowrap;">
              <?= date('M j, Y H:i', strtotime($log['created_at'])) ?>
            </td>
            <td>
              <div class="fw-500 small"><?= e($log['user_name'] ?? 'System') ?></div>
            </td>
            <td>
              <?php if ($log['user_role']): ?>
                <?php $map=['super_admin'=>'danger','admin'=>'primary','venue_owner'=>'success'];
                      $c=$map[$log['user_role']]??'secondary'; ?>
                <span class="badge bg-<?= $c ?>" style="font-size:.65rem;"><?= ucwords(str_replace('_',' ',$log['user_role'])) ?></span>
              <?php else: ?><span class="text-muted small">—</span><?php endif; ?>
            </td>
            <td>
              <span class="badge bg-dark" style="font-size:.68rem;font-family:monospace;"><?= e($log['action']) ?></span>
            </td>
            <td class="text-muted small"><?= e($log['model'] ?? '—') ?></td>
            <td class="text-muted small"><?= $log['model_id'] ?: '—' ?></td>
            <td class="text-muted" style="font-size:.72rem;font-family:monospace;"><?= e($log['ip_address'] ?? '—') ?></td>
            <td>
              <?php if ($log['new_values'] && $log['new_values'] !== 'null' && $log['new_values'] !== '{}'): ?>
              <button class="btn btn-xs btn-outline-secondary"
                      onclick="showAuditDetail(<?= htmlspecialchars(json_encode(['old'=>$log['old_values'],'new'=>$log['new_values']]), ENT_QUOTES) ?>)">
                <i class="bi bi-info-circle"></i>
              </button>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($result['data'])): ?>
            <tr><td colspan="8" class="text-center py-5 text-muted">No audit logs found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="panel-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
    <small class="text-muted">Showing <?= count($result['data']) ?> of <?= $result['total'] ?> entries</small>
    <?= paginate_links($result['page'], $result['pages'], url('/reports/audit-logs')) ?>
  </div>
</div>

<!-- Detail Modal -->
<div class="modal fade" id="auditDetailModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title">Audit Log Detail</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <h6 class="text-muted small text-uppercase mb-2">Old Values</h6>
            <pre id="auditOld" class="code-block"></pre>
          </div>
          <div class="col-md-6">
            <h6 class="text-muted small text-uppercase mb-2">New Values</h6>
            <pre id="auditNew" class="code-block"></pre>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function showAuditDetail(data) {
  document.getElementById('auditOld').textContent = JSON.stringify(JSON.parse(data.old || '{}'), null, 2);
  document.getElementById('auditNew').textContent = JSON.stringify(JSON.parse(data.new || '{}'), null, 2);
  new bootstrap.Modal(document.getElementById('auditDetailModal')).show();
}
</script>
