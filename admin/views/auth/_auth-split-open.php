<?php
/**
 * Split auth layout — open
 * @var string $portalType  owner|admin
 * @var string $portalTitle
 * @var string $portalSubtitle
 */
$portalType     = $portalType ?? 'owner';
$portalTitle    = $portalTitle ?? 'Sign In';
$portalSubtitle = $portalSubtitle ?? '';
$authVariant    = $authVariant ?? '';
$isOwner        = $portalType === 'owner';
$splitClass     = 'auth-split auth-split--' . e($portalType);
if ($authVariant !== '') {
    $splitClass .= ' auth-split--' . e($authVariant);
}
?>
<div class="<?= $splitClass ?>">
  <aside class="auth-split-hero">
    <a href="<?= e(site_home_url()) ?>" class="auth-split-back">
      <i class="bi bi-arrow-left"></i> Website
    </a>
    <div class="auth-split-hero-inner">
      <a href="<?= e(site_home_url()) ?>" class="auth-split-brand-link">
        <img src="<?= e(site_logo_url()) ?>" alt="Findownn" class="auth-split-logo-img" width="48" height="48">
        <span class="auth-split-brand">FIND<span class="brand-accent">OWNN</span></span>
      </a>
      <?php if ($isOwner): ?>
        <span class="auth-split-badge"><i class="bi bi-building"></i> Venue Owner</span>
        <h1 class="auth-split-heading">Grow your playground business</h1>
        <p class="auth-split-lead">Manage courts, bookings, payments and players from one dashboard.</p>
        <ul class="auth-split-points">
          <li><i class="bi bi-check2"></i> Live booking calendar</li>
          <li><i class="bi bi-check2"></i> WhatsApp reminders</li>
          <li><i class="bi bi-check2"></i> Revenue tracking</li>
        </ul>
      <?php else: ?>
        <span class="auth-split-badge auth-split-badge--admin"><i class="bi bi-shield-lock"></i> Staff Only</span>
        <h1 class="auth-split-heading">Admin Control Panel</h1>
        <p class="auth-split-lead">Platform management for Findownn super admins and internal staff.</p>
        <ul class="auth-split-points">
          <li><i class="bi bi-check2"></i> Venues &amp; users</li>
          <li><i class="bi bi-check2"></i> Reports &amp; settings</li>
          <li><i class="bi bi-check2"></i> Online bookings &amp; subscriptions</li>
        </ul>
      <?php endif; ?>
    </div>
  </aside>

  <main class="auth-split-form-wrap">
    <div class="auth-split-form-card">
      <div class="auth-split-form-head">
        <h2 class="auth-split-form-title"><?= e($portalTitle) ?></h2>
        <?php if ($portalSubtitle !== ''): ?>
          <p class="auth-split-form-sub"><?= e($portalSubtitle) ?></p>
        <?php endif; ?>
      </div>
