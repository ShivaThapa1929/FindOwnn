<?php 
$user = auth(); 
$role = $user['role']; 
$canAdmin = in_array($role, ['super_admin','admin']); 

// Provide safe defaults for all venue fields
$venue['price_per_hour'] = $venue['price_per_hour'] ?? 0;
$venue['rating'] = $venue['rating'] ?? 0;
$venue['total_reviews'] = $venue['total_reviews'] ?? 0;
$venue['city'] = $venue['city'] ?? '';
$venue['state'] = $venue['state'] ?? '';
$venue['pincode'] = $venue['pincode'] ?? '';
$venue['address'] = $venue['address'] ?? '';
$venue['google_map_link'] = $venue['google_map_link'] ?? '';
$venue['description'] = $venue['description'] ?? '';
$venue['amenities'] = $venue['amenities'] ?? '';
$venue['is_verified'] = $venue['is_verified'] ?? 0;
$venue['badge_expires_at'] = $venue['badge_expires_at'] ?? null;
$venue['verification_notes'] = $venue['verification_notes'] ?? '';
$venue['verification_status'] = $venue['verification_status'] ?? 'pending';
$venue['status'] = $venue['status'] ?? 'inactive';

// Get featured image or first gallery image for banner
$bannerImage = null;
if (!empty($images)) {
    // Try to find featured image first
    foreach ($images as $img) {
        if (($img['image_type'] ?? '') === 'featured') {
            $bannerImage = $img['image_path'];
            break;
        }
    }
    // If no featured image, use first available
    if (!$bannerImage && isset($images[0])) {
        $bannerImage = $images[0]['image_path'];
    }
}

$bannerUrl = null;
if ($bannerImage) {
    if (str_starts_with($bannerImage, 'http://') || str_starts_with($bannerImage, 'https://')) {
        $bannerUrl = $bannerImage;
    } elseif (str_starts_with($bannerImage, 'assets/')) {
        $bannerUrl = url('/' . $bannerImage);
    } else {
        $bannerUrl = url('/public/uploads/' . $bannerImage);
    }
}
?>

