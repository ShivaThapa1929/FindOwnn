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

<div class="auth-card-wrapper animate-on-scroll d-flex align-items-center justify-content-center min-vh-100 py-4 px-3">
  <div class="verify-card position-relative overflow-hidden w-100" style="max-width: 520px; background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: 1px solid rgba(255, 255, 255, 0.12); border-radius: 24px; box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.7);">
    
    <!-- Top Glowing Accent Line -->
    <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #10b981, #3b82f6, #10b981); background-size: 200% 100%; animation: gradientGlow 3s ease infinite;"></div>

    <div class="p-4 p-sm-5 text-center">
      
      <!-- Animated Hero Icon -->
      <div class="position-relative d-inline-block mb-3">
        <div class="pulse-ring position-absolute top-50 start-50 translate-middle rounded-circle" style="width: 100px; height: 100px; background: rgba(16, 185, 129, 0.15); animation: pulseRing 2.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;"></div>
        <div class="hero-icon-box rounded-circle d-flex align-items-center justify-content-center mx-auto position-relative" style="width: 80px; height: 80px; background: linear-gradient(135deg, rgba(16, 185, 129, 0.25), rgba(59, 130, 246, 0.15)); border: 1px solid rgba(16, 185, 129, 0.4); box-shadow: 0 0 25px rgba(16, 185, 129, 0.3);">
          <i class="bi bi-envelope-check-fill fs-1 text-emerald" style="color: #34d399; filter: drop-shadow(0 2px 8px rgba(52, 211, 153, 0.5));"></i>
        </div>
      </div>

      <!-- Real-time Polling Live Indicator Pill -->
      <div id="pollingStatusBadge" class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill mb-3 border" style="background: rgba(16, 185, 129, 0.08); border-color: rgba(16, 185, 129, 0.25) !important;">
        <span class="live-pulse-dot" style="width: 8px; height: 8px; background: #34d399; border-radius: 50%; display: inline-block; animation: pulseDot 1.5s infinite;"></span>
        <span class="small fw-medium" style="color: #34d399; font-size: 0.82rem; letter-spacing: 0.02em;">Waiting for email verification...</span>
      </div>

      <!-- Title & Header -->
      <h2 class="h3 fw-bold text-white mb-2" style="font-family: 'Plus Jakarta Sans', sans-serif; letter-spacing: -0.02em;">Check Your Email</h2>
      <p class="text-secondary mb-3 small" style="font-size: 0.9rem; line-height: 1.5;">
        We've sent a secure verification link to your registered email address:
      </p>

      <!-- Highlighted Email Pill Badge -->
      <div class="d-inline-flex align-items-center gap-2 px-3 py-2 rounded-pill mb-4 border" style="background: rgba(255, 255, 255, 0.05); border-color: rgba(255, 255, 255, 0.15) !important;">
        <i class="bi bi-person-circle" style="color: #34d399;"></i>
        <span class="fw-semibold text-white font-monospace" style="font-size: 0.92rem; letter-spacing: 0.03em;"><?= e($maskedEmail ?: $userEmail) ?></span>
      </div>

      <!-- 3-Step Simple Guide -->
      <div class="grid-steps text-start mb-4 p-3 rounded-4" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.06);">
        <div class="d-flex align-items-start gap-3 mb-2 pb-2 border-bottom border-secondary border-opacity-10">
          <div class="step-num rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width: 24px; height: 24px; background: rgba(52, 211, 153, 0.2); color: #34d399 !important; font-size: 0.75rem;">1</div>
          <div class="small text-secondary" style="font-size: 0.83rem;">Open your email inbox on your mobile phone or browser (e.g. Gmail / Outlook).</div>
        </div>
        <div class="d-flex align-items-start gap-3 mb-2 pb-2 border-bottom border-secondary border-opacity-10">
          <div class="step-num rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width: 24px; height: 24px; background: rgba(245, 158, 11, 0.2); color: #fbbf24 !important; font-size: 0.75rem;">2</div>
          <div class="small text-secondary" style="font-size: 0.83rem;">Check <strong>Spam / Junk</strong> or <strong>Promotions</strong> tab if not found in Primary inbox.</div>
        </div>
        <div class="d-flex align-items-start gap-3">
          <div class="step-num rounded-circle d-flex align-items-center justify-content-center text-white fw-bold flex-shrink-0" style="width: 24px; height: 24px; background: rgba(59, 130, 246, 0.2); color: #60a5fa !important; font-size: 0.75rem;">3</div>
          <div class="small text-secondary" style="font-size: 0.83rem;">Click <strong>Verify Email Address</strong> button inside the email.</div>
        </div>
      </div>

      <!-- Resend Verification Form -->
      <form action="<?= url('/owner/resend-verification') ?>" method="POST" class="mb-3" id="resendForm">
        <?= csrf_field() ?>
        <?php if ($userEmail): ?>
          <input type="hidden" name="email" value="<?= e($userEmail) ?>">
        <?php else: ?>
          <div class="mb-3">
            <input type="email" name="email" class="form-control glass-input text-center" placeholder="Enter your email address" required style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.15); color: #fff; border-radius: 12px; padding: 0.75rem;">
          </div>
        <?php endif; ?>
        <button type="submit" class="btn btn-emerald-gradient w-100 py-2.5 fs-6 fw-bold rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-2" id="resendBtn" style="background: linear-gradient(135deg, #10b981, #059669); color: #fff; border: none; transition: all 0.3s ease;">
          <i class="bi bi-send-fill"></i>
          <span>Resend Verification Email</span>
        </button>
      </form>

      <?php 
        $isLocalHost = in_array($_SERVER['HTTP_HOST'] ?? '', ['localhost', '127.0.0.1'], true) || str_contains($_SERVER['HTTP_HOST'] ?? '', 'localhost:');
        if ($isLocalHost && !empty($unverifiedUser['id'])): 
      ?>
      <!-- Localhost / Dev Instant Verification Button -->
      <form action="<?= url('/owner/direct-verify') ?>" method="POST" class="mb-3">
        <?= csrf_field() ?>
        <input type="hidden" name="user_id" value="<?= (int)$unverifiedUser['id'] ?>">
        <input type="hidden" name="email" value="<?= e($userEmail) ?>">
        <button type="submit" class="btn btn-outline-success btn-sm w-100 py-2 rounded-3" style="border: 1px dashed #34d399; background: rgba(52, 211, 153, 0.05); color: #34d399;">
          <i class="bi bi-lightning-charge-fill me-1"></i> Dev Mode: Instant Verify & Login
        </button>
      </form>
      <?php endif; ?>

      <!-- Actions Links -->
      <div class="d-flex align-items-center justify-content-between gap-2 pt-3 border-top border-secondary border-opacity-25">
        <button type="button" class="btn btn-link btn-sm text-secondary p-0 text-decoration-none hover-white" data-bs-toggle="collapse" data-bs-target="#changeEmailBox" style="font-size: 0.85rem;">
          <i class="bi bi-pencil-square me-1" style="color: #38bdf8;"></i> Wrong email? Change it
        </button>
        <a href="<?= url('/owner/login') ?>" class="text-secondary small text-decoration-none hover-white" style="font-size: 0.85rem;">
          <i class="bi bi-arrow-left me-1"></i> Back to Login
        </a>
      </div>

      <!-- Change Email Collapse Form -->
      <div class="collapse text-start mt-3 pt-3 border-top border-secondary border-opacity-25" id="changeEmailBox">
        <div class="p-3 rounded-3" style="background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08);">
          <h6 class="text-white small fw-bold mb-2 d-flex align-items-center gap-1">
            <i class="bi bi-envelope-at text-info"></i> Update Email Address
          </h6>
          <form action="<?= url('/owner/change-email') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="user_id" value="<?= (int)($unverifiedUser['id'] ?? ($_SESSION['user']['id'] ?? 0)) ?>">
            <div class="mb-2">
              <input type="email" name="new_email" class="form-control glass-input text-white small" placeholder="Enter new email address" required style="background: rgba(0, 0, 0, 0.3); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 8px;">
            </div>
            <button type="submit" class="btn btn-info btn-sm w-100 py-1.5 fw-semibold" style="border-radius: 8px;">
              <i class="bi bi-check-circle me-1"></i> Update & Send Verification Link
            </button>
          </form>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Verification Success Glassmorphism Modal -->
