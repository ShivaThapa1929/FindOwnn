<div class="panel">
  <div class="panel-head">
    <h6 class="panel-title">Activity Logs <span class="badge bg-secondary ms-1"><?= $result['total'] ?></span></h6>
    <a href="<?= url('/reports') ?>" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Back to Reports
    </a>
  </div>
  <div class="panel-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0 table-sm">
        <thead>
          <tr>
            <th>Time</th><th>User</th><th>Role</th><th>Type</th><th>Description</th><th>Subject</th><th>IP</th>
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
                <?php $map=['super_admin'=>'danger','admin'=>'primary','venue_owner'=>'success','player'=>'info'];
                      $c=$map[$log['user_role']]??'secondary'; ?>
                <span class="badge bg-<?= $c ?>" style="font-size:.65rem;"><?= ucwords(str_replace('_',' ',$log['user_role'])) ?></span>
              <?php else: ?><span class="text-muted small">—</span><?php endif; ?>
            </td>
            <td>
              <?php
                $typeColors = ['auth'=>'info','info'=>'secondary','warning'=>'warning','error'=>'danger','success'=>'success'];
                $tc = $typeColors[$log['type'] ?? 'info'] ?? 'secondary';
              ?>
              <span class="badge bg-<?= $tc ?>" style="font-size:.68rem;"><?= e($log['type'] ?? 'info') ?></span>
            </td>
            <td class="small"><?= e($log['description'] ?? '—') ?></td>
            <td class="text-muted small">
              <?= e($log['subject_type'] ?? '—') ?>
              <?php if ($log['subject_id']): ?><span class="text-muted">#<?= $log['subject_id'] ?></span><?php endif; ?>
            </td>
            <td class="text-muted" style="font-size:.72rem;font-family:monospace;"><?= e($log['ip_address'] ?? '—') ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if (empty($result['data'])): ?>
            <tr><td colspan="7" class="text-center py-5 text-muted">No activity logs found</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="panel-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
    <small class="text-muted">Showing <?= count($result['data']) ?> of <?= $result['total'] ?> entries</small>
    <?= paginate_links($result['page'], $result['pages'], url('/reports/activity')) ?>
  </div>
</div>
