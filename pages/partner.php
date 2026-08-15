<?php include 'includes/header.php'; ?>

<!-- Leaflet CSS for Map Selector -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />

<style>
#partnerMap {
    height: 320px;
    width: 100%;
    border-radius: 14px;
    border: 1px solid rgba(255,255,255,0.15);
    z-index: 1;
}
select.form-select.glass-input {
    background-color: #0d1711 !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2322c55e' stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
    background-position: right 0.85rem center !important;
    background-size: 14px 10px !important;
    background-repeat: no-repeat !important;
    border: 1px solid rgba(34, 197, 94, 0.35) !important;
    color: #ffffff !important;
    border-radius: 10px !important;
    padding: 11px 36px 11px 16px !important;
    font-size: 0.95rem !important;
    font-weight: 500 !important;
    cursor: pointer !important;
    -webkit-appearance: none !important;
    -moz-appearance: none !important;
    appearance: none !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25) !important;
    transition: all 0.25s ease-in-out !important;
}
select.form-select.glass-input:focus {
    background-color: #060b08 !important;
    border-color: #22c55e !important;
    box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.25) !important;
    color: #ffffff !important;
}
select.form-select.glass-input option {
    background-color: #121d15 !important;
    color: #ffffff !important;
}
.validation-error-msg {
    color: #ef4444;
    font-size: 0.82rem;
    margin-top: 4px;
    display: none;
}
.glass-input.is-invalid {
    border-color: #ef4444 !important;
    box-shadow: 0 0 0 2px rgba(239,68,68,0.2) !important;
}
.map-coords-badge {
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.3);
    color: #4ade80;
    font-size: 0.8rem;
    padding: 4px 10px;
    border-radius: 20px;
}
/* Map Search Autocomplete */
.map-search-wrapper {
    position: relative;
}
#mapSuggestions {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 9999;
    background: #0d1711;
    border: 1px solid rgba(34,197,94,0.35);
    border-top: none;
    border-radius: 0 0 10px 10px;
    max-height: 220px;
    overflow-y: auto;
    box-shadow: 0 8px 24px rgba(0,0,0,0.5);
}
#mapSuggestions .suggestion-item {
    padding: 10px 16px;
    cursor: pointer;
    color: #d1fae5;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 1px solid rgba(34,197,94,0.1);
    transition: background 0.15s;
}
#mapSuggestions .suggestion-item:last-child {
    border-bottom: none;
    border-radius: 0 0 10px 10px;
}
#mapSuggestions .suggestion-item:hover,
#mapSuggestions .suggestion-item.active {
    background: rgba(34,197,94,0.15);
    color: #ffffff;
}
#mapSuggestions .suggestion-item i {
    color: #4ade80;
    font-size: 0.85rem;
    flex-shrink: 0;
}
#mapSuggestions .suggestion-item .suggestion-coords {
    margin-left: auto;
    font-size: 0.75rem;
    color: #6ee7b7;
    opacity: 0.7;
}
</style>

<!-- Page Header -->
<header class="page-header">
    <div class="glow-orb glow-orb-top-right"></div>
    <div class="container text-center position-relative z-1 animate-on-scroll">
        <span class="badge-premium mb-3">List Your Playground</span>
        <h1 class="display-3 fw-bold text-white">Own a Sports Playground?</h1>
        <p class="text-secondary lead mx-auto" style="max-width: 600px;">
            Join Bhuj's premium playground network. Grow your customer base, eliminate empty slots, and manage operations automatically.
        </p>
    </div>
</header>

<!-- Why Partner Section -->
<section class="py-5 my-5 position-relative">
    <div class="container">
        
        <div class="text-center mb-5 animate-on-scroll">
            <span class="badge-premium mb-3">Benefits</span>
            <h2 class="display-4 fw-bold text-white mb-2">Why List With Findownn?</h2>
            <p class="text-secondary">We give you the tech tools and player reach to run a highly profitable sports playground business.</p>
        </div>

        <div class="row g-4">
            
            <div class="col-md-4 animate-on-scroll">
                <div class="glass-card p-4 h-100">
                    <div class="step-icon-box mb-4"><i class="bi bi-graph-up"></i></div>
                    <h4 class="text-white mb-2" style="font-size: 1.2rem;">Increase Bookings</h4>
                    <p class="text-secondary mb-0" style="font-size: 0.95rem; line-height: 1.6;">Reach thousands of active players in Bhuj. Fill morning, afternoon, and late-night slot dead zones easily.</p>
                </div>
            </div>

            <div class="col-md-4 animate-on-scroll delay-100">
                <div class="glass-card p-4 h-100">
                    <div class="step-icon-box mb-4"><i class="bi bi-cpu"></i></div>
                    <h4 class="text-white mb-2" style="font-size: 1.2rem;">Smart Management</h4>
                    <p class="text-secondary mb-0" style="font-size: 0.95rem; line-height: 1.6;">Automate court scheduling. Say goodbye to manual ledger registers, double-booking errors, and phone call stress.</p>
                </div>
            </div>

            <div class="col-md-4 animate-on-scroll delay-200">
                <div class="glass-card p-4 h-100">
                    <div class="step-icon-box mb-4"><i class="bi bi-cash-stack"></i></div>
                    <h4 class="text-white mb-2" style="font-size: 1.2rem;">Instant Settlement</h4>
                    <p class="text-secondary mb-0" style="font-size: 0.95rem; line-height: 1.6;">Receive digital payments securely. Earnings are credited directly to your bank account weekly with clear statements.</p>
                </div>
            </div>

        </div>

    </div>
