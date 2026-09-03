<?php
/**
 * Footer legal links for login / auth screens.
 *
 * @var string $variant  'site' | 'admin'
 */
$variant = $variant ?? 'site';
$class   = $variant === 'admin'
    ? 'auth-split-foot text-center text-muted small legal-login-links mb-0'
    : 'text-center text-secondary small legal-login-links mt-3 mb-0';
?>
<p class="<?= e($class) ?>">
  <a href="<?= e(legal_privacy_url()) ?>" target="_blank" rel="noopener" class="text-success text-decoration-none">Privacy Policy</a>
  <span class="mx-1 opacity-50">·</span>
  <a href="<?= e(legal_terms_url()) ?>" target="_blank" rel="noopener" class="text-success text-decoration-none">Terms &amp; Conditions</a>
</p>
