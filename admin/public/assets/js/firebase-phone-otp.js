/**
 * Firebase Phone Auth OTP — Google sends SMS (free / low-cost, no Fast2SMS needed).
 */
export async function initFirebasePhoneOtp(opts) {
  const {
    firebaseConfig,
    verifyUrl,
    csrf,
    phoneInput,
    otpStatus,
    phoneVerified,
    sendBtn,
    verifyBtn,
    otpInput,
    form,
  } = opts;

  const { initializeApp } = await import('https://www.gstatic.com/firebasejs/11.6.0/firebase-app.js');
  const { getAuth, RecaptchaVerifier, signInWithPhoneNumber } = await import(
    'https://www.gstatic.com/firebasejs/11.6.0/firebase-auth.js'
  );

  const app = initializeApp(firebaseConfig);
  const auth = getAuth(app);
  auth.languageCode = 'en';

  let confirmationResult = null;
  let recaptchaVerifier = null;

  function getRecaptcha() {
    if (!recaptchaVerifier) {
      recaptchaVerifier = new RecaptchaVerifier(auth, 'recaptcha-container', {
        size: 'invisible',
      });
    }
    return recaptchaVerifier;
  }

  async function parseResponse(res) {
    const text = await res.text();
    try {
      return JSON.parse(text);
    } catch {
      return {
        success: false,
        message: res.status === 419 ? 'Session expired — refresh page and try again.' : 'Server error. Try again.',
      };
    }
  }

  sendBtn?.addEventListener('click', async function () {
    const phone = phoneInput.value.trim();
    if (!/^[6-9]\d{9}$/.test(phone)) {
      otpStatus.textContent = 'Enter valid 10-digit mobile.';
      otpStatus.className = 'text-danger';
      return;
    }

    this.disabled = true;
    otpStatus.textContent = 'Sending OTP via SMS...';
    otpStatus.className = 'text-muted';

    try {
      if (recaptchaVerifier) {
        try {
          recaptchaVerifier.clear();
        } catch (_) {}
        recaptchaVerifier = null;
      }

      confirmationResult = await signInWithPhoneNumber(auth, '+91' + phone, getRecaptcha());
      otpStatus.textContent = 'OTP sent to your mobile via SMS. Check your messages.';
      otpStatus.className = 'text-success';
    } catch (err) {
      console.error('Firebase send OTP:', err);
      const code = err?.code || '';
      let msg = 'Could not send OTP. Try again.';
      if (code === 'auth/too-many-requests') msg = 'Too many attempts. Wait a few minutes.';
      else if (code === 'auth/invalid-phone-number') msg = 'Invalid mobile number.';
      else if (code === 'auth/captcha-check-failed') msg = 'Security check failed. Refresh page and retry.';
      else if (err?.message) msg = err.message;
      otpStatus.textContent = msg;
      otpStatus.className = 'text-danger';
    }

    this.disabled = false;
  });

  verifyBtn?.addEventListener('click', async function () {
    const phone = phoneInput.value.trim();
    const otp = otpInput.value.trim();

    if (!otp) {
      otpStatus.textContent = 'Enter the 6-digit OTP.';
      otpStatus.className = 'text-danger';
      return;
    }
    if (!confirmationResult) {
      otpStatus.textContent = 'Send OTP first.';
      otpStatus.className = 'text-danger';
      return;
    }

    this.disabled = true;
    otpStatus.textContent = 'Verifying...';
    otpStatus.className = 'text-muted';

    try {
      const cred = await confirmationResult.confirm(otp);
      const idToken = await cred.user.getIdToken();

      const res = await fetch(verifyUrl, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest',
          Accept: 'application/json',
        },
        credentials: 'same-origin',
        body: new URLSearchParams({
          _csrf: csrf,
          phone,
          id_token: idToken,
          purpose: 'registration',
        }),
      });

      const data = await parseResponse(res);
      otpStatus.textContent = data.message || (data.success ? 'Mobile verified.' : 'Verification failed.');
      otpStatus.className = data.success ? 'text-success' : 'text-danger';

      if (data.success) {
        phoneVerified.value = '1';
        phoneInput.readOnly = true;
      }
    } catch (err) {
      console.error('Firebase verify OTP:', err);
      otpStatus.textContent = err?.code === 'auth/invalid-verification-code' ? 'Wrong OTP. Try again.' : 'Verification failed.';
      otpStatus.className = 'text-danger';
    }

    this.disabled = false;
  });

  form?.addEventListener('submit', function (e) {
    if (phoneVerified.value !== '1') {
      e.preventDefault();
      otpStatus.textContent = 'Verify mobile with OTP before registering.';
      otpStatus.className = 'text-danger';
    }
  });
}
