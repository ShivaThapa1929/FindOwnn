<?php // Sports Edit View ?>

<div class="mb-4">
    <a href="<?= url('/sports') ?>" class="text-muted text-decoration-none small">
        <i class="bi bi-arrow-left me-1"></i> Back to Sports
    </a>
    <h4 class="fw-700 mt-2 mb-0"><?= e($title) ?></h4>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0" style="background: rgba(255,255,255,0.03); border-radius: 16px; max-width: 680px;">
    <div class="card-body p-4">
        <form method="POST" action="<?= url("/sports/{$sport['id']}/update") ?>">
            <?= csrf_field() ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-500">Sport Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control form-control-lg"
                           value="<?= e($sport['name']) ?>" required autofocus
                           style="background:#FFFFFF; border-color:rgba(56,135,198,0.2); color:#1a2332;">
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-500">Slug <span class="text-danger">*</span></label>
                    <input type="text" name="slug" id="slug" class="form-control form-control-lg"
                           value="<?= e($sport['slug']) ?>"
                           style="background:#FFFFFF; border-color:rgba(56,135,198,0.2); color:#1a2332;">
                </div>
                <div class="col-12">
                    <label class="form-label text-muted small fw-500">Description</label>
                    <textarea name="description" class="form-control" rows="3"
                              style="background:#FFFFFF; border-color:rgba(56,135,198,0.2); color:#1a2332; resize:none;"><?= e($sport['description'] ?? '') ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label text-muted small fw-500">Bootstrap Icon Class</label>
                    <div class="input-group">
                        <span class="input-group-text" id="icon-preview" style="background:rgba(255,255,255,0.05); border-color:rgba(255,255,255,0.1);">
                            <i class="<?= e($sport['icon'] ?? 'bi-trophy') ?> text-success" id="icon-display"></i>
                        </span>
                        <input type="text" name="icon" id="icon-input" class="form-control"
                               value="<?= e($sport['icon'] ?? 'bi-trophy') ?>"
                               style="background:#FFFFFF; border-color:rgba(56,135,198,0.2); color:#1a2332;">
                    </div>
                    <small class="text-muted">See <a href="https://icons.getbootstrap.com" target="_blank" class="text-success">Bootstrap Icons</a></small>
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-500">Brand Color</label>
                    <input type="color" name="color" class="form-control form-control-color w-100"
                           value="<?= e($sport['color'] ?? '#3887C6') ?>"
                           style="background:rgba(255,255,255,0.05); border-color:rgba(255,255,255,0.1); height: 48px;">
                </div>
                <div class="col-md-3">
                    <label class="form-label text-muted small fw-500">Sort Order</label>
                    <input type="number" name="sort_order" class="form-control"
                           value="<?= (int)($sport['sort_order'] ?? 99) ?>" min="1" max="999"
                           style="background:#FFFFFF; border-color:rgba(56,135,198,0.2); color:#1a2332;">
                </div>
                <div class="col-md-6">
                    <div class="d-flex gap-4 mt-2">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active" <?= $sport['is_active'] ? 'checked' : '' ?>>
                            <label class="form-check-label text-muted small" for="is_active">Active (shown on website)</label>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" <?= $sport['is_featured'] ? 'checked' : '' ?>>
                            <label class="form-check-label text-muted small" for="is_featured">Featured</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-3 mt-4">
                <button type="submit" class="btn btn-success px-4">
                    <i class="bi bi-check-lg me-1"></i> Save Changes
                </button>
                <a href="<?= url('/sports') ?>" class="btn btn-outline-secondary px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('icon-input').addEventListener('input', function() {
    document.getElementById('icon-display').className = this.value + ' text-success';
});
</script>
