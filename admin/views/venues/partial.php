<?php $user = auth(); $role = $user['role']; ?>

<!-- Venues Grid Container -->
<div id="venuesContainer">
<?php if (empty($result['data'])): ?>
<div class="panel text-center py-5">
  <i class="bi bi-building-x" style="font-size: 3rem; color: var(--text-muted); opacity: 0.5;"></i>
  <p class="text-muted mt-3 mb-0">No venues found</p>
</div>
<?php else: ?>
<div class="row g-3 mb-4">
  <?php foreach ($result['data'] as $v): ?>
  <div class="col-12 col-md-6 col-lg-4">
    <div class="venue-card">
      <!-- Card Header with Status -->
      <div class="venue-card__header">
        <div class="d-flex align-items-start justify-content-between">
          <div class="flex-grow-1">
            <h6 class="venue-card__title">
              <a href="<?= url('/venues/'.$v['id']) ?>" class="text-decoration-none">
                <?= e($v['name']) ?>
              </a>
            </h6>
            <div class="venue-card__location">
              <i class="bi bi-geo-alt-fill"></i>
              <?= e($v['city']) ?>
            </div>
          </div>
          <div class="d-flex flex-column gap-1 align-items-end">
            <?= statusBadge($v['verification_status']) ?>
            <?php if ($v['is_verified']): ?>
              <span class="badge bg-success"><i class="bi bi-patch-check-fill"></i></span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- Card Body -->
      <div class="venue-card__body">
        <!-- Owner Info -->
        <div class="venue-card__owner">
          <div class="venue-card__owner-avatar">
            <i class="bi bi-person-circle"></i>
          </div>
          <div class="venue-card__owner-info">
            <div class="venue-card__owner-name"><?= e($v['owner_name'] ?? '—') ?></div>
            <div class="venue-card__owner-email"><?= e($v['owner_email'] ?? '') ?></div>
          </div>
        </div>

        <!-- Sports Tags -->
        <div class="venue-card__sports">
          <?php
          $sports = isset($v['sports']) ? $v['sports'] : '';
          if ($sports):
            $sportList = explode(',', $sports);
            foreach($sportList as $sport):
              $sport = trim($sport);
              if($sport):
          ?>
            <span class="venue-card__sport-tag"><?= e($sport) ?></span>
          <?php 
              endif;
            endforeach;
          else: ?>
            <span class="text-muted small">No sports available</span>
          <?php endif; ?>
        </div>

        <!-- Price Info -->
        <div class="venue-card__price">
          <div class="venue-card__price-label">Starting from</div>
          <div class="venue-card__price-amount">₹<?= number_format($v['price_per_hour']) ?><span>/hr</span></div>
        </div>
      </div>

      <!-- Card Footer with Actions -->
      <div class="venue-card__footer">
        <a href="<?= url('/venues/'.$v['id']) ?>" class="btn btn-sm btn-outline-secondary flex-fill">
          <i class="bi bi-eye me-1"></i>View
        </a>
        <a href="<?= url('/venues/'.$v['id'].'/edit') ?>" class="btn btn-sm btn-outline-primary flex-fill">
          <i class="bi bi-pencil me-1"></i>Edit
        </a>
        <?php if (in_array($role, ['super_admin','admin'])): ?>
          <?php if ($v['verification_status'] === 'pending'): ?>
          <div class="dropdown">
            <button class="btn btn-sm btn-outline-success dropdown-toggle" data-bs-toggle="dropdown">
              <i class="bi bi-three-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <form action="<?= url('/venues/'.$v['id'].'/approve') ?>" method="POST">
                  <?= csrf_field() ?>
                  <button class="dropdown-item text-success">
                    <i class="bi bi-check-lg me-2"></i>Approve
                  </button>
                </form>
              </li>
              <li>
                <button class="dropdown-item text-danger" onclick="rejectVenue(<?= $v['id'] ?>)">
                  <i class="bi bi-x-lg me-2"></i>Reject
                </button>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <form action="<?= url('/venues/'.$v['id'].'/delete') ?>" method="POST"
                  onsubmit="return confirm('Delete this venue permanently?')">
                  <?= csrf_field() ?>
                  <button class="dropdown-item text-danger">
                    <i class="bi bi-trash me-2"></i>Delete
                  </button>
                </form>
              </li>
            </ul>
          </div>
          <?php else: ?>
          <form action="<?= url('/venues/'.$v['id'].'/delete') ?>" method="POST" class="d-inline"
            onsubmit="return confirm('Delete this venue permanently?')">
            <?= csrf_field() ?>
            <button class="btn btn-sm btn-outline-danger" title="Delete">
              <i class="bi bi-trash"></i>
            </button>
          </form>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Pagination -->
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4">
  <small class="text-muted" id="resultCount">Showing <?= count($result['data']) ?> of <?= $result['total'] ?> venues</small>
  <?php 
    $filterParams = http_build_query(array_filter([
      'status' => $filter !== 'all' ? $filter : null,
      'search' => $search ?: null,
      'city' => $city ?: null,
      'verified' => $verified ?: null,
      'sort' => $sortBy !== 'newest' ? $sortBy : null,
    ]));
    $paginationUrl = url('/venues') . ($filterParams ? '?' . $filterParams : '');
  ?>
  <?= paginate_links($result['page'], $result['pages'], $paginationUrl) ?>
</div>
<?php endif; ?>
</div>
