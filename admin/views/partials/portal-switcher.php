<?php
/**
 * Role-based portal links (website login hub + admin portals).
 * @var string $currentPortal admin|owner|player
 */
$currentPortal = $currentPortal ?? 'admin';
$compact       = !empty($compact);

$portals = [
    'player' => [
        'label' => 'Player',
        'desc'  => 'Book courts & manage bookings',
        'icon'  => 'bi-person-fill',
        'href'  => site_login_url('player'),
    ],
    'owner' => [
        'label' => 'Venue Owner',
        'desc'  => 'Venues, courts & revenue',
        'icon'  => 'bi-building',
        'href'  => site_login_url('venue_owner'),
    ],
    'admin' => [
        'label' => 'Admin / Staff',
        'desc'  => 'Platform management',
        'icon'  => 'bi-shield-lock',
        'href'  => site_login_url('admin'),
    ],
];
?>
<div class="portal-switcher<?= $compact ? ' portal-switcher--compact' : '' ?>">
  <?php if (!$compact): ?>
  <div class="portal-switcher-head">
    <h6 class="mb-1"><i class="bi bi-box-arrow-in-right me-2 text-success"></i>Role-based login</h6>
    <p class="text-muted small mb-0">Switch portal — each role uses its own sign-in page.</p>
  </div>
  <?php endif; ?>
  <div class="portal-switcher-grid">
    <?php foreach ($portals as $key => $portal): ?>
    <a href="<?= e($portal['href']) ?>"
       class="portal-switcher-card<?= $currentPortal === $key ? ' portal-switcher-card--active' : '' ?>"
       target="_blank" rel="noopener">
      <span class="portal-switcher-icon"><i class="bi <?= e($portal['icon']) ?>"></i></span>
      <span class="portal-switcher-body">
        <span class="portal-switcher-label"><?= e($portal['label']) ?></span>
        <?php if (!$compact): ?>
        <span class="portal-switcher-desc"><?= e($portal['desc']) ?></span>
        <?php endif; ?>
      </span>
      <i class="bi bi-box-arrow-up-right portal-switcher-ext"></i>
    </a>
    <?php endforeach; ?>
  </div>
</div>

<style>
.portal-switcher { margin-bottom: 1rem; }
.portal-switcher-head { margin-bottom: .75rem; }
.portal-switcher-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: .75rem;
}
@media (max-width: 767px) {
  .portal-switcher-grid { grid-template-columns: 1fr; }
}
.portal-switcher-card {
  display: flex;
  align-items: center;
  gap: .65rem;
  padding: .85rem 1rem;
  border-radius: 12px;
  border: 1px solid rgba(255,255,255,.08);
  background: rgba(255,255,255,.03);
  text-decoration: none;
  color: inherit;
  transition: border-color .2s, background .2s;
}
.portal-switcher-card:hover {
  border-color: rgba(34,197,94,.4);
  background: rgba(34,197,94,.06);
  color: inherit;
}
.portal-switcher-card--active {
  border-color: rgba(34,197,94,.55);
  background: rgba(34,197,94,.1);
}
.portal-switcher-icon {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(34,197,94,.12);
  color: #4ade80;
  flex-shrink: 0;
}
.portal-switcher-body { flex: 1; min-width: 0; }
.portal-switcher-label {
  display: block;
  font-weight: 600;
  font-size: .84rem;
  color: #fff;
}
.portal-switcher-desc {
  display: block;
  font-size: .72rem;
  color: #94a3b8;
  margin-top: .1rem;
}
.portal-switcher-ext {
  font-size: .75rem;
  color: #64748b;
  flex-shrink: 0;
}
.portal-switcher--compact .portal-switcher-card { padding: .65rem .75rem; }
.sidebar-portals { padding: .5rem .75rem 0; }
.sidebar-portals-label {
  font-size: .65rem;
  letter-spacing: .08em;
  text-transform: uppercase;
  color: #64748b;
  padding: .5rem .5rem .35rem;
  font-weight: 600;
}
.sidebar-portal-link {
  display: flex;
  align-items: center;
  gap: .5rem;
  padding: .45rem .5rem;
  border-radius: 8px;
  font-size: .78rem;
  color: #94a3b8;
  text-decoration: none;
  transition: background .15s, color .15s;
}
.sidebar-portal-link:hover { background: rgba(255,255,255,.05); color: #fff; }
.sidebar-portal-link i { color: #4ade80; }
</style>
