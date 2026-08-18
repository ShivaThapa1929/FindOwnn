<?php
$portalType     = 'owner';
$authVariant    = 'register';
$portalTitle    = 'Create Owner Account';
$portalSubtitle = 'Register your venue and access the owner dashboard';
include __DIR__ . '/_auth-split-open.php';
?>

      <?php if ($error = flash('error')): ?>
        <div class="alert alert-danger py-2 small"><i class="bi bi-exclamation-circle me-1"></i><?= $error ?></div>
      <?php endif; ?>

      <?php if ($success = flash('success')): ?>
        <div class="alert alert-success py-2 small"><i class="bi bi-check-circle me-1"></i><?= $success ?></div>
      <?php endif; ?>

      <form action="<?= url('/owner/register') ?>" method="POST" autocomplete="off" class="auth-form-fields" id="ownerRegisterForm">
        <?= csrf_field() ?>

        <div class="auth-field">
          <label for="reg-name">Full Name</label>
          <input type="text" id="reg-name" name="name" class="auth-input" placeholder="Your full name" required autofocus>
        </div>

        <div class="auth-field">
          <label for="reg-email">Email</label>
          <input type="email" id="reg-email" name="email" class="auth-input" placeholder="owner@email.com" required>
        </div>

        <div class="auth-field">
          <label for="reg-phone">Mobile</label>
          <input type="tel" id="reg-phone" name="phone" class="auth-input" placeholder="9876543210" pattern="[6-9][0-9]{9}" maxlength="10" required>
          <small class="text-muted">Your contact number for playground bookings and support.</small>
        </div>

        <div class="auth-field">
          <label for="reg-password">Password</label>
          <div class="auth-input-pass">
            <input type="password" id="reg-password" name="password" class="auth-input" placeholder="Min 8 characters" minlength="8" required>
            <button type="button" class="auth-eye" onclick="toggleOwnerPass('reg-password','reg-pass-eye')" aria-label="Show password">
              <i class="bi bi-eye" id="reg-pass-eye"></i>
            </button>
          </div>
        </div>

        <div class="auth-field">
          <label for="reg-confirm">Confirm Password</label>
          <div class="auth-input-pass">
            <input type="password" id="reg-confirm" name="password_confirm" class="auth-input" placeholder="Repeat password" minlength="8" required>
            <button type="button" class="auth-eye" onclick="toggleOwnerPass('reg-confirm','reg-confirm-eye')" aria-label="Show password">
              <i class="bi bi-eye" id="reg-confirm-eye"></i>
            </button>
          </div>
        </div>

        <button type="submit" class="auth-btn auth-btn--owner w-100" id="registerSubmitBtn">
          <i class="bi bi-box-arrow-in-right me-2"></i>Create Owner Account
        </button>
      </form>

      <p class="auth-split-foot text-center text-muted small mb-0">
        <i class="bi bi-gift text-success me-1"></i>Starter plan included — upgrade anytime from your dashboard.
      </p>

      <p class="auth-split-foot text-center text-muted small">
        Already registered?
        <a href="<?= url('/owner/login') ?>" class="text-success fw-600 text-decoration-none">Sign in</a>
      </p>

<?php
$isOwner = true;
include __DIR__ . '/_auth-split-close.php';
?>
<script>sessionStorage.removeItem('adminSplashShown');</script>
