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
                sort: 'rating',
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
        const price = parseInt(venue.price_per_hour || 0).toLocaleString('en-IN');
        const sports = Array.isArray(venue.sports) ? venue.sports : [];
        const sportTag = sports[0] || 'Sports';
        const address = venue.address || venue.city || 'Bhuj';
        const delay = index % 3 === 0 ? '' : ` delay-${Math.min(index * 100, 300)}`;

        let image = resolveImage(venue.featured_image, null);
        if (!image) {
            const slug = sports[0]?.toLowerCase().replace(/\s+/g, '-') || '';
            image = SPORT_IMAGES[slug] || VENUE_FALLBACKS[(venue.id - 1) % VENUE_FALLBACKS.length];
        }

        const badge = venue.is_verified
            ? '<span class="sport-badge-live mb-2"><i class="bi bi-patch-check-fill"></i> Verified</span>'
            : '<span class="sport-badge-live mb-2"><i class="bi bi-star-fill"></i> Top Rated</span>';

        const ratingStat = rating > 0
            ? `<span><i class="bi bi-star-fill"></i> ${rating.toFixed(1)} rating</span>`
            : `<span><i class="bi bi-stars"></i> New</span>`;

        const statsParts = [ratingStat];
        if (venue.total_courts) {
            statsParts.push(`<span><i class="bi bi-grid-3x3"></i> ${venue.total_courts} court${venue.total_courts !== 1 ? 's' : ''}</span>`);
        }
        statsParts.push(`<span><i class="bi bi-currency-rupee"></i> ${price}/hr</span>`);
        if (venue.available_courts != null) {
            statsParts.push(`<span><i class="bi bi-check-circle"></i> ${venue.available_courts} free today</span>`);
        }

        const stats = `<div class="sport-card-stats">${statsParts.join('')}</div>`;
        const subtitle = address.length > 55 ? address.slice(0, 52) + '…' : address;

        return `
            <div class="home-featured-item animate-on-scroll${delay}">
                <div class="glass-card sport-card sport-card-live"
                     role="button" tabindex="0"
                     data-venue-id="${venue.id}"
                     aria-label="View ${escapeHtml(venue.name)}">
                    <img src="${image}" alt="${escapeHtml(venue.name)}" loading="lazy">
                    <div class="sport-card-overlay"></div>
                    <div class="sport-card-icon"><i class="bi bi-geo-alt-fill"></i></div>
                    <div class="sport-card-content">
                        ${badge}
                        <h3 class="sport-title">${escapeHtml(venue.name)}</h3>
                        <p class="sport-subtitle">${escapeHtml(subtitle)} · ${escapeHtml(sportTag)}</p>
                        ${stats}
                        <span class="sport-card-cta">Book now <i class="bi bi-arrow-right"></i></span>
                    </div>
                </div>
            </div>
        `;
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
