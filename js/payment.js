/**
 * Findownn Payment Integration
 * Razorpay Checkout — uses API v1 when logged in, legacy endpoints otherwise
 */

const FindownnPayment = {

    getBasePath() {
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

    getToken() {
        return localStorage.getItem('findownn_token');
    },

    /**
     * Initialize Razorpay Payment
     */
    initPayment: async function(bookingData) {
        try {
            this.showLoading();

            const orderData = await this.createOrder(bookingData);

            if (!orderData.success) {
                throw new Error(orderData.message || 'Failed to create order');
            }

            this.openCheckout(orderData.data, bookingData);

        } catch (error) {
            console.error('Payment Error:', error);
            if (error.code === 'AUTH_REQUIRED') {
                this.showError('Please login first to complete payment.');
            } else {
                this.showError(error.message || 'Payment failed');
            }
        } finally {
            this.hideLoading();
        }
    },

    /**
     * Create Razorpay Order via API
     */
    createOrder: async function(bookingData) {
        const token = this.getToken();
        const base = this.getBasePath();

        // Prefer authenticated API v1 when user is logged in
        if (token && typeof FindownnAPI !== 'undefined') {
            try {
                const response = await FindownnAPI.initiatePayment(bookingData.booking_id);
                return {
                    success: true,
                    data: {
                        order_id: response.data.order_id,
                        amount: response.data.amount,
                        currency: response.data.currency,
                        key_id: response.data.key_id,
                        booking_reference: response.data.booking_reference
                    }
                };
            } catch (error) {
                if (error.message === 'Authentication required') {
                    const authError = new Error('Authentication required');
                    authError.code = 'AUTH_REQUIRED';
                    throw authError;
                }
                throw error;
            }
        }

        // Legacy endpoint (no JWT — requires booking_id, amount, user_id)
        const response = await fetch(`${base}/api/payment/create-order`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                booking_id: bookingData.booking_id,
                amount: bookingData.amount,
                user_id: bookingData.user_id
            })
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Failed to create order');
        }

        return result;
    },

    /**
     * Open Razorpay Checkout Modal
     */
    openCheckout: function(orderData, bookingData) {
        const base = this.getBasePath();

        const options = {
            key: orderData.key_id,
            amount: orderData.amount,
            currency: orderData.currency,
            name: 'Findownn',
            description: `Booking at ${bookingData.venue_name}`,
            image: `${base}/assets/images/logo.png`,
            order_id: orderData.order_id,

            prefill: {
                name: bookingData.user_name,
                email: bookingData.user_email,
                contact: bookingData.user_phone
            },

            notes: {
                booking_id: bookingData.booking_id,
                venue_id: bookingData.venue_id,
                user_id: bookingData.user_id
            },

            theme: { color: '#22c55e' },

            method: {
                netbanking: true,
                card: true,
                upi: true,
                wallet: true
            },

            handler: (response) => {
                this.handlePaymentSuccess(response, bookingData);
            },

            modal: {
                ondismiss: () => {
                    this.handlePaymentCancelled();
                }
            }
        };

        const rzp = new Razorpay(options);

        rzp.on('payment.failed', (response) => {
            this.handlePaymentFailed(response);
        });

        rzp.open();
    },

    /**
     * Handle Payment Success
     */
    handlePaymentSuccess: async function(razorpayResponse, bookingData) {
        try {
            this.showLoading('Verifying payment...');

            const token = this.getToken();
            const base = this.getBasePath();
            let result;

            if (token && typeof FindownnAPI !== 'undefined') {
                result = await FindownnAPI.verifyPayment({
                    booking_id: bookingData.booking_id,
                    razorpay_order_id: razorpayResponse.razorpay_order_id,
                    razorpay_payment_id: razorpayResponse.razorpay_payment_id,
                    razorpay_signature: razorpayResponse.razorpay_signature
                });
            } else {
                const verifyResponse = await fetch(`${base}/api/payment/verify`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        razorpay_order_id: razorpayResponse.razorpay_order_id,
                        razorpay_payment_id: razorpayResponse.razorpay_payment_id,
                        razorpay_signature: razorpayResponse.razorpay_signature,
                        booking_id: bookingData.booking_id
                    })
                });
                result = await verifyResponse.json();
            }

            if (result.success) {
                const ref = bookingData.booking_reference || result.data?.booking_reference;
                window.location.href = `${base}/booking-success?ref=${encodeURIComponent(ref)}`;
            } else {
                throw new Error(result.message || 'Payment verification failed');
            }

        } catch (error) {
            console.error('Verification Error:', error);
            this.showError('Payment verification failed. Please contact support.');
        } finally {
            this.hideLoading();
        }
    },

    handlePaymentFailed: function(response) {
        console.error('Payment Failed:', response);

        const errorReason = response.error?.reason;
        const errorDescription = response.error?.description;

        let message = 'Payment failed. ';

        if (errorReason === 'payment_failed') {
            message += 'Your payment could not be processed. Please try again.';
        } else if (errorReason === 'authentication_failed') {
            message += 'Card authentication failed. Please use another card.';
        } else {
            message += errorDescription || 'Please try again.';
        }

        this.showError(message);
    },

    handlePaymentCancelled: function() {
        this.showInfo('Payment cancelled. Your booking is still pending.');
    },

    showLoading: function(message = 'Processing...') {
        const overlay = document.getElementById('payment-loading');
        if (overlay) {
            overlay.querySelector('.loading-message').textContent = message;
            overlay.classList.remove('hidden');
        }
    },

    hideLoading: function() {
        const overlay = document.getElementById('payment-loading');
        if (overlay) {
            overlay.classList.add('hidden');
        }
    },

    showError: function(message) {
        this.showToast(message, 'error');
    },

    showInfo: function(message) {
        this.showToast(message, 'info');
    },

    showSuccess: function(message) {
        this.showToast(message, 'success');
    },

    showToast: function(message, type = 'info') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;

        const icon = type === 'error' ? '❌' : type === 'success' ? '✅' : 'ℹ️';

        toast.innerHTML = `
            <span class="toast-icon">${icon}</span>
            <span class="toast-message">${message}</span>
            <button class="toast-close" onclick="this.parentElement.remove()">×</button>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('toast-fade-out');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }
};

window.FindownnPayment = FindownnPayment;
