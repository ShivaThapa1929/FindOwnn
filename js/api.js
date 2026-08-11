/**
 * Findownn API Service
 * Handles all API calls to /api/v1/
 */

const FindownnAPI = {
    baseURL: (() => {
        let path = window.location.pathname;

        // Strip known page routes from the end to find the subfolder base path
        const routes = [
            '/venue-details', '/booking-payment', '/venues',
            '/sports', '/partner', '/about', '/contact', '/home',
            '/index.php', '/index'
        ];
        for (const route of routes) {
            if (path.endsWith(route)) {
                path = path.slice(0, -route.length);
                break;
            }
        }

        // Normalise — remove trailing slashes
        path = path.replace(/\/+$/, '');
        return path + '/api/v1';
    })(),

    /**
     * Resolve playground upload paths to a browser-loadable URL
     */
    getSiteBase() {
        let path = window.location.pathname;
        const routes = [
            '/venue-details', '/booking-payment', '/venues',
            '/sports', '/partner', '/about', '/contact', '/home',
            '/index.php', '/index'
        ];
        for (const route of routes) {
            if (path.endsWith(route)) {
                path = path.slice(0, -route.length);
                break;
            }
        }
        return path.replace(/\/+$/, '') || '';
    },

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
        const url = `${this.baseURL}${endpoint}`;
        const config = {
            method: options.method || 'GET',
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
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
                const err = new Error(data.message || 'API request failed');
                err.code = data.code || null;
                err.status = response.status;
                throw err;
            }

            if ((config.method || 'GET') === 'GET') {
                this.setCached(endpoint, data);
            }

            return data;
        } catch (error) {
            const isOffline = !navigator.onLine
                || error.name === 'TypeError'
                || error.message === 'Failed to fetch';

            if (isOffline && (options.method || 'GET') === 'GET') {
                const cached = this.getCached(endpoint);
                if (cached) {
                    cached._fromCache = true;
                    return cached;
                }

                const offlineErr = new Error('You are offline. Please check your internet connection.');
                offlineErr.code = 'OFFLINE';
                offlineErr.offline = true;
                throw offlineErr;
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
    async getSports() {
        return await this.request('?resource=sports');
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
        return await this.request('/bookings', {
            method: 'POST',
            body: bookingData
        });
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
        return await this.request(`/bookings/${bookingId}/cancel`, {
            method: 'POST'
        });
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
        return await this.request('/payments/initiate', {
            method: 'POST',
            body: { booking_id: bookingId }
        });
    },

    /**
     * Verify Razorpay payment after checkout
     */
    async verifyPayment({ booking_id, razorpay_order_id, razorpay_payment_id, razorpay_signature }) {
        return await this.request('/payments/verify', {
            method: 'POST',
            body: {
                booking_id,
                razorpay_order_id,
                razorpay_payment_id,
                razorpay_signature
            }
        });
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