</section>

<!-- Pricing & Subscription Plans Section -->
<section id="pricing-plans" class="py-5 position-relative">
    <div class="container">
        
        <div class="text-center mb-5 animate-on-scroll">
            <span class="badge-premium mb-3">Pricing Plans</span>
            <h2 class="display-4 fw-bold text-white mb-2">Flexible Subscription Plans</h2>
            <p class="text-secondary">Marketplace model — monthly fee plus a platform fee per successful booking. Upgrade as you grow.</p>
        </div>

        <div class="row justify-content-center mb-4 animate-on-scroll">
            <div class="col-lg-10">
                <div class="glass-card p-3 p-md-4">
                    <div class="row g-3 text-center small">
                        <div class="col-6 col-md-3"><span class="text-secondary d-block">Starter</span><strong class="text-white">₹0/mo · 5%</strong></div>
                        <div class="col-6 col-md-3"><span class="text-secondary d-block">Growth</span><strong class="text-white">₹999/mo · 3%</strong></div>
                        <div class="col-6 col-md-3"><span class="text-secondary d-block">Professional</span><strong class="text-success">₹2,499/mo · 1%</strong></div>
                        <div class="col-6 col-md-3"><span class="text-secondary d-block">Enterprise</span><strong class="text-white">Custom · Negotiable</strong></div>
                    </div>
                </div>
            </div>
        </div>

        <?php
        $plansList = [];
        try {
            $dsn = "mysql:host=localhost;dbname=findownn_admin;charset=utf8mb4";
            $pdo = new PDO($dsn, "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo->query("SELECT * FROM subscription_plans WHERE is_active = 1 ORDER BY sort_order ASC");
            $plansList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $plansList = [
                [
                    'name' => 'Starter',
                    'slug' => 'starter',
                    'price' => 0,
                    'platform_fee_percent' => 5,
                    'billing_cycle' => 'monthly',
                    'description' => 'Best for new venue owners starting with Findownn',
                    'max_venues' => 1,
                    'is_featured' => 0,
                    'features' => "1 Playground Listing\nOnline Booking Management\nOnline Payment Collection\nBooking Calendar\nEmail Support"
                ],
                [
                    'name' => 'Growth',
                    'slug' => 'growth',
                    'price' => 999,
                    'platform_fee_percent' => 3,
                    'billing_cycle' => 'monthly',
                    'description' => 'Best for growing sports venues',
                    'max_venues' => 3,
                    'is_featured' => 0,
                    'features' => "Everything in Starter\nWhatsApp Booking Confirmation\nWeekday & Weekend Pricing\nCustomer Database\nBooking Reports\nEmail & Chat Support"
                ],
                [
                    'name' => 'Professional',
                    'slug' => 'professional',
                    'price' => 2499,
                    'platform_fee_percent' => 1,
                    'billing_cycle' => 'monthly',
                    'description' => 'Best for professional venue owners who want to automate and grow',
                    'max_venues' => 10,
                    'is_featured' => 1,
                    'features' => "Everything in Growth\nPartial Payment Support\nQR Code Check-in\nStaff Accounts\nVerified Venue Badge\nFeatured Listing\nPriority Support"
                ],
                [
                    'name' => 'Enterprise',
                    'slug' => 'enterprise',
                    'price' => 0,
                    'platform_fee_percent' => null,
                    'billing_cycle' => 'monthly',
                    'description' => 'Best for clubs, academies & multi-location businesses',
                    'max_venues' => 999,
                    'is_featured' => 0,
                    'features' => "Everything in Professional\nUnlimited Venue Management\nMulti-location Dashboard\nDedicated Account Manager\nCustom Reports\nCustom Pricing"
                ]
            ];
        }
        ?>

        <div class="row g-4 justify-content-center">
            <?php foreach ($plansList as $p): ?>
                <?php 
                $isFeatured = !empty($p['is_featured']);
                $slug = $p['slug'] ?? '';
                if ($slug === 'enterprise') {
                    $priceDisplay = 'Custom';
                } elseif ((float)$p['price'] == 0) {
                    $priceDisplay = '₹0';
                } else {
                    $priceDisplay = '₹' . number_format((float)$p['price']);
                }
                $feeDisplay = ($slug === 'enterprise')
                    ? 'Negotiable platform fee'
                    : (($p['platform_fee_percent'] ?? '') !== '' && $p['platform_fee_percent'] !== null
                        ? rtrim(rtrim(number_format((float)$p['platform_fee_percent'], 2), '0'), '.') . '% platform fee per booking'
                        : '');
                $featuresArr = array_values(array_filter(array_map('trim', explode("\n", $p['features'] ?? '')), function ($feat) {
                    return $feat !== '' && !preg_match('/\b(unlimited\s+)?(images?|time\s*slots?)\b/i', $feat);
                }));
                
                $venueTxt = ($p['max_venues'] >= 999) ? 'Unlimited Playgrounds' : $p['max_venues'] . ' Playground' . ($p['max_venues'] > 1 ? 's' : '');
                ?>
                <div class="col-lg-3 col-md-6 animate-on-scroll">
                    <div class="glass-card p-4 h-100 position-relative d-flex flex-column" 
                         style="<?= $isFeatured ? 'border: 1px solid #22c55e; box-shadow: 0 0 25px rgba(34,197,94,0.25); background: linear-gradient(180deg, rgba(34,197,94,0.08) 0%, rgba(13,23,17,0.85) 100%);' : '' ?>">
                        
                        <?php if ($isFeatured): ?>
                            <div class="position-absolute top-0 end-0 m-3">
                                <span class="badge bg-success text-dark fw-bold px-2 py-1" style="font-size: 0.7rem; letter-spacing: 0.5px;">MOST POPULAR</span>
                            </div>
                        <?php endif; ?>

                        <div class="text-center mb-4">
                            <h3 class="text-white fw-bold mb-2"><?= htmlspecialchars($p['name']) ?></h3>
                            <div class="display-5 fw-bold text-white mb-1">
                                <?= $priceDisplay ?>
                                <?php if ((float)$p['price'] > 0): ?>
                                    <span class="fs-6 text-secondary fw-normal">/<?= htmlspecialchars($p['billing_cycle']) ?></span>
                                <?php elseif ((float)$p['price'] == 0 && $slug !== 'enterprise'): ?>
                                    <span class="fs-6 text-secondary fw-normal">/month</span>
                                <?php endif; ?>
                            </div>
                            <?php if ($feeDisplay): ?>
                            <p class="text-success small mb-1"><i class="bi bi-percent me-1"></i><?= htmlspecialchars($feeDisplay) ?></p>
                            <?php endif; ?>
                            <p class="text-secondary small mb-0"><?= htmlspecialchars($p['description']) ?></p>
                        </div>

                        <!-- Highlights list -->
                        <div class="p-3 rounded-3 mb-4" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05);">
                            <div class="d-flex align-items-center gap-2 text-secondary small">
                                <i class="bi bi-building text-success"></i>
                                <span class="text-white fw-semibold"><?= $venueTxt ?></span>
                            </div>
                        </div>

                        <!-- Features checklist -->
                        <ul class="list-unstyled mb-4 flex-grow-1">
                            <?php foreach ($featuresArr as $feat):
                                $featLabel = strtr($feat, [
                                    'Venues' => 'Playgrounds',
                                    'venues' => 'playgrounds',
                                    'Venue' => 'Playground',
                                    'venue' => 'playground',
                                    'Arenas' => 'Playgrounds',
                                    'Arena' => 'Playground',
                                    'arena' => 'playground',
                                ]);
                            ?>
                                <li class="d-flex align-items-start gap-2 mb-2 text-secondary small">
                                    <i class="bi bi-check2 text-success fw-bold me-1 mt-1"></i>
                                    <span><?= htmlspecialchars($featLabel) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                        <a href="#partnerRegisterForm" class="btn <?= $isFeatured ? 'btn-premium' : 'btn-outline-success' ?> w-100 py-2.5 fs-6 mt-auto">
                            Choose <?= htmlspecialchars($p['name']) ?>
                        </a>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

    </div>
