<?php include 'includes/header.php'; ?>

<!-- Page Header -->
<header class="page-header">
    <div class="glow-orb glow-orb-top-right"></div>
    <div class="container text-center position-relative z-1 animate-on-scroll">
        <span class="badge-premium mb-3">Bhuj Playgrounds</span>
        <h1 class="display-3 fw-bold text-white">Explore Playgrounds</h1>
        <p class="text-secondary lead mx-auto" style="max-width: 600px;">
            Find the perfect playground for your next game. Filter by sport, search by name, and check slot prices.
        </p>
    </div>
</header>

<!-- Filter & Search Section -->
<section class="py-4 position-relative z-2">
    <div class="container">
        <div class="glass-card p-4 animate-on-scroll">
            <div class="row g-3 align-items-center">
                <!-- Search Input -->
                <div class="col-lg-5">
                    <div class="d-flex align-items-center position-relative">
                        <i class="bi bi-search text-success position-absolute ms-3"></i>
                        <input type="text" id="venue-search" class="form-control glass-input w-100 ps-5" placeholder="Search by playground name or location in Bhuj...">
                    </div>
                </div>

                <!-- Sport Filter Buttons (static - All Sports, Box Cricket, Pickleball) -->
                <div class="col-lg-7 d-flex gap-2 justify-content-lg-end flex-wrap" id="sport-filter-buttons">
                    <button class="filter-btn" data-slug="all">All Sports</button>
                    <button class="filter-btn" data-slug="box-cricket"><i class="bi bi-circle-fill me-1" style="color:#22c55e;font-size:0.5rem;"></i>Box Cricket</button>
                    <button class="filter-btn" data-slug="pickleball"><i class="bi bi-circle-fill me-1" style="color:#3b82f6;font-size:0.5rem;"></i>Pickleball</button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Playground Cards Grid -->
<section class="py-5 position-relative">
    <div class="container">
        <!-- Loading State -->
        <div id="loading-spinner" class="text-center py-5">
            <div class="spinner-border text-success mb-3" role="status"></div>
            <p class="text-muted">Loading playgrounds...</p>
        </div>

        <!-- Playgrounds Grid -->
        <div class="row g-4" id="venues-container"></div>
        
        <!-- Load More Button -->
        <div class="text-center mt-5">
            <button id="load-more-btn" class="btn btn-premium px-5 py-3" style="display: none;">
                Load More Playgrounds
            </button>
        </div>
    </div>
</section>

<!-- App Booking Modal -->
<div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-white" id="bookingModalLabel">Book on Mobile App</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="mb-4 text-success" style="font-size: 3rem;">
                    <i class="bi bi-phone-vibrate"></i>
                </div>
                <h5 class="text-white mb-3" id="modal-venue-name">Playground Name</h5>
                <p class="text-secondary mb-4 px-3" style="font-size: 0.95rem; line-height: 1.6;">
                    To secure your playing slot and check real-time availability, please download the <strong>Findownn</strong> mobile app.
                </p>
                <div class="d-inline-block p-3 bg-white rounded-3 mb-4 shadow" style="border: 2px solid var(--primary);">
                    <div style="width: 140px; height: 140px; background: url('https://api.qrserver.com/v1/create-qr-code/?size=140x140&data=https://findownn.com/download') no-repeat center; background-size: contain;"></div>
                    <small class="text-dark d-block mt-2 fw-bold">Scan to Download</small>
                </div>
                <div class="d-flex gap-2 justify-content-center">
                    <a href="#" class="btn btn-premium btn-sm" onclick="alert('App is coming soon!'); return false;"><i class="bi bi-google-play me-1"></i> Play Store</a>
                    <a href="#" class="btn btn-premium btn-sm" style="background:#1e293b; border-color:rgba(255,255,255,0.05)" onclick="alert('App is coming soon!'); return false;"><i class="bi bi-apple me-1"></i> App Store</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
