<?php
require_once __DIR__ . '/../includes/user-auth.php';

if (site_user()) {
    site_redirect('dashboard');
}

$selectedRole = trim($_GET['role'] ?? 'player');
if (!in_array($selectedRole, ['player', 'venue_owner', 'admin'], true)) {
    $selectedRole = 'player';
}

$adminBase = rtrim($asset_base ?? '/', '/') . '/admin/';

include __DIR__ . '/../includes/header.php';
?>

<header class="page-header page-header--compact">
    <div class="glow-orb glow-orb-bottom-left"></div>
    <div class="container text-center position-relative z-1 animate-on-scroll">
        <span class="badge-premium mb-3">Sign In</span>
        <h1 class="display-5 fw-bold text-white mb-2">Choose your portal</h1>
        <p class="text-secondary mx-auto mb-0" style="max-width: 520px;">
            Role-based login — pick how you use Findownn, then sign in with the right dashboard.
        </p>
    </div>
</header>

<section class="py-5 position-relative">
    <div class="container" style="max-width: 960px;">
        <?php if ($err = site_flash('error')): ?>
            <div class="alert alert-danger mb-4"><?= e($err) ?></div>
        <?php endif; ?>

        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <a href="?role=player" class="text-decoration-none role-portal-card <?= $selectedRole === 'player' ? 'role-portal-card--active' : '' ?>">
                    <div class="glass-card p-4 h-100 text-center">
                        <div class="step-icon-box mx-auto mb-3"><i class="bi bi-person-fill"></i></div>
                        <h3 class="text-white h5 mb-2">Player</h3>
                        <p class="text-secondary small mb-0">Book playgrounds &amp; manage bookings</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="?role=venue_owner" class="text-decoration-none role-portal-card <?= $selectedRole === 'venue_owner' ? 'role-portal-card--active' : '' ?>">
                    <div class="glass-card p-4 h-100 text-center">
                        <div class="step-icon-box mx-auto mb-3"><i class="bi bi-building"></i></div>
                        <h3 class="text-white h5 mb-2">Venue Owner</h3>
                        <p class="text-secondary small mb-0">Manage venues, courts &amp; revenue</p>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="?role=admin" class="text-decoration-none role-portal-card <?= $selectedRole === 'admin' ? 'role-portal-card--active' : '' ?>">
                    <div class="glass-card p-4 h-100 text-center">
                        <div class="step-icon-box mx-auto mb-3"><i class="bi bi-shield-lock"></i></div>
                        <h3 class="text-white h5 mb-2">Admin / Staff</h3>
                        <p class="text-secondary small mb-0">Platform management &amp; reports</p>
                    </div>
                </a>
            </div>
        </div>

        <div class="glass-card p-4 p-md-5 mx-auto" style="max-width: 440px;">
            <?php if ($selectedRole === 'player'): ?>
                <h2 class="text-white h4 mb-1 text-center">Player sign in</h2>
                <p class="text-secondary small text-center mb-4">Use your player account email &amp; password</p>
                <form id="roleLoginForm" autocomplete="off">
                    <?= site_csrf_field() ?>
                    <input type="hidden" name="login_as" value="player">
                    <div class="mb-3">
                        <label class="glass-input-label" for="role-login-email">Email</label>
                        <input type="email" id="role-login-email" name="email" class="form-control glass-input" required>
                    </div>
                    <div class="mb-4">
                        <label class="glass-input-label" for="role-login-password">Password</label>
                        <input type="password" id="role-login-password" name="password" class="form-control glass-input" required>
                    </div>
                    <div id="roleLoginAlert" class="alert d-none small py-2"></div>
                    <button type="submit" class="btn btn-premium w-100 py-3">Sign In as Player</button>
                </form>
                <p class="text-center text-secondary small mt-3 mb-0">
                    New player? <a href="<?= e($asset_base) ?>register" class="text-success text-decoration-none fw-600">Create account</a>
                </p>

            <?php elseif ($selectedRole === 'venue_owner'): ?>
                <h2 class="text-white h4 mb-1 text-center">Venue owner portal</h2>
                <p class="text-secondary small text-center mb-4">Owners sign in on the dedicated dashboard to manage venues and bookings.</p>
                <a href="<?= e($adminBase) ?>owner/login" class="btn btn-premium w-100 py-3">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Continue to Owner Login
                </a>
                <p class="text-center text-secondary small mt-3 mb-0">
                    New owner? <a href="<?= e($adminBase) ?>owner/register" class="text-success text-decoration-none fw-600">Create owner account</a>
                </p>

            <?php else: ?>
                <h2 class="text-white h4 mb-1 text-center">Admin / staff portal</h2>
                <p class="text-secondary small text-center mb-4">Super admin and internal staff only — not for players or venue owners.</p>
                <a href="<?= e($adminBase) ?>login" class="btn btn-premium w-100 py-3">
                    <i class="bi bi-shield-lock me-2"></i>Continue to Admin Login
                </a>
            <?php endif; ?>
        </div>

        <?php include __DIR__ . '/../includes/partials/legal-login-links.php'; ?>
    </div>
</section>

<style>
.role-portal-card .glass-card { transition: border-color .2s, box-shadow .2s; border: 1px solid transparent; }
.role-portal-card--active .glass-card,
.role-portal-card:hover .glass-card {
    border-color: rgba(56, 135, 198, 0.45);
    box-shadow: 0 0 0 1px rgba(56, 135, 198, 0.2);
}
.page-header--compact { padding: 4rem 0 2rem; }
</style>

<script>
(function () {
  var form = document.getElementById('roleLoginForm');
  if (!form) return;
  var alertEl = document.getElementById('roleLoginAlert');
  var base = document.documentElement.getAttribute('data-site-base') || '/';

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    alertEl.classList.add('d-none');
    fetch(base.replace(/\/?$/, '/') + 'auth/login', {
      method: 'POST',
      body: new FormData(form),
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
      .then(function (r) { return r.json(); })
      .then(function (body) {
        if (body.ok) {
          window.location.href = base.replace(/\/?$/, '/') + (body.redirect || 'dashboard');
          return;
        }
        var msg = body.error || 'Login failed';
        if (body.redirect_url) {
          msg += ' <a href="' + body.redirect_url + '" class="alert-link">Continue</a>';
          setTimeout(function () { window.location.href = body.redirect_url; }, 1500);
        }
        alertEl.className = 'alert alert-danger small py-2';
        alertEl.innerHTML = msg;
        alertEl.classList.remove('d-none');
      })
      .catch(function () {
        alertEl.className = 'alert alert-danger small py-2';
        alertEl.textContent = 'Network error. Try again.';
        alertEl.classList.remove('d-none');
      });
  });
})();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
