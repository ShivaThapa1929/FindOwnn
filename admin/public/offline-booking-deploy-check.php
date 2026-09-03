<?php
/**
 * Offline booking deploy checker — run once on Hostinger after upload.
 * URL: https://yoursite.com/admin/public/offline-booking-deploy-check.php
 * Delete this file after all checks pass.
 */
header('Content-Type: text/html; charset=utf-8');

$adminRoot = dirname(__DIR__);

$fileChecks = [
    'BookingController.php' => $adminRoot . '/app/Controllers/BookingController.php',
    'CourtController.php'   => $adminRoot . '/app/Controllers/CourtController.php',
    'Booking.php (model)'   => $adminRoot . '/app/Models/Booking.php',
    'create-offline.php'    => $adminRoot . '/views/bookings/create-offline.php',
    'bookings/index.php'    => $adminRoot . '/views/bookings/index.php',
    'routes/web.php'        => $adminRoot . '/routes/web.php',
    'Model.php (schema-safe)' => $adminRoot . '/app/Core/Model.php',
];

$missing = [];
$present = [];

foreach ($fileChecks as $label => $path) {
    if (is_file($path)) {
        $present[$label] = $path;
    } else {
        $missing[] = $label;
    }
}

$contentChecks = [];

$bookingCtrl = $present['BookingController.php'] ?? null;
if ($bookingCtrl) {
    $src = (string) file_get_contents($bookingCtrl);
    $contentChecks[] = [
        'label' => 'BookingController: no silent redirect when owner has no venues',
        'ok'    => str_contains($src, "'noVenues'")
                 && !preg_match('/if\s*\(\s*empty\s*\(\s*\$myVenues\s*\)\s*\)\s*\{[^}]*redirect\s*\(\s*url\s*\(\s*[\'"]\/bookings/s', $src),
        'fix'   => 'Re-upload admin/app/Controllers/BookingController.php from local XAMPP',
    ];
    $contentChecks[] = [
        'label' => 'BookingController: ownerBookableVenues() helper present',
        'ok'    => str_contains($src, 'function ownerBookableVenues'),
        'fix'   => 'Re-upload admin/app/Controllers/BookingController.php',
    ];
}

$createView = $present['create-offline.php'] ?? null;
if ($createView) {
    $src = (string) file_get_contents($createView);
    $contentChecks[] = [
        'label' => 'create-offline.php: shows “No venue found” guidance',
        'ok'    => str_contains($src, 'No venue found') && str_contains($src, '$noVenues'),
        'fix'   => 'Re-upload admin/views/bookings/create-offline.php',
    ];
}

$indexView = $present['bookings/index.php'] ?? null;
if ($indexView) {
    $src = (string) file_get_contents($indexView);
    $contentChecks[] = [
        'label' => 'bookings/index.php: venue warning before offline button',
        'ok'    => str_contains($src, 'add a venue') && str_contains($src, '/bookings/offline/create'),
        'fix'   => 'Re-upload admin/views/bookings/index.php',
    ];
}

$bookingModel = $present['Booking.php (model)'] ?? null;
if ($bookingModel) {
    $src = (string) file_get_contents($bookingModel);
    $contentChecks[] = [
        'label' => 'Booking.php: owner-scoped stats (getStatsForOwner)',
        'ok'    => str_contains($src, 'function getStatsForOwner'),
        'fix'   => 'Re-upload admin/app/Models/Booking.php',
    ];
}

$routes = $present['routes/web.php'] ?? null;
if ($routes) {
    $src = (string) file_get_contents($routes);
    $contentChecks[] = [
        'label' => 'Route: GET /bookings/offline/create',
        'ok'    => str_contains($src, '/offline/create') && str_contains($src, 'createOffline'),
        'fix'   => 'Re-upload admin/routes/web.php',
    ];
}

$courtCtrl = $present['CourtController.php'] ?? null;
if ($courtCtrl) {
    $src = (string) file_get_contents($courtCtrl);
    $contentChecks[] = [
        'label' => 'CourtController: courts API returns price_per_hour',
        'ok'    => str_contains($src, 'price_per_hour') && str_contains($src, 'apiGetCourts'),
        'fix'   => 'Re-upload admin/app/Controllers/CourtController.php',
    ];
}

$allContentOk = !empty($contentChecks) && !in_array(false, array_column($contentChecks, 'ok'), true);
$allFilesOk   = empty($missing);

?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Findownn — Offline Booking Deploy Check</title>
  <style>
    body { font-family: system-ui, sans-serif; background: #0a0f0c; color: #e2e8f0; padding: 24px; max-width: 760px; margin: 0 auto; }
    h1 { color: #22c55e; font-size: 1.25rem; }
    .pass { color: #4ade80; }
    .fail { color: #f87171; }
    ul { line-height: 1.8; }
    code { background: #1a2e1f; padding: 2px 6px; border-radius: 4px; font-size: 0.85rem; }
    .box { background: #111916; border: 1px solid #1f3d28; border-radius: 12px; padding: 16px 20px; margin: 16px 0; }
    a { color: #4ade80; }
  </style>
</head>
<body>
  <h1>Findownn — Offline Booking Deploy Check</h1>

  <div class="box">
    <h2>Files on server</h2>
    <?php if ($allFilesOk): ?>
      <p class="pass"><strong>All required files exist.</strong></p>
    <?php else: ?>
      <p class="fail"><strong><?= count($missing) ?> file(s) missing — upload from local XAMPP:</strong></p>
      <ul><?php foreach ($missing as $m): ?><li><code><?= htmlspecialchars($m) ?></code></li><?php endforeach; ?></ul>
    <?php endif; ?>
  </div>

  <div class="box">
    <h2>Code version checks</h2>
    <?php if (empty($contentChecks)): ?>
      <p class="fail">Cannot run content checks — controller/view files missing.</p>
    <?php else: ?>
      <ul>
        <?php foreach ($contentChecks as $c): ?>
          <li class="<?= $c['ok'] ? 'pass' : 'fail' ?>">
            <?= $c['ok'] ? '✓' : '✗' ?> <?= htmlspecialchars($c['label']) ?>
            <?php if (!$c['ok']): ?><br><small><?= htmlspecialchars($c['fix']) ?></small><?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </div>

  <?php if ($allFilesOk && $allContentOk): ?>
    <p class="pass"><strong>Offline booking code looks up to date.</strong></p>
    <p>Next: owner login → ensure at least 1 venue + 1 court → test
      <a href="/admin/bookings/offline/create">/admin/bookings/offline/create</a></p>
  <?php else: ?>
    <p class="fail"><strong>Live still has old/partial offline booking code.</strong> Upload the files listed above, then refresh this page.</p>
  <?php endif; ?>

  <p><small>Delete <code>admin/public/offline-booking-deploy-check.php</code> after verification.</small></p>
</body>
</html>