</section>

<!-- Onboarding Form Section -->
<section class="py-5 position-relative">
    <div class="glow-orb glow-orb-bottom-left" style="opacity: 0.05;"></div>
    <div class="container">
        
        <div class="text-center mb-5 animate-on-scroll">
            <span class="badge-premium mb-3">Onboarding</span>
            <h2 class="display-4 fw-bold text-white mb-2">Register Your Playground</h2>
            <p class="text-secondary">Fill out the details below. Our onboarding team will contact you for a physical verification visit.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8 animate-on-scroll">
                <div class="glass-card p-4 p-md-5">
                    
                    <!-- Alert Message Container -->
                    <div id="partnerFormAlert" class="alert d-none mb-4" role="alert"></div>

                    <form id="partnerRegisterForm" class="row g-4" novalidate>
                        
                        <!-- Owner Name -->
                        <div class="col-md-6">
                            <label class="glass-input-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" id="owner_name" name="owner_name" class="form-control glass-input" placeholder="e.g. Rajesh Patel" required>
                            <div class="validation-error-msg" id="err_owner_name">Please enter a valid owner name (min 3 letters).</div>
                        </div>

                        <!-- Phone Number -->
                        <div class="col-md-6">
                            <label class="glass-input-label">Phone Number <span class="text-danger">*</span></label>
                            <input type="tel" id="phone" name="phone" class="form-control glass-input" placeholder="e.g. 9876543210" maxlength="10" required>
                            <div class="validation-error-msg" id="err_phone">Please enter a valid 10-digit Indian phone number starting with 6-9.</div>
                        </div>

                        <!-- Playground Name -->
                        <div class="col-md-6">
                            <label class="glass-input-label">Playground Name <span class="text-danger">*</span></label>
                            <input type="text" id="venue_name" name="venue_name" class="form-control glass-input" placeholder="e.g. Bhuj Box Cricket Turf" required>
                            <div class="validation-error-msg" id="err_venue_name">Please enter your playground name (min 3 characters).</div>
                        </div>

                        <!-- State Database (Default: Gujarat, Locked) -->
                        <div class="col-md-6">
                            <label class="glass-input-label">State <span class="text-danger">*</span></label>
                            <select id="state" name="state" class="form-select glass-input" readonly required style="pointer-events: none; opacity: 0.9;">
                                <option value="Gujarat" selected>Gujarat (Active Region)</option>
                            </select>
                            <small class="text-muted" style="font-size:0.75rem;"><i class="bi bi-info-circle me-1"></i>Onboarding currently active exclusively in Gujarat.</small>
                            <div class="validation-error-msg" id="err_state">State must be Gujarat.</div>
                        </div>

                        <!-- City (Default: Bhuj, Locked) -->
                        <div class="col-md-6">
                            <label class="glass-input-label">City <span class="text-danger">*</span></label>
                            <input type="text" id="city" name="city" class="form-control glass-input" value="Bhuj" readonly required style="pointer-events: none; opacity: 0.9;">
                            <small class="text-muted" style="font-size:0.75rem;"><i class="bi bi-info-circle me-1"></i>Currently onboarding playgrounds in Bhuj only.</small>
                            <div class="validation-error-msg" id="err_city">City is required.</div>
                        </div>

                        <!-- Playground Location / Area -->
                        <div class="col-md-6">
                            <label class="glass-input-label">Playground Area <span class="text-danger">*</span></label>
                            <input type="text" id="area" name="area" class="form-control glass-input" placeholder="e.g. Sanskar Nagar, Near Jubilee Ground" required>
                            <div class="validation-error-msg" id="err_area">Please enter the area/landmark of your playground.</div>
                        </div>

                        <!-- Select Location from Map Field -->
                        <div class="col-12">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="glass-input-label mb-0"><i class="bi bi-geo-alt-fill text-success me-1"></i> Select Location from Map</label>
                                <span class="map-coords-badge" id="mapCoordsText">Bhuj (23.2420° N, 69.6669° E)</span>
                            </div>
                            <p class="text-secondary small mb-2">Search sports area in Bhuj or drag the pin marker on the map to set exact playground coordinates.</p>
                            
                            <!-- Map Area Search Bar with Autocomplete -->
                            <div class="map-search-wrapper mb-2">
                                <div class="input-group">
                                    <input type="text" id="mapSearchInput" class="form-control glass-input" placeholder="Search area in Kutch (e.g. Bhuj, Gandhidham, Anjar, Mundra)..." autocomplete="off">
                                    <button type="button" id="mapSearchBtn" class="btn btn-premium px-3 px-md-4">
                                        <i class="bi bi-search me-1"></i> Search
                                    </button>
                                </div>
                                <!-- Live Suggestions Dropdown -->
                                <div id="mapSuggestions"></div>
                            </div>
                            <div id="mapSearchStatus" class="small mb-2 d-none"></div>

                            <!-- Interactive Map Box -->
                            <div id="partnerMap" class="mb-3"></div>

                            <input type="hidden" id="latitude" name="latitude" value="23.2420">
                            <input type="hidden" id="longitude" name="longitude" value="69.6669">
                            <input type="text" id="map_address" name="map_address" class="form-control glass-input" placeholder="Selected Sports Area Coordinates (Auto-filled upon map selection)" readonly>
                        </div>

                        <!-- Sports Checked -->
                        <div class="col-12">
                            <label class="glass-input-label d-block mb-3">Sports Facilities Available <span class="text-danger">*</span></label>
                            <div class="d-flex flex-wrap gap-4">
                                <div class="form-check">
                                    <input class="form-check-input sport-checkbox" type="checkbox" name="sports[]" value="Cricket" id="sportCricket" style="background-color: transparent; border-color: rgba(255,255,255,0.25); cursor:pointer;">
                                    <label class="form-check-label text-secondary" for="sportCricket" style="cursor:pointer;">Box Cricket Turf</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input sport-checkbox" type="checkbox" name="sports[]" value="Pickleball" id="sportPickleball" style="background-color: transparent; border-color: rgba(255,255,255,0.25); cursor:pointer;">
                                    <label class="form-check-label text-secondary" for="sportPickleball" style="cursor:pointer;">Pickleball Court</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input sport-checkbox" type="checkbox" name="sports[]" value="Football" id="sportFootball" style="background-color: transparent; border-color: rgba(255,255,255,0.25); cursor:pointer;">
                                    <label class="form-check-label text-secondary" for="sportFootball" style="cursor:pointer;">Football Turf</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input sport-checkbox" type="checkbox" name="sports[]" value="Badminton" id="sportBadminton" style="background-color: transparent; border-color: rgba(255,255,255,0.25); cursor:pointer;">
                                    <label class="form-check-label text-secondary" for="sportBadminton" style="cursor:pointer;">Badminton Court</label>
                                </div>
                            </div>
                            <div class="validation-error-msg" id="err_sports">Please select at least one sports facility.</div>
                        </div>

                        <!-- Comments -->
                        <div class="col-12">
                            <label class="glass-input-label">Additional Comments / Specific Features</label>
                            <textarea id="comments" name="comments" class="form-control glass-input" rows="3" placeholder="Mention number of courts, lighting systems, spectator seating, parking space, etc."></textarea>
                        </div>

                        <!-- Submit -->
                        <div class="col-12 mt-4">
                            <button type="submit" id="submitBtn" class="btn btn-premium w-100 py-3 fs-6">
                                <span id="submitSpinner" class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                                Submit Playground Listing
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>

    </div>
