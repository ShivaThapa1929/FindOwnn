<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
  <p class="text-muted small mb-0">Messages from the public contact form on <?= e(site_home_url()) ?>contact</p>
</div>

<?php if ($success = ($success ?? null)): ?>
  <div class="alert alert-success py-2 small"><?= e($success) ?></div>
<?php endif; ?>

<div class="panel">
  <div class="panel-head">
    <h6 class="panel-title"><i class="bi bi-envelope-fill me-2"></i>Contact Messages</h6>
    <span class="badge bg-secondary"><?= (int) ($total ?? 0) ?> total</span>
  </div>
  <div class="panel-body p-0">
    <?php if (empty($rows)): ?>
      <div class="text-center py-5 text-muted">
        <i class="bi bi-inbox d-block mb-2" style="font-size:2rem;opacity:0.35;"></i>
        No messages yet.
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th>From</th>
              <th>Subject</th>
              <th>Message</th>
              <th>Date</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $row): ?>
            <tr class="<?= empty($row['is_read']) ? 'table-warning' : '' ?>">
              <td>
                <div class="fw-600 small"><?= e($row['name']) ?></div>
                <div class="text-muted" style="font-size:.72rem;">
                  <a href="mailto:<?= e($row['email']) ?>"><?= e($row['email']) ?></a>
                  <?php if (!empty($row['phone'])): ?>
                    · <?= e($row['phone']) ?>
                  <?php endif; ?>
                </div>
              </td>
              <td class="small"><?= e($row['subject']) ?></td>
              <td class="small text-muted" style="max-width:280px;"><?= e(mb_strimwidth($row['message'], 0, 120, '…')) ?></td>
              <td class="small text-muted"><?= timeAgo($row['created_at']) ?></td>
              <td>
                <?php if (empty($row['is_read'])): ?>
                <form action="<?= url('/contact-messages/' . (int) $row['id'] . '/read') ?>" method="POST" class="d-inline">
                  <?= csrf_field() ?>
                  <button type="submit" class="btn btn-xs btn-outline-success">Mark read</button>
                </form>
                <?php else: ?>
                <span class="badge bg-success">Read</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
  <?php if (!empty($pages) && $pages > 1): ?>
  <div class="panel-footer d-flex justify-content-between align-items-center flex-wrap gap-2">
    <small class="text-muted">Showing <?= count($rows ?? []) ?> of <?= $total ?? 0 ?></small>
    <?= paginate_links($page ?? 1, $pages ?? 1, url('/contact-messages')) ?>
  </div>
  <?php endif; ?>
</div>
