<?php
$portalType     = 'owner';
$portalTitle    = $portalTitle ?? 'Owner Sign In';
$portalSubtitle = $portalSubtitle ?? 'Enter your venue owner credentials';
$prefillEmail   = $prefillEmail ?? '';
include __DIR__ . '/_auth-split-open.php';
?>

      <?php if ($error = flash('error')): ?>
        <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-circle me-1"></i><?= $error ?></div>
      <?php endif; ?>

      <?php if ($success = flash('success')): ?>
        <div class="alert alert-success py-2 small"><i class="bi bi-check-circle me-1"></i><?= $success ?></div>
      <?php endif; ?>

      <form action="<?= url('/owner/login') ?>" method="POST" autocomplete="off" class="auth-form-fields">
        <?= csrf_field() ?>

        <div class="auth-field">
          <label for="owner-email">Email</label>
          <input type="email" id="owner-email" name="email" class="auth-input" placeholder="owner@email.com"
                 value="<?= e($prefillEmail ?? '') ?>" required autofocus>
        </div>

        <div class="auth-field">
          <label for="owner-password">Password</label>
          <div class="auth-input-pass">
            <input type="password" id="owner-password" name="password" class="auth-input" placeholder="Your password" required>
            <button type="button" class="auth-eye" onclick="toggleOwnerPass('owner-password','owner-pass-eye')" aria-label="Show password">
              <i class="bi bi-eye" id="owner-pass-eye"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="auth-btn auth-btn--owner w-100">
          <i class="bi bi-box-arrow-in-right me-2"></i>Sign In to Dashboard
        </button>
      </form>

      <p class="auth-split-foot text-center text-muted small">
        New here?
        <a href="<?= url('/owner/register') ?>" class="text-success fw-600 text-decoration-none">Create owner account</a>
      </p>

<?php
$isOwner = true;
include __DIR__ . '/_auth-split-close.php';
?>
<script>sessionStorage.removeItem('adminSplashShown');</script>