</section>

<!-- Leaflet JS for Map Picker -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // City field is locked to Bhuj (no dynamic loading needed)

    // -------------------------------------------------------------
    // 1. Interactive Map Picker Setup (Default: Bhuj 23.2420, 69.6669)
    // -------------------------------------------------------------
    const defaultLat = 23.2420;
    const defaultLng = 69.6669;

    const map = L.map('partnerMap').setView([defaultLat, defaultLng], 14);

    // OpenStreetMap Tile Layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Custom Draggable Green Pin Marker
    const marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

    // Kutch District soft bounds (allow pan but warn)
    map.options.minZoom = 10;

    function updateCoords(lat, lng, knownName) {
        const validLat = parseFloat(lat);
        const validLng = parseFloat(lng);
        const formattedLat = validLat.toFixed(4);
        const formattedLng = validLng.toFixed(4);

        document.getElementById('latitude').value  = formattedLat;
        document.getElementById('longitude').value = formattedLng;
        document.getElementById('mapCoordsText').innerText = `${formattedLat}° N, ${formattedLng}° E`;

        // If name is already known (from suggestion click), use it directly
        if (knownName) {
            document.getElementById('map_address').value = `${knownName} (${formattedLat}, ${formattedLng})`;
            marker.bindPopup(`<b>${knownName}</b><br><small>${formattedLat}, ${formattedLng}</small>`).openPopup();
            return;
        }

        // Reverse geocode via PHP proxy (not direct Nominatim - avoids 429)
        fetch(`api/nominatim-proxy.php?type=reverse&lat=${validLat}&lon=${validLng}`)
            .then(res => res.json())
            .then(data => {
                if (data && data.display_name) {
                    const parts = data.display_name.split(',');
                    const areaName = (parts[0] || '').trim();
                    document.getElementById('map_address').value = `${areaName}, Kutch (${formattedLat}, ${formattedLng})`;
                    marker.bindPopup(`<b>${areaName}</b><br><small>${formattedLat}, ${formattedLng}</small>`).openPopup();
                } else {
                    document.getElementById('map_address').value = `Kutch (${formattedLat}, ${formattedLng})`;
                }
            })
            .catch(() => {
                document.getElementById('map_address').value = `Kutch (${formattedLat}, ${formattedLng})`;
            });
    }

    // Initial address load
    updateCoords(defaultLat, defaultLng);

    // Marker Drag Event
    marker.on('dragend', function (e) {
        const position = marker.getLatLng();
        updateCoords(position.lat, position.lng);
    });

    // Map Click Event
    map.on('click', function (e) {
        marker.setLatLng(e.latlng);
        updateCoords(e.latlng.lat, e.latlng.lng);
    });

    // Kutch District Major Area Coordinates Lookup
    const kutchAreaCoordinates = {
        'bhuj': [23.2420, 69.6669],
        'sanskar nagar': [23.2458, 69.6582],
        'jubilee ground': [23.2505, 69.6710],
        'madhapar': [23.2300, 69.6950],
        'college road': [23.2440, 69.6630],
        'mirzapar': [23.2650, 69.6450],
        'gandhidham': [23.0753, 70.1337],
        'anjar': [23.1147, 70.0267],
        'mundra': [22.8407, 69.7214],
        'mandvi': [22.8340, 69.3567],
        'nakhatrana': [23.3516, 69.2644],
        'bhachau': [23.2942, 70.3541],
        'rapar': [23.5700, 70.6400]
    };

    function searchAreaLocation(query) {
        if (!query || !query.trim()) return;

        const cleanQuery = query.trim();
        const lowerQuery = cleanQuery.toLowerCase();
        const searchStatusEl = document.getElementById('mapSearchStatus');

        if (searchStatusEl) {
            searchStatusEl.className = 'small mb-2 text-info d-block';
            searchStatusEl.innerHTML = `<i class="bi bi-hourglass-split me-1"></i> Searching area "${cleanQuery}" in Kutch...`;
        }

        // Check local Kutch area lookup first
        let matchedCoords = null;
        for (const [areaKey, coords] of Object.entries(kutchAreaCoordinates)) {
            if (lowerQuery.includes(areaKey)) {
                matchedCoords = coords;
                break;
            }
        }

        if (matchedCoords) {
            map.setView(matchedCoords, 15);
            marker.setLatLng(matchedCoords);
            updateCoords(matchedCoords[0], matchedCoords[1]);
            if (searchStatusEl) {
                searchStatusEl.className = 'small mb-2 text-success d-block';
                searchStatusEl.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Area found in Kutch: <strong>${cleanQuery}</strong> (${matchedCoords[0]}, ${matchedCoords[1]})`;
            }
            return;
        }

        // Search via PHP proxy (avoids CORS & rate limiting)
        const fullSearchTerm = cleanQuery.toLowerCase().includes('kutch') ? cleanQuery : `${cleanQuery}, Kutch, Gujarat, India`;

        fetch(`api/nominatim-proxy.php?q=${encodeURIComponent(fullSearchTerm)}`)
            .then(res => res.json())
            .then(results => {
                if (!Array.isArray(results) || results.length === 0) {
                    if (searchStatusEl) {
                        searchStatusEl.className = 'small mb-2 text-warning d-block';
                        searchStatusEl.innerHTML = `<i class="bi bi-exclamation-triangle me-1"></i> Area not found in Kutch. Try typing partial name or click on map directly.`;
                    }
                    return;
                }

                // Pick the first result inside Kutch bounds
                const match = results.find(r =>
                    parseFloat(r.lat) >= 22.7 && parseFloat(r.lat) <= 24.2 &&
                    parseFloat(r.lon) >= 68.5 && parseFloat(r.lon) <= 71.2
                );

                if (match) {
                    const lat = parseFloat(match.lat);
                    const lon = parseFloat(match.lon);
                    map.setView([lat, lon], 16);
                    marker.setLatLng([lat, lon]);
                    updateCoords(lat, lon);
                    if (searchStatusEl) {
                        searchStatusEl.className = 'small mb-2 text-success d-block';
                        searchStatusEl.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> Found: <strong>${match.display_name.split(',')[0]}</strong> (${lat.toFixed(4)}, ${lon.toFixed(4)})`;
                    }
                } else {
                    if (searchStatusEl) {
                        searchStatusEl.className = 'small mb-2 text-warning d-block';
                        searchStatusEl.innerHTML = `<i class="bi bi-exclamation-triangle me-1"></i> Location is outside Kutch district. Please select an area within Kutch.`;
                    }
                }
            })
            .catch(() => {
                if (searchStatusEl) {
                    searchStatusEl.className = 'small mb-2 text-warning d-block';
                    searchStatusEl.innerHTML = `<i class="bi bi-wifi-off me-1"></i> No internet connection. Drag the pin on the map to set location.`;
                }
            });
    }


    // -------------------------------------------------------
    // Map Search Autocomplete Logic
    // -------------------------------------------------------
    const mapSearchBtn    = document.getElementById('mapSearchBtn');
    const mapSearchInput  = document.getElementById('mapSearchInput');
    const mapSuggestions  = document.getElementById('mapSuggestions');
    let debounceTimer     = null;
    let activeSuggIdx     = -1;

    function hideSuggestions() {
        if (mapSuggestions) {
            mapSuggestions.style.display = 'none';
            mapSuggestions.innerHTML = '';
            activeSuggIdx = -1;
        }
    }

    function selectSuggestion(name, lat, lng) {
        const latF = parseFloat(lat);
        const lngF = parseFloat(lng);
        mapSearchInput.value = name;
        hideSuggestions();
        map.setView([latF, lngF], 16);
        marker.setLatLng([latF, lngF]);
        // Pass name directly — skips reverse geocode API call
        updateCoords(latF, lngF, name);
        const statusEl = document.getElementById('mapSearchStatus');
        if (statusEl) {
            statusEl.className = 'small mb-2 text-success d-block';
            statusEl.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i> <strong>${name}</strong> — ${latF.toFixed(4)}° N, ${lngF.toFixed(4)}° E`;
        }
    }

    function showSuggestions(items) {
        if (!mapSuggestions || !items.length) { hideSuggestions(); return; }
        mapSuggestions.innerHTML = items.map((item, idx) =>
            `<div class="suggestion-item" data-lat="${item.lat}" data-lng="${item.lng}" data-name="${item.name}">
                <i class="bi bi-geo-alt-fill"></i>
                <span>${item.name}</span>
                <span class="suggestion-coords">${parseFloat(item.lat).toFixed(3)}, ${parseFloat(item.lng).toFixed(3)}</span>
            </div>`
        ).join('');
        mapSuggestions.style.display = 'block';

        mapSuggestions.querySelectorAll('.suggestion-item').forEach(el => {
            el.addEventListener('mousedown', function (e) {
                e.preventDefault();
                selectSuggestion(this.dataset.name, parseFloat(this.dataset.lat), parseFloat(this.dataset.lng));
            });
        });
    }

    function getSuggestions(query) {
        if (!query || query.trim().length < 2) { hideSuggestions(); return; }

        // Call the dedicated venue-search API:
        // Priority: Local hardcoded DB (Google Maps verified) → Overpass OSM → Nominatim
        fetch(`api/venue-search.php?q=${encodeURIComponent(query.trim())}`)
            .then(r => r.json())
            .then(results => {
                if (!Array.isArray(results) || !results.length) {
                    hideSuggestions();
                    return;
                }
                const items = results.map(r => ({
                    name: r.display_name,
                    lat:  parseFloat(r.lat),
                    lng:  parseFloat(r.lon),
                    type: r.type || 'place',
                    source: r.source || 'api'
                }));
                showSuggestions(items);
            })
            .catch(() => hideSuggestions());
    }

    if (mapSearchInput) {
        mapSearchInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => getSuggestions(this.value), 280);
        });

        mapSearchInput.addEventListener('keydown', function (e) {
            const items = mapSuggestions ? mapSuggestions.querySelectorAll('.suggestion-item') : [];
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeSuggIdx = Math.min(activeSuggIdx + 1, items.length - 1);
                items.forEach((el, i) => el.classList.toggle('active', i === activeSuggIdx));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeSuggIdx = Math.max(activeSuggIdx - 1, 0);
                items.forEach((el, i) => el.classList.toggle('active', i === activeSuggIdx));
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeSuggIdx >= 0 && items[activeSuggIdx]) {
                    const el = items[activeSuggIdx];
                    selectSuggestion(el.dataset.name, parseFloat(el.dataset.lat), parseFloat(el.dataset.lng));
                } else {
                    searchAreaLocation(this.value);
                    hideSuggestions();
                }
            } else if (e.key === 'Escape') {
                hideSuggestions();
            }
        });

        mapSearchInput.addEventListener('blur', () => {
            setTimeout(hideSuggestions, 200);
        });
    }

    if (mapSearchBtn) {
        mapSearchBtn.addEventListener('click', function () {
            hideSuggestions();
            searchAreaLocation(mapSearchInput.value);
        });
    }

    // Close suggestions when clicking outside
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.map-search-wrapper')) hideSuggestions();
    });

    // City is locked to Bhuj — map is already centered on Bhuj by default

    // -------------------------------------------------------------
    // 2. Client-side Form Validation & AJAX Submission
    // -------------------------------------------------------------
    const form = document.getElementById('partnerRegisterForm');
    const alertBox = document.getElementById('partnerFormAlert');
    const submitBtn = document.getElementById('submitBtn');
    const submitSpinner = document.getElementById('submitSpinner');

    function clearErrors() {
        document.querySelectorAll('.glass-input').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.validation-error-msg').forEach(el => el.style.display = 'none');
        alertBox.className = 'alert d-none mb-4';
        alertBox.innerText = '';
    }

    function showError(fieldId, errorMsg) {
        const field = document.getElementById(fieldId);
        const errEl = document.getElementById('err_' + fieldId);
        if (field) field.classList.add('is-invalid');
        if (errEl) {
            errEl.innerText = errorMsg;
            errEl.style.display = 'block';
        }
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearErrors();

        let isValid = true;

        // 1. Owner Name Validation
        const ownerNameVal = document.getElementById('owner_name').value.trim();
        if (!ownerNameVal || ownerNameVal.length < 3 || !/^[a-zA-Z\s\.\'-]+$/.test(ownerNameVal)) {
            showError('owner_name', 'Please enter a valid full name (at least 3 letters).');
            isValid = false;
        }

        // 2. Phone Validation (10 digit starting with 6-9)
        const phoneVal = document.getElementById('phone').value.trim();
        if (!phoneVal || !/^[6-9]\d{9}$/.test(phoneVal)) {
            showError('phone', 'Please enter a valid 10-digit phone number starting with 6, 7, 8, or 9.');
            isValid = false;
        }

        // 3. Playground Name Validation
        const venueNameVal = document.getElementById('venue_name').value.trim();
        if (!venueNameVal || venueNameVal.length < 3) {
            showError('venue_name', 'Please enter a valid playground name (at least 3 characters).');
            isValid = false;
        }

        // 4. State Validation (Strict: Gujarat)
        const stateVal = document.getElementById('state').value.trim();
        if (stateVal !== 'Gujarat') {
            showError('state', 'Onboarding is currently available only for Gujarat state.');
            isValid = false;
        }

        // 5. City Validation
        const cityVal = document.getElementById('city').value.trim();
        if (!cityVal) {
            showError('city', 'Please select a city.');
            isValid = false;
        }

        // 6. Area Validation
        const areaVal = document.getElementById('area').value.trim();
        if (!areaVal) {
            showError('area', 'Please enter your playground area or landmark.');
            isValid = false;
        }

        // 7. Sports Checked Validation
        const checkedSports = document.querySelectorAll('.sport-checkbox:checked');
        if (checkedSports.length === 0) {
            document.getElementById('err_sports').style.display = 'block';
            isValid = false;
        }

        if (!isValid) {
            alertBox.className = 'alert alert-danger mb-4 d-block';
            alertBox.innerText = 'Please fix the errors highlighted in red before submitting.';
            return;
        }

        // AJAX Form Submission
        submitBtn.disabled = true;
        submitSpinner.classList.remove('d-none');

        const formData = new FormData(form);
        const dataObj = {};
        formData.forEach((val, key) => {
            if (key.endsWith('[]')) {
                const k = key.replace('[]', '');
                if (!dataObj[k]) dataObj[k] = [];
                dataObj[k].push(val);
            } else {
                dataObj[key] = val;
            }
        });

        fetch('api/partner-request.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(dataObj)
        })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(res => {
            submitBtn.disabled = false;
            submitSpinner.classList.add('d-none');

            if (res.status === 201 && res.body.success) {
                alertBox.className = 'alert alert-success mb-4 d-block';
                alertBox.innerHTML = `<i class="bi bi-check-circle-fill me-2"></i> ${res.body.message}`;
                form.reset();
                // Reset defaults
                document.getElementById('state').value = 'Gujarat';
                document.getElementById('city').value = 'Bhuj';
                map.setView([defaultLat, defaultLng], 14);
                marker.setLatLng([defaultLat, defaultLng]);
                updateCoords(defaultLat, defaultLng);
            } else if (res.status === 422 && res.body.errors) {
                alertBox.className = 'alert alert-danger mb-4 d-block';
                alertBox.innerText = res.body.message || 'Validation errors occurred.';
                Object.keys(res.body.errors).forEach(key => {
                    showError(key, res.body.errors[key]);
                });
            } else {
                alertBox.className = 'alert alert-danger mb-4 d-block';
                alertBox.innerText = res.body.message || 'An error occurred while submitting your request.';
            }
        })
        .catch(err => {
            submitBtn.disabled = false;
            submitSpinner.classList.add('d-none');
            alertBox.className = 'alert alert-danger mb-4 d-block';
            alertBox.innerText = 'Network connection error. Please try again.';
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
