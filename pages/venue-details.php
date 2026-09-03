<?php include 'includes/header.php'; ?>

<style>
/* ── Modern Premium & Mobile-Friendly CSS ─────────────────── */
.glass-card small,
.court-card small,
.court-card .text-secondary,
#courtDetailModal small,
#courtDetailModal .text-secondary {
    color: var(--text-muted) !important;
    font-weight: 500 !important;
}
.text-muted {
    color: var(--text-muted) !important;
}
.gallery-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    grid-template-rows: 240px 240px;
    gap: 12px;
    border-radius: 20px;
    overflow: hidden;
    margin-bottom: 28px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}
.gallery-grid .gallery-item:first-child {
    grid-row: span 2;
}
.gallery-item {
    overflow: hidden;
    position: relative;
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border-glass);
    border-radius: 12px;
}
.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}
.gallery-item:hover img { transform: scale(1.06); }

/* Mobile Gallery Layout */
@media (max-width: 768px) {
    .gallery-grid {
        grid-template-columns: 1fr;
        grid-template-rows: 220px 140px 140px;
        gap: 8px;
    }
    .gallery-grid .gallery-item:first-child {
        grid-row: span 1;
    }
}

/* ── Occupancy Banner ──────────────────────── */
.occupancy-card {
    background: rgba(15, 23, 42, 0.8);
    border: 1px solid rgba(56, 135, 198, 0.35);
    border-radius: 16px;
    padding: 20px;
    backdrop-filter: blur(12px);
    margin-bottom: 28px;
}
.occupancy-bar-bg {
    height: 10px;
    background: rgba(255,255,255,0.1);
    border-radius: 99px;
    overflow: hidden;
}
.occupancy-bar-fill {
    height: 100%;
    background: linear-gradient(90deg, #3887C6, #2a6ba0);
    border-radius: 99px;
    transition: width 0.8s ease;
}

/* ── Courts Grid ──────────────────────────── */
.court-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid var(--border-glass);
    border-radius: 14px;
    padding: 18px;
    cursor: pointer;
    transition: all 0.3s ease;
}
.court-card:hover {
    border-color: rgba(56,135,198,0.5);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(56,135,198,0.15);
}

/* ── Amenities Grid ───────────────────────── */
#venue-amenities {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
}
.amenity-item {
    background: rgba(255,255,255,0.03);
    border: 1px solid var(--border-glass);
    border-radius: 12px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    color: var(--text-primary);
    font-size: 0.88rem;
    font-weight: 500;
}
.amenity-item i { color: #3887C6; font-size: 1.2rem; flex-shrink: 0; }

/* ── Reviews ─────────────────────────────── */
.review-card {
    background: rgba(255,255,255,0.02);
    border: 1px solid var(--border-glass);
    border-radius: 14px;
    padding: 18px;
    margin-bottom: 14px;
}
.review-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 8px;
}
.review-avatar {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3887C6, #2a6ba0);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 1rem;
    flex-shrink: 0;
}

/* ── Time Slots Grid ─────────────────────── */
#venue-availability {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(105px, 1fr));
    gap: 10px;
    max-height: 320px;
    overflow-y: auto;
    padding-right: 4px;
}
.time-slot {
    border-radius: 10px;
    padding: 10px 6px;
    font-size: 0.82rem;
    font-weight: 600;
    text-align: center;
    background: rgba(56, 135, 198, 0.08);
    border: 1px solid rgba(56, 135, 198, 0.4);
    color: #5a9fd4;
    cursor: pointer;
    transition: all 0.2s ease;
    line-height: 1.3;
}
.time-slot:hover:not(:disabled) {
    background: rgba(56, 135, 198, 0.25);
    border-color: #3887C6;
    transform: translateY(-1px);
}
.time-slot.selected {
    background: #3887C6 !important;
    color: #052e16 !important;
    border-color: #3887C6 !important;
    font-weight: 700;
}
.time-slot:disabled, .time-slot.disabled {
    background: rgba(255, 255, 255, 0.02) !important;
    border-color: rgba(255, 255, 255, 0.1) !important;
    color: #64748b !important;
    opacity: 0.55;
    cursor: not-allowed;
    text-decoration: line-through;
}

