<?php // Sports Index View ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-700 text-white mb-1"><?= e($title) ?></h4>
        <p class="text-muted small mb-0">Manage sport categories shown on the website and app.</p>
    </div>
    <a href="<?= url('/sports/create') ?>" class="btn btn-success">
        <i class="bi bi-plus-lg me-1"></i> Add Sport
    </a>
</div>

<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show mb-4">
        <i class="bi bi-check-circle-fill me-2"></i><?= e($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0" style="background: rgba(255,255,255,0.03); border-radius: 16px;">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0" style="border-collapse: separate; border-spacing: 0;">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.08);">
                        <th class="px-4 py-3 text-muted fw-500" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">Sport</th>
                        <th class="px-4 py-3 text-muted fw-500" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">Slug</th>
                        <th class="px-4 py-3 text-muted fw-500" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">Icon</th>
                        <th class="px-4 py-3 text-muted fw-500" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">Venues</th>
                        <th class="px-4 py-3 text-muted fw-500" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">Courts</th>
                        <th class="px-4 py-3 text-muted fw-500" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">Order</th>
                        <th class="px-4 py-3 text-muted fw-500" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">Status</th>
                        <th class="px-4 py-3 text-muted fw-500" style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.5px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sports)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            <i class="bi bi-trophy display-4 d-block mb-3 opacity-25"></i>
                            No sports found. <a href="<?= url('/sports/create') ?>" class="text-success">Add your first sport</a>.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($sports as $sport): ?>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.04);">
                        <td class="px-4 py-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center justify-content-center rounded-3" 
                                     style="width:40px; height:40px; background: <?= e($sport['color'] ?? '#3887C6') ?>22; border: 1px solid <?= e($sport['color'] ?? '#3887C6') ?>44;">
                                    <i class="<?= e($sport['icon'] ?? 'bi-trophy') ?>" style="color: <?= e($sport['color'] ?? '#3887C6') ?>; font-size: 1.1rem;"></i>
                                </div>
                                <div>
                                    <div class="fw-600 text-white"><?= e($sport['name']) ?></div>
                                    <?php if ($sport['is_featured'] ?? false): ?>
                                    <span class="badge" style="background: rgba(251,191,36,0.15); color: #fbbf24; font-size: 0.65rem;">⭐ Featured</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <code class="text-success" style="font-size: 0.82rem;"><?= e($sport['slug']) ?></code>
                        </td>
                        <td class="px-4 py-3">
                            <code class="text-muted" style="font-size: 0.82rem;"><?= e($sport['icon'] ?? '-') ?></code>
                        </td>
                        <td class="px-4 py-3">
                            <span class="badge bg-primary bg-opacity-25 text-primary"><?= (int)$sport['total_venues'] ?> venues</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="badge bg-info bg-opacity-25 text-info"><?= (int)$sport['total_courts'] ?> courts</span>
                        </td>
                        <td class="px-4 py-3 text-muted"><?= (int)$sport['sort_order'] ?></td>
                        <td class="px-4 py-3">
                            <?php if ($sport['is_active']): ?>
                                <span class="badge" style="background:rgba(56,135,198,0.15);color:#3887C6;font-size:0.75rem;">Active</span>
                            <?php else: ?>
                                <span class="badge" style="background:rgba(239,68,68,0.15);color:#ef4444;font-size:0.75rem;">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3">
                            <div class="d-flex gap-2">
                                <a href="<?= url("/sports/{$sport['id']}/edit") ?>" class="btn btn-sm btn-outline-secondary" title="Edit">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <form method="POST" action="<?= url("/sports/{$sport['id']}/toggle") ?>">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm <?= $sport['is_active'] ? 'btn-outline-warning' : 'btn-outline-success' ?>" title="<?= $sport['is_active'] ? 'Deactivate' : 'Activate' ?>">
                                        <i class="bi bi-<?= $sport['is_active'] ? 'pause-fill' : 'play-fill' ?>"></i>
                                    </button>
                                </form>
                                <?php if (in_array(auth()['role'] ?? '', ['super_admin'])): ?>
                                <form method="POST" action="<?= url("/sports/{$sport['id']}/delete") ?>" onsubmit="return confirm('Delete sport \'<?= e($sport['name']) ?>\'? This cannot be undone.');">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" <?= $sport['total_venues'] > 0 ? 'disabled title="In use by venues"' : '' ?>>
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
