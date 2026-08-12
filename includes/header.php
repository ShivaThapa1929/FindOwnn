<?php
$script_dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
/** @var string $asset_base Base path for assets — set by index.php */
$asset_base = $asset_base ?? (($script_dir === '') ? '/' : $script_dir . '/');
$current_page = $route_name ?? basename($_SERVER['PHP_SELF'], '.php');
require_once __DIR__ . '/site-contact.php';
require_once __DIR__ . '/user-auth.php';
$site_user = site_user();
?>
<!DOCTYPE html>
<html lang="en" data-site-base="<?= htmlspecialchars($asset_base, ENT_QUOTES, 'UTF-8') ?>">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Reload on any page → always land on home -->
  <script>
  (function () {
    var nav = performance.getEntriesByType && performance.getEntriesByType('navigation')[0];
    var isReload = (nav && nav.type === 'reload') ||
      (performance.navigation && performance.navigation.type === 1);
    if (!isReload) return;
    if (location.pathname.indexOf('/admin') !== -1) return;

    var keepPaths = ['/login', '/register', '/account', '/dashboard', '/logout'];
    if (keepPaths.some(function (k) { return location.pathname.indexOf(k) !== -1; })) return;

    var base = <?= json_encode($asset_base) ?>;
    var b = base === '/' ? '' : base.replace(/\/+$/, '');
    var p = location.pathname.replace(/\/+$/, '') || '/';

    var homePaths = b === ''
      ? ['', '/', '/index.php', '/home']
      : [b, b + '/index.php', b + '/home'];

    var onHome = homePaths.some(function (h) {
      var nh = (h.replace(/\/+$/, '') || '/');
      var np = (p.replace(/\/+$/, '') || '/');
      return nh === np;
    });

    if (!onHome) location.replace(base);
  })();
  </script>

  <!-- SEO -->
  <title>Findownn — Bhuj's Sports Playground Booking Platform</title>
  <meta name="description" content="Discover and book Box Cricket & Pickleball playgrounds across Bhuj instantly. No calls, no waiting — just book and play.">
  <meta name="keywords" content="Findownn, Box Cricket Bhuj, Pickleball Bhuj, Sports Booking, Book Turfs, Gujarat">
  <meta name="author" content="Findownn">
  <meta name="theme-color" content="#080c09">
  <?php if (!$site_user): ?>
  <meta name="csrf-token" content="<?= e(site_csrf_token()) ?>">
  <?php endif; ?>

  <!-- Favicon (same logo as footer — square crop for browser tab) -->
  <link rel="icon" type="image/png" sizes="32x32" href="<?= $asset_base ?>assets/images/favicon-32x32.png?v=6">
  <link rel="icon" type="image/png" sizes="16x16" href="<?= $asset_base ?>assets/images/favicon-16x16.png?v=6">
  <link rel="shortcut icon" href="<?= $asset_base ?>assets/images/favicon-32x32.png?v=6">
  <link rel="apple-touch-icon" href="<?= $asset_base ?>assets/images/apple-touch-icon.png?v=6">

  <!-- PWA -->
  <link rel="manifest" href="<?= $asset_base ?>manifest.json">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

  <!-- Preconnect -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,400&family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  <!-- Custom Stylesheet -->
  <link rel="stylesheet" href="<?= $asset_base ?>css/style.css?v=4.4">
  <link rel="stylesheet" href="<?= $asset_base ?>css/responsive.css?v=1.0">
  <?php if (($route_name ?? '') === 'index'): ?>
  <link rel="stylesheet" href="<?= $asset_base ?>css/home-enhancements.css?v=4.3">
  <?php endif; ?>
</head>
<body class="splash-active">

  <!-- ============================================================
       SPLASH SCREEN
  ============================================================ -->
  <div id="splash-screen" aria-hidden="true">
    <div class="splash-logo">
      <img src="<?= $asset_base ?>assets/images/logo.png" alt="Findownn" style="width:48px;height:48px;object-fit:contain;border-radius:10px;">
    </div>
    <div class="splash-wordmark">FIND<span class="brand-accent">OWNN</span></div>
    <div class="splash-tagline">Book playgrounds. Play more.</div>
    <div class="splash-bar"><div class="splash-bar-fill"></div></div>
  </div>

  <!-- ============================================================
       NAVBAR
  ============================================================ -->
  <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="main-navbar">
    <div class="container">

      <a class="navbar-brand" href="<?= $asset_base ?>">
        <div class="navbar-brand-logo" style="background:none;box-shadow:none;">
          <img src="<?= $asset_base ?>assets/images/logo.png" alt="Findownn" style="width:32px;height:32px;object-fit:contain;border-radius:8px;">
        </div>
        <span class="navbar-brand-text">FIND<span class="brand-accent">OWNN</span></span>
      </a>

      <button class="navbar-toggler" type="button"
              data-bs-toggle="collapse" data-bs-target="#navbarNav"
              aria-controls="navbarNav" aria-expanded="false"
              aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-1">
          <li class="nav-item">
            <a class="nav-link <?php echo ($current_page === 'index' || $current_page === '') ? 'active' : ''; ?>" href="./">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'venues' ? 'active' : ''; ?>" href="venues">Playgrounds</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'sports' ? 'active' : ''; ?>" href="sports">Sports</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'partner' ? 'active' : ''; ?>" href="partner">List Your Playground</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'about' ? 'active' : ''; ?>" href="about">About</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo $current_page === 'contact' ? 'active' : ''; ?>" href="contact">Support</a>
          </li>
          <?php if ($site_user): ?>
          <li class="nav-item">
            <a class="nav-link <?php echo ($current_page === 'dashboard' || $current_page === 'account') ? 'active' : ''; ?>" href="dashboard">
              <i class="bi bi-speedometer2 me-1"></i>Dashboard
            </a>
          </li>
          <?php else: ?>
          <li class="nav-item ms-lg-1 mt-2 mt-lg-0">
            <button type="button" class="btn btn-premium-outline btn-sm w-100" data-auth-open="login">
              <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
            </button>
          </li>
          <?php endif; ?>
          <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
            <a href="#download-cta" class="btn btn-premium btn-sm w-100">
              <i class="bi bi-download"></i> Download App
            </a>
          </li>
        </ul>
      </div>

    </div>
  </nav>

  <?php include __DIR__ . '/auth-modal.php'; ?>
