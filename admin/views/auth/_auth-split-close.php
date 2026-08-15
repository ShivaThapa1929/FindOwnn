      <?php if (!empty($isOwner)): ?>
        <p class="auth-split-switch text-center text-muted small mb-0">
          <a href="<?= e(site_login_url()) ?>" class="text-success fw-600 text-decoration-none">All portals</a>
          · Staff?
          <a href="<?= url('/login') ?>" class="text-success fw-600 text-decoration-none">Admin login</a>
        </p>
      <?php else: ?>
        <p class="auth-split-switch text-center text-muted small mb-0">
          <a href="<?= e(site_login_url()) ?>" class="text-success fw-600 text-decoration-none">All portals</a>
          · Venue owner?
          <a href="<?= url('/owner/login') ?>" class="text-success fw-600 text-decoration-none">Owner portal</a>
        </p>
      <?php endif; ?>
    </div>
  </main>
</div>

<script>
function toggleOwnerPass(inputId, eyeId) {
  const p = document.getElementById(inputId);
  const e = document.getElementById(eyeId);
  if (!p || !e) return;
  if (p.type === 'password') { p.type = 'text'; e.className = 'bi bi-eye-slash'; }
  else { p.type = 'password'; e.className = 'bi bi-eye'; }
}
</script>
