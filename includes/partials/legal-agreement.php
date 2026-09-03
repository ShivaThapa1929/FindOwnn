<?php
/**
 * Required Terms & Privacy agreement checkbox.
 *
 * @var string $variant       'site' | 'admin'
 * @var string $checkbox_id
 * @var string $checkbox_name
 * @var bool   $required
 */
$variant       = $variant ?? 'site';
$checkbox_id   = $checkbox_id ?? 'accept_terms';
$checkbox_name = $checkbox_name ?? 'accept_terms';
$required      = $required ?? true;
$privacyUrl    = legal_privacy_url();
$termsUrl      = legal_terms_url();
$reqAttr       = $required ? ' required' : '';
?>
<?php if ($variant === 'admin'): ?>
<div class="auth-field legal-agreement mb-3">
  <div class="form-check">
    <input class="form-check-input" type="checkbox" name="<?= e($checkbox_name) ?>" value="1"
           id="<?= e($checkbox_id) ?>"<?= $reqAttr ?>>
    <label class="form-check-label text-muted small" for="<?= e($checkbox_id) ?>">
      I agree to the
      <a href="<?= e($termsUrl) ?>" target="_blank" rel="noopener" class="text-success text-decoration-none">Terms &amp; Conditions</a>
      and
      <a href="<?= e($privacyUrl) ?>" target="_blank" rel="noopener" class="text-success text-decoration-none">Privacy Policy</a>.
    </label>
  </div>
</div>
<?php else: ?>
<div class="form-check legal-agreement mb-4">
  <input class="form-check-input" type="checkbox" name="<?= e($checkbox_name) ?>" value="1"
         id="<?= e($checkbox_id) ?>"<?= $reqAttr ?>>
  <label class="form-check-label text-secondary small" for="<?= e($checkbox_id) ?>">
    I agree to the
    <a href="<?= e($termsUrl) ?>" target="_blank" rel="noopener" class="text-success text-decoration-none fw-600">Terms &amp; Conditions</a>
    and
    <a href="<?= e($privacyUrl) ?>" target="_blank" rel="noopener" class="text-success text-decoration-none fw-600">Privacy Policy</a>.
  </label>
</div>
<?php endif; ?>
