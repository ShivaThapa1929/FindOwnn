/**
 * Contact form — AJAX submit with validation & friendly errors
 */
document.addEventListener('DOMContentLoaded', function () {
  var form = document.getElementById('contactForm');
  if (!form) return;

  var alertEl = document.getElementById('contactAlert');
  var submitBtn = form.querySelector('[type="submit"]');
  var base = document.documentElement.getAttribute('data-site-base') || '/';

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (alertEl) alertEl.classList.add('d-none');

    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.dataset.original = submitBtn.innerHTML;
      submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Sending…';
    }

    fetch(base.replace(/\/?$/, '/') + 'contact/submit', {
      method: 'POST',
      body: new FormData(form),
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
    })
      .then(function (r) { return r.json().then(function (b) { return { ok: r.ok, body: b }; }); })
      .then(function (res) {
        if (res.body.ok) {
          if (window.FindownnUI) FindownnUI.showSuccess(res.body.message);
          if (alertEl) {
            alertEl.className = 'alert alert-success small py-2';
            alertEl.textContent = res.body.message;
            alertEl.classList.remove('d-none');
          }
          form.reset();
          return;
        }
        var msg = res.body.error || (window.FindownnUI ? FindownnUI.UNAVAILABLE : 'Something went wrong.');
        if (alertEl) {
          alertEl.className = 'alert alert-danger small py-2';
          alertEl.textContent = msg;
          alertEl.classList.remove('d-none');
        }
        if (window.FindownnUI) FindownnUI.showError(msg);
      })
      .catch(function () {
        var msg = window.FindownnUI ? FindownnUI.UNAVAILABLE : "We're unavailable right now. Please try again in a few minutes.";
        if (alertEl) {
          alertEl.className = 'alert alert-danger small py-2';
          alertEl.textContent = msg;
          alertEl.classList.remove('d-none');
        }
        if (window.FindownnUI) FindownnUI.showError(msg, { retry: function () { form.requestSubmit(); } });
      })
      .finally(function () {
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.innerHTML = submitBtn.dataset.original || 'Send Message';
        }
      });
  });
});
