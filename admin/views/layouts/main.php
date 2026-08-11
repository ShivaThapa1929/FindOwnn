<?php
/** @var string $content Rendered view output from Controller::render() */
$content = $content ?? '';
$showSplashOnLoad = (bool) flash('show_splash');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? 'Findownn Admin') ?></title>
  <link rel="icon" type="image/png" sizes="32x32" href="<?= e(url('/public/assets/images/favicon-32x32.png') . '?v=6') ?>">
  <link rel="icon" type="image/png" sizes="16x16" href="<?= e(url('/public/assets/images/favicon-16x16.png') . '?v=6') ?>">
  <link rel="shortcut icon" href="<?= e(url('/public/assets/images/favicon-32x32.png') . '?v=6') ?>">
  <link rel="apple-touch-icon" href="<?= e(url('/public/assets/images/apple-touch-icon.png') . '?v=6') ?>">
  <meta name="theme-color" content="#080c09">
  <meta name="csrf-token" content="<?= csrf_token() ?>">

  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
  <!-- Admin CSS -->
  <link href="<?= url('/public/assets/css/admin.css') ?>?v=<?= @filemtime(ROOT_PATH . '/public/assets/css/admin.css') ?: time() ?>" rel="stylesheet">
</head>
<body<?= $showSplashOnLoad ? ' class="splash-active"' : '' ?>>

<!-- Splash (same as website — shown after login) -->
<?php if ($showSplashOnLoad): ?>
<div id="splash-screen" aria-hidden="true">
  <div class="splash-logo">
    <img src="<?= url('/public/assets/images/logo.png') ?>" alt="Findownn" style="width:48px;height:48px;object-fit:contain;border-radius:10px;">
  </div>
  <div class="splash-wordmark">Findownn <span class="brand-accent">Dashboard</span></div>
  <div class="splash-tagline">Book playgrounds. Play more.</div>
  <div class="splash-bar"><div class="splash-bar-fill"></div></div>
</div>
<?php endif; ?>

<style>
:root {
  --gradient-primary: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
  --gradient-text:    linear-gradient(135deg, #dcfce7 0%, #4ade80 50%, #22c55e 100%);
  --border-glass:     rgba(255, 255, 255, 0.07);
  --text-primary:     #ffffff;
  --text-muted:       #94a3b8;
  --font-heading:     'Plus Jakarta Sans', 'Inter', system-ui, sans-serif;
  --ease-out:         cubic-bezier(0.22, 1, 0.36, 1);
  --ease-spring:      cubic-bezier(0.34, 1.56, 0.64, 1);
}

#splash-screen {
  position: fixed;
  inset: 0;
  z-index: 9999;
  background: #080c09;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 20px;
  animation: splashExit 0.5s var(--ease-out) 2.0s both;
  pointer-events: none;
}

@keyframes splashExit {
  to { opacity: 0; transform: scale(1.04); }
}

.splash-logo {
  width: 72px;
  height: 72px;
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: splashLogoIn 0.6s var(--ease-spring) 0.2s both;
  box-shadow: 0 0 40px rgba(34,197,94,0.15), 0 8px 24px rgba(0,0,0,0.4);
  overflow: hidden;
}

@keyframes splashLogoIn {
  from { opacity: 0; transform: scale(0.6) translateY(12px); }
  to   { opacity: 1; transform: scale(1) translateY(0); }
}

.splash-wordmark {
  font-family: var(--font-heading);
  font-weight: 900;
  font-size: 2rem;
  letter-spacing: -0.04em;
  color: var(--text-primary);
  animation: splashTextIn 0.55s var(--ease-out) 0.55s both;
}

.splash-wordmark span,
.splash-wordmark .brand-accent {
  background: var(--gradient-text);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

@keyframes splashTextIn {
  from { opacity: 0; transform: translateY(10px); }
  to   { opacity: 1; transform: translateY(0); }
}

.splash-tagline {
  font-size: 0.82rem;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  color: var(--text-muted);
  animation: splashTextIn 0.5s var(--ease-out) 0.9s both;
}

.splash-bar {
  width: 48px;
  height: 2px;
  border-radius: 2px;
  background: var(--border-glass);
  overflow: hidden;
  animation: splashTextIn 0.4s var(--ease-out) 1.0s both;
}

.splash-bar-fill {
  height: 100%;
  width: 0%;
  background: var(--gradient-primary);
  border-radius: 2px;
  animation: splashLoad 1.5s var(--ease-out) 0.5s forwards;
}

@keyframes splashLoad { to { width: 100%; } }

body.splash-active { overflow: hidden; }

#admin-app.admin-app-hidden {
  visibility: hidden;
  opacity: 0;
  pointer-events: none;
}