<div id="verificationSuccessModal" class="position-fixed top-0 start-0 w-100 h-100 d-none align-items-center justify-content-center p-3" style="background: rgba(4, 8, 17, 0.90); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); z-index: 99999;">
  <div class="success-modal-card text-center p-4 p-sm-5 rounded-4 position-relative overflow-hidden" style="max-width: 480px; width: 100%; background: linear-gradient(145deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.95)); border: 1px solid rgba(16, 185, 129, 0.4); box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.8), 0 0 40px rgba(16, 185, 129, 0.3);">
    
    <!-- Top Accent Line -->
    <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #10b981, #34d399, #10b981);"></div>

    <!-- Animated Success Icon -->
    <div class="mb-4 d-inline-block position-relative">
      <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 88px; height: 88px; background: rgba(16, 185, 129, 0.2); border: 2px solid #34d399; box-shadow: 0 0 35px rgba(52, 211, 153, 0.5);">
        <i class="bi bi-patch-check-fill text-emerald" style="font-size: 3rem; color: #34d399; filter: drop-shadow(0 2px 10px rgba(52, 211, 153, 0.6));"></i>
      </div>
    </div>

    <!-- Title & Subtext -->
    <h3 class="h4 fw-bold text-white mb-2" style="font-family: 'Plus Jakarta Sans', sans-serif;">Your email is verified successfully! 🎉</h3>
    <p class="text-secondary small mb-4" style="font-size: 0.92rem; line-height: 1.6;">
      Your venue owner account has been verified. Redirecting you to your dashboard...
    </p>

    <!-- Progress Bar -->
    <div class="progress rounded-pill overflow-hidden mb-2" style="height: 6px; background: rgba(255, 255, 255, 0.1);">
      <div class="progress-bar progress-bar-striped progress-bar-animated bg-emerald" role="progressbar" id="redirectProgressBar" style="width: 0%; background-color: #10b981 !important; transition: width 2.2s ease-in-out;"></div>
    </div>
    <span class="text-muted font-monospace" style="font-size: 0.78rem;">Redirecting to Venue Owner Dashboard...</span>

  </div>
