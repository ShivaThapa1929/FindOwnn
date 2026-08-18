<!DOCTYPE html>
<html lang="en">
<head>
    <?php
    require_once __DIR__ . '/../includes/user-auth.php';
    site_send_no_cache_headers();
    ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Payment - Findownn</title>
    <link rel="icon" type="image/png" sizes="32x32" href="<?= htmlspecialchars($asset_base ?? '/') ?>assets/images/favicon-32x32.png?v=6">
    <link rel="shortcut icon" href="<?= htmlspecialchars($asset_base ?? '/') ?>assets/images/favicon-32x32.png?v=6">
    <link rel="apple-touch-icon" href="<?= htmlspecialchars($asset_base ?? '/') ?>assets/images/apple-touch-icon.png?v=6">
    
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <link rel="stylesheet" href="<?= htmlspecialchars($asset_base ?? '/') ?>css/payment.css">
    <link rel="stylesheet" href="<?= htmlspecialchars($asset_base ?? '/') ?>css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>

<!-- Payment Loading Overlay -->
<div id="payment-loading" class="hidden">
    <div class="loading-content">
        <div class="loading-spinner"></div>
        <div class="loading-message">Processing payment...</div>
    </div>
</div>

<!-- Main Content -->
<div class="container" style="max-width: 800px; margin: 40px auto; padding: 20px;">
    
    <h1 style="text-align: center; margin-bottom: 30px;">Complete Your Payment</h1>
    
    <!-- Booking Details Card -->
    <div class="payment-card">
        <h3 style="margin-bottom: 20px;">Booking Details</h3>
        
        <div class="payment-row">
            <span>Playground:</span>
            <strong id="venue-name">Loading...</strong>
        </div>
        
        <div class="payment-row">
            <span>Date:</span>
            <strong id="booking-date">Loading...</strong>
        </div>
        
        <div class="payment-row">
            <span>Time:</span>
            <strong id="booking-time">Loading...</strong>
        </div>
        
        <div class="payment-row">
            <span>Sport:</span>
            <strong id="sport-name">Loading...</strong>
        </div>
        
        <div class="payment-row">
            <span>Duration:</span>
            <strong id="duration">Loading...</strong>
        </div>
        
        <!-- Payment Summary -->
        <div class="payment-summary">
            <div class="payment-row">
                <span>Subtotal:</span>
                <span id="subtotal">₹0.00</span>
            </div>
            
            <div class="payment-row">
                <span>Tax (GST 18%):</span>
                <span id="tax">₹0.00</span>
            </div>
            
            <div class="payment-row total">
                <span>Total Amount:</span>
                <span id="total-amount">₹0.00</span>
            </div>
        </div>
    </div>
    
    <!-- Payment Methods -->
    <div class="payment-card">
        <h3 style="margin-bottom: 20px;">Payment Methods</h3>
        
        <div class="payment-methods">
            <div class="payment-method">
                <i class="bi bi-credit-card"></i>
                <span>Card</span>
            </div>
            <div class="payment-method">
                <i class="bi bi-bank"></i>
                <span>Net Banking</span>
            </div>
            <div class="payment-method">
                <i class="bi bi-phone"></i>
                <span>UPI</span>
            </div>
            <div class="payment-method">
                <i class="bi bi-wallet2"></i>
                <span>Wallet</span>
            </div>
        </div>
        
        <div style="text-align: center; margin-top: 30px;">
            <button class="btn-pay-now" id="pay-now-btn" onclick="initiatePayment()">
                <i class="bi bi-lock-fill"></i>
                Pay Now
            </button>
            
            <div class="secure-badge">
                <i class="bi bi-shield-check"></i>
                <span>Secure payment powered by Razorpay</span>
            </div>
        </div>
    </div>
    
</div>

<!-- Payment JS -->
<script src="<?= htmlspecialchars($asset_base ?? '/') ?>js/api.js?v=1.4"></script>
<script src="<?= htmlspecialchars($asset_base ?? '/') ?>js/payment.js?v=1.1"></script>

<script>
const bookingData = {
    booking_id: <?= (int) ($_GET['booking_id'] ?? 0) ?>,
    booking_reference: '',
    venue_id: 0,
    venue_name: 'Loading...',
    user_id: 0,
    user_name: '',
    user_email: '',
    user_phone: '',
    booking_date: '',
    start_time: '',
    end_time: '',
    sport_name: '',
    total_hours: 0,
    amount: 0,
    subtotal: 0,
    tax: 0
};

function formatCurrency(value) {
    return `₹${Number(value || 0).toFixed(2)}`;
}

function renderBookingDetails() {
    document.getElementById('venue-name').textContent = bookingData.venue_name || '—';
    document.getElementById('booking-date').textContent = bookingData.booking_date
        ? new Date(bookingData.booking_date).toLocaleDateString('en-IN', { day: 'numeric', month: 'long', year: 'numeric' })
        : '—';
    document.getElementById('booking-time').textContent = bookingData.start_time && bookingData.end_time
        ? `${bookingData.start_time} - ${bookingData.end_time}`
        : '—';
    document.getElementById('sport-name').textContent = bookingData.sport_name || '—';
    document.getElementById('duration').textContent = bookingData.total_hours ? `${bookingData.total_hours} hours` : '—';
    document.getElementById('subtotal').textContent = formatCurrency(bookingData.subtotal);
    document.getElementById('tax').textContent = formatCurrency(bookingData.tax);
    document.getElementById('total-amount').textContent = formatCurrency(bookingData.amount);
}

async function loadBookingDetails() {
    if (!bookingData.booking_id) {
        FindownnPayment.showError('Missing booking ID. Open this page with ?booking_id=YOUR_ID');
        return;
    }

    const token = localStorage.getItem('findownn_token');
    if (!token) {
        FindownnPayment.showInfo('Tip: Login via the app first, or ensure booking_id and user_id are valid for payment.');
        return;
    }

    try {
        const response = await FindownnAPI.getBooking(bookingData.booking_id);
        const booking = response.data;

        bookingData.booking_reference = booking.booking_number || booking.booking_reference || '';
        bookingData.venue_id = booking.venue?.id || 0;
        bookingData.venue_name = booking.venue?.name || '';
        bookingData.user_id = booking.user_id || 0;
        bookingData.user_name = booking.user?.name || '';
        bookingData.user_email = booking.user?.email || '';
        bookingData.user_phone = booking.user?.phone || '';
        bookingData.booking_date = booking.date || booking.booking_date || '';
        bookingData.start_time = booking.start_time || '';
        bookingData.end_time = booking.end_time || '';
        bookingData.sport_name = booking.sport?.name || booking.sport_name || '';
        bookingData.amount = Number(booking.amount || booking.total_amount || 0);
        bookingData.subtotal = bookingData.amount / 1.18;
        bookingData.tax = bookingData.amount - bookingData.subtotal;

        renderBookingDetails();
    } catch (error) {
        FindownnPayment.showError(error.message || 'Could not load booking details');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    renderBookingDetails();
    loadBookingDetails();
});

function initiatePayment() {
    if (!bookingData.booking_id) {
        FindownnPayment.showError('Missing booking ID');
        return;
    }

    const btn = document.getElementById('pay-now-btn');
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass"></i> Processing...';

    FindownnPayment.initPayment(bookingData)
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-lock-fill"></i> Pay Now';
        });
}
</script>

</body>
</html>