#admin-app.admin-ready {
  visibility: visible;
  opacity: 1;
  transition: opacity 0.35s var(--ease-out);
}
</style>

<?php $user = auth(); $role = $user['role'] ?? ''; ?>

<div id="admin-app" class="<?= $showSplashOnLoad ? 'admin-app-hidden' : 'admin-ready' ?>">

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="sidebar-logo" style="background: none; box-shadow: none; padding: 0;">
      <img src="<?= url('/public/assets/images/logo.png') ?>" alt="Findownn" style="width: 100%; height: 100%; object-fit: contain;">
    </div>
    <span>FINDOWNN</span>
  </div>

  <nav class="sidebar-nav">

    <!-- Dashboard -->
    <a href="<?= url('/dashboard') ?>" class="sidebar-link <?= str_contains($_SERVER['REQUEST_URI'], '/dashboard') || $_SERVER['REQUEST_URI'] === '/' ? 'active' : '' ?>">
      <i class="bi bi-grid-1x2-fill"></i><span>Dashboard</span>
    </a>

    <!-- Venues -->
    <a href="<?= url('/venues') ?>" class="sidebar-link <?= str_contains($_SERVER['REQUEST_URI'], '/venues') ? 'active' : '' ?>">
      <i class="bi bi-building"></i><span>Venues</span>
    </a>

    <!-- Sports -->
    <?php if (in_array($role, ['super_admin','admin'])): ?>
    <a href="<?= url('/sports') ?>" class="sidebar-link <?= str_contains($_SERVER['REQUEST_URI'], '/sports') ? 'active' : '' ?>">
      <i class="bi bi-trophy-fill"></i><span>Sports</span>
    </a>
    <?php endif; ?>

    <!-- Bookings -->
    <a href="<?= url('/bookings') ?>" class="sidebar-link <?= str_contains($_SERVER['REQUEST_URI'], '/bookings') && !str_contains($_SERVER['REQUEST_URI'], '/slots') ? 'active' : '' ?>">
      <i class="bi bi-calendar-check-fill"></i><span>Bookings</span>
    </a>
    
    <!-- Booking Slots -->
    <a href="<?= url('/bookings/slots') ?>" class="sidebar-link <?= str_contains($_SERVER['REQUEST_URI'], '/slots') ? 'active' : '' ?>">
      <i class="bi bi-grid-3x3-gap-fill"></i><span>Booking Slots</span>
    </a>

    <!-- Players -->
    <a href="<?= url('/players') ?>" class="sidebar-link <?= str_contains($_SERVER['REQUEST_URI'], '/players') ? 'active' : '' ?>">
      <i class="bi bi-person-badge-fill"></i><span>Players</span>
    </a>

    <?php if (in_array($role, ['super_admin','admin'])): ?>
    <!-- Users -->
    <a href="<?= url('/users') ?>" class="sidebar-link <?= str_contains($_SERVER['REQUEST_URI'], '/users') ? 'active' : '' ?>">
      <i class="bi bi-people-fill"></i><span>Users</span>
    </a>
    <?php endif; ?>

    <!-- Subscriptions -->
    <?php if (in_array($role, ['super_admin','admin'])): ?>
    <a href="<?= url('/subscriptions') ?>" class="sidebar-link <?= str_contains($_SERVER['REQUEST_URI'], '/subscriptions') && !str_contains($_SERVER['REQUEST_URI'], '/my-plans') ? 'active' : '' ?>">
      <i class="bi bi-credit-card-fill"></i><span>Subscriptions</span>
    </a>
    <?php endif; ?>

    <?php if ($role === 'venue_owner'): ?>
    <a href="<?= url('/subscriptions/my-plans') ?>" class="sidebar-link <?= str_contains($_SERVER['REQUEST_URI'], '/my-plans') ? 'active' : '' ?>">
      <i class="bi bi-layers-fill"></i><span>My Plans</span>
    </a>
    <?php endif; ?>

    <?php if ($role === 'super_admin'): ?>
    <a href="<?= url('/subscriptions/plans') ?>" class="sidebar-link <?= str_contains($_SERVER['REQUEST_URI'], '/plans') ? 'active' : '' ?>">
      <i class="bi bi-layers-fill"></i><span>Plans</span>
    </a>
    <?php endif; ?>

    <?php if (in_array($role, ['super_admin','admin'])): ?>
    <!-- Reports -->
    <a href="<?= url('/reports') ?>" class="sidebar-link <?= str_contains($_SERVER['REQUEST_URI'], '/reports') ? 'active' : '' ?>">
      <i class="bi bi-bar-chart-fill"></i><span>Reports</span>
    </a>

    <a href="<?= url('/reports/audit-logs') ?>" class="sidebar-link <?= str_contains($_SERVER['REQUEST_URI'], 'audit') ? 'active' : '' ?>">
      <i class="bi bi-journal-text"></i><span>Audit Logs</span>
    </a>
    <?php endif; ?>

    <?php if (in_array($role, ['super_admin','admin'])): ?>
    <!-- OpenWA -->
    <a href="<?= url('/openwa') ?>" class="sidebar-link <?= str_contains($_SERVER['REQUEST_URI'], '/openwa') ? 'active' : '' ?>">
      <i class="bi bi-whatsapp"></i><span>OpenWA</span>
    </a>
    <?php endif; ?>

    <?php if ($role === 'super_admin'): ?>
    <a href="<?= url('/settings') ?>" class="sidebar-link <?= str_contains($_SERVER['REQUEST_URI'], '/settings') ? 'active' : '' ?>">
      <i class="bi bi-gear-fill"></i><span>Settings</span>
    </a>
    <?php endif; ?>

    <!-- Profile -->
    <a href="<?= url('/profile') ?>" class="sidebar-link <?= str_contains($_SERVER['REQUEST_URI'], '/profile') ? 'active' : '' ?>">
      <i class="bi bi-person-circle"></i><span>My Profile</span>
    </a>

  </nav>

  <div class="sidebar-footer">
    <div class="d-flex align-items-center gap-2 mb-3 px-1">
      <div class="sidebar-avatar"><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></div>
      <div class="lh-sm">
        <div class="fw-600 small text-white"><?= e($user['name'] ?? '') ?></div>
        <div class="text-muted" style="font-size:.7rem;"><?= ucwords(str_replace('_', ' ', $role)) ?></div>
      </div>
    </div>
    <form action="<?= url('/logout') ?>" method="POST">
      <?= csrf_field() ?>
      <button type="submit" class="btn btn-outline-danger btn-sm w-100">
        <i class="bi bi-box-arrow-right me-1"></i>Logout
      </button>
    </form>
  </div>