</div>

<style>
@keyframes gradientGlow {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
@keyframes pulseRing {
  0% { transform: translate(-50%, -50%) scale(0.8); opacity: 0.8; }
  50% { transform: translate(-50%, -50%) scale(1.2); opacity: 0; }
  100% { transform: translate(-50%, -50%) scale(0.8); opacity: 0; }
}
@keyframes pulseDot {
  0% { opacity: 1; transform: scale(1); }
  50% { opacity: 0.3; transform: scale(1.3); }
  100% { opacity: 1; transform: scale(1); }
}
.btn-emerald-gradient:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(16, 185, 129, 0.45) !important;
  filter: brightness(1.1);
}
.hover-white:hover {
  color: #fff !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const userEmail = <?= json_encode($userEmail) ?>;
    const statusUrl = <?= json_encode(url('/owner/verification-status')) ?>;
    const redirectUrl = <?= json_encode(url('/dashboard')) ?>;
    let isRedirecting = false;

    // Resend Button Cooldown Manager
    const resendBtn = document.getElementById('resendBtn');
    function startTimer(seconds) {
        if (!resendBtn) return;
        resendBtn.disabled = true;
        resendBtn.style.opacity = '0.7';
        const originalHTML = resendBtn.innerHTML;
        const interval = setInterval(function() {
            resendBtn.innerHTML = '<i class="bi bi-clock-history me-1"></i><span>Resend available in ' + seconds + 's</span>';
            seconds--;
            if (seconds < 0) {
                clearInterval(interval);
                resendBtn.disabled = false;
                resendBtn.style.opacity = '1';
                resendBtn.innerHTML = originalHTML;
            }
        }, 1000);
    }

    if (resendBtn) {
        const lastSent = localStorage.getItem('findownn_last_resend_ts');
        if (lastSent) {
            const elapsed = Math.floor((Date.now() - parseInt(lastSent, 10)) / 1000);
            if (elapsed < 60) {
                startTimer(60 - elapsed);
            }
        }
    }

    // AJAX Resend Verification
    const resendForm = document.getElementById('resendForm');
    if (resendForm) {
        resendForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            if (resendBtn) resendBtn.disabled = true;

            try {
                const formData = new FormData(resendForm);
                const res = await fetch(resendForm.action + '?json=1', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                
                let feedbackEl = document.getElementById('resendFeedback');
                if (!feedbackEl) {
                    feedbackEl = document.createElement('div');
                    feedbackEl.id = 'resendFeedback';
                    feedbackEl.className = 'alert mt-3 mb-0 py-2 px-3 small rounded-3';
                    resendForm.parentNode.insertBefore(feedbackEl, resendForm.nextSibling);
                }
                
                if (data.success) {
                    feedbackEl.className = 'alert alert-success border-0 mt-3 mb-0 py-2 px-3 small rounded-3';
                    feedbackEl.style.background = 'rgba(16, 185, 129, 0.15)';
                    feedbackEl.style.color = '#34d399';
                    feedbackEl.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> ' + data.message;
                    localStorage.setItem('findownn_last_resend_ts', Date.now().toString());
                    startTimer(60);
                } else {
                    feedbackEl.className = 'alert alert-danger border-0 mt-3 mb-0 py-2 px-3 small rounded-3';
                    feedbackEl.style.background = 'rgba(239, 68, 68, 0.15)';
                    feedbackEl.style.color = '#f87171';
                    feedbackEl.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> ' + data.message;
                    if (resendBtn) resendBtn.disabled = false;
                }
            } catch (err) {
                resendForm.submit();
            }
        });
    }

    // Real-Time Cross-Device Verification Status Polling (Every 2 seconds)
    if (userEmail) {
        const pollInterval = setInterval(async function() {
            if (isRedirecting) return;
            try {
                const res = await fetch(statusUrl + '?email=' + encodeURIComponent(userEmail) + '&t=' + Date.now(), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) return;
                const data = await res.json();

                if (data.verified && !isRedirecting) {
                    isRedirecting = true;
                    clearInterval(pollInterval);

                    const modal = document.getElementById('verificationSuccessModal');
                    if (modal) {
                        modal.classList.remove('d-none');
                        modal.classList.add('d-flex');

                        setTimeout(function() {
                            const pb = document.getElementById('redirectProgressBar');
                            if (pb) pb.style.width = '100%';
                        }, 50);

                        setTimeout(function() {
                            window.location.href = data.redirect_url || redirectUrl;
                        }, 2300);
                    } else {
                        window.location.href = data.redirect_url || redirectUrl;
                    }
                }
            } catch (e) {
                // Silently ignore network hiccup, will retry in next interval
            }
        }, 2000);
    }
});
</script>