/* Sticky Widget on Desktop */
@media (min-width: 992px) {
    .booking-widget {
        position: sticky;
        top: 100px;
    }
}
@media (max-width: 991px) {
    .booking-widget {
        margin-top: 20px;
    }
}
</style>

<!-- Page Wrapper -->
<div class="container py-4 py-md-5" style="margin-top:75px; min-height:100vh;">

    <!-- Loading State -->
    <div id="venue-loader" class="text-center py-5">
        <div class="spinner-border text-success mb-3" style="width:3rem;height:3rem;" role="status"></div>
        <p class="text-muted">Loading playground details…</p>
    </div>

    <!-- Main Content -->
    <div id="venue-content" style="display:none;">

        <!-- Breadcrumb -->
        <a href="venues" class="text-success text-decoration-none fw-semibold mb-3 d-inline-flex align-items-center gap-2">
            <i class="bi bi-arrow-left"></i> Back to Playgrounds
        </a>

        <div class="row g-4">

            <!-- ══ LEFT COLUMN ══════════════════════════════════ -->
            <div class="col-lg-8">

                <!-- Header Info -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                    <h1 id="venue-name" class="display-6 fw-bold text-white mb-0 lh-sm">—</h1>
                    <div id="venue-verified-badge"></div>
                </div>

                <!-- Meta Row -->
                <div class="d-flex flex-wrap align-items-center gap-2 gap-md-3 mb-3 text-secondary small">
                    <div id="venue-rating" class="text-warning d-flex align-items-center gap-1 fw-semibold"></div>
                    <span class="text-muted">·</span>
                    <div id="venue-location" class="d-flex align-items-center gap-1"></div>
                    <span class="text-muted d-none d-sm-inline">·</span>
                    <div id="venue-timing" class="text-success d-flex align-items-center gap-1"></div>
                </div>

                <!-- Sports Tags -->
                <div id="venue-sports-tags" class="d-flex flex-wrap gap-2 mb-4"></div>

                <!-- Image Gallery Grid -->
                <div id="venue-gallery"></div>

                <!-- Daily Occupancy & Slot Analytics Banner -->
                <div class="occupancy-card">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold text-white fs-6">
                            <i class="bi bi-bar-chart-fill text-success me-2"></i>Daily Slot Occupancy
                        </span>
                        <span id="occupancy-percentage" class="badge bg-success fs-6">0% Occupied</span>
                    </div>
                    <div class="occupancy-bar-bg mb-3">
                        <div id="occupancy-bar-fill" class="occupancy-bar-fill" style="width: 0%;"></div>
                    </div>
                    <div class="row g-2 text-center small">
                        <div class="col-4">
                            <div class="p-2 glass-card rounded-3">
                                <small class="d-block text-success fw-semibold mb-1">Total Slots</small>
                                <strong id="stat-total-slots" class="text-white fs-6">0</strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 glass-card rounded-3">
                                <small class="d-block text-danger fw-semibold mb-1">Booked</small>
                                <strong id="stat-booked-slots" class="text-danger fs-6">0</strong>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 glass-card rounded-3">
                                <small class="d-block text-success fw-semibold mb-1">Available</small>
                                <strong id="stat-available-slots" class="text-success fs-6">0</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="glass-card p-3 p-md-4 mb-4">
                    <h5 class="text-white fw-bold mb-3">About this Playground</h5>
                    <p id="venue-description" class="text-secondary mb-0" style="line-height:1.8; font-size:0.95rem;"></p>
                </div>

                <!-- Courts Section -->
                <div class="glass-card p-3 p-md-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-white fw-bold mb-0">Courts &amp; Pitches</h5>
                        <span id="courts-count-badge" class="badge bg-success">0 Courts</span>
                    </div>
                    <div id="venue-courts" class="row g-3">
                        <p class="text-muted small">Loading courts…</p>
                    </div>
                </div>

                <!-- Amenities Section -->
                <div class="glass-card p-3 p-md-4 mb-4">
                    <h5 class="text-white fw-bold mb-3">Amenities &amp; Facilities</h5>
                    <div id="venue-amenities">
                        <p class="text-muted small">Loading amenities…</p>
                    </div>
                </div>

                <!-- Location & Map -->
                <div class="glass-card p-3 p-md-4 mb-4">
                    <h5 class="text-white fw-bold mb-3">Location &amp; Address</h5>
                    <p id="venue-full-address" class="text-secondary mb-3 small"></p>
                    <a id="venue-map-link" href="#" target="_blank" rel="noopener" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-geo-alt me-1"></i>Open in Google Maps
                    </a>
                </div>

                <!-- Reviews Section -->
                <div class="glass-card p-3 p-md-4 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="text-white fw-bold mb-0">Reviews &amp; Ratings</h5>
                        <div id="reviews-summary-badge" class="text-warning fw-bold small"></div>
                    </div>
                    <div id="venue-reviews">
                        <p class="text-muted small">Loading reviews…</p>
                    </div>
                </div>

            </div>

            <!-- ══ RIGHT COLUMN — Booking Widget ════════════════ -->
            <div class="col-lg-4">
                <div class="booking-widget glass-card p-3 p-md-4">

                    <!-- Price Header -->
                    <div class="mb-3 pb-3" style="border-bottom:1px solid var(--border-glass);">
                        <small class="text-muted d-block mb-1">Starting Price</small>
                        <span id="venue-price" class="fw-bold text-white" style="font-size:2rem;">₹—/hr</span>
                    </div>

                    <!-- Date Selection -->
                    <div class="mb-3">
                        <label for="booking-date" class="form-label text-secondary small fw-semibold">
                            <i class="bi bi-calendar3 me-1"></i>Select Date
                        </label>
                        <input type="date" id="booking-date" class="form-control glass-input">
                    </div>

                    <!-- Slot Selector Grid -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label text-secondary small fw-semibold mb-0">
                                <i class="bi bi-clock me-1"></i>Hourly Slots
                            </label>
                            <small class="text-muted"><span class="badge bg-success p-1 me-1"></span>Available</small>
                        </div>
                        <div id="venue-availability">
                            <p class="text-muted small">Select a date to see slots.</p>
                        </div>
                    </div>

                    <!-- Booking Action Button (Opens App Download Modal) -->
                    <button id="book-slot-btn" class="btn btn-premium w-100 py-3 fw-bold mb-3" disabled>
                        <i class="bi bi-calendar-check me-2"></i>Select Slot to Book
                    </button>

                    <!-- WhatsApp Contact Button -->
                    <a id="whatsapp-btn" href="#" target="_blank" rel="noopener"
                       class="btn w-100 py-3 fw-bold mb-3"
                       style="background:#25d366; border:none; color:#fff; border-radius:12px;">
                        <i class="bi bi-whatsapp me-2"></i>Book via WhatsApp
                    </a>

                    <!-- Direct Contact Section -->
                    <div class="pt-3" style="border-top:1px solid var(--border-glass);">
                        <p class="text-center text-muted small mb-2">Reach out to Playground Manager:</p>
                        <div class="d-flex flex-column gap-2 align-items-center">
                            <a id="venue-phone" href="#"
                               class="text-success text-decoration-none small fw-semibold d-flex align-items-center gap-2">
                                <i class="bi bi-telephone-fill"></i><span></span>
                            </a>
                            <a id="venue-email" href="#"
                               class="text-success text-decoration-none small fw-semibold d-flex align-items-center gap-2">
                                <i class="bi bi-envelope-fill"></i><span></span>
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