</aside>

<!-- Main -->
<div class="main-wrapper" id="mainWrapper">

  <!-- Topbar -->
  <header class="topbar">
    <button class="sidebar-toggle" id="sidebarToggle">
      <i class="bi bi-list"></i>
    </button>
    <div class="topbar-title"><?= e($title ?? 'Dashboard') ?></div>
    <div class="ms-auto d-flex align-items-center gap-3">
      <span class="badge bg-success"><?= ucwords(str_replace('_', ' ', $role)) ?></span>
    </div>
  </header>

  <!-- Content -->
  <main class="main-content">
    <?php if ($f = flash('success')): ?>
      <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i><?= e($f) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>
    <?php if ($f = flash('error')): ?>
      <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= e($f) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <?= $content ?>
  </main>

</div><!-- /.main-wrapper -->

</div><!-- /#admin-app -->

<script>
(function () {
  var forceSplash = <?= json_encode($showSplashOnLoad) ?>;
  var adminApp = document.getElementById('admin-app');

  if (forceSplash) {
    sessionStorage.removeItem('adminSplashShown');
  }

  var splashShown = sessionStorage.getItem('adminSplashShown');
  var shouldShowSplash = forceSplash;

  function revealDashboard() {
    if (adminApp) {
      adminApp.classList.remove('admin-app-hidden');
      adminApp.classList.add('admin-ready');
    }
    document.documentElement.classList.remove('splash-active');
    document.body.classList.remove('splash-active');
  }

  if (shouldShowSplash) {
    if (adminApp) adminApp.classList.add('admin-app-hidden');
    document.documentElement.classList.add('splash-active');
    document.body.classList.add('splash-active');

    window.addEventListener('load', function () {
      setTimeout(function () {
        var splash = document.getElementById('splash-screen');
        if (splash) splash.remove();
        revealDashboard();
        sessionStorage.setItem('adminSplashShown', 'true');
      }, 2500);
    });
  } else {
    var splash = document.getElementById('splash-screen');
    if (splash) {
      splash.style.display = 'none';
      splash.remove();
    }
    revealDashboard();
  }
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script src="<?= url('/public/assets/js/ajax-filters.js') ?>"></script>
<script src="<?= url('/public/assets/js/admin.js') ?>"></script>
</body>
</html>
