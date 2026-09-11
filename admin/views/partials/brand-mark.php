<?php
/**
 * Admin brand — same logo + wordmark as login page (auth-split).
 *
 * @var string $brandContext sidebar|splash
 * @var string|null $brandHref
 */
$brandContext = $brandContext ?? 'sidebar';
$brandHref    = $brandHref ?? url('/dashboard');
$logoSize     = $brandContext === 'splash' ? 48 : 40;
$tag          = $brandHref !== '' ? 'a' : 'div';
?>
<<?= $tag ?>
  <?php if ($brandHref !== ''): ?>href="<?= e($brandHref) ?>"<?php endif; ?>
  class="admin-brand-mark admin-brand-mark--<?= e($brandContext) ?>">
  <img src="<?= e(site_logo_url()) ?>" alt="Findownn" class="admin-brand-logo" width="<?= (int) $logoSize ?>" height="<?= (int) $logoSize ?>">
  <span class="admin-brand-text">FIND<span class="brand-accent">OWNN</span></span>
</<?= $tag ?>>
