<?php
/**
 * @var array $booking Booking record from BookingController::show()
 */
$booking   = $booking ?? [];
$role      = auth()['role'] ?? '';
$isOffline = str_starts_with($booking['booking_reference'] ?? '', 'OFL-');
?>

<!-- Print-Only Stylesheet -->
<style media="print">
  /* PRINT ONLY - Premium Professional Design */
  @page {
    margin: 0.4in;
    size: letter portrait;
  }
  
  * {
    background: white !important;
    background-color: white !important;
    background-image: none !important;
    box-shadow: none !important;
    text-shadow: none !important;
    filter: none !important;
    transform: none !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
  
  *::before,
  *::after {
    content: none !important;
    display: none !important;
  }
  
  body * {
    visibility: hidden !important;
  }
  
  .invoice-print,
  .invoice-print * {
    visibility: visible !important;
  }
  
  .no-print {
    display: none !important;
  }
  
  .invoice-print {
    position: absolute !important;
    left: 0 !important;
    top: 0 !important;
    width: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
    border: none !important;
    page-break-inside: avoid !important;
    font-family: 'Inter', 'Segoe UI', system-ui, sans-serif !important;
  }
  
  /* Hide old logo structure */
  .company-logo-box {
    display: none !important;
  }
  
  /* SHOW company name prominently */
  .company-logo-row {
    display: flex !important;
    align-items: center !important;
    gap: 0.8rem !important;
    margin-bottom: 0.4rem !important;
    visibility: visible !important;
  }
  
  /* HEADER - Eye-catching Layout */
  .invoice-header {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 2rem !important;
    margin-bottom: 1.5rem !important;
    padding-bottom: 1.2rem !important;
    border-bottom: 4px solid #3887C6 !important;
    position: relative !important;
  }
  
  /* Add decorative corner accent */
  .invoice-header::after {
    content: '' !important;
    display: block !important;
    position: absolute !important;
    bottom: -4px !important;
    right: 0 !important;
    width: 120px !important;
    height: 4px !important;
    background: linear-gradient(90deg, #3887C6, #10b981) !important;
  }
  
  .company-info h2 {
    font-size: 2.5rem !important;
    font-weight: 900 !important;
    margin: 0 !important;
    color: #E5EFFB !important;
    letter-spacing: -0.5px !important;
    line-height: 1 !important;
    background: linear-gradient(135deg, #E5EFFB 0%, #1a1a1a 100%) !important;
    -webkit-background-clip: text !important;
    -webkit-text-fill-color: transparent !important;
    background-clip: text !important;
  }
  
  .company-tagline {
    font-size: 0.9rem !important;
    margin: 0.4rem 0 0.5rem 0 !important;
    color: #3887C6 !important;
    font-weight: 600 !important;
    letter-spacing: 0.3px !important;
  }
  
  .company-contact {
    font-size: 0.8rem !important;
    margin: 0 !important;
    color: #666 !important;
    line-height: 1.6 !important;
  }
  
  .company-contact a {
    color: #3887C6 !important;
    text-decoration: none !important;
  }
  
  .invoice-meta {
    text-align: right !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: flex-end !important;
    justify-content: center !important;
  }
  
  .invoice-title {
    font-size: 2rem !important;
    font-weight: 900 !important;
    margin-bottom: 0.6rem !important;
    color: #E5EFFB !important;
    letter-spacing: 3px !important;
  }
  
  .invoice-ref {
    font-size: 1.1rem !important;
    font-weight: 700 !important;
    padding: 0.6rem 1.2rem !important;
    margin-bottom: 0.5rem !important;
    border: 2px solid #3887C6 !important;
    border-radius: 8px !important;
    display: inline-block !important;
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%) !important;
    color: #2a6ba0 !important;
    font-family: 'Courier New', monospace !important;
    letter-spacing: 0.5px !important;
  }
  
  .invoice-date {
    font-size: 0.9rem !important;
    color: #666 !important;
    font-weight: 600 !important;
  }
  
  /* STATUS BADGES - Modern Pill Design */
  .status-row {
    display: grid !important;
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 1rem !important;
    margin-bottom: 1.5rem !important;
  }
  
  .status-badge {
    padding: 1rem 1.2rem !important;
    border-radius: 10px !important;
    text-align: center !important;
    border: 2px solid !important;
    position: relative !important;
    overflow: hidden !important;
  }
  
  .status-badge.success {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%) !important;
    border-color: #3887C6 !important;
  }
  
  .status-badge.info {
    background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%) !important;
    border-color: #3b82f6 !important;
  }
  
  .status-label {
    font-size: 0.7rem !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
    letter-spacing: 1.2px !important;
    margin-bottom: 0.4rem !important;
    color: #666 !important;
  }
  
  .status-value {
    font-size: 1.15rem !important;
    font-weight: 900 !important;
    letter-spacing: 0.5px !important;
  }
  
  .status-confirmed { color: #15803d !important; }
  .status-completed { color: #1d4ed8 !important; }
  .status-pending { color: #b45309 !important; }
  .status-cancelled { color: #b91c1c !important; }
  .status-paid { color: #1e40af !important; }
  .status-refunded { color: #6b7280 !important; }
  .status-failed { color: #b91c1c !important; }
  
  /* INFO SECTION - Card Design */
  .info-section {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 1.5rem !important;
    margin-bottom: 1.5rem !important;
    padding: 1.2rem !important;
    border: 2px solid #e5e5e5 !important;
    border-radius: 10px !important;
    background: #fafafa !important;
  }
  
  .info-block h6 {
    font-size: 0.75rem !important;
    font-weight: 900 !important;
    text-transform: uppercase !important;
    letter-spacing: 1.2px !important;
    margin: 0 0 0.7rem 0 !important;
    padding-bottom: 0.5rem !important;
    border-bottom: 3px solid #3887C6 !important;
    color: #E5EFFB !important;
  }
  
  .info-block p {
    font-size: 0.9rem !important;
    margin: 0.4rem 0 !important;
    line-height: 1.6 !important;
    color: #333 !important;
  }
  
  .info-block p strong {
    font-weight: 800 !important;
    color: #000 !important;
    font-size: 1rem !important;
  }
  
  .info-block p i {
    color: #3887C6 !important;
    margin-right: 0.4rem !important;
    font-size: 0.85rem !important;
  }
  
  /* BOOKING DETAILS - Icon Cards */
  .booking-details {
    display: grid !important;
    grid-template-columns: repeat(3, 1fr) !important;
    gap: 0.8rem !important;
    margin: 1.5rem 0 !important;
  }
  
  .detail-item {
    padding: 1rem 0.8rem !important;
    text-align: center !important;
    border: 2px solid #e5e5e5 !important;
    border-radius: 10px !important;
    background: #fafafa !important;
  }
  
  .detail-icon {
    font-size: 1.6rem !important;
    margin-bottom: 0.5rem !important;
    display: block !important;
  }
  
  .icon-date { color: #f59e0b !important; }
  .icon-time { color: #3b82f6 !important; }
  .icon-duration { color: #a855f7 !important; }
  
  .detail-label {
    font-size: 0.7rem !important;
    font-weight: 800 !important;
    text-transform: uppercase !important;
    letter-spacing: 1px !important;
    margin-bottom: 0.4rem !important;
    color: #666 !important;
  }
  
  .detail-value {
    font-size: 1.05rem !important;
    font-weight: 800 !important;
    color: #000 !important;
  }
  
  /* PRICING TABLE - Modern Stripes */
  .pricing-table {
    margin: 1.5rem 0 !important;
    border: 2px solid #e5e5e5 !important;
    border-radius: 10px !important;
    overflow: hidden !important;
  }
  
  .pricing-row {
    display: flex !important;
    justify-content: space-between !important;
    padding: 0.75rem 1.2rem !important;
    border-bottom: 1px solid #f0f0f0 !important;
    background: white !important;
  }
  
  .pricing-row:nth-child(odd) {
    background: #fafafa !important;
  }
  
  .pricing-row:last-child {
    border-bottom: none !important;
  }
  
  .pricing-row span:first-child {
    font-size: 0.9rem !important;
    color: #333 !important;
    font-weight: 600 !important;
  }
  
  .pricing-row span:last-child {
    font-size: 0.9rem !important;
    font-weight: 800 !important;
    color: #000 !important;
  }
  
  /* TOTAL - Eye-catching */
  .pricing-total {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%) !important;
    border-top: 4px solid #3887C6 !important;
    padding: 1.2rem 1.2rem !important;
    position: relative !important;
  }
  
  .pricing-total::before {
    content: '' !important;
    display: block !important;
    position: absolute !important;
    top: -4px !important;
    left: 0 !important;
    width: 100px !important;
    height: 4px !important;
    background: #10b981 !important;
  }
  
  .pricing-total span:first-child {
    font-size: 1.1rem !important;
    font-weight: 900 !important;
    text-transform: uppercase !important;
    letter-spacing: 1.5px !important;
    color: #E5EFFB !important;
  }
  
  .pricing-total .amount {
    font-size: 2rem !important;
    font-weight: 900 !important;
    color: #15803d !important;
    letter-spacing: -1px !important;
  }
  
  /* FOOTER - Clean */
  .footer-note {
    margin-top: 1.5rem !important;
    padding-top: 1.2rem !important;
    border-top: 2px solid #e5e5e5 !important;
    text-align: center !important;
  }
  
  .footer-note p {
    font-size: 0.85rem !important;
    margin: 0.4rem 0 !important;
    line-height: 1.6 !important;
    color: #666 !important;
  }
  
  .footer-note p strong {
    font-weight: 800 !important;
    color: #3887C6 !important;
  }
  
  .footer-note p:last-child {
    font-size: 0.75rem !important;
    color: #999 !important;
    margin-top: 0.6rem !important;
    font-style: italic !important;
  }
</style>

<!-- Screen-Only Stylesheet -->
<style media="screen">
  .invoice-wrapper {
    max-width: 1200px;
    margin: 0 auto;
  }

  /* Main Invoice Card */
  .invoice-print {
    background: linear-gradient(135deg, rgba(13,21,16,0.98) 0%, rgba(10,15,11,0.98) 100%);
    border: 1px solid rgba(56,135,198,0.25);
    border-radius: 16px;
    padding: 2.5rem;
    box-shadow: 0 8px 32px rgba(0,0,0,0.5), 0 0 0 1px rgba(56,135,198,0.1) inset;
    position: relative;
    overflow: hidden;
  }

  /* Decorative Background Pattern */
  .invoice-print::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -20%;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, rgba(56,135,198,0.08) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
  }

  .invoice-print::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -10%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(22,163,74,0.06) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
  }

  /* All content above decorations */
  .invoice-print > * {
    position: relative;
    z-index: 1;
  }

  /* Enhanced Header */
  .invoice-header {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 2rem;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 3px solid;
    border-image: linear-gradient(90deg, #3887C6, #2a6ba0, transparent) 1;
  }

  .company-info {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
  }

  .company-logo-row {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 0.5rem;
  }

  .company-logo-box {
    width: 48px;
    height: 48px;
    background: linear-gradient(135deg, #3887C6, #2a6ba0);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 16px rgba(56,135,198,0.4);
  }

  .company-logo-box img {
    width: 70%;
    height: 70%;
    object-fit: contain;
  }

  .company-info h2 {
    color: #f0fdf4;
    font-size: 2rem;
    font-weight: 800;
    margin: 0;
    letter-spacing: 1px;
    background: linear-gradient(135deg, #5a9fd4, #3887C6);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
  }

  .company-tagline {
    color: #86a892;
    font-size: 0.9rem;
    font-weight: 500;
    margin: 0;
    letter-spacing: 0.3px;
  }

  .company-contact {
    color: #6b7c75;
    font-size: 0.8rem;
    margin: 0.5rem 0 0 0;
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
  }

  .company-contact a {
    color: #86a892;
    text-decoration: none;
    transition: color 0.2s;
  }

  .company-contact a:hover {
    color: #3887C6;
  }

  /* Invoice Meta */
  .invoice-meta {
    text-align: right;
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    justify-content: center;
  }

  .invoice-title {
    font-size: 1.5rem;
    font-weight: 800;
    color: #f0fdf4;
    margin-bottom: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 3px;
    text-shadow: 0 2px 10px rgba(56,135,198,0.3);
  }

  .invoice-ref {
    font-size: 1.1rem;
    font-weight: 700;
    color: #3887C6;
    font-family: 'Courier New', monospace;
    background: rgba(56,135,198,0.18);
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    display: inline-block;
    margin-bottom: 0.75rem;
    border: 2px solid rgba(56,135,198,0.4);
    box-shadow: 0 4px 12px rgba(56,135,198,0.2);
  }

  .invoice-date {
    color: #86a892;
    font-size: 0.85rem;
    font-weight: 500;
  }

  /* Status Badges */
  .status-row {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1rem;
    margin-bottom: 2rem;
  }

  .status-badge {
    padding: 1rem 1.25rem;
    border-radius: 10px;
    text-align: center;
    border: 1px solid;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
  }

  .status-badge::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, transparent, rgba(255,255,255,0.05));
    opacity: 0;
    transition: opacity 0.3s;
  }

  .status-badge:hover::before {
    opacity: 1;
  }

  .status-badge.success { 
    background: rgba(56,135,198,0.12); 
    border-color: rgba(56,135,198,0.4);
    box-shadow: 0 4px 12px rgba(56,135,198,0.15);
  }

  .status-badge.info { 
    background: rgba(59,130,246,0.12); 
    border-color: rgba(59,130,246,0.4);
    box-shadow: 0 4px 12px rgba(59,130,246,0.15);
  }

  .status-label {
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #86a892;
    margin-bottom: 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
  }

  .status-label::before {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
  }

  .status-value {
    font-size: 1.1rem;
    font-weight: 800;
    letter-spacing: 0.5px;
  }

  .status-confirmed { color: #5a9fd4; }
  .status-completed { color: #60a5fa; }
  .status-pending { color: #fbbf24; }
  .status-cancelled { color: #f87171; }
  .status-paid { color: #60a5fa; }
  .status-refunded { color: #94a3b8; }
  .status-failed { color: #f87171; }

  /* Info Section */
  .info-section {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: rgba(15,25,18,0.6);
    border-radius: 12px;
    border: 1px solid rgba(56,135,198,0.15);
    box-shadow: 0 4px 16px rgba(0,0,0,0.2);
  }

  .info-block h6 {
    color: #3887C6;
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin: 0 0 1rem 0;
    padding-bottom: 0.6rem;
    border-bottom: 2px solid rgba(56,135,198,0.3);
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .info-block h6::before {
    content: '';
    width: 4px;
    height: 16px;
    background: linear-gradient(180deg, #3887C6, #2a6ba0);
    border-radius: 2px;
  }

  .info-block p {
    color: #d1e7d9;
    margin: 0.5rem 0;
    font-size: 0.9rem;
    line-height: 1.6;
    display: flex;
    align-items: center;
    gap: 0.75rem;
  }

  .info-block p strong {
    font-weight: 700;
    color: #f0fdf4;
    font-size: 1.05rem;
  }

  .info-block p i {
    color: #3887C6;
    width: 18px;
    font-size: 0.9rem;
  }

  /* Booking Details */
  .booking-details {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1rem;
    margin: 2rem 0;
  }

  .detail-item {
    text-align: center;
    padding: 1.25rem;
    border-radius: 10px;
    background: rgba(15,25,18,0.5);
    border: 1px solid rgba(56,135,198,0.15);
    transition: all 0.3s ease;
  }

  .detail-item:hover {
    transform: translateY(-2px);
    border-color: rgba(56,135,198,0.3);
    box-shadow: 0 8px 20px rgba(56,135,198,0.15);
  }

  .detail-icon {
    font-size: 1.75rem;
    margin-bottom: 0.75rem;
    filter: drop-shadow(0 2px 8px currentColor);
  }

  .icon-date { color: #f59e0b; }
  .icon-time { color: #3b82f6; }
  .icon-duration { color: #a855f7; }

  .detail-label {
    color: #86a892;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 0.5rem;
    letter-spacing: 0.8px;
  }

  .detail-value {
    color: #f0fdf4;
    font-size: 1.05rem;
    font-weight: 700;
  }

  /* Pricing Table */
  .pricing-table {
    margin: 2rem 0;
    border: 1px solid rgba(56,135,198,0.25);
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0,0,0,0.3);
  }

  .pricing-row {
    display: flex;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid rgba(134,168,146,0.12);
    background: rgba(15,25,18,0.4);
    transition: background 0.2s;
  }

  .pricing-row:hover {
    background: rgba(15,25,18,0.6);
  }

  .pricing-row:last-child {
    border-bottom: none;
  }

  .pricing-row span:first-child {
    color: #d1e7d9;
    font-size: 0.95rem;
    font-weight: 500;
  }

  .pricing-row span:last-child {
    color: #f0fdf4;
    font-weight: 700;
    font-size: 0.95rem;
  }

  .pricing-total {
    background: linear-gradient(135deg, rgba(56,135,198,0.2), rgba(22,163,74,0.15)) !important;
    border-top: 3px solid #3887C6 !important;
    padding: 1.25rem 1.5rem !important;
    box-shadow: 0 -4px 20px rgba(56,135,198,0.15) inset;
  }

  .pricing-total span:first-child {
    font-weight: 800;
    font-size: 1.1rem;
    color: #f0fdf4 !important;
    text-transform: uppercase;
    letter-spacing: 1px;
  }

  .pricing-total .amount {
    color: #5a9fd4;
    font-size: 2rem;
    font-weight: 800;
    text-shadow: 0 2px 10px rgba(56,135,198,0.4);
  }

  /* Footer */
  .footer-note {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid rgba(134,168,146,0.2);
    text-align: center;
  }

  .footer-note p {
    color: #86a892;
    font-size: 0.85rem;
    margin: 0.4rem 0;
    line-height: 1.6;
  }

  .footer-note p strong {
    color: #3887C6;
    font-weight: 700;
  }

  /* Sidebar */
  .action-sidebar {
    position: sticky;
    top: 80px;
  }

  .action-card {
    background: rgba(13,21,16,0.95);
    border: 1px solid rgba(56,135,198,0.2);
    border-radius: 12px;
    margin-bottom: 1rem;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0,0,0,0.3);
    transition: all 0.3s ease;
  }

  .action-card:hover {
    border-color: rgba(56,135,198,0.4);
    box-shadow: 0 8px 24px rgba(56,135,198,0.2);
  }

  .action-card-header {
    background: linear-gradient(135deg, rgba(56,135,198,0.15), rgba(22,163,74,0.1));
    padding: 1rem 1.25rem;
    border-bottom: 1px solid rgba(56,135,198,0.2);
    font-weight: 700;
    color: #f0fdf4;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 0.5rem;
  }

  .action-card-header i {
    color: #3887C6;
  }

  .action-card-body {
    padding: 1.25rem;
  }

  .action-card-body .btn {
    font-weight: 600;
    border-width: 2px;
    transition: all 0.3s ease;
  }

  .action-card-body .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(56,135,198,0.3);
  }

/* Responsive */
@media screen and (max-width: 768px) {
  .invoice-print {
    padding: 1.5rem;
  }

  .invoice-header {
    grid-template-columns: 1fr;
    gap: 1.5rem;
  }

  .invoice-meta {
    text-align: left;
    align-items: flex-start;
  }

  .status-row {
    grid-template-columns: 1fr;
  }

  .info-section {
    grid-template-columns: 1fr;
  }

  .booking-details {
    grid-template-columns: 1fr;
  }
}
</style>

<div class="invoice-wrapper">
  <div class="row g-4">
    <div class="col-lg-8">
      <div class="invoice-print">
        <!-- Header -->
        <div class="invoice-header">
          <div class="company-info">
            <div class="company-logo-row">
              <div class="company-logo-box">
                <img src="<?= url('/public/assets/images/logo.png') ?>" 
                     alt="Findownn">
              </div>
              <h2>FINDOWNN</h2>
            </div>
            <p class="company-tagline">Your Sports Booking Platform</p>
            <p class="company-contact">
              <a href="https://www.findownn.com">www.findownn.com</a>
              <span>|</span>
              <a href="mailto:<?= e(site_contact_email()) ?>"><?= e(site_contact_email()) ?></a>
            </p>
          </div>
          <div class="invoice-meta">
            <div class="invoice-title">INVOICE</div>
            <div class="invoice-ref"><?= e($booking['booking_reference']) ?></div>
            <div class="invoice-date">Date: <?= date('d M Y', strtotime($booking['created_at'])) ?></div>
          </div>
        </div>

        <!-- Status Badges -->
        <div class="status-row">
          <div class="status-badge success">
            <div class="status-label">Booking Status</div>
            <div class="status-value status-<?= e(preg_replace('/[^a-z_]/', '', strtolower($booking['status']))) ?>"><?= strtoupper(e($booking['status'])) ?></div>
          </div>
          <div class="status-badge info">
            <div class="status-label">Payment Status</div>
            <div class="status-value status-<?= e(preg_replace('/[^a-z_]/', '', strtolower($booking['payment_status']))) ?>"><?= strtoupper(e($booking['payment_status'])) ?></div>
          </div>
        </div>

        <!-- Bill To & Venue Info -->
        <div class="info-section">
          <div class="info-block">
            <h6>BILL TO</h6>
            <p><strong><?= e($booking['user_name'] ?? 'Walk-in Customer') ?></strong></p>
            <?php if (!empty($booking['user_phone'])): ?>
            <p><i class="bi bi-telephone-fill"></i> <?= e($booking['user_phone']) ?></p>
            <?php endif; ?>
            <?php if (!empty($booking['user_email']) && !str_contains($booking['user_email'],'@offline.findownn')): ?>
            <p><i class="bi bi-envelope-fill"></i> <?= e($booking['user_email']) ?></p>
            <?php endif; ?>
          </div>
          <div class="info-block">
            <h6>VENUE DETAILS</h6>
            <p><strong><?= e($booking['venue_name']) ?></strong></p>
            <p><i class="bi bi-geo-alt-fill"></i> <?= e($booking['venue_city']) ?></p>
            <?php if (!empty($booking['court_name'])): ?>
            <p><i class="bi bi-grid-3x3-gap-fill"></i> <?= e($booking['court_name']) ?> (Court #<?= e($booking['court_number']) ?>)</p>
            <?php endif; ?>
            <?php if (!empty($booking['sport_name'])): ?>
            <p><i class="bi bi-trophy-fill"></i> <?= e($booking['sport_name']) ?></p>
            <?php endif; ?>
          </div>
        </div>

        <!-- Booking Details -->
        <div class="booking-details">
          <div class="detail-item">
            <div class="detail-icon icon-date"><i class="bi bi-calendar-event-fill"></i></div>
            <div class="detail-label">Date</div>
            <div class="detail-value"><?= date('d M Y', strtotime($booking['booking_date'])) ?></div>
          </div>
          <div class="detail-item">
            <div class="detail-icon icon-time"><i class="bi bi-clock-fill"></i></div>
            <div class="detail-label">Time</div>
            <div class="detail-value"><?= date('g:i A', strtotime($booking['start_time'])) ?> – <?= date('g:i A', strtotime($booking['end_time'])) ?></div>
          </div>
          <div class="detail-item">
            <div class="detail-icon icon-duration"><i class="bi bi-hourglass-split"></i></div>
            <div class="detail-label">Duration</div>
            <div class="detail-value"><?= number_format((float) $booking['total_hours'], 1) ?> Hour<?= (float) $booking['total_hours'] != 1 ? 's' : '' ?></div>
          </div>
        </div>

        <!-- Pricing Breakdown -->
        <div class="pricing-table">
          <div class="pricing-row">
            <span>Court Rate (<?= e($booking['court_name'] ?? 'Court') ?> #<?= e($booking['court_number'] ?? 'N/A') ?>)</span>
            <span>₹<?= number_format($booking['price_per_hour']) ?>/hr</span>
          </div>
          <div class="pricing-row">
            <span>Duration</span>
            <span><?= number_format($booking['total_hours'],1) ?> hours</span>
          </div>
          <div class="pricing-row">
            <span>Subtotal</span>
            <span>₹<?= number_format($booking['subtotal'], 2) ?></span>
          </div>
          <?php if (!empty($booking['discount_amount']) && $booking['discount_amount'] > 0): ?>
          <div class="pricing-row" style="color: #3887C6;">
            <span>Discount <?= $booking['discount_percent'] > 0 ? '('.number_format($booking['discount_percent']).'%)' : '' ?></span>
            <span>-₹<?= number_format($booking['discount_amount'], 2) ?></span>
          </div>
          <?php endif; ?>
          <div class="pricing-row pricing-total">
            <span>TOTAL AMOUNT</span>
            <span class="amount">₹<?= number_format($booking['amount']) ?></span>
          </div>
        </div>

        <!-- Footer -->
        <div class="footer-note">
          <p><strong>Thank you for choosing Findownn!</strong></p>
          <p>For inquiries, please contact <?= e(site_contact_email()) ?> | +91 99999 00001</p>
          <p style="margin-top: 0.5rem; font-size: 0.75rem;">This is a computer-generated invoice and does not require a signature.</p>
        </div>
      </div>
    </div>

    <!-- Sidebar Actions -->
    <div class="col-lg-4 no-print">
      <div class="action-sidebar">
        <!-- Actions -->
        <div class="action-card">
          <div class="action-card-header"><i class="bi bi-printer me-2"></i>Actions</div>
          <div class="action-card-body">
            <button onclick="window.print()" class="btn btn-primary w-100 mb-2">
              <i class="bi bi-printer me-1"></i>Print Invoice
            </button>
            <?php if (in_array($booking['status'], ['confirmed', 'pending'], true)): ?>
            <form action="<?= url('/bookings/'.$booking['id'].'/reminder') ?>" method="POST" class="mb-2"
                  onsubmit="return confirm('Send WhatsApp booking reminder to <?= e($booking['user_name']) ?>?')">
              <?= csrf_field() ?>
              <button type="submit" class="btn btn-success w-100">
                <i class="bi bi-whatsapp me-1"></i>Send Reminder
              </button>
            </form>
            <?php endif; ?>
            <?php if (!empty($booking['user_id'])): ?>
            <a href="<?= url('/players/'.$booking['user_id']) ?>" class="btn btn-outline-info w-100 mb-2">
              <i class="bi bi-person-badge me-1"></i>View Player
            </a>
            <?php endif; ?>
            <a href="<?= url('/bookings') ?>" class="btn btn-outline-secondary w-100">
              <i class="bi bi-arrow-left me-1"></i>Back to Bookings
            </a>
          </div>
        </div>

        <!-- Update Booking Status -->
        <div class="action-card">
          <div class="action-card-header"><i class="bi bi-toggle-on me-2"></i>Booking Status</div>
          <div class="action-card-body">
            <form action="<?= url('/bookings/'.$booking['id'].'/status') ?>" method="POST">
              <?= csrf_field() ?>
              <div class="d-grid gap-2">
                <?php 
                $statuses = [
                  'confirmed' => 'success', 'completed' => 'primary',
                  'cancelled' => 'danger', 'pending' => 'warning'
                ];
                foreach ($statuses as $st => $color): 
                  if ($booking['status'] !== $st): ?>
                <button type="submit" name="status" value="<?= $st ?>"
                        class="btn btn-outline-<?= $color ?> btn-sm"
                        onclick="return confirm('Update to <?= ucfirst($st) ?>?')">
                  <?= ucfirst($st) ?>
                </button>
                <?php endif; endforeach; ?>
              </div>
            </form>
          </div>
        </div>

        <!-- Update Payment Status -->
        <div class="action-card">
          <div class="action-card-header"><i class="bi bi-credit-card me-2"></i>Payment Status</div>
          <div class="action-card-body">
            <form action="<?= url('/bookings/'.$booking['id'].'/payment') ?>" method="POST">
              <?= csrf_field() ?>
              <div class="d-grid gap-2">
                <?php 
                $payments = [
                  'paid' => 'success', 'pending' => 'warning',
                  'refunded' => 'info', 'failed' => 'danger'
                ];
                foreach ($payments as $ps => $color): 
                  if ($booking['payment_status'] !== $ps): ?>
                <button type="submit" name="payment_status" value="<?= $ps ?>"
                        class="btn btn-outline-<?= $color ?> btn-sm"
                        onclick="return confirm('Update to <?= ucfirst($ps) ?>?')">
                  <?= ucfirst($ps) ?>
                </button>
                <?php endif; endforeach; ?>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
