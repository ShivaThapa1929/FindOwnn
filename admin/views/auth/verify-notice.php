<?php
$userEmail = $email ?? ($unverifiedUser['email'] ?? ($_SESSION['unverified_email'] ?? ''));
$maskedEmail = '';

if ($userEmail !== '') {
    $parts = explode('@', $userEmail);
    $name = $parts[0];
    $domain = $parts[1] ?? '';
    $maskedName = strlen($name) > 2 ? substr($name, 0, 1) . str_repeat('*', max(1, strlen($name) - 2)) . substr($name, -1) : $name . '***';
    $maskedEmail = $maskedName . '@' . $domain;
}
?>

<div class="auth-card-wrapper animate-on-scroll">
  <div class="glass-card p-4 text-center border-emerald">
    <div class="mb-3">
      <div class="icon-shape bg-success-subtle text-success rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 72px; height: 72px;">
        <i class="bi bi-envelope-check fs-1 text-success"></i>
      </div>
    </div>

    <h2 class="h4 fw-bold text-white mb-2">Check Your Email</h2>
    <p class="text-secondary mb-3 small">
      We've sent a secure verification link to your registered email address:
      <br>
      <strong class="text-white fs-6"><?= e($maskedEmail ?: $userEmail) ?></strong>
    </p>

    <div class="alert alert-warning py-2 small mb-4 text-start">
      <i class="bi bi-exclamation-triangle-fill me-2"></i>
      <strong>Action Required:</strong> Please check your email inbox and click the verification link to activate your Venue Owner account. You cannot access your dashboard until your email is verified.
    </div>

    <!-- Resend Verification Form -->
    <form action="<?= url('/owner/resend-verification') ?>" method="POST" class="mb-2" id="resendForm">
      <?= csrf_field() ?>
      <input type="hidden" name="email" value="<?= e($userEmail) ?>">
      <button type="submit" class="btn btn-premium w-100 py-2" id="resendBtn">
        <i class="bi bi-send me-1"></i> Resend Verification Email
      </button>
    </form>

    <!-- Instant Activation Fallback Form -->
    <form action="<?= url('/owner/direct-verify') ?>" method="POST" class="mb-3">
      <?= csrf_field() ?>
      <input type="hidden" name="user_id" value="<?= (int)($unverifiedUser['id'] ?? ($_SESSION['user']['id'] ?? 0)) ?>">
      <button type="submit" class="btn btn-outline-success w-100 py-2 small fw-bold">
        <i class="bi bi-check-circle-fill me-1"></i> Verify & Activate Account Instantly
      </button>
    </form>

    <div class="d-flex justify-content-between align-items-center gap-2 pt-2 border-top border-secondary border-opacity-25 mt-3">
      <button type="button" class="btn btn-link btn-sm text-secondary p-0 text-decoration-none" data-bs-toggle="collapse" data-bs-target="#changeEmailBox">
        <i class="bi bi-pencil-square me-1"></i> Wrong email? Change it here
      </button>
      <a href="<?= url('/owner/login') ?>" class="text-secondary small text-decoration-none">
        <i class="bi bi-box-arrow-left me-1"></i> Back to Login
      </a>
    </div>

    <!-- Change Email Collapse Form -->
    <div class="collapse text-start mt-3 pt-3 border-top border-secondary border-opacity-25" id="changeEmailBox">
      <h6 class="text-white small fw-bold mb-2">Update Unverified Email Address</h6>
      <form action="<?= url('/owner/change-email') ?>" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="user_id" value="<?= (int)($unverifiedUser['id'] ?? ($_SESSION['user']['id'] ?? 0)) ?>">
        <div class="mb-3">
          <label class="form-label text-secondary small">New Email Address</label>
          <input type="email" name="new_email" class="form-control glass-input" placeholder="newemail@example.com" required>
        </div>
        <button type="submit" class="btn btn-outline-light btn-sm w-100">
          <i class="bi bi-check-circle me-1"></i> Update Email & Send Link
        </button>
      </form>
    </div>

  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const resendBtn = document.getElementById('resendBtn');
    if (resendBtn) {
        let cooldown = 60;
        const lastSent = localStorage.getItem('findownn_last_resend_ts');
        if (lastSent) {
            const elapsed = Math.floor((Date.now() - parseInt(lastSent, 10)) / 1000);
            if (elapsed < 60) {
                cooldown = 60 - elapsed;
                startTimer(cooldown);
            }
        }

        document.getElementById('resendForm')?.addEventListener('submit', function() {
            localStorage.setItem('findownn_last_resend_ts', Date.now().toString());
        });

        function startTimer(seconds) {
            resendBtn.disabled = true;
            const originalText = resendBtn.innerHTML;
            const interval = setInterval(function() {
                resendBtn.innerHTML = '<i class="bi bi-clock-history me-1"></i> Resend available in ' + seconds + 's';
                seconds--;
                if (seconds < 0) {
                    clearInterval(interval);
                    resendBtn.disabled = false;
                    resendBtn.innerHTML = originalText;
                }
            }, 1000);
        }
    }
});
</script>
