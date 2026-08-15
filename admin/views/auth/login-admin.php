<?php
$portalType     = 'admin';
$portalTitle    = $portalTitle ?? 'Admin Sign In';
$portalSubtitle = $portalSubtitle ?? 'Super admin & internal staff only';
include __DIR__ . '/_auth-split-open.php';
?>

      <?php if ($error = flash('error')): ?>
        <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-circle me-1"></i><?= $error ?></div>
      <?php endif; ?>

      <form action="<?= url('/login') ?>" method="POST" autocomplete="off" class="auth-form-fields">
        <?= csrf_field() ?>

        <div class="auth-field">
          <label for="admin-email">Work Email</label>
          <input type="email" id="admin-email" name="email" class="auth-input" placeholder="admin@findownn.com" required autofocus>
        </div>

        <div class="auth-field">
          <label for="admin-password">Password</label>
          <div class="auth-input-pass">
            <input type="password" id="admin-password" name="password" class="auth-input" placeholder="Your password" required>
            <button type="button" class="auth-eye" onclick="toggleOwnerPass('admin-password','admin-pass-eye')" aria-label="Show password">
              <i class="bi bi-eye" id="admin-pass-eye"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="auth-btn auth-btn--owner w-100">
          <i class="bi bi-shield-lock me-2"></i>Sign In to Admin Panel
        </button>
      </form>

      <p class="auth-split-foot text-center text-muted small">
        <a href="<?= e(site_login_url()) ?>" class="text-success fw-600 text-decoration-none">Choose portal</a>
        · <a href="<?= e(site_home_url()) ?>" class="text-secondary text-decoration-none">Website</a>
      </p>

<?php
$isOwner = false;
include __DIR__ . '/_auth-split-close.php';
?>
<script>sessionStorage.removeItem('adminSplashShown');</script>
