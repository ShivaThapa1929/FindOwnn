<?php
/**
 * @var array $myVenues    Owner/admin venues for the offline booking form
 * @var array $venueCourts Courts for the pre-selected venue
 * @var array $old         Previous form input after validation errors
 * @var array $errors      Field validation errors
 * @var bool  $noVenues    True when owner has no venues yet
 */
$old         = $old         ?? [];
$errors      = $errors      ?? [];
$myVenues    = $myVenues    ?? [];
$venueCourts = $venueCourts ?? [];
$noVenues    = $noVenues    ?? empty($myVenues);
$old_v  = fn(string $k, mixed $d = '') => $old[$k] ?? $d;
$err    = fn(string $k) => isset($errors[$k])
    ? '<div class="invalid-feedback d-block mt-1">'.e($errors[$k]).'</div>' : '';
?>

<style>
/* Dark Theme for Booking Form */
#offlineBookingForm .form-select,
#offlineBookingForm .form-control,
#offlineBookingForm input[type="date"],
#offlineBookingForm input[type="time"],
#offlineBookingForm input[type="text"],
#offlineBookingForm input[type="email"],
#offlineBookingForm input[type="number"],
#offlineBookingForm select,
#offlineBookingForm textarea {
    background: #0d1510 !important;
    border: 1px solid rgba(134,168,146,0.3) !important;
    color: #f0fdf4 !important;
    border-radius: 8px;
}

#offlineBookingForm .form-select:focus,
#offlineBookingForm .form-control:focus,
#offlineBookingForm input:focus,
#offlineBookingForm select:focus {
    background: #0a0f0b !important;
    border-color: #22c55e !important;
    box-shadow: 0 0 0 0.2rem rgba(34,197,94,0.15) !important;
    color: #f0fdf4 !important;
}

#offlineBookingForm .form-select:disabled,
#offlineBookingForm .form-control:disabled {
    background: rgba(13,21,16,0.5) !important;
    border-color: rgba(134,168,146,0.15) !important;
    color: #6b8576 !important;
    cursor: not-allowed;
    opacity: 0.6;
}

#offlineBookingForm option {
    background: #0d1510;
    color: #f0fdf4;
}

#offlineBookingForm .form-select option:disabled {
    color: #6b8576;
}

#offlineBookingForm .input-group-text {
    background: #0d1510 !important;
    border: 1px solid rgba(134,168,146,0.3) !important;
    color: #a3c4af !important;
}

/* Date and Time Picker Improvements */
#offlineBookingForm input[type="date"]::-webkit-calendar-picker-indicator,
#offlineBookingForm input[type="time"]::-webkit-calendar-picker-indicator {
    filter: invert(0.8);
    cursor: pointer;
}

/* Summary Box Styling */
.booking-summary-box {
    background: linear-gradient(135deg, rgba(34,197,94,0.1), rgba(16,185,129,0.05));
    border: 1px solid rgba(34,197,94,0.3);
    border-radius: 12px;
    padding: 20px;
}

.booking-summary-box .fw-700 {
    color: #22c55e;
}

.booking-summary-box .text-muted {
    color: #a3c4af !important;
}

.booking-summary-box .text-success {
    color: #22c55e !important;
}

/* Labels */
#offlineBookingForm .form-label-sm {
    color: #d1e7d9;
    font-weight: 600;
}

#offlineBookingForm small.text-muted {
    color: #a3c4af !important;
    font-size: 0.8rem;
}
</style>

<div class="row justify-content-center">
<div class="col-lg-9">

