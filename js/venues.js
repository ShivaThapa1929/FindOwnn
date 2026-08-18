/**
 * Findownn Venues Page - API Integration
 * Dynamic venue loading and filtering from admin dashboard database
 */

document.addEventListener('DOMContentLoaded', async () => {

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    // ==================== STATE ====================
    let currentFilters = {
        page: 1,
        per_page: 50,
        sport: null,
        search: null,
        sort: 'rating'
    };

    let isLoading = false;

    // ==================== DOM ELEMENTS ====================
    const venuesContainer  = document.getElementById('venues-container');
    const loadingSpinner   = document.getElementById('loading-spinner');
    const searchInput      = document.getElementById('venue-search');
    const filterButtons    = document.getElementById('sport-filter-buttons');
    const loadMoreBtn      = document.getElementById('load-more-btn');

    // ==================== READ URL PARAM ====================
    // If coming from sports.php with ?sport=box-cricket or ?sport=pickleball
    const urlParams   = new URLSearchParams(window.location.search);
    const sportParam  = urlParams.get('sport');
    if (sportParam) {
        currentFilters.sport = sportParam;
    }

    // ==================== ACTIVATE BUTTONS ====================
    function activateFilterButtons() {
        if (!filterButtons) return;
        filterButtons.querySelectorAll('.filter-btn').forEach(btn => {
            const slug = btn.getAttribute('data-slug');
            if (slug === (currentFilters.sport || 'all')) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }

    // Initial button activation based on URL param
    activateFilterButtons();

    // Bind click events to filter buttons
    if (filterButtons) {
        filterButtons.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const slug = btn.getAttribute('data-slug');
                currentFilters.sport = (slug === 'all') ? null : slug;
                currentFilters.page = 1;
                activateFilterButtons();
                loadVenues();
            });
        });
    }

    // ==================== LOAD VENUES ====================
    async function loadVenues(append = false) {
        if (isLoading) return;
        isLoading = true;
        showLoading();

        try {
            // Build params — only send sport if it is set
            const params = {
                page:     currentFilters.page,
                per_page: currentFilters.per_page,
                sort:     currentFilters.sort,
            };
            if (currentFilters.sport) params.sport = currentFilters.sport;
            if (currentFilters.search) params.search = currentFilters.search;

            const response = await FindownnAPI.getVenues(params);

            if (response.success) {
                renderVenues(response.data.venues, append);
                updatePagination(response.data.meta);
            } else {
                showError('Failed to load playgrounds. Please try again.');
            }
        } catch (error) {
            console.error('Error loading venues:', error);
            const msg = window.FindownnUI
                ? FindownnUI.friendlyApiMessage(error)
                : 'Network error. Please try again.';
            showError(msg);
        } finally {
            isLoading = false;
            hideLoading();
        }
    }

    // ==================== RENDER VENUES ====================
    function renderVenues(venues, append = false) {
        if (!venuesContainer) return;

        if (!append) venuesContainer.innerHTML = '';

        if (!venues || venues.length === 0) {
            venuesContainer.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bi bi-building-x display-4 text-muted mb-3 d-block opacity-50"></i>
                    <h5 class="text-white">No playgrounds found</h5>
                    <p class="text-muted">No playgrounds available for this sport right now.</p>
                    <button class="btn btn-outline-success mt-2" onclick="document.querySelector('[data-slug=all]').click()">View All Playgrounds</button>
                </div>
            `;
            return;
        }

        venuesContainer.innerHTML = venues.map(venue => createVenueCard(venue)).join('');

        // Bind Book Now buttons
        document.querySelectorAll('.btn-book-trigger').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const el = document.getElementById('modal-venue-name');
                if (el) el.textContent = btn.getAttribute('data-venue-name');
                const modal = new bootstrap.Modal(document.getElementById('bookingModal'));
                modal.show();
            });
        });

        // Animate cards in
        if (typeof window.refreshScrollAnimations === 'function') {
            window.refreshScrollAnimations(venuesContainer);
        }

        setTimeout(() => {
            document.querySelectorAll('.venue-item-card:not(.appear)').forEach((card, i) => {
                setTimeout(() => card.classList.add('appear'), i * 80);
            });
        }, 50);
    }

    // ==================== CREATE VENUE CARD ====================
    function createVenueCard(venue) {
        const rating    = parseFloat(venue.rating || 0);
        const ratingHtml = rating > 0
            ? `<span class="text-warning fw-bold" style="font-size:.9rem;"><i class="bi bi-star-fill me-1"></i>${rating.toFixed(1)}</span>`
            : `<span class="text-muted" style="font-size:.8rem;">No reviews</span>`;

        const price    = parseInt(venue.price_per_hour || 0).toLocaleString('en-IN');
        const sports   = Array.isArray(venue.sports) ? venue.sports : [];
        const sportTag = sports.length > 0 ? sports[0] : 'Sports';
        const address  = venue.address || venue.city || 'Bhuj';
        const verified = venue.is_verified
            ? `<span class="badge ms-2" style="background:rgba(34,197,94,.15);color:#22c55e;font-size:.6rem;"><i class="bi bi-patch-check-fill me-1"></i>Verified</span>`
            : '';

        // Sport → image map
        const sportImageMap = {
            'box-cricket': 'assets/images/venue1.jpg',
            'cricket':     'assets/images/venue1.jpg',
            'pickleball':  'assets/images/venue2.jpg',
            'football':    'assets/images/venue3.jpg',
            'badminton':   'assets/images/venue2.jpg',
        };
        const fallbacks = [
            'assets/images/venue1.jpg',
            'assets/images/venue2.jpg',
            'assets/images/venue3.jpg',
        ];

        let image = FindownnAPI.resolveImageUrl(venue.featured_image);
        if (!image) {
            const slugs = sports.map(s => s.toLowerCase().replace(/\s+/g, '-'));
            for (const sl of slugs) {
                if (sportImageMap[sl]) { image = FindownnAPI.resolveImageUrl(sportImageMap[sl]); break; }
            }
        }
        if (!image) image = FindownnAPI.resolveImageUrl(fallbacks[(venue.id - 1) % fallbacks.length]);

        // Amenities
        const amenityIconMap = {
            'Floodlights': 'bi-lightbulb-fill',
            'Parking':     'bi-p-circle-fill',
            'Water':       'bi-droplet-fill',
            'Cafeteria':   'bi-cup-hot-fill',
            'Indoor':      'bi-wind',
            'Washroom':    'bi-door-open-fill',
            'Coaching':    'bi-person-video2',
        };
        const amenities = Array.isArray(venue.amenities) ? venue.amenities.slice(0, 3) : [];
        let amenityHtml = '';
        if (amenities.length > 0) {
            amenityHtml = amenities.map(a => {
                const key  = Object.keys(amenityIconMap).find(k => a.toLowerCase().includes(k.toLowerCase()));
                const icon = key ? amenityIconMap[key] : 'bi-check-circle-fill';
                return `<span class="venue-amenity"><i class="${icon}"></i> ${a}</span>`;
            }).join('');
        } else if (venue.has_floodlights || venue.has_parking || venue.has_water) {
            const list = [];
            if (venue.has_floodlights) list.push('<span class="venue-amenity"><i class="bi bi-lightbulb-fill"></i> Floodlights</span>');
            if (venue.has_parking)     list.push('<span class="venue-amenity"><i class="bi bi-p-circle-fill"></i> Parking</span>');
            if (venue.has_water)       list.push('<span class="venue-amenity"><i class="bi bi-droplet-fill"></i> Water</span>');
            amenityHtml = list.slice(0, 3).join('');
        }

        return `
        <div class="col-md-6 col-lg-4 animate-on-scroll venue-item-card">
            <div class="glass-card h-100" style="cursor:pointer;" onclick="window.location.href='venue-details?id=${venue.id}'">
                <div class="venue-img-wrapper">
                    <img src="${image}" alt="${venue.name}" loading="lazy" onerror="this.onerror=null;this.src='${FindownnAPI.resolveImageUrl('assets/images/venue1.jpg')}'">
                    <span class="venue-tag">${sportTag}</span>
                    <span class="venue-price-badge">₹${price}/hr</span>
                </div>
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 class="text-white mb-0" style="font-size:1.15rem;">${venue.name}${verified}</h4>
                        ${ratingHtml}
                    </div>
                    <p class="text-secondary mb-3" style="font-size:.88rem;">
                        <i class="bi bi-geo-alt-fill text-success me-1"></i>${address}
                    </p>
                    <div class="d-flex flex-wrap gap-2 mb-4">${amenityHtml}</div>
                    <button class="btn btn-premium w-100 btn-book-trigger" data-venue-name="${venue.name}">
                        <i class="bi bi-calendar-check me-2"></i>Book Now
                    </button>
                </div>
            </div>
        </div>`;
    }

    // ==================== PAGINATION ====================
    function updatePagination(meta) {
        if (!loadMoreBtn || !meta) return;
        if (meta.current_page >= meta.total_pages) {
            loadMoreBtn.style.display = 'none';
        } else {
            loadMoreBtn.style.display = 'inline-block';
            loadMoreBtn.textContent   = `Load More (${meta.current_page}/${meta.total_pages})`;
        }
    }

    // ==================== LOADING STATES ====================
    function showLoading() {
        if (loadingSpinner) {
            loadingSpinner.style.display = 'block';
            loadingSpinner.classList.remove('d-none');
        }
        if (venuesContainer) venuesContainer.innerHTML = '';
    }

    function hideLoading() {
        if (loadingSpinner) {
            loadingSpinner.style.display = 'none';
            loadingSpinner.classList.add('d-none');
        }
    }

    function showError(message) {
        if (venuesContainer) {
            venuesContainer.innerHTML = `
                <div class="col-12 text-center py-5">
                    <i class="bi bi-exclamation-triangle display-4 text-danger mb-3 d-block"></i>
                    <h5 class="text-white">${escapeHtml(message)}</h5>
                    <button class="btn btn-premium mt-3" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise me-1"></i>Try Again
                    </button>
                </div>
            `;
        }
    }

    // ==================== SEARCH ====================
    if (searchInput) {
        let timer;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                currentFilters.search = e.target.value.trim() || null;
                currentFilters.page   = 1;
                loadVenues();
            }, 350);
        });
    }

    // Load more button
    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', () => {
            currentFilters.page++;
            loadVenues(true);
        });
    }

    // ==================== INITIALIZE ====================
    await loadVenues();
});
