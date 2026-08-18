<?php
/** Player sign-in / register modal — included site-wide when guest */

/** @var string|null $asset_base Base path — may be set by index.php or header.php */
$script_dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$asset_base = $asset_base ?? (($script_dir === '') ? '/' : $script_dir . '/');

if (!empty($site_user)) {
    return;
}

$auth_csrf = site_csrf_token();
$prefill_auth_email = site_normalize_email($_GET['email'] ?? '');
?>

<div class="modal fade auth-modal" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered auth-modal-dialog">
    <div class="modal-content auth-modal-content border-0">

      <button type="button" class="auth-modal-close btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>

      <div class="auth-modal-header text-center">
        <div class="auth-modal-logo">
          <img src="<?= e($asset_base) ?>assets/images/logo.png" alt="Findownn" width="44" height="44">
        </div>
        <h2 class="auth-modal-title" id="authModalLabel">FIND<span class="brand-accent">OWNN</span></h2>
        <p class="auth-modal-subtitle">Book playgrounds. Play more.</p>
      </div>

      <ul class="auth-modal-tabs nav nav-pills" role="tablist">
        <li class="nav-item flex-fill" role="presentation">
          <button class="auth-tab nav-link active w-100" id="auth-login-tab" data-bs-toggle="tab"
                  data-bs-target="#authLoginPane" type="button" role="tab" aria-controls="authLoginPane" aria-selected="true">
            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
          </button>
        </li>
        <li class="nav-item flex-fill" role="presentation">
          <button class="auth-tab nav-link w-100" id="auth-register-tab" data-bs-toggle="tab"
                  data-bs-target="#authRegisterPane" type="button" role="tab" aria-controls="authRegisterPane" aria-selected="false">
            <i class="bi bi-person-plus me-1"></i> Register
          </button>
        </li>
      </ul>

      <div class="auth-modal-body tab-content">
        <div id="authAlert" class="auth-alert d-none" role="alert"></div>

        <div class="tab-pane fade show active" id="authLoginPane" role="tabpanel" aria-labelledby="auth-login-tab" tabindex="0">
          <form id="authLoginForm" autocomplete="off" novalidate>
            <input type="hidden" name="_csrf" value="<?= e($auth_csrf) ?>">

            <div class="mb-3">
              <label class="glass-input-label" for="auth-login-as">Sign in as</label>
              <select id="auth-login-as" name="login_as" class="form-select glass-input">
                <option value="player">Player — Book playgrounds</option>
                <option value="venue_owner">Venue Owner — Manage venues</option>
              </select>
              <p class="text-secondary small mt-2 mb-0">
                Staff? <a href="<?= e(rtrim($asset_base, '/')) ?>/login?role=admin" class="text-success text-decoration-none">Admin login</a>
              </p>
            </div>

            <div id="authOwnerLoginNote" class="alert py-2 px-3 small d-none mb-3" style="background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.25);color:#bbf7d0;">
              Venue owners use a separate dashboard. Continue to owner login to manage your playgrounds.
            </div>

            <div class="mb-3">
              <label class="glass-input-label" for="auth-login-email">Email Address</label>
              <input type="email" id="auth-login-email" name="email" class="form-control glass-input"
                     placeholder="you@email.com" value="<?= e($prefill_auth_email) ?>" required>
            </div>

            <div class="mb-4">
              <label class="glass-input-label" for="auth-login-password">Password</label>
              <div class="auth-pass-wrap">
                <input type="password" id="auth-login-password" name="password" class="form-control glass-input"
                       placeholder="••••••••" required>
                <button type="button" class="auth-pass-toggle" data-target="auth-login-password" aria-label="Show password">
                  <i class="bi bi-eye"></i>
                </button>
              </div>
            </div>

            <button type="submit" class="btn btn-premium w-100 py-3 auth-submit-btn">
              <span class="auth-btn-text"><i class="bi bi-box-arrow-in-right me-2"></i>Sign In</span>
              <span class="auth-btn-loading d-none"><span class="spinner-border spinner-border-sm me-2"></span>Signing in…</span>
            </button>
            <p class="text-center text-secondary small mt-3 mb-0">
              Venue owner?
              <a href="<?= e(rtrim($asset_base, '/')) ?>/admin/owner/login" class="text-success text-decoration-none fw-600">Owner dashboard</a>
            </p>
          </form>
        </div>

        <div class="tab-pane fade" id="authRegisterPane" role="tabpanel" aria-labelledby="auth-register-tab" tabindex="0">
          <form id="authRegisterForm" autocomplete="off" novalidate>
            <input type="hidden" name="_csrf" value="<?= e($auth_csrf) ?>">

            <div class="mb-3">
              <label class="glass-input-label" for="auth-reg-name">Full Name</label>
              <input type="text" id="auth-reg-name" name="name" class="form-control glass-input"
                     placeholder="Your name" required>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-sm-6">
                <label class="glass-input-label" for="auth-reg-email">Email</label>
                <input type="email" id="auth-reg-email" name="email" class="form-control glass-input"
                       placeholder="you@email.com" required>
              </div>
              <div class="col-sm-6">
                <label class="glass-input-label" for="auth-reg-phone">Mobile</label>
                <input type="tel" id="auth-reg-phone" name="phone" class="form-control glass-input"
                       placeholder="9876543210" pattern="[6-9][0-9]{9}" maxlength="10" required>
              </div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-sm-6">
                <label class="glass-input-label" for="auth-reg-password">Password</label>
                <div class="auth-pass-wrap">
                  <input type="password" id="auth-reg-password" name="password" class="form-control glass-input"
                         placeholder="Min 8 characters" minlength="8" required>
                  <button type="button" class="auth-pass-toggle" data-target="auth-reg-password" aria-label="Show password">
                    <i class="bi bi-eye"></i>
                  </button>
                </div>
              </div>
              <div class="col-sm-6">
                <label class="glass-input-label" for="auth-reg-confirm">Confirm Password</label>
                <input type="password" id="auth-reg-confirm" name="password_confirm" class="form-control glass-input"
                       placeholder="Repeat password" minlength="8" required>
              </div>
            </div>

            <div class="form-check mb-4">
              <input class="form-check-input" type="checkbox" name="whatsapp_opt_in" value="1" id="authWaOptIn">
              <label class="form-check-label text-secondary small" for="authWaOptIn">
                Send booking reminders on WhatsApp
              </label>
            </div>

            <button type="submit" class="btn btn-premium w-100 py-3 auth-submit-btn">
              <span class="auth-btn-text"><i class="bi bi-person-plus me-2"></i>Create Account</span>
              <span class="auth-btn-loading d-none"><span class="spinner-border spinner-border-sm me-2"></span>Creating…</span>
            </button>
            <p class="text-center text-secondary small mt-3 mb-0">
              Venue owner?
              <a href="<?= e(rtrim($asset_base, '/')) ?>/admin/owner/register" class="text-success text-decoration-none fw-600">Register as venue owner</a>
            </p>
          </form>
        </div>

        <p class="auth-modal-footer-note text-center text-secondary mb-0">
          Book a court?
          <a href="<?= e($asset_base) ?>" class="text-success text-decoration-none fw-600">Find playgrounds</a>
        </p>
      </div>
    </div>
  </div>
</div>

