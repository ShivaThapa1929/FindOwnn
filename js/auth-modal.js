(function () {
  'use strict';

  var modalEl = document.getElementById('authModal');
  if (!modalEl) return;

  var base = document.documentElement.getAttribute('data-site-base') || '/';
  var loginForm = document.getElementById('authLoginForm');
  var registerForm = document.getElementById('authRegisterForm');
  var alertEl = document.getElementById('authAlert');
  var loginTabBtn = document.getElementById('auth-login-tab');
  var registerTabBtn = document.getElementById('auth-register-tab');
  var loginAsSelect = document.getElementById('auth-login-as');
  var bsModal = null;

  function siteUrl(path) {
    return base.replace(/\/?$/, '/') + String(path || '').replace(/^\//, '');
  }

  function adminUrl(path) {
    return siteUrl('admin/' + String(path || '').replace(/^\//, ''));
  }

  function getModal() {
    if (!bsModal) {
      bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
    }
    return bsModal;
  }

  function showAlert(message, type) {
    if (!alertEl) return;
    alertEl.className = 'auth-alert auth-alert--' + (type || 'error');
    alertEl.innerHTML = '<i class="bi bi-' + (type === 'success' ? 'check-circle' : 'exclamation-circle') + ' me-1"></i>' + message;
    alertEl.classList.remove('d-none');
  }

  function hideAlert() {
    if (alertEl) alertEl.classList.add('d-none');
  }

  function setTab(tab) {
    hideAlert();
    var btn = tab === 'register' ? registerTabBtn : loginTabBtn;
    if (btn) {
      bootstrap.Tab.getOrCreateInstance(btn).show();
    }
  }

  function toggleLoginRoleUi() {
    var role = loginAsSelect ? loginAsSelect.value : 'player';
    var ownerNote = document.getElementById('authOwnerLoginNote');
    if (ownerNote) {
      ownerNote.classList.toggle('d-none', role !== 'venue_owner');
    }
  }

  window.openAuthModal = function (tab) {
    var navCollapse = document.getElementById('navbarNav');
    if (navCollapse && navCollapse.classList.contains('show')) {
      var inst = bootstrap.Collapse.getInstance(navCollapse);
      if (inst) inst.hide();
    }
    getModal().show();
    setTab(tab === 'register' ? 'register' : 'login');
  };

  modalEl.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (btn) {
    btn.addEventListener('shown.bs.tab', hideAlert);
  });

  document.querySelectorAll('[data-auth-open]').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      openAuthModal(btn.getAttribute('data-auth-open') || 'login');
    });
  });

  loginAsSelect?.addEventListener('change', toggleLoginRoleUi);
  toggleLoginRoleUi();

  document.querySelectorAll('#authModal .auth-pass-toggle').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = document.getElementById(btn.getAttribute('data-target'));
      var icon = btn.querySelector('i');
      if (!input) return;
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      if (icon) icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
  });

  function setLoading(form, loading) {
    var submit = form.querySelector('.auth-submit-btn');
    if (!submit) return;
    submit.disabled = loading;
    submit.querySelector('.auth-btn-text').classList.toggle('d-none', loading);
    submit.querySelector('.auth-btn-loading').classList.toggle('d-none', !loading);
  }

  function parseJsonResponse(res) {
    return res.text().then(function (text) {
      try {
        return JSON.parse(text);
      } catch (e) {
        return {
          ok: false,
          error: res.status === 403
            ? 'Session expired. Refresh the page and try again.'
            : 'Server error. Please try again.'
        };
      }
    });
  }

  function submitAuth(form, endpoint) {
    hideAlert();
    setLoading(form, true);

    var loginAs = loginAsSelect ? loginAsSelect.value : 'player';
    if (form === loginForm && loginAs === 'venue_owner') {
      var email = document.getElementById('auth-login-email')?.value.trim() || '';
      var target = adminUrl('owner/login') + (email ? ('?email=' + encodeURIComponent(email)) : '');
      window.location.href = target;
      return;
    }

    var data = new FormData(form);
    if (form === loginForm) {
      data.set('login_as', loginAs);
    }

    fetch(siteUrl(endpoint), {
      method: 'POST',
      body: data,
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
      .then(function (res) { return parseJsonResponse(res).then(function (body) { return { res: res, body: body }; }); })
      .then(function (_ref) {
        var res = _ref.res;
        var body = _ref.body;
        setLoading(form, false);

        if (body.redirect_url) {
          showAlert((body.error || 'Redirecting…') + ' <a href="' + body.redirect_url + '" class="auth-alert-link">Continue</a>', 'error');
          setTimeout(function () { window.location.href = body.redirect_url; }, 1200);
          return;
        }

        if (body.ok) {
          if (body.user && body.user.token) {
            try {
              localStorage.setItem('findownn_token', body.user.token);
              localStorage.setItem('findownn_user', JSON.stringify(body.user));
            } catch (e) {}
          }
          window.location.href = siteUrl(body.redirect || 'dashboard');
          return;
        }

        var msg = body.error || 'Something went wrong. Please try again.';
        if (body.portal) {
          var href = body.portal === 'owner' ? adminUrl('owner/login') : adminUrl('login');
          var label = body.portal === 'owner' ? 'Venue owner dashboard' : 'Admin login';
          msg += ' <a href="' + href + '" class="auth-alert-link">' + label + '</a>';
        }
        showAlert(msg, 'error');
      })
      .catch(function () {
        setLoading(form, false);
        showAlert('Network error. Check your connection and try again.', 'error');
      });
  }

  if (loginForm) {
    loginForm.addEventListener('submit', function (e) {
      e.preventDefault();
      submitAuth(loginForm, 'auth/login');
    });
  }

  if (registerForm) {
    registerForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var terms = registerForm.querySelector('[name="accept_terms"]');
      if (terms && !terms.checked) {
        showAlert('Please agree to the Terms & Conditions and Privacy Policy to create an account.', 'error');
        terms.focus();
        return;
      }
      submitAuth(registerForm, 'auth/register');
    });
  }

  var params = new URLSearchParams(window.location.search);
  var authParam = params.get('auth');
  if (authParam === 'login' || authParam === 'register') {
    openAuthModal(authParam);
    if (window.history.replaceState) {
      params.delete('auth');
      var qs = params.toString();
      var clean = window.location.pathname + (qs ? '?' + qs : '');
      window.history.replaceState({}, '', clean);
    }
  }
})();
