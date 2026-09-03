<?php
$status = $status ?? 'invalid';
$message = $message ?? '';
$userEmail = $user['email'] ?? '';
?>

<div class="auth-card-wrapper animate-on-scroll d-flex align-items-center justify-content-center min-vh-100 py-4 px-3">
  <div class="verify-card position-relative overflow-hidden w-100" style="max-width: 520px; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 24px; box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7);">
    
    <!-- Top Accent Line -->
    <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: <?= $status === 'success' ? 'linear-gradient(90deg, #10b981, #059669)' : ($status === 'expired' ? 'linear-gradient(90deg, #f59e0b, #d97706)' : 'linear-gradient(90deg, #ef4444, #dc2626)') ?>;"></div>

    <div class="p-4 p-sm-5 text-center">
      
    <?php if ($status === 'success'): ?>
      <!-- Success Icon -->
      <div class="position-relative d-inline-block mb-4">
        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px; background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.4); box-shadow: 0 0 30px rgba(16, 185, 129, 0.3);">
          <i class="bi bi-patch-check-fill fs-1" style="color: #34d399; filter: drop-shadow(0 2px 8px rgba(52, 211, 153, 0.5));"></i>
        </div>
      </div>

      <h2 class="h3 fw-bold text-white mb-2" style="font-family: 'Plus Jakarta Sans', sans-serif;">Email Verified Successfully!</h2>
      <p class="text-secondary mb-4 small" style="font-size: 0.92rem; line-height: 1.5;">
        <?= e($message ?: 'Your Venue Owner account has been verified successfully. You can now access your dashboard and start managing your venues.') ?>
      </p>

      <a href="<?= url('/owner/login') ?>" class="btn btn-emerald-gradient w-100 py-2.5 fs-6 fw-bold rounded-3 shadow-sm d-inline-flex align-items-center justify-content-center gap-2" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; text-decoration: none;">
        <i class="bi bi-speedometer2"></i>
        <span>Go to Owner Dashboard</span>
      </a>

    <?php elseif ($status === 'expired'): ?>
      <!-- Expired Icon -->
      <div class="position-relative d-inline-block mb-4">
        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px; background: rgba(245, 158, 11, 0.2); border: 1px solid rgba(245, 158, 11, 0.4); box-shadow: 0 0 30px rgba(245, 158, 11, 0.3);">
          <i class="bi bi-clock-history fs-1" style="color: #fbbf24; filter: drop-shadow(0 2px 8px rgba(251, 191, 36, 0.5));"></i>
        </div>
      </div>

      <h2 class="h3 fw-bold text-white mb-2" style="font-family: 'Plus Jakarta Sans', sans-serif;">Verification Link Expired</h2>
      <p class="text-secondary mb-4 small" style="font-size: 0.92rem; line-height: 1.5;">
        <?= e($message ?: 'Your verification link has expired. Security verification links are valid for 60 minutes.') ?>
      </p>

      <form action="<?= url('/owner/resend-verification') ?>" method="POST">
        <?= csrf_field() ?>
        <input type="hidden" name="email" value="<?= e($userEmail) ?>">
        <button type="submit" class="btn btn-warning w-100 py-2.5 fs-6 fw-bold rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2" style="background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; border: none;">
          <i class="bi bi-send-fill"></i>
          <span>Send New Verification Link</span>
        </button>
      </form>

    <?php else: ?>
      <!-- Invalid Icon -->
      <div class="position-relative d-inline-block mb-4">
        <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px; background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.4); box-shadow: 0 0 30px rgba(239, 68, 68, 0.3);">
          <i class="bi bi-shield-x fs-1" style="color: #f87171; filter: drop-shadow(0 2px 8px rgba(248, 113, 113, 0.5));"></i>
        </div>
      </div>

      <h2 class="h3 fw-bold text-white mb-2" style="font-family: 'Plus Jakarta Sans', sans-serif;">Verification Link Information</h2>
      <p class="text-secondary mb-4 small" style="font-size: 0.92rem; line-height: 1.5;">
        <?= e($message ?: 'This verification link is invalid or has already been used to activate your account. If your account is already active, please sign in directly.') ?>
      </p>

      <a href="<?= url('/owner/login' . ($userEmail ? '?email=' . urlencode($userEmail) : '')) ?>" class="btn btn-emerald-gradient w-100 py-2.5 fs-6 fw-bold rounded-3 shadow-sm mb-3 d-inline-flex align-items-center justify-content-center gap-2" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; text-decoration: none;">
        <i class="bi bi-box-arrow-in-right"></i>
        <span>Sign In to Owner Dashboard</span>
      </a>

      <!-- Resend Verification Form -->
      <form action="<?= url('/owner/resend-verification') ?>" method="POST" class="mb-3">
        <?= csrf_field() ?>
        <div class="input-group">
          <input type="email" name="email" class="form-control glass-input text-white small" placeholder="Enter your email" value="<?= e($userEmail) ?>" required style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 10px 0 0 10px;">
          <button type="submit" class="btn btn-outline-warning small fw-semibold" style="border-radius: 0 10px 10px 0;">
            <i class="bi bi-send me-1"></i> Resend Link
          </button>
        </div>
      </form>

      <div class="d-flex justify-content-between align-items-center gap-2 pt-3 border-top border-secondary border-opacity-25">
        <a href="<?= url('/owner/register') ?>" class="text-secondary small text-decoration-none hover-white" style="font-size: 0.85rem;">
          <i class="bi bi-person-plus me-1"></i> Create New Account
        </a>
        <a href="<?= url('/owner/login') ?>" class="text-secondary small text-decoration-none hover-white" style="font-size: 0.85rem;">
          <i class="bi bi-arrow-left me-1"></i> Back to Login
        </a>
      </div>
    <?php endif; ?>

    </div>
  </div>
</div>
