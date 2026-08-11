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
        <input type="hidden" name="phone_verified" id="phoneVerified" value="0">

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
          <div class="d-flex gap-2">
            <input type="tel" id="reg-phone" name="phone" class="auth-input flex-grow-1" placeholder="9876543210" pattern="[6-9][0-9]{9}" maxlength="10" required>
            <button type="button" class="btn btn-sm btn-outline-success px-3" id="sendOtpBtn" style="white-space:nowrap;">Send OTP</button>
          </div>
          <small class="text-muted" id="otpStatus"><?= ($otpMode ?? 'sms') === 'firebase' ? 'Enter mobile → Send OTP (free SMS via Google) → Verify → register' : 'Enter mobile → Send OTP via SMS → Verify → then register' ?></small>
        </div>

        <div id="recaptcha-container" class="visually-hidden" aria-hidden="true"></div>

        <div class="auth-field" id="otpFieldWrap">
          <label for="reg-otp">Enter OTP</label>
          <div class="d-flex gap-2">
            <input type="text" id="reg-otp" name="otp" class="auth-input flex-grow-1" placeholder="6-digit code" maxlength="6" inputmode="numeric">
            <button type="button" class="btn btn-sm btn-outline-light px-3" id="verifyOtpBtn">Verify</button>
          </div>
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
<script>
(function () {
  const form = document.getElementById('ownerRegisterForm');
  if (!form) return;
  const phoneInput = document.getElementById('reg-phone');
  const otpStatus = document.getElementById('otpStatus');
  const phoneVerified = document.getElementById('phoneVerified');
  const csrf = form.querySelector('input[name="_csrf"]')?.value
    || document.querySelector('meta[name="csrf-token"]')?.content || '';
  const otpMode = <?= json_encode($otpMode ?? 'sms') ?>;

  if (otpMode === 'firebase') {
    import('<?= asset('js/firebase-phone-otp.js') ?>').then(({ initFirebasePhoneOtp }) => {
      initFirebasePhoneOtp({
        firebaseConfig: <?= json_encode($firebaseConfig ?? [], JSON_UNESCAPED_SLASHES) ?>,
        verifyUrl: '<?= url('/otp/firebase-verify') ?>',
        csrf,
        phoneInput,
        otpStatus,
        phoneVerified,
        sendBtn: document.getElementById('sendOtpBtn'),
        verifyBtn: document.getElementById('verifyOtpBtn'),
        otpInput: document.getElementById('reg-otp'),
        form,
      });
    }).catch((e) => {
      console.error(e);
      otpStatus.textContent = 'OTP module failed to load. Refresh the page.';
      otpStatus.className = 'text-danger';
    });
    return;
  }

  async function parseOtpResponse(res) {
    const text = await res.text();
    try {
      return JSON.parse(text);
    } catch {
      return { success: false, message: res.status === 419 ? 'Session expired — refresh page and try again.' : 'Server error. Try again.' };
    }
  }

  document.getElementById('sendOtpBtn')?.addEventListener('click', async function () {
    const phone = phoneInput.value.trim();
    if (!/^[6-9]\d{9}$/.test(phone)) {
      otpStatus.textContent = 'Enter valid 10-digit mobile.';
      otpStatus.className = 'text-danger';
      return;
    }
    if (!csrf) {
      otpStatus.textContent = 'Session error — refresh the page.';
      otpStatus.className = 'text-danger';
      return;
    }
    this.disabled = true;
    otpStatus.textContent = 'Sending OTP...';
    otpStatus.className = 'text-muted';
    try {
      const res = await fetch('<?= url('/otp/send') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        credentials: 'same-origin',
        body: new URLSearchParams({ _csrf: csrf, phone, purpose: 'registration' })
      });
      const data = await parseOtpResponse(res);
      const msg = data.message || (data.success ? 'OTP sent to your mobile via SMS.' : 'Could not send OTP.');
      otpStatus.textContent = msg;
      otpStatus.className = data.success ? 'text-success' : 'text-danger';
      if (!data.success && msg) {
        console.error('OTP send failed:', msg);
      }
    } catch (e) {
      otpStatus.textContent = 'Failed to send OTP. Refresh page and retry.';
      otpStatus.className = 'text-danger';
    }
    this.disabled = false;
  });

  document.getElementById('verifyOtpBtn')?.addEventListener('click', async function () {
    const phone = phoneInput.value.trim();
    const otp = document.getElementById('reg-otp').value.trim();
    if (!otp) {
      otpStatus.textContent = 'Enter the 6-digit OTP.';
      otpStatus.className = 'text-danger';
      return;
    }
    try {
      const res = await fetch('<?= url('/otp/verify') ?>', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
        credentials: 'same-origin',
        body: new URLSearchParams({ _csrf: csrf, phone, otp, purpose: 'registration' })
      });
      const data = await parseOtpResponse(res);
      otpStatus.textContent = data.message;
      otpStatus.className = data.success ? 'text-success' : 'text-danger';
      if (data.success) {
        phoneVerified.value = '1';
        phoneInput.readOnly = true;
      }
    } catch (e) {
      otpStatus.textContent = 'Verification failed.';
      otpStatus.className = 'text-danger';
    }
  });

  form.addEventListener('submit', function (e) {
    if (phoneVerified.value !== '1') {
      e.preventDefault();
      otpStatus.textContent = 'Verify mobile with OTP before registering.';
      otpStatus.className = 'text-danger';
    }
  });
})();
</script>