<div class="panel">
  <div class="panel-head">
    <div>
      <h6 class="panel-title"><i class="bi bi-plus-circle-fill me-2 text-success"></i>Add Offline / Walk-in Booking</h6>
      <p class="text-muted small mb-0 mt-1">Record bookings taken offline, by phone, WhatsApp, or walk-in.</p>
    </div>
    <a href="<?= url('/bookings') ?>" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-arrow-left me-1"></i>Back
    </a>
  </div>
  <div class="panel-body">

    <?php if ($noVenues): ?>
      <div class="alert alert-warning mb-4">
        <div class="fw-600 mb-1"><i class="bi bi-building me-1"></i>No venue found</div>
        <p class="small mb-2">Offline booking ke liye pehle ek venue add karna hoga.</p>
        <a href="<?= url('/venues/create') ?>" class="btn btn-sm btn-success me-2">
          <i class="bi bi-plus-lg me-1"></i>Add Venue
        </a>
        <a href="<?= url('/venues') ?>" class="btn btn-sm btn-outline-secondary">My Venues</a>
      </div>
    <?php endif; ?>

    <?php if ($flash = flash('error')): ?>
      <div class="alert alert-danger py-2 small mb-3"><i class="bi bi-exclamation-circle me-1"></i><?= e($flash) ?></div>
    <?php endif; ?>

    <form action="<?= url('/bookings/offline/store') ?>" method="POST" novalidate id="offlineBookingForm"<?= $noVenues ? ' class="pe-none opacity-50"' : '' ?>>
      <?= csrf_field() ?>

      <!-- ── Venue ───────────────────────────────────────────────── -->
      <div class="section-divider mb-3">
        <span class="section-divider-label">Venue & Slot</span>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label-sm">Venue *</label>
          <select name="venue_id" id="venueSelect" class="form-select <?= isset($errors['venue_id']) ? 'is-invalid' : '' ?>" required>
            <option value="">— Select your venue —</option>
            <?php foreach ($myVenues as $v): ?>
            <option value="<?= $v['id'] ?>" <?= $old_v('venue_id') == $v['id'] ? 'selected' : '' ?>>
              <?= e($v['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
          <?= $err('venue_id') ?>
        </div>

        <div class="col-md-6">
          <label class="form-label-sm">Court *</label>
          <select name="court_id" id="courtSelect" class="form-select <?= isset($errors['court_id']) ? 'is-invalid' : '' ?>" required>
            <?php if (!$old_v('venue_id')): ?>
              <option value="">— First select a venue —</option>
            <?php elseif (empty($venueCourts)): ?>
              <option value="">— No courts found for this venue —</option>
            <?php else: ?>
              <option value="">— Select a court —</option>
              <?php foreach ($venueCourts as $c): ?>
              <option value="<?= (int) $c['id'] ?>"
                      data-price="<?= e((string) ($c['price_per_hour'] ?? 0)) ?>"
                      data-name="<?= e($c['name']) ?>"
                      <?= $old_v('court_id') == $c['id'] ? 'selected' : '' ?>>
                Court <?= e($c['court_number'] ?: '1') ?> — <?= e($c['name']) ?>
              </option>
              <?php endforeach; ?>
            <?php endif; ?>
          </select>
          <?= $err('court_id') ?>
          <small class="text-muted">Courts load automatically when you pick a venue</small>
        </div>

        <div class="col-md-4">
          <label class="form-label-sm">Booking Date *</label>
          <input type="date" name="booking_date" id="bookingDate"
                 class="form-control <?= isset($errors['booking_date']) ? 'is-invalid' : '' ?>"
                 value="<?= e($old_v('booking_date', date('Y-m-d'))) ?>"
                 min="<?= date('Y-m-d') ?>" required>
          <?= $err('booking_date') ?>
        </div>

        <div class="col-md-4">
          <label class="form-label-sm">Start Time *</label>
          <input type="time" name="start_time" id="startTime"
                 class="form-control <?= isset($errors['start_time']) ? 'is-invalid' : '' ?>"
                 value="<?= e($old_v('start_time', '06:00')) ?>"
                 step="3600" required>
          <small class="text-muted">Only hourly slots (e.g., 5:00, 6:00)</small>
          <?= $err('start_time') ?>
        </div>

        <div class="col-md-4">
          <label class="form-label-sm">End Time *</label>
          <input type="time" name="end_time" id="endTime"
                 class="form-control <?= isset($errors['end_time']) ? 'is-invalid' : '' ?>"
                 value="<?= e($old_v('end_time', '07:00')) ?>"
                 step="3600" required>
          <small class="text-muted">Only hourly slots (e.g., 6:00, 7:00)</small>
          <?= $err('end_time') ?>
        </div>
      </div>

      <!-- ── Auto-calculated amount ─────────────────────────────── -->
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label class="form-label-sm">Duration</label>
          <div class="form-control bg-opacity-50" id="durationDisplay" style="background:rgba(255,255,255,0.03);color:var(--text-muted);">
            1 hour(s)
          </div>
        </div>
        <div class="col-md-4">
          <label class="form-label-sm">Calculated Amount</label>
          <div class="form-control" id="calcAmount" style="background:rgba(34,197,94,0.06);color:#4ade80;font-weight:700;">
            ₹0
          </div>
        </div>
        <div class="col-md-4">
          <label class="form-label-sm">
            Custom Amount
            <span class="text-muted fw-400">(override if needed)</span>
          </label>
          <div class="input-group">
            <span class="input-group-text">₹</span>
            <input type="number" name="custom_amount" id="customAmount"
                   class="form-control" placeholder="Leave blank to use calculated"
                   min="0" step="1"
                   value="<?= e($old_v('custom_amount', '')) ?>">
          </div>
        </div>
      </div>

      <!-- ── Customer ────────────────────────────────────────────── -->
      <div class="section-divider mb-3">
        <span class="section-divider-label">Customer Info</span>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label class="form-label-sm">Customer Name *</label>
          <input type="text" name="customer_name"
                 class="form-control <?= isset($errors['customer_name']) ? 'is-invalid' : '' ?>"
                 value="<?= e($old_v('customer_name')) ?>"
                 placeholder="e.g. Rahul Patel" required>
          <?= $err('customer_name') ?>
        </div>
        <div class="col-md-4">
          <label class="form-label-sm">Phone <span class="text-muted fw-400">(optional)</span></label>
          <input type="text" name="customer_phone"
                 class="form-control"
                 value="<?= e($old_v('customer_phone')) ?>"
                 placeholder="+91 98765 43210"
                 maxlength="15">
        </div>
        <div class="col-md-4">
          <label class="form-label-sm">Email <span class="text-muted fw-400">(optional)</span></label>
          <input type="email" name="customer_email"
                 class="form-control"
                 value="<?= e($old_v('customer_email')) ?>"
                 placeholder="customer@example.com">
        </div>
      </div>

      <!-- ── Payment & Notes ─────────────────────────────────────── -->
      <div class="section-divider mb-3">
        <span class="section-divider-label">Payment & Notes</span>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <label class="form-label-sm">Payment Status *</label>
          <select name="payment_status" class="form-select" required>
            <option value="paid"    <?= $old_v('payment_status','paid')==='paid'    ? 'selected':'' ?>>✅ Paid</option>
            <option value="pending" <?= $old_v('payment_status','paid')==='pending' ? 'selected':'' ?>>⏳ Pending</option>
          </select>
        </div>
        <div class="col-md-8">
          <label class="form-label-sm">Notes <span class="text-muted fw-400">(source: WhatsApp / Phone / Walk-in)</span></label>
          <input type="text" name="notes"
                 class="form-control"
                 value="<?= e($old_v('notes')) ?>"
                 placeholder="e.g. Booked via WhatsApp — paid cash">
        </div>
      </div>

      <!-- ── Summary Box ─────────────────────────────────────────── -->
      <div class="booking-summary-box mb-4" id="summaryBox" style="display:none;">
        <div class="d-flex align-items-center gap-2 mb-2">
          <i class="bi bi-clipboard-check-fill text-success"></i>
          <span class="fw-700">Booking Summary</span>
        </div>
        <div class="row g-2 small">
          <div class="col-6"><span class="text-muted">Venue:</span> <span id="sumVenue">—</span></div>
          <div class="col-6"><span class="text-muted">Date:</span> <span id="sumDate">—</span></div>
          <div class="col-6"><span class="text-muted">Time:</span> <span id="sumTime">—</span></div>
          <div class="col-6"><span class="text-muted">Duration:</span> <span id="sumDur">—</span></div>
          <div class="col-6"><span class="text-muted">Amount:</span> <strong id="sumAmt" class="text-success">₹0</strong></div>
        </div>
      </div>

      <div class="d-flex gap-3">
        <button type="submit" class="btn btn-success px-5">
          <i class="bi bi-check-circle me-2"></i>Create Offline Booking
        </button>
        <a href="<?= url('/bookings') ?>" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>

  </div>
</div>

</div>
</div>

<script>
(function(){
  const venueSelect  = document.getElementById('venueSelect');
  const courtSelect  = document.getElementById('courtSelect');
  const startInput   = document.getElementById('startTime');
  const endInput     = document.getElementById('endTime');
  const dateInput    = document.getElementById('bookingDate');
  const customAmt    = document.getElementById('customAmount');
  const durationDisp = document.getElementById('durationDisplay');
  const calcAmtDisp  = document.getElementById('calcAmount');
  const summaryBox   = document.getElementById('summaryBox');

  // Load courts for venue with optional target court auto-selection
  function loadCourtsForVenue(venueId, targetCourtId) {
    courtSelect.classList.add('is-loading');
    courtSelect.innerHTML = '<option value="">Loading courts...</option>';

    if (!venueId) {
      courtSelect.innerHTML = '<option value="">— First select a venue —</option>';
      courtSelect.classList.remove('is-loading');
      update();
      return;
    }

    fetch(`<?= url('/api/courts') ?>?venue_id=${venueId}`, {
      headers: { 'Accept': 'application/json' },
      credentials: 'same-origin'
    })
      .then(res => {
        if (!res.ok) throw new Error('HTTP ' + res.status);
        return res.json();
      })
      .then(res => {
        const courts = res.courts || res;
        if (!Array.isArray(courts) || courts.length === 0) {
          courtSelect.innerHTML = '<option value="">No courts available — add a court first</option>';
        } else {
          let html = '<option value="">— Select a court —</option>';
          courts.forEach(court => {
            const selected = (targetCourtId && String(targetCourtId) === String(court.id)) ? 'selected' : '';
            html += `<option value="${court.id}" ${selected} data-price="${court.price_per_hour || 1000}" data-name="${court.name}">Court ${court.court_number || '1'} — ${court.name}</option>`;
          });
          courtSelect.innerHTML = html;
        }
        courtSelect.classList.remove('is-loading');
        update();
      })
      .catch(err => {
        console.error('Error loading courts:', err);
        courtSelect.innerHTML = '<option value="">Could not load courts — refresh and try again</option>';
        courtSelect.classList.remove('is-loading');
      });
  }

  venueSelect.addEventListener('change', function() {
    loadCourtsForVenue(this.value, null);
  });

  // Auto-fetch courts on load if venue is pre-selected (skip if server already rendered courts)
  const preVenueId = venueSelect.value;
  const preCourtId = '<?= e($old_v("court_id")) ?>';
  if (preVenueId && courtSelect.options.length <= 2) {
    loadCourtsForVenue(preVenueId, preCourtId);
  }

  document.getElementById('offlineBookingForm').addEventListener('submit', function(e) {
    if (!venueSelect.value) {
      e.preventDefault();
      alert('Please select a venue.');
      return;
    }
    if (!courtSelect.value) {
      e.preventDefault();
      alert('Please select a court. If none appear, add courts to your venue first.');
    }
  });

  function pad(n){ return String(n).padStart(2,'0'); }

  function getHours(){
    const s = startInput.value, e = endInput.value;
    if(!s || !e) return 0;
    const [sh,sm] = s.split(':').map(Number);
    const [eh,em] = e.split(':').map(Number);
    const diff = (eh*60+em) - (sh*60+sm);
    return diff > 0 ? diff/60 : 0;
  }

  function getPrice(){
    const opt = courtSelect.options[courtSelect.selectedIndex];
    return opt && opt.value ? parseFloat(opt.dataset.price||0) : 0;
  }

  function getVenueName(){
    const opt = venueSelect.options[venueSelect.selectedIndex];
    return opt && opt.value ? opt.text : '—';
  }

  function getCourtName(){
    const opt = courtSelect.options[courtSelect.selectedIndex];
    return opt && opt.value ? opt.dataset.name || opt.text : '—';
  }

  function update(){
    const hours = getHours();
    const price = getPrice();
    const amt   = Math.round(hours * price);

    durationDisp.textContent = hours > 0 ? hours.toFixed(1) + ' hour(s)' : '—';
    calcAmtDisp.textContent  = hours > 0 ? '₹' + amt.toLocaleString('en-IN') : '₹0';

    // Summary
    const d = dateInput.value, s = startInput.value, e = endInput.value;
    if(venueSelect.value && courtSelect.value && d && s && e && hours > 0){
      summaryBox.style.display = 'block';
      document.getElementById('sumVenue').textContent = getVenueName() + ' - ' + getCourtName();
      document.getElementById('sumDate').textContent  = d;
      document.getElementById('sumTime').textContent  = s + ' – ' + e;
      document.getElementById('sumDur').textContent   = hours.toFixed(1) + 'h';
      const finalAmt = customAmt.value ? parseFloat(customAmt.value) : amt;
      document.getElementById('sumAmt').textContent   = '₹' + finalAmt.toLocaleString('en-IN');
    } else {
      summaryBox.style.display = 'none';
    }
  }

  [venueSelect, courtSelect, startInput, endInput, dateInput, customAmt].forEach(el => el.addEventListener('input', update));

  // Auto-set end time = start + 1h
  startInput.addEventListener('change', function(){
    const [h,m] = this.value.split(':').map(Number);
    endInput.value = pad(h+1) + ':' + pad(m);
    update();
  });

  // Run once on load (for old values)
  update();
})();
</script>
