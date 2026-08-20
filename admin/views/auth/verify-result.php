<?php
$status = $status ?? 'invalid';
$message = $message ?? '';
$userEmail = $user['email'] ?? '';
?>

<div class="auth-card-wrapper animate-on-scroll">
  <div class="glass-card p-4 text-center border-emerald">
    
    <?php if ($status === 'success'): ?>
      <div class="mb-3">
        <div class="icon-shape bg-success text-dark rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 72px; height: 72px;">
          <i class="bi bi-patch-check-fill fs-1"></i>
        </div>
      </div>

      <h2 class="h4 fw-bold text-white mb-2">Email Verified Successfully!</h2>
      <p class="text-secondary mb-4">
        <?= e($message ?: 'Your Venue Owner account has been verified successfully. You can now access your dashboard and manage your venues.') ?>
      </p>

      <a href="<?= url('/owner/login') ?>" class="btn btn-premium w-100 py-2 fs-6">
        <i class="bi bi-speedometer2 me-1"></i> Go to Owner Dashboard
      </a>

    <?php elseif ($status === 'expired'): ?>
      <div class="mb-3">
        <div class="icon-shape bg-warning-subtle text-warning rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 72px; height: 72px;">
          <i class="bi bi-clock-history fs-1 text-warning"></i>
        </div>
      </div>

      <h2 class="h4 fw-bold text-white mb-2">Verification Link Expired</h2>
      <p class="text-secondary mb-4">
        <?= e($message ?: 'Your verification link has expired. Verification links are valid for 60 minutes.') ?>
      </p>

      <form action="<?= url('/owner/resend-verification') ?>" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="email" value="<?= e($userEmail) ?>">
        <button type="submit" class="btn btn-premium w-100 py-2">
          <i class="bi bi-arrow-clockwise me-1"></i> Send New Verification Link
        </button>
      </form>

    <?php else: ?>
      <div class="mb-3">
        <div class="icon-shape bg-danger-subtle text-danger rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 72px; height: 72px;">
          <i class="bi bi-x-circle fs-1 text-danger"></i>
        </div>
      </div>

      <h2 class="h4 fw-bold text-white mb-2">Invalid Verification Link</h2>
      <p class="text-secondary mb-4">
        <?= e($message ?: 'This verification link is invalid or has already been used. Please request a new verification email.') ?>
      </p>

      <a href="<?= url('/owner/register') ?>" class="btn btn-premium w-100 py-2 mb-2">
        <i class="bi bi-person-plus me-1"></i> Register New Account
      </a>
      <a href="<?= url('/owner/login') ?>" class="btn btn-outline-light w-100 py-2">
        <i class="bi bi-box-arrow-in-right me-1"></i> Back to Login
      </a>
    <?php endif; ?>

  </div>
</div>