<!-- Venue Banner -->
<div class="venue-banner">
  <?php if ($bannerUrl): ?>
    <img src="<?= $bannerUrl ?>" alt="<?= e($venue['name']) ?>" class="venue-banner-image" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
    <svg class="venue-banner-dummy" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1600 400" width="1600" height="400" preserveAspectRatio="xMidYMid slice" style="display:none;">
      <defs>
        <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" style="stop-color:#0a0f0b;stop-opacity:1" />
          <stop offset="50%" style="stop-color:#0d1510;stop-opacity:1" />
          <stop offset="100%" style="stop-color:#111a13;stop-opacity:1" />
        </linearGradient>
        <pattern id="grid" width="80" height="80" patternUnits="userSpaceOnUse">
          <path d="M 80 0 L 0 0 0 80" fill="none" stroke="#22c55e" stroke-width="0.5" opacity="0.15"/>
        </pattern>
      </defs>
      <rect width="1600" height="400" fill="url(#bgGrad)"/>
      <rect width="1600" height="400" fill="url(#grid)"/>
      <circle cx="400" cy="200" r="150" fill="#22c55e" opacity="0.08"/>
      <circle cx="1200" cy="200" r="180" fill="#22c55e" opacity="0.08"/>
      <path d="M 700 150 L 750 180 L 750 220 L 700 250 L 650 220 L 650 180 Z" fill="#22c55e" opacity="0.15"/>
      <path d="M 900 150 L 950 180 L 950 220 L 900 250 L 850 220 L 850 180 Z" fill="#22c55e" opacity="0.15"/>
    </svg>
  <?php else: ?>
    <svg class="venue-banner-dummy" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1600 400" width="1600" height="400" preserveAspectRatio="xMidYMid slice">
      <defs>
        <linearGradient id="bgGrad" x1="0%" y1="0%" x2="100%" y2="100%">
          <stop offset="0%" style="stop-color:#0a0f0b;stop-opacity:1" />
          <stop offset="50%" style="stop-color:#0d1510;stop-opacity:1" />
          <stop offset="100%" style="stop-color:#111a13;stop-opacity:1" />
        </linearGradient>
        <pattern id="grid" width="80" height="80" patternUnits="userSpaceOnUse">
          <path d="M 80 0 L 0 0 0 80" fill="none" stroke="#22c55e" stroke-width="0.5" opacity="0.15"/>
        </pattern>
      </defs>
      <rect width="1600" height="400" fill="url(#bgGrad)"/>
      <rect width="1600" height="400" fill="url(#grid)"/>
      <circle cx="400" cy="200" r="150" fill="#22c55e" opacity="0.08"/>
      <circle cx="1200" cy="200" r="180" fill="#22c55e" opacity="0.08"/>
      <path d="M 700 150 L 750 180 L 750 220 L 700 250 L 650 220 L 650 180 Z" fill="#22c55e" opacity="0.15"/>
      <path d="M 900 150 L 950 180 L 950 220 L 900 250 L 850 220 L 850 180 Z" fill="#22c55e" opacity="0.15"/>
    </svg>
  <?php endif; ?>
  <div class="venue-banner-overlay"></div>
  <div class="venue-banner-content">
    <h1 class="venue-banner-title"><?= e($venue['name']) ?></h1>
    <div class="venue-banner-meta">
      <span><i class="bi bi-geo-alt-fill me-1"></i><?= e($venue['city']) ?>, <?= e($venue['state']) ?></span>
      <span class="mx-2">•</span>
      <span><i class="bi bi-star-fill me-1"></i><?= number_format($venue['rating'], 1) ?> / 5</span>
      <?php if ($venue['is_verified']): ?>
        <span class="mx-2">•</span>
        <span><i class="bi bi-patch-check-fill me-1"></i>Verified</span>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="row g-4">

  <!-- ── Left col ──────────────────────────────────────────────── -->
  <div class="col-lg-8">

    <!-- Venue Info -->
    <div class="panel mb-4">
      <div class="panel-head">
        <h6 class="panel-title">Venue Details</h6>
        <div class="d-flex gap-2">
          <a href="<?= url('/courts?venue_id='.$venue['id']) ?>" class="btn btn-sm btn-success">
            <i class="bi bi-grid-3x3-gap me-1"></i>Manage Courts
          </a>
          <a href="<?= url('/venues/'.$venue['id'].'/edit') ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-pencil me-1"></i>Edit
          </a>
        </div>
      </div>
      <div class="panel-body">
        <!-- Status Badges -->
        <div class="d-flex flex-wrap gap-2 mb-3 pb-3" style="border-bottom: 1px solid rgba(134,168,146,0.15);">
          <?= statusBadge($venue['verification_status']) ?>
          <?= statusBadge($venue['status']) ?>
          <?php if ($venue['is_verified']): ?>
            <span class="badge bg-success"><i class="bi bi-patch-check-fill me-1"></i>Verified</span>
          <?php endif; ?>
        </div>
        
        <div class="row g-3">
          <div class="col-sm-6">
            <div class="info-group">
              <span class="info-label">Price / Hour</span>
              <span class="fw-700 text-success" style="font-size:1.1rem;">₹<?= number_format($venue['price_per_hour']) ?></span>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="info-group">
              <span class="info-label">Rating</span>
              <span>
                <i class="bi bi-star-fill text-warning me-1" style="font-size:.8rem;"></i>
                <?= number_format($venue['rating'],1) ?> / 5
                <span class="text-muted small">(<?= $venue['total_reviews'] ?> reviews)</span>
              </span>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="info-group">
              <span class="info-label">City / State</span>
              <span><?= e($venue['city']) ?>, <?= e($venue['state']) ?> <?= $venue['pincode'] ? '— '.$venue['pincode'] : '' ?></span>
            </div>
          </div>
          <div class="col-sm-6">
            <div class="info-group">
              <span class="info-label">Added</span>
              <span class="text-muted"><?= date('M j, Y', strtotime($venue['created_at'])) ?></span>
            </div>
          </div>
          <div class="col-12">
            <div class="info-group">
              <span class="info-label">Address</span>
              <span><?= e($venue['address'] ?? '—') ?></span>
            </div>
          </div>
          <?php if ($venue['google_map_link']): ?>
          <div class="col-12">
            <div class="info-group">
              <span class="info-label">Google Maps</span>
              <a href="<?= e($venue['google_map_link']) ?>" target="_blank" class="text-primary">
                <i class="bi bi-geo-alt-fill me-1"></i>Open in Maps
              </a>
            </div>
          </div>
          <?php endif; ?>
          <?php if ($venue['description']): ?>
          <div class="col-12">
            <div class="info-group">
              <span class="info-label">Description</span>
              <p class="mb-0 text-secondary small lh-lg"><?= nl2br(e($venue['description'])) ?></p>
            </div>
          </div>
          <?php endif; ?>
        </div>

        <!-- Amenities -->
        <?php
          $amenities = [];
          if ($venue['amenities']) {
            $decoded = json_decode($venue['amenities'], true);
            $amenities = is_array($decoded) ? $decoded : explode(',', $venue['amenities']);
          }
        ?>
        <?php if (!empty($amenities)): ?>
        <div class="mt-3">
          <span class="info-label d-block mb-2">Amenities</span>
          <div class="d-flex flex-wrap gap-2">
            <?php foreach ($amenities as $a): ?>
              <?php $a = trim($a); if ($a): ?>
              <span class="badge bg-secondary px-2 py-1"><?= e($a) ?></span>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <!-- Images -->
        <?php if (!empty($images)): ?>
        <div class="mt-3">
          <span class="info-label d-block mb-2">Gallery Images</span>
          <div class="row g-2">
            <?php foreach ($images as $img): ?>
              <?php
                $imgPath = $img['image_path'];
                if (str_starts_with($imgPath, 'assets/')) {
                    $imgUrl = url('/' . $imgPath);
                } elseif (str_starts_with($imgPath, 'http')) {
                    $imgUrl = $imgPath;
                } else {
                    $imgUrl = url('/public/uploads/' . $imgPath);
                }
              ?>
            <div class="col-4 col-md-3">
              <img src="<?= $imgUrl ?>"
                   class="img-fluid rounded" alt="venue"
                   style="height:88px;object-fit:cover;width:100%;border-radius:8px;">
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Courts Section -->
    <?php if (!empty($courts)): ?>
    <div class="panel mb-4">
      <div class="panel-head">
        <h6 class="panel-title"><i class="bi bi-grid-3x3-gap me-2"></i>Courts (<?= count($courts) ?>)</h6>
        <div class="d-flex gap-2">
          <?php if (count($courts) > 3): ?>
          <button class="btn btn-xs btn-outline-primary" id="toggleCourtsBtn" onclick="toggleAllCourts()">
            <i class="bi bi-eye me-1"></i>Show All
          </button>
          <?php endif; ?>
          <a href="<?= url('/courts?venue_id='.$venue['id']) ?>" class="btn btn-xs btn-success">
            <i class="bi bi-plus-lg me-1"></i>Add Court
          </a>
        </div>
      </div>
      <div class="panel-body">
        <div class="row g-3" id="courtsGrid">
          <?php 
          $displayLimit = 3;
          foreach ($courts as $index => $court): 
            $isHidden = $index >= $displayLimit;
          ?>
          <div class="col-md-6 col-lg-4 court-item <?= $isHidden ? 'hidden-court' : '' ?>" <?= $isHidden ? 'style="display:none;"' : '' ?>>
            <div class="court-card">
              <?php 
              // Determine image to display: featured > gallery > dummy
              $imageUrl = null;
              if ($court['featured_image']) {
                $cPath = $court['featured_image'];
                if (str_starts_with($cPath, 'assets/')) {
                    $imageUrl = url('/' . $cPath);
                } elseif (str_starts_with($cPath, 'http')) {
                    $imageUrl = $cPath;
                } else {
                    $imageUrl = url('/public/uploads/' . $cPath);
                }
              }
              ?>
              
              <?php if ($imageUrl): ?>
              <div class="court-card-image">
                <img src="<?= $imageUrl ?>" alt="<?= e($court['name']) ?>">
                <div class="court-card-status">
                  <?= $court['status'] === 'active' 
                      ? '<span class="badge bg-success">Active</span>' 
                      : '<span class="badge bg-secondary">Inactive</span>' ?>
                </div>
              </div>
              <?php else: ?>
              <div class="court-card-image court-card-dummy-image">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 225" width="400" height="225">
                  <rect width="400" height="225" fill="#0d1510"/>
                  <circle cx="200" cy="112.5" r="40" fill="#22c55e" opacity="0.2"/>
                  <path d="M160 112.5 L180 92.5 L220 92.5 L240 112.5 L220 132.5 L180 132.5 Z" fill="#22c55e" opacity="0.3"/>
                  <text x="200" y="170" font-family="Arial, sans-serif" font-size="14" fill="#86a892" text-anchor="middle">No Image</text>
                </svg>
                <div class="court-card-status">
                  <?= $court['status'] === 'active' 
                      ? '<span class="badge bg-success">Active</span>' 
                      : '<span class="badge bg-secondary">Inactive</span>' ?>
                </div>
              </div>
              <?php endif; ?>
              
              <div class="court-card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <div>
                    <h6 class="court-card-title mb-1"><?= e($court['name']) ?></h6>
                    <div class="court-card-meta">
                      <span class="badge bg-primary-soft"><?= e($court['court_number']) ?></span>
                      <span class="text-muted small">•</span>
                      <span class="text-muted small"><?= e($court['sport_type'] ?? 'Sport') ?></span>
                    </div>
                  </div>
                  <div class="text-end">
                    <div class="court-card-price">₹<?= number_format($court['price_per_hour'] ?? 0) ?></div>
                    <div class="text-muted" style="font-size:0.7rem;">per hour</div>
                  </div>
                </div>
                
                <?php if ($court['description']): ?>
                <p class="court-card-description"><?= e(substr($court['description'], 0, 80)) ?><?= strlen($court['description']) > 80 ? '...' : '' ?></p>
                <?php endif; ?>
                
                <div class="court-card-actions">
                  <a href="<?= url('/courts/' . $court['id'] . '/images') ?>" class="btn btn-xs btn-outline-primary">
                    <i class="bi bi-images me-1"></i>Images
                  </a>
                  <a href="<?= url('/courts/' . $court['id'] . '/edit') ?>" class="btn btn-xs btn-outline-secondary">
                    <i class="bi bi-pencil me-1"></i>Edit
                  </a>
                  <a href="<?= url('/bookings/slots?court_id=' . $court['id']) ?>" class="btn btn-xs btn-outline-success">
                    <i class="bi bi-calendar-check me-1"></i>Slots
                  </a>
                </div>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <?php else: ?>
    <div class="panel mb-4">
      <div class="panel-head">
        <h6 class="panel-title"><i class="bi bi-grid-3x3-gap me-2"></i>Courts</h6>
      </div>
      <div class="panel-body text-center py-5">
        <i class="bi bi-grid-3x3-gap d-block mb-3 text-muted" style="font-size:3rem;opacity:0.3;"></i>
        <p class="text-muted mb-3">No courts added yet</p>
        <a href="<?= url('/courts?venue_id='.$venue['id']) ?>" class="btn btn-primary">
          <i class="bi bi-plus-lg me-1"></i>Add Your First Court
        </a>
      </div>
    </div>
    <?php endif; ?>

    <!-- Image Gallery Component -->
    <?php
      require_once __DIR__ . '/../components/image-gallery.php';
      renderImageGallery('venue', $venue['id'], $images ?? []);
    ?>

    <!-- Booking Stats -->
    <div class="row g-3 mb-4">
      <div class="col-4">
        <div class="panel text-center">
          <div class="panel-body py-3">
            <div class="fw-900 text-primary" style="font-size:1.6rem;"><?= number_format($bookingStats['total'] ?? 0) ?></div>
            <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;">Total Bookings</div>
          </div>
        </div>
      </div>
      <div class="col-4">
        <div class="panel text-center">
          <div class="panel-body py-3">
            <div class="fw-900 text-success" style="font-size:1.6rem;"><?= number_format($bookingStats['confirmed'] ?? 0) ?></div>
            <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;">Confirmed</div>
          </div>
        </div>
      </div>
      <div class="col-4">
        <div class="panel text-center">
          <div class="panel-body py-3">
            <div class="fw-900 text-orange" style="font-size:1.6rem;">₹<?= number_format($bookingStats['revenue'] ?? 0) ?></div>
            <div class="text-muted" style="font-size:.72rem;text-transform:uppercase;letter-spacing:.05em;">Revenue</div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Bookings -->
    <?php if (!empty($recentBooks)): ?>
    <div class="panel">
      <div class="panel-head">
        <h6 class="panel-title"><i class="bi bi-calendar-event me-2"></i>Recent Bookings</h6>
        <a href="<?= url('/bookings?search='.urlencode($venue['name'])) ?>" class="btn btn-xs btn-outline-secondary">View All</a>
      </div>
      <div class="panel-body p-0">
        <table class="table table-hover mb-0">
          <thead><tr><th>Ref</th><th>Player</th><th>Date</th><th>Time</th><th>Amount</th><th>Status</th></tr></thead>
          <tbody>
            <?php foreach ($recentBooks as $b): ?>
            <tr>
              <td><a href="<?= url('/bookings/'.$b['id']) ?>" class="font-monospace small text-primary"><?= e($b['booking_reference']) ?></a></td>
              <td>
                <div class="small"><?= e($b['user_name'] ?? 'Walk-in') ?></div>
                <?php if ($b['user_phone']): ?><div class="text-muted" style="font-size:.7rem;"><?= e($b['user_phone']) ?></div><?php endif; ?>
              </td>
              <td class="small"><?= date('M j', strtotime($b['booking_date'])) ?></td>
              <td class="text-muted small"><?= substr($b['start_time'],0,5) ?>–<?= substr($b['end_time'],0,5) ?></td>
              <td class="fw-500 small">₹<?= number_format($b['amount']) ?></td>
              <td><?= statusBadge($b['status']) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php endif; ?>
  </div>

  <!-- ── Right col ─────────────────────────────────────────────── -->
  <div class="col-lg-4">

    <!-- Owner -->
    <div class="panel mb-3">
      <div class="panel-head"><h6 class="panel-title"><i class="bi bi-person-fill me-2"></i>Venue Owner</h6></div>
      <div class="panel-body">
        <div class="d-flex align-items-center gap-3">
          <div class="avatar-lg"><?= strtoupper(substr($venue['owner_name'] ?? 'O', 0, 1)) ?></div>
          <div>
            <div class="fw-700"><?= e($venue['owner_name'] ?? '—') ?></div>
            <div class="text-muted small"><?= e($venue['owner_email'] ?? '') ?></div>
            <?php if (!empty($venue['owner_phone'])): ?>
            <div class="text-muted small"><i class="bi bi-telephone me-1"></i><?= e($venue['owner_phone']) ?></div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>

    <?php if ($canAdmin): ?>

    <!-- Verification -->
    <?php if ($venue['verification_status'] === 'pending'): ?>
    <div class="panel mb-3">
      <div class="panel-head"><h6 class="panel-title"><i class="bi bi-shield-check me-2 text-warning"></i>Verification</h6></div>
      <div class="panel-body">
        <form action="<?= url('/venues/'.$venue['id'].'/approve') ?>" method="POST" class="mb-2">
          <?= csrf_field() ?>
          <button class="btn btn-success w-100"><i class="bi bi-check-circle me-2"></i>Approve</button>
        </form>
        <button class="btn btn-danger w-100" onclick="showRejectModal(<?= $venue['id'] ?>)">
          <i class="bi bi-x-circle me-2"></i>Reject
        </button>
      </div>
    </div>
    <?php endif; ?>

    <!-- Verified Badge -->
    <div class="panel mb-3">
      <div class="panel-head"><h6 class="panel-title"><i class="bi bi-patch-check me-2 text-primary"></i>Verified Badge</h6></div>
      <div class="panel-body">
        <?php if (!$venue['is_verified']): ?>
        <form action="<?= url('/venues/'.$venue['id'].'/badge/assign') ?>" method="POST">
          <?= csrf_field() ?>
          <label class="form-label-sm">Badge Expiry Date *</label>
          <input type="date" name="badge_expires_at" class="form-control mb-2"
                 min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
          <label class="form-label-sm">Verification Notes</label>
          <textarea name="notes" class="form-control mb-3" rows="2" placeholder="e.g. Physically inspected — all facilities verified"></textarea>
          <button class="btn btn-primary w-100">
            <i class="bi bi-patch-check me-2"></i>Assign Badge
          </button>
        </form>
        <?php else: ?>
        <div class="d-flex align-items-center gap-2 mb-3 p-2 rounded" style="background:rgba(34,197,94,0.08);">
          <i class="bi bi-patch-check-fill text-success fs-4"></i>
          <div>
            <div class="fw-600 small text-success">Badge Active</div>
            <div class="text-muted" style="font-size:.74rem;">
              Expires <?= $venue['badge_expires_at'] ? date('M j, Y', strtotime($venue['badge_expires_at'])) : 'N/A' ?>
            </div>
          </div>
        </div>
        <?php if ($venue['verification_notes']): ?>
        <p class="text-muted small mb-3"><?= e($venue['verification_notes']) ?></p>
        <?php endif; ?>
        <form action="<?= url('/venues/'.$venue['id'].'/badge/remove') ?>" method="POST"
              onsubmit="return confirm('Remove verified badge from this venue?')">
          <?= csrf_field() ?>
          <button class="btn btn-outline-danger w-100">
            <i class="bi bi-shield-x me-2"></i>Remove Badge
          </button>
        </form>
        <?php endif; ?>
      </div>
    </div>

    <!-- Status Controls -->
    <div class="panel mb-3">
      <div class="panel-head"><h6 class="panel-title"><i class="bi bi-toggles me-2"></i>Venue Controls</h6></div>
      <div class="panel-body d-grid gap-2">
        <form action="<?= url('/venues/'.$venue['id'].'/toggle') ?>" method="POST"
              onsubmit="return confirm('Change venue status?')">
          <?= csrf_field() ?>
          <button class="btn btn-outline-<?= $venue['status']==='suspended' ? 'success' : 'warning' ?> w-100">
            <i class="bi bi-<?= $venue['status']==='suspended' ? 'play-circle' : 'pause-circle' ?> me-2"></i>
            <?= $venue['status']==='suspended' ? 'Reactivate Venue' : 'Suspend Venue' ?>
          </button>
        </form>
        <form action="<?= url('/venues/'.$venue['id'].'/delete') ?>" method="POST"
              onsubmit="return confirm('Permanently delete this venue? This cannot be undone.')">
          <?= csrf_field() ?>
          <button class="btn btn-outline-danger w-100">
            <i class="bi bi-trash me-2"></i>Delete Venue
          </button>
        </form>
      </div>
    </div>

    <?php endif; ?>

    <a href="<?= url('/venues') ?>" class="btn btn-outline-secondary w-100">
      <i class="bi bi-arrow-left me-1"></i>Back to Venues
    </a>
  </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="rejectForm" method="POST">
        <?= csrf_field() ?>
        <div class="modal-header">
          <h6 class="modal-title"><i class="bi bi-x-circle-fill text-danger me-2"></i>Reject Venue</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label class="form-label-sm">Rejection Reason * <small class="text-muted">(min 10 chars)</small></label>
          <textarea name="notes" class="form-control" rows="4"
                    placeholder="e.g. Images are unclear, location cannot be verified. Please resubmit with updated photos."
                    minlength="10" required></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger"><i class="bi bi-x-circle me-1"></i>Reject Venue</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