</div>

<!-- ── APP DOWNLOAD / MOBILE BOOKING MODAL ─────────────────── -->
<div class="modal fade" id="appDownloadModal" tabindex="-1" aria-labelledby="appDownloadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-0 text-white p-2">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="appDownloadModalLabel">
                    <i class="bi bi-phone-vibrate text-success fs-4"></i> Complete Booking on App
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-3">
                <!-- Slot Summary Box -->
                <div class="glass-card p-3 mb-3 text-start border-success" style="background: rgba(56,135,198,0.08);">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <strong id="modal-venue-title" class="text-white fs-6">Playground Name</strong>
                        <span id="modal-slot-price" class="badge bg-success fs-6">₹1,000</span>
                    </div>
                    <div class="text-secondary small d-flex gap-3 flex-wrap">
                        <span><i class="bi bi-calendar3 me-1 text-success"></i><span id="modal-slot-date">Today</span></span>
                        <span><i class="bi bi-clock me-1 text-success"></i><span id="modal-slot-time">08:00 AM</span></span>
                    </div>
                </div>

                <p class="text-secondary mb-3 px-2" style="font-size: 0.92rem; line-height: 1.5;">
                    To lock your slot in real-time and get instant digital entry pass, download the <strong>Findownn Mobile App</strong>.
                </p>

                <!-- QR Code Box -->
                <div class="d-inline-block p-3 bg-white rounded-4 mb-3 shadow-lg border border-2 border-success">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=https://findownn.com/download" alt="Download QR Code" style="width: 140px; height: 140px;" class="d-block mx-auto">
                    <small class="text-dark d-block mt-2 fw-bold" style="font-size:0.8rem;"><i class="bi bi-qr-code-scan me-1"></i>Scan to Download</small>
                </div>

                <!-- Store Buttons -->
                <div class="d-flex gap-2 justify-content-center flex-wrap mb-2">
                    <a href="#" class="btn btn-premium px-4 py-2" onclick="alert('Findownn App is launching soon on Google Play Store!'); return false;">
                        <i class="bi bi-google-play me-2"></i>Play Store
                    </a>
                    <a href="#" class="btn btn-outline-light px-4 py-2" style="border-color:rgba(255,255,255,0.2)" onclick="alert('Findownn App is launching soon on Apple App Store!'); return false;">
                        <i class="bi bi-apple me-2"></i>App Store
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── COURT DETAILS MODAL ─────────────────── -->
<div class="modal fade" id="courtDetailModal" tabindex="-1" aria-labelledby="courtDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content glass-card border-0 text-white p-2">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="courtDetailModalLabel">
                    <i class="bi bi-shield-check text-success fs-4"></i> <span id="court-modal-title">Court Details</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                <div id="court-modal-img-container" class="mb-3 rounded-3 overflow-hidden" style="height:180px; position:relative; background:#0d1510; display:none;">
                    <img id="court-modal-img" src="" alt="Court Photo" style="width:100%; height:100%; object-fit:cover;">
                </div>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span id="court-modal-number" class="badge bg-dark border border-success text-success fs-6">Court #C1</span>
                    <span id="court-modal-price" class="badge bg-success fs-6">₹1,000/hr</span>
                </div>

                <p id="court-modal-desc" class="text-secondary small mb-3"></p>

                <div class="row g-2 text-center small mb-3">
                    <div class="col-4">
                        <div class="p-2 glass-card rounded-3">
                            <small class="d-block text-success fw-semibold mb-1">Surface</small>
                            <strong id="court-modal-surface" class="text-white">Turf</strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 glass-card rounded-3">
                            <small class="d-block text-success fw-semibold mb-1">Capacity</small>
                            <strong id="court-modal-capacity" class="text-white">16 Players</strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 glass-card rounded-3">
                            <small class="d-block text-success fw-semibold mb-1">Lighting</small>
                            <strong id="court-modal-lighting" class="text-white">LED Lighting</strong>
                        </div>
                    </div>
                </div>

                <button class="btn btn-premium w-100 py-2 mt-2" data-bs-dismiss="modal">
                    <i class="bi bi-check-lg me-1"></i>Got It
                </button>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
