/**
 * Findownn API Service
 * Handles all API calls to /api/v1/
 */

const FindownnAPI = {
    /** In-memory GET cache — only for static-ish data (venues list, sports, cities) */
    _memoryCache: {},
    _memoryCacheTtlMs: 15000,

    /** Endpoints that must always reflect live server state */
    _dynamicPatterns: [
        /\/bookings/i,
        /\/availability/i,
        /\/payments/i,
        /\/user\/stats/i,
        /\/user\/profile/i,
        /venues/i,
    ],

    _isDynamicEndpoint(endpoint) {
        return this._dynamicPatterns.some((re) => re.test(endpoint));
    },

    _getMemoryCache(endpoint) {
        if (this._isDynamicEndpoint(endpoint)) return null;
        const entry = this._memoryCache[endpoint];
        if (!entry || Date.now() - entry.ts > this._memoryCacheTtlMs) return null;
        return entry.data;
    },

    _setMemoryCache(endpoint, data) {
        if (this._isDynamicEndpoint(endpoint)) return;
        this._memoryCache[endpoint] = { ts: Date.now(), data };
    },

    /**
     * Clear cached booking/payment/availability data after mutations.
     * Call after payment verify, booking create/cancel, etc.
     */
    invalidateBookingCache(bookingId = null) {
        const patterns = ['/bookings', '/availability', '/payments', '/user/stats', '/user/profile'];

        Object.keys(this._memoryCache).forEach((key) => {
            if (patterns.some((p) => key.includes(p))) {
                delete this._memoryCache[key];
            }
        });

        try {
            for (let i = localStorage.length - 1; i >= 0; i--) {
                const key = localStorage.key(i);
                if (!key || !key.startsWith('findownn_api_')) continue;
                const normalized = key.replace('findownn_api_', '').replace(/_/g, '/');
                if (patterns.some((p) => normalized.includes(p.replace(/^\//, '')))) {
                    localStorage.removeItem(key);
                }
            }
        } catch (_) { /* ignore */ }

        if (bookingId) {
            const single = `/bookings/${bookingId}`;
            delete this._memoryCache[single];
            try {
                localStorage.removeItem(this.cacheKey(single));
            } catch (_) { /* ignore */ }
        }
    },

    /** Known public page path segments (for resolving API base URL) */
    _pageRoutes: [
        '/venue-details', '/booking-payment', '/booking-success', '/venues', '/sports',
        '/partner', '/about', '/contact', '/home', '/login', '/register',
        '/dashboard', '/account', '/index.php', '/index'
    ],

    getSiteBase() {
        let path = window.location.pathname;
        for (const route of this._pageRoutes) {
            if (path.endsWith(route)) {
                path = path.slice(0, -route.length);
                break;
            }
        }
        return path.replace(/\/+$/, '') || '';
    },

    baseURL: (() => {
        let path = window.location.pathname;
        const routes = [
            '/venue-details', '/booking-payment', '/booking-success', '/venues', '/sports',
            '/partner', '/about', '/contact', '/home', '/login', '/register',
            '/dashboard', '/account', '/index.php', '/index'
        ];
        for (const route of routes) {
            if (path.endsWith(route)) {
                path = path.slice(0, -route.length);
                break;
            }
        }
        path = path.replace(/\/+$/, '');
        return path + '/api/v1';
    })(),

    resolveImageUrl(path, fallback = null) {
        if (!path) return fallback;
        if (/^https?:\/\//i.test(path)) return path;
        if (path.startsWith('/')) return path;

        const base = this.getSiteBase();

        if (path.startsWith('admin/public/uploads/') || path.startsWith('assets/')) {
            return `${base}/${path}`;
        }
        if (path.startsWith('public/uploads/')) {
            return `${base}/admin/${path}`;
        }
        if (path.startsWith('venues/') || path.startsWith('courts/')) {
            return `${base}/admin/public/uploads/${path}`;
        }

        return `${base}/${path.replace(/^\//, '')}`;
    },
    
    /**
     * Generic API request handler
     */
    async request(endpoint, options = {}) {
        const method = options.method || 'GET';

        if (method === 'GET') {
            const memoryHit = this._getMemoryCache(endpoint);
            if (memoryHit) return memoryHit;
        }

        const url = `${this.baseURL}${endpoint}`;
        const isDynamicGet = method === 'GET' && this._isDynamicEndpoint(endpoint);
        const config = {
            method: options.method || 'GET',
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            cache: isDynamicGet ? 'no-store' : 'default',
        };
        
        // Add auth token if available
        const token = this.getToken();
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
        }
        
        // Add body for POST/PUT requests
        if (options.body && (config.method === 'POST' || config.method === 'PUT')) {
            config.body = JSON.stringify(options.body);
        }
        
        try {
            const response = await fetch(url, config);

            const contentType = response.headers.get('content-type') || '';
            let data;

            if (contentType.includes('application/json')) {
                data = await response.json();
            } else {
                throw new Error('Server returned an invalid response');
            }

            if (!response.ok) {
                const err = new Error(data.message || 'Request failed');
                err.code = data.code || null;
                err.status = response.status;
                throw err;
            }

            if (method === 'GET' && !this._isDynamicEndpoint(endpoint)) {
                this._setMemoryCache(endpoint, data);
                this.setCached(endpoint, data);
            }

            return data;
        } catch (error) {
            const isOffline = !navigator.onLine
                || error.name === 'TypeError'
                || error.message === 'Failed to fetch';

            if (isOffline && (options.method || 'GET') === 'GET' && !this._isDynamicEndpoint(endpoint)) {
                const cached = this.getCached(endpoint);
                if (cached) {
                    cached._fromCache = true;
                    return cached;
                }

                const offlineErr = new Error('Offline');
                offlineErr.code = 'OFFLINE';
                offlineErr.offline = true;
                throw offlineErr;
            }

            if (window.FindownnUI) {
                error.userMessage = FindownnUI.friendlyApiMessage(error);
            }

            console.error('API Error:', error);
            throw error;
        }
    },

    /** Cache GET responses for offline use (24h TTL) */
    cacheKey(endpoint) {
        return 'findownn_api_' + endpoint.replace(/[^a-z0-9]/gi, '_');
    },

    setCached(endpoint, data) {
        try {
            localStorage.setItem(this.cacheKey(endpoint), JSON.stringify({
                ts: Date.now(),
                data,
            }));
        } catch (_) { /* quota exceeded — ignore */ }
    },

    getCached(endpoint, maxAgeMs = 86400000) {
        try {
            const raw = localStorage.getItem(this.cacheKey(endpoint));
            if (!raw) return null;
            const entry = JSON.parse(raw);
            if (Date.now() - entry.ts > maxAgeMs) return null;
            return entry.data;
        } catch (_) {
            return null;
        }
    },
    
    /**
     * Get auth token from localStorage
     */
    getToken() {
        return localStorage.getItem('findownn_token');
    },
    
    /**
     * Set auth token
     */
    setToken(token) {
        localStorage.setItem('findownn_token', token);
    },
    
    /**
     * Clear auth token
     */
    clearToken() {
        localStorage.removeItem('findownn_token');
    },
    
    // ==================== VENUES ====================
    
    /**
     * Get all venues with filters
     * @param {Object} filters - { city, sport, search, min_price, max_price, page, per_page, sort }
     */
    async getVenues(filters = {}) {
        const params = new URLSearchParams({ resource: 'venues', ...filters }).toString();
        return await this.request(`?${params}`);
    },
    
    /**
     * Get single venue by ID
     */
    async getVenue(venueId) {
        return await this.request(`/venues/${venueId}`);
    },
    
    /**
     * Get venue reviews
     */
    async getVenueReviews(venueId, page = 1) {
        return await this.request(`/venues/${venueId}/reviews?page=${page}`);
    },
    
    /**
     * Get venue images
     */
    async getVenueImages(venueId) {
        return await this.request(`/venues/${venueId}/images`);
    },
    
    /**
     * Get venue WhatsApp link
     */
    async getVenueWhatsApp(venueId) {
        return await this.request(`/venues/${venueId}/whatsapp`);
    },
    
    /**
     * Get venue availability
     */
    async getVenueAvailability(venueId, date) {
        return await this.request(`/venues/${venueId}/availability?date=${date}`);
    },
    
    // ==================== SPORTS ====================
    
    /**
     * Get all sports
     */
    async getSports(params = {}) {
        const query = new URLSearchParams({ resource: 'sports', ...params });
        return await this.request(`?${query.toString()}`);
    },
    
    /**
     * Get sport by ID
     */
    async getSport(sportId) {
        return await this.request(`/sports/${sportId}`);
    },
    
    // ==================== COURTS ====================
    
    /**
     * Get courts for a venue
     */
    async getCourts(venueId) {
        return await this.request(`/courts?venue_id=${venueId}`);
    },
    
    /**
     * Get court availability
     */
    async getCourtAvailability(courtId, date) {
        return await this.request(`/courts/${courtId}/availability?date=${date}`);
    },
    
    // ==================== BOOKINGS ====================
    
    /**
     * Create new booking
     */
    async createBooking(bookingData) {
        const result = await this.request('/bookings', {
            method: 'POST',
            body: bookingData
        });
        this.invalidateBookingCache();
        return result;
    },
    
    /**
     * Get user bookings
     */
    async getUserBookings(status = null) {
        const params = status ? `?status=${status}` : '';
        return await this.request(`/bookings${params}`);
    },
    
    /**
     * Get booking by ID
     */
    async getBooking(bookingId) {
        return await this.request(`/bookings/${bookingId}`);
    },
    
    /**
     * Cancel booking
     */
    async cancelBooking(bookingId) {
        const result = await this.request(`/bookings/${bookingId}/cancel`, {
            method: 'POST'
        });
        this.invalidateBookingCache(bookingId);
        return result;
    },
    
    // ==================== AUTH ====================
    
    /**
     * Login
     */
    async login(email, password) {
        const response = await this.request('/auth/login', {
            method: 'POST',
            body: { email, password }
        });
        
        if (response.success && response.data.token) {
            this.setToken(response.data.token);
        }
        
        return response;
    },
    
    /**
     * Register
     */
    async register(userData) {
        const response = await this.request('/auth/register', {
            method: 'POST',
            body: userData
        });
        
        if (response.success && response.data.token) {
            this.setToken(response.data.token);
        }
        
        return response;
    },
    
    /**
     * Logout
     */
    async logout() {
        const response = await this.request('/auth/logout', {
            method: 'POST'
        });
        
        this.clearToken();
        return response;
    },
    
    /**
     * Get current user
     */
    async getCurrentUser() {
        return await this.request('/user/profile');
    },
    
    // ==================== USER ====================
    
    /**
     * Update user profile
     */
    async updateProfile(profileData) {
        return await this.request('/user/profile', {
            method: 'PUT',
            body: profileData
        });
    },
    
    /**
     * Get user stats
     */
    async getUserStats() {
        return await this.request('/user/stats');
    },
    
    // ==================== SEARCH ====================
    
    /**
     * Search venues, sports, cities
     */
    async search(query) {
        return await this.request(`?resource=search&q=${encodeURIComponent(query)}`);
    },
    
    // ==================== REVIEWS ====================
    
    /**
     * Submit review
     */
    async submitReview(venueId, reviewData) {
        return await this.request('/reviews', {
            method: 'POST',
            body: {
                venue_id: venueId,
                ...reviewData
            }
        });
    },
    
    // ==================== PAYMENTS ====================
    
    /**
     * Initiate Razorpay payment for a booking
     */
    async initiatePayment(bookingId) {
        const result = await this.request('/payments/initiate', {
            method: 'POST',
            body: { booking_id: bookingId }
        });
        this.invalidateBookingCache(bookingId);
        return result;
    },

    /**
     * Verify Razorpay payment after checkout
     */
    async verifyPayment({ booking_id, razorpay_order_id, razorpay_payment_id, razorpay_signature }) {
        const result = await this.request('/payments/verify', {
            method: 'POST',
            body: {
                booking_id,
                razorpay_order_id,
                razorpay_payment_id,
                razorpay_signature
            }
        });
        this.invalidateBookingCache(booking_id);
        return result;
    },
    
    // ==================== CITIES ====================
    
    /**
     * Get all cities
     */
    async getCities() {
        return await this.request('?resource=cities');
    }
};

// Export for use in other files
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FindownnAPI;
}