function showRejectModal(id) {
  document.getElementById('rejectForm').action = '<?= url('/venues/') ?>' + id + '/reject';
  new bootstrap.Modal(document.getElementById('rejectModal')).show();
}

function toggleAllCourts() {
  const hiddenCourts = document.querySelectorAll('.hidden-court');
  const btn = document.getElementById('toggleCourtsBtn');
  const icon = btn.querySelector('i');
  
  const isShowing = btn.textContent.includes('Show All');
  
  hiddenCourts.forEach(court => {
    if (isShowing) {
      court.style.display = 'block';
      setTimeout(() => court.style.opacity = '1', 10);
    } else {
      court.style.opacity = '0';
      setTimeout(() => court.style.display = 'none', 300);
    }
  });
  
  if (isShowing) {
    btn.innerHTML = '<i class="bi bi-eye-slash me-1"></i>Show Less';
  } else {
    btn.innerHTML = '<i class="bi bi-eye me-1"></i>Show All';
  }
}
</script>

<style>
/* ========== Venue Banner ========== */
.venue-banner {
  position: relative;
  width: 100%;
  aspect-ratio: 16 / 4;
  overflow: hidden;
  border-radius: 16px;
  margin-bottom: 1.5rem;
  background: linear-gradient(135deg, #0a0f0b, #0d1510, #111a13);
}

.venue-banner-image {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  object-position: center;
}

.venue-banner-dummy {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.venue-banner-overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 70%;
  background: linear-gradient(
    to top,
    rgba(10, 15, 11, 0.95) 0%,
    rgba(13, 21, 16, 0.85) 25%,
    rgba(13, 21, 16, 0.6) 50%,
    transparent 100%
  );
  z-index: 1;
}

.venue-banner-content {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  padding: 2rem;
  z-index: 2;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  text-align: center;
}

.venue-banner-title {
  font-size: 2.5rem;
  font-weight: 900;
  color: #f0fdf4;
  margin: 0 0 0.75rem 0;
  text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
  letter-spacing: -0.02em;
}

.venue-banner-meta {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.95rem;
  color: #d1e7d9;
  text-shadow: 0 1px 4px rgba(0, 0, 0, 0.4);
}

.venue-banner-meta i {
  opacity: 0.9;
}

/* Responsive Banner */
@media (max-width: 768px) {
  .venue-banner {
    aspect-ratio: 16 / 6;
  }
  
  .venue-banner-title {
    font-size: 1.75rem;
  }
  
  .venue-banner-meta {
    font-size: 0.85rem;
    flex-wrap: wrap;
    justify-content: center;
  }
  
  .venue-banner-content {
    padding: 1.5rem 1rem;
  }
}

/* ========== Courts Section ========== */
.hidden-court {
  opacity: 0;
  transition: opacity 0.3s ease;
}

.court-card {
  background: rgba(13,22,15,0.5);
  border: 1px solid rgba(134,168,146,0.2);
  border-radius: 12px;
  overflow: hidden;
  transition: all 0.3s;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.court-card:hover {
  border-color: rgba(34,197,94,0.4);
  box-shadow: 0 4px 16px rgba(34,197,94,0.1);
  transform: translateY(-2px);
}

.court-card-image {
  position: relative;
  width: 100%;
  padding-top: 56.25%; /* 16:9 aspect ratio */
  background: rgba(0,0,0,0.3);
  overflow: hidden;
}

.court-card-image img {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.court-card-dummy-image {
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(13,21,16,0.8);
}

.court-card-dummy-image svg {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
}

.court-card-no-image {
  display: flex;
  align-items: center;
  justify-content: center;
  background: linear-gradient(135deg, rgba(13,22,15,0.8), rgba(10,15,11,0.9));
}

.court-card-no-image i {
  position: absolute;
  font-size: 3rem;
  color: rgba(134,168,146,0.3);
}

.court-card-status {
  position: absolute;
  top: 0.75rem;
  right: 0.75rem;
  z-index: 1;
}

.court-card-body {
  padding: 1rem;
  flex: 1;
  display: flex;
  flex-direction: column;
}

.court-card-title {
  font-size: 1rem;
  font-weight: 700;
  color: #f0fdf4;
  margin: 0;
}

.court-card-meta {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.25rem;
}

.court-card-price {
  font-size: 1.1rem;
  font-weight: 700;
  color: #22c55e;
}

.court-card-description {
  font-size: 0.85rem;
  color: #86a892;
  margin: 0.5rem 0 1rem;
  line-height: 1.5;
}

.court-card-actions {
  display: flex;
  gap: 0.5rem;
  margin-top: auto;
  flex-wrap: wrap;
}

.bg-primary-soft {
  background-color: rgba(59,130,246,0.15);
  color: #60a5fa;
  font-size: 0.75rem;
  font-weight: 600;
  padding: 0.25rem 0.5rem;
}
</style>
