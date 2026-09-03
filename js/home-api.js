/**
 * Findownn Homepage — dynamic sports & featured venues
 */

document.addEventListener('DOMContentLoaded', async () => {

    const SPORT_IMAGES = {
        'box-cricket': 'assets/images/venue-cricket.jpg',
        'pickleball':  'assets/images/venue-pickleball.jpg',
        'football':    'assets/images/venue-football.jpg',
        'badminton':   'assets/images/venue-badminton.jpg',
        'tennis':      'assets/images/venue-pickleball.jpg',
        'basketball':  'assets/images/venue-football.jpg',
    };

    const SPORT_DESCRIPTIONS = {
        'box-cricket': 'Floodlit turf courts with full equipment',
        'pickleball':  'Pro courts with paddle rentals on-site',
        'football':    '5-a-side artificial grass turfs',
        'badminton':   'Indoor courts with premium flooring',
        'tennis':      'Hard courts for singles & doubles',
        'basketball':  'Full-court indoor & outdoor setups',
    };

    const VENUE_FALLBACKS = [
        'assets/images/venue1.jpg',
        'assets/images/venue2.jpg',
        'assets/images/venue3.jpg',
    ];

    function resolveImage(path, fallback) {
        return FindownnAPI.resolveImageUrl(path, fallback);
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str ?? '';
        return div.innerHTML;
    }

    function normalizeLiveSports(sports) {
        const live = (sports ?? []).filter(s => (s.total_venues ?? 0) > 0);
        const seen = new Set();

        return live.filter(sport => {
            const key = (sport.name || sport.slug || '').toLowerCase().trim();
            if (!key || seen.has(key)) return false;
            seen.add(key);
            return true;
        });
    }

    // ==================== SPORTS ====================

    async function loadSports() {
        const container = document.getElementById('home-sports-container');
        if (!container) return;

        try {
            const response = await FindownnAPI.getSports({ live: 1 });
            const sports = normalizeLiveSports(response?.data?.sports ?? []);

            if (!sports.length) {
                container.innerHTML = renderSportsEmpty();
                return;
            }

            container.innerHTML = sports.map((sport, i) => createSportCard(sport, i)).join('');
            bindSportCards(container);
            if (typeof window.refreshScrollAnimations === 'function') {
                window.refreshScrollAnimations(container);
            }
        } catch (err) {
            console.error('Error loading sports:', err);
            container.innerHTML = renderSportsFallback();
            bindSportCards(container);
            if (typeof window.refreshScrollAnimations === 'function') {
                window.refreshScrollAnimations(container);
            }
        }
    }

    function createSportCard(sport, index) {
        const isLive = (sport.total_venues ?? 0) > 0;
        const image = resolveImage(sport.image, SPORT_IMAGES[sport.slug] || VENUE_FALLBACKS[index % VENUE_FALLBACKS.length]);
        const desc = sport.description || SPORT_DESCRIPTIONS[sport.slug] || 'Book courts instantly in Bhuj';
        const icon = sport.icon || 'bi-trophy-fill';
        const delay = index % 4 === 0 ? '' : ` delay-${Math.min(index * 100, 300)}`;

        let badge = '';
        if (!isLive) {
            badge = '<span class="sport-badge-soon mb-2">Coming Soon</span>';
        } else if (sport.is_featured) {
            badge = '<span class="badge-premium mb-2"><i class="bi bi-fire"></i> Most Popular</span>';
        } else {
            badge = '<span class="sport-badge-live mb-2"><i class="bi bi-lightning-charge-fill"></i> Live Now</span>';
        }

        const stats = isLive
            ? `<div class="sport-card-stats">
                   <span><i class="bi bi-building"></i> ${sport.total_venues} playground${sport.total_venues !== 1 ? 's' : ''}</span>
                   <span><i class="bi bi-grid-3x3"></i> ${sport.total_courts} court${sport.total_courts !== 1 ? 's' : ''}</span>
               </div>`
            : '';

        const cardClass = isLive ? 'sport-card sport-card-live' : 'sport-card sport-card-disabled';
        const attrs = isLive
            ? `role="button" tabindex="0" data-sport-slug="${escapeHtml(sport.slug)}" aria-label="Browse ${escapeHtml(sport.name)} playgrounds"`
            : 'aria-disabled="true"';

        return `
            <div class="home-sport-item animate-on-scroll${delay}">
                <div class="glass-card ${cardClass}" ${attrs}>
                    <img src="${image}" alt="${escapeHtml(sport.name)} in Bhuj" loading="lazy">
                    <div class="sport-card-overlay"></div>
                    <div class="sport-card-icon"><i class="bi ${escapeHtml(icon)}"></i></div>
                    <div class="sport-card-content">
                        ${badge}
                        <h3 class="sport-title">${escapeHtml(sport.name)}</h3>
                        <p class="sport-subtitle">${escapeHtml(desc)}</p>
                        ${stats}
                        ${isLive ? '<span class="sport-card-cta">Browse playgrounds <i class="bi bi-arrow-right"></i></span>' : ''}
                    </div>
                </div>
            </div>
        `;
    }

    function bindSportCards(container) {
        container.querySelectorAll('[data-sport-slug]').forEach(card => {
            const go = () => {
                window.location.href = `venues?sport=${encodeURIComponent(card.dataset.sportSlug)}`;
            };
            card.addEventListener('click', go);
            card.addEventListener('keydown', e => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    go();
                }
            });
        });
    }

    function renderSportsEmpty() {
        return `<div class="home-empty-state"><i class="bi bi-controller"></i><p>Sports loading soon — check back shortly.</p></div>`;
    }

    function renderSportsFallback() {
        const fallbackSports = [
            { name: 'Box Cricket', slug: 'box-cricket', total_venues: 1, total_courts: 3, is_featured: false, icon: 'bi-trophy' },
            { name: 'Pickleball', slug: 'pickleball', total_venues: 1, total_courts: 1, is_featured: false, icon: 'bi-circle' },
        ];
        return fallbackSports.map((s, i) => createSportCard(s, i)).join('');
    }

    // ==================== FEATURED VENUES ====================

    async function loadFeaturedVenues() {
        const container = document.getElementById('featured-venues-container');
        if (!container) return;

        try {
            const response = await FindownnAPI.getVenues({
                per_page: 6,
                sort: 'recent',
            });

            const venues = response?.data?.venues ?? [];

            if (!venues.length) {
                container.innerHTML = renderVenuesEmpty();
                return;
            }

            container.innerHTML = venues.slice(0, 3).map((v, i) => createFeaturedVenueCard(v, i)).join('');
            bindFeaturedCards(container);
            if (typeof window.refreshScrollAnimations === 'function') {
                window.refreshScrollAnimations(container);
            }
        } catch (err) {
            console.error('Error loading featured venues:', err);
            container.innerHTML = renderVenuesEmpty();
        }
    }

    function createFeaturedVenueCard(venue, index) {
        const rating = parseFloat(venue.rating || 0);
        const ratingHtml = rating > 0
            ? `<span class="text-warning fw-bold" style="font-size:.9rem;"><i class="bi bi-star-fill me-1"></i>${rating.toFixed(1)}</span>`
            : `<span class="text-muted" style="font-size:.8rem;">No reviews</span>`;

        const price = parseInt(venue.price_per_hour || 0).toLocaleString('en-IN');
        const sports = Array.isArray(venue.sports) ? venue.sports : [];
        const sportTag = sports.length > 0 ? sports[0] : 'Sports';
        const address = venue.address || venue.city || 'Bhuj';
        const verified = venue.is_verified
            ? `<span class="badge ms-2" style="background:rgba(56,135,198,.15);color:#3887C6;font-size:.6rem;"><i class="bi bi-patch-check-fill me-1"></i>Verified</span>`
            : '';
        const delay = index % 3 === 0 ? '' : ` delay-${Math.min(index * 100, 300)}`;

        const fallbackImage = resolveImage('assets/images/venue1.jpg', null);
        let image = resolveImage(venue.featured_image, null) || fallbackImage;

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
                return `<span class="venue-amenity" style="font-size:0.75rem; background:rgba(255,255,255,0.05); color:var(--text-secondary); padding:4px 8px; border-radius:4px; margin-right:4px; display:inline-flex; align-items:center; gap:4px;"><i class="${icon}"></i> ${a}</span>`;
            }).join('');
        } else if (venue.has_floodlights || venue.has_parking || venue.has_water) {
            const list = [];
            if (venue.has_floodlights) list.push('<span class="venue-amenity" style="font-size:0.75rem; background:rgba(255,255,255,0.05); color:var(--text-secondary); padding:4px 8px; border-radius:4px; margin-right:4px; display:inline-flex; align-items:center; gap:4px;"><i class="bi bi-lightbulb-fill"></i> Floodlights</span>');
            if (venue.has_parking)     list.push('<span class="venue-amenity" style="font-size:0.75rem; background:rgba(255,255,255,0.05); color:var(--text-secondary); padding:4px 8px; border-radius:4px; margin-right:4px; display:inline-flex; align-items:center; gap:4px;"><i class="bi bi-p-circle-fill"></i> Parking</span>');
            if (venue.has_water)       list.push('<span class="venue-amenity" style="font-size:0.75rem; background:rgba(255,255,255,0.05); color:var(--text-secondary); padding:4px 8px; border-radius:4px; margin-right:4px; display:inline-flex; align-items:center; gap:4px;"><i class="bi bi-droplet-fill"></i> Water</span>');
            amenityHtml = list.slice(0, 3).join('');
        }

        return `
        <div class="home-featured-item animate-on-scroll${delay}" style="cursor:pointer;" onclick="window.location.href='venue-details?id=${venue.id}'">
            <div class="glass-card h-100 venue-card-premium" style="border-radius: 20px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05); transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);">
                <div class="position-relative overflow-hidden" style="height: 200px;">
                    <img src="${image}" alt="${escapeHtml(venue.name)}" loading="lazy" style="width:100%; height:100%; object-fit:cover; transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);" class="venue-card-img">
                    <span class="position-absolute" style="top: 16px; left: 16px; background: rgba(8, 12, 9, 0.75); color: var(--primary); font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; padding: 6px 12px; border-radius: 6px; border: 1px solid rgba(56, 135, 198, 0.25); z-index: 2;">${escapeHtml(sportTag)}</span>
                    <span class="position-absolute" style="bottom: 16px; right: 16px; background: var(--primary); color: #1a2332; font-size: 0.85rem; font-weight: 700; padding: 6px 12px; border-radius: 6px; z-index: 2; box-shadow: 0 4px 12px rgba(56, 135, 198, 0.3);">₹${price}/hr</span>
                    <div class="position-absolute inset-0" style="background: linear-gradient(180deg, transparent 50%, rgba(8,12,9,0.8) 100%); pointer-events: none;"></div>
                </div>
                <div class="p-4 bg-transparent">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 class="text-white mb-0" style="font-size:1.15rem; font-weight:700; letter-spacing:-0.01em;">${escapeHtml(venue.name)}${verified}</h4>
                        ${ratingHtml}
                    </div>
                    <p class="text-secondary mb-3" style="font-size:.85rem; display: flex; align-items: center; gap: 4px;">
                        <i class="bi bi-geo-alt-fill text-success"></i>${escapeHtml(address)}
                    </p>
                    <div class="d-flex flex-wrap gap-2 mb-4" style="min-height: 28px;">${amenityHtml}</div>
                    <button class="btn btn-premium w-100" style="border-radius: 10px; padding: 12px; font-size: 0.9rem; font-weight: 700;">
                        <i class="bi bi-calendar-check me-2"></i>Book Now
                    </button>
                </div>
            </div>
        </div>`;
    }

    function bindFeaturedCards(container) {
        container.querySelectorAll('[data-venue-id]').forEach(card => {
            const go = () => {
                window.location.href = `venue-details?id=${card.dataset.venueId}`;
            };
            card.addEventListener('click', go);
            card.addEventListener('keydown', e => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    go();
                }
            });
        });
    }

    function renderVenuesEmpty() {
        return `
            <div class="home-empty-state">
                <i class="bi bi-building"></i>
                <p>No playgrounds available right now. <a href="venues">Browse all playgrounds</a></p>
            </div>
        `;
    }

    // ==================== INIT ====================
    await Promise.all([loadSports(), loadFeaturedVenues()]);
});
