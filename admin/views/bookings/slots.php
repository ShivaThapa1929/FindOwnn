<?php
/**
 * Booking slot grid — venue/court/day picker and hourly availability.
 *
 * @var array       $venues
 * @var array       $courts
 * @var array|null  $venue
 * @var array|null  $court
 * @var array       $slots
 * @var string|int  $venue_id
 * @var string|int  $court_id
 * @var string      $date
 */
$venues   = $venues ?? [];
$courts   = $courts ?? [];
$venue    = $venue ?? null;
$court    = $court ?? null;
$slots    = $slots ?? [];
$venue_id = $venue_id ?? '';
$court_id = $court_id ?? '';
$date     = $date ?? date('Y-m-d');
$pricePerHourJs = (float) (is_array($court) ? ($court['price_per_hour'] ?? 0) : 0);
?>
<style>
/* Dark Theme Styling */
.slot-booking-container { padding: 2rem; max-width: 1400px; margin: 0 auto; }
.slots-card { background: rgba(15,25,18,0.95) !important; border: 1px solid rgba(134,168,146,0.15); border-radius: 12px; margin-bottom: 1.5rem; }
.slots-card .card-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid rgba(134,168,146,0.15); }
.slots-card .card-header h3 { font-size: 1.1rem; font-weight: 600; color: #f0fdf4; margin: 0; display: flex; align-items: center; gap: 0.5rem; }
.slots-card .card-header h3 i { color: #22c55e; }
.slots-card .card-body { padding: 1.5rem; background: transparent !important; }
.slot-filter-form .form-row { display: grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 1rem; align-items: end; }
.form-group { margin-bottom: 0; }
.form-group label { display: block; margin-bottom: 0.5rem; color: #d1e7d9; font-weight: 500; font-size: 0.875rem; }
.form-control { width: 100%; padding: 0.65rem 1rem; background: #0d1510; border: 1px solid rgba(134,168,146,0.15); border-radius: 8px; color: #f0fdf4; font-size: 0.875rem; }
.form-control:focus { outline: none; border-color: #22c55e; background: #0a0f0b; }
.form-control option { background: #0d1510; color: #f0fdf4; }
.btn { padding: 0.65rem 1.5rem; border-radius: 8px; font-weight: 600; font-size: 0.875rem; display: inline-flex; align-items: center; gap: 0.5rem; border: none; cursor: pointer; text-decoration: none; transition: all 0.3s; }
.btn-primary { background: #22c55e; color: #0a0f0b; }
.btn-primary:hover { background: #16a34a; }
.empty-state { text-align: center; padding: 4rem 2rem; }
.empty-icon { font-size: 4rem; color: #86a892; margin-bottom: 1.5rem; }
.empty-state h3 { color: #f0fdf4; font-size: 1.5rem; margin-bottom: 0.75rem; }
.empty-state p { color: #a3c4af; font-size: 1rem; margin-bottom: 2rem; }
.stats-summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin: 1.5rem 0; }
.stat-card { background: rgba(15,25,18,0.95); border: 1px solid rgba(134,168,146,0.15); border-radius: 12px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; }
.stat-icon { width: 50px; height: 50px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
.stat-icon.available-color { background: rgba(34,197,94,0.15); color: #22c55e; }
.stat-icon.booked-color { background: rgba(239,68,68,0.15); color: #ef4444; }
.stat-icon.occupancy-color { background: rgba(59,130,246,0.15); color: #3b82f6; }
.stat-icon.revenue-color { background: rgba(245,158,11,0.15); color: #f59e0b; }
.stat-value { font-size: 1.75rem; font-weight: 700; color: #f0fdf4; margin-bottom: 0.25rem; }
.stat-label { font-size: 0.875rem; color: #a3c4af; font-weight: 500; }
.venue-info-card { background: linear-gradient(135deg, rgba(34,197,94,0.1) 0%, rgba(16,185,129,0.05) 100%); border: 1px solid rgba(34,197,94,0.2); border-radius: 12px; padding: 1.5rem; margin: 1.5rem 0; }
.venue-info-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem; }
.venue-info-header h2 { color: #f0fdf4; font-size: 1.75rem; margin: 0 0 0.5rem 0; }
.venue-location { color: #a3c4af; font-size: 1rem; margin: 0; }
.venue-date { background: rgba(34,197,94,0.15); padding: 0.5rem 1rem; border-radius: 8px; color: #22c55e; font-weight: 600; }
.court-info-bar { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; padding-top: 1rem; border-top: 1px solid rgba(34,197,94,0.2); }
.court-detail { display: flex; align-items: center; gap: 0.5rem; color: #d1e7d9; font-size: 0.95rem; }
.court-detail i { color: #22c55e; font-size: 1.1rem; }
.time-slots-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
.time-slot { background: rgba(15,25,18,0.95); border: 2px solid; border-radius: 16px; padding: 1.5rem; transition: all 0.3s; position: relative; overflow: hidden; }
.time-slot::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 4px; }
.time-slot.available::before { background: linear-gradient(90deg, #22c55e, #4ade80); }
.time-slot.booked::before { background: linear-gradient(90deg, #ef4444, #f87171); }
.time-slot.available { border-color: rgba(34,197,94,0.4); cursor: pointer; }
.time-slot.available:hover { border-color: #22c55e; background: rgba(34,197,94,0.08); transform: translateY(-4px); box-shadow: 0 8px 16px rgba(34,197,94,0.2); }
.time-slot.booked { border-color: rgba(239,68,68,0.4); background: rgba(239,68,68,0.03); cursor: not-allowed; }
.slot-time { font-size: 1.25rem; font-weight: 700; color: #f0fdf4; margin-bottom: 1rem; letter-spacing: -0.02em; }
.slot-status { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 8px; font-size: 0.875rem; font-weight: 600; margin-bottom: 1rem; }
.available-badge { background: rgba(34,197,94,0.2); color: #22c55e; border: 1px solid rgba(34,197,94,0.3); }
.booked-badge { background: rgba(239,68,68,0.2); color: #ef4444; border: 1px solid rgba(239,68,68,0.3); }
.slot-price { font-size: 1.75rem; font-weight: 700; color: #22c55e; margin: 1rem 0; letter-spacing: -0.02em; }
.btn-book-slot { width: 100%; margin-top: 1rem; padding: 0.875rem 1rem; background: linear-gradient(135deg, rgba(34,197,94,0.15), rgba(34,197,94,0.25)); border: 2px solid #22c55e; color: #22c55e; border-radius: 10px; font-weight: 700; cursor: pointer; transition: all 0.3s; font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; }
.btn-book-slot:hover { background: linear-gradient(135deg, #22c55e, #16a34a); color: #0a0f0b; transform: scale(1.02); box-shadow: 0 4px 12px rgba(34,197,94,0.4); }
.btn-book-slot:active { transform: scale(0.98); }
.booking-ref { font-size: 0.9rem; color: #3b82f6; font-weight: 600; margin-bottom: 0.5rem; font-family: 'Courier New', monospace; }
.booking-user { font-size: 0.875rem; color: #d1e7d9; margin-bottom: 0.4rem; display: flex; align-items: center; gap: 0.5rem; }
.booking-amount { font-size: 1.1rem; color: #22c55e; font-weight: 700; margin-top: 0.75rem; }
.slot-info { margin-top: 1rem; padding-top: 1rem; border-top: 1px solid rgba(134,168,146,0.2); }
.slot-info small { display: block; margin-top: 0.5rem; font-size: 0.8rem; color: #86a892; font-style: italic; }
.btn-slot-action { background: linear-gradient(135deg, rgba(59,130,246,0.15), rgba(59,130,246,0.25)); border: 1px solid rgba(59,130,246,0.4); color: #3b82f6; padding: 0.625rem 1.25rem; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; font-weight: 600; transition: all 0.3s; }
.btn-slot-action:hover { background: #3b82f6; color: white; transform: scale(1.02); box-shadow: 0 4px 12px rgba(59,130,246,0.3); }
.time-slot.available.selected { border-color: #fbbf24; background: rgba(251, 191, 36, 0.12); box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.35); }
.time-slot.merged-booked { grid-column: span 1; min-height: 160px; }
.slot-duration-badge { display:inline-flex; align-items:center; gap:.35rem; margin-top:.5rem; padding:.35rem .65rem; border-radius:999px; background:rgba(59,130,246,.15); color:#93c5fd; font-size:.78rem; font-weight:700; }
.selected-slot-bar { position:fixed; left:50%; transform:translateX(-50%); bottom:24px; z-index:1000; display:none; align-items:center; gap:12px; padding:14px 18px; background:#0f172a; border:1px solid rgba(34,197,94,.35); border-radius:14px; box-shadow:0 12px 30px rgba(0,0,0,.35); color:#f0fdf4; }
.selected-slot-bar.visible { display:flex; }
.selected-slot-bar .summary { font-size:.92rem; font-weight:600; }
.selected-slot-bar .btn-merge-book { background:#22c55e; color:#0a0f0b; border:none; border-radius:10px; padding:.65rem 1rem; font-weight:700; cursor:pointer; }
@media (max-width: 768px) {
  .slot-filter-form .form-row { grid-template-columns: 1fr; }
  .stats-summary { grid-template-columns: 1fr; }
  .time-slots-grid { grid-template-columns: 1fr; }
  .selected-slot-bar { left: 1rem; right: 1rem; transform: none; flex-direction: column; align-items: stretch; }
}
</style>

<div class="content-header">
    <div class="content-header-left">
        <h1><i class="fas fa-calendar-check"></i> Booking Slots</h1>
        <p class="text-muted">View and manage time slot bookings</p>
    </div>
    <div class="content-header-right">
        <a href="<?= url('/bookings/offline/create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Booking
        </a>
    </div>
</div>

<div class="slot-booking-container">
    <div class="slots-card">
        <div class="card-header">
            <h3><i class="fas fa-building"></i> Select Venue & Court</h3>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="slot-filter-form">
                <div class="form-row">
                    <div class="form-group">
                        <label>Venue</label>
                        <select name="venue_id" id="venue_id" class="form-control" required onchange="loadCourts(this.value)">
                            <option value="">Select Venue</option>
                            <?php foreach ($venues as $v): ?>
                                <option value="<?= (int) $v['id'] ?>" <?= (string) $v['id'] === (string) $venue_id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) ($v['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Court</label>
                        <select name="court_id" id="court_id" class="form-control" required>
                            <option value="">Select Court</option>
                            <?php foreach ($courts as $c): ?>
                                <option value="<?= (int) $c['id'] ?>" <?= (string) $c['id'] === (string) $court_id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string) ($c['name'] ?? ''), ENT_QUOTES, 'UTF-8') ?> (Court #<?= htmlspecialchars((string) ($c['court_number'] ?? ''), ENT_QUOTES, 'UTF-8') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Date</label>
                        <input type="date" name="date" class="form-control" value="<?= htmlspecialchars((string) $date, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> View Slots
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if ($venue_id && $court_id && is_array($venue) && is_array($court)): ?>
    <!-- Stats Summary -->
    <div class="stats-summary">
        <?php
        $totalSlots = count($slots);
        $bookedSlots = count(array_filter($slots, fn($s) => $s['is_booked']));
        $availableSlots = $totalSlots - $bookedSlots;
        $occupancyRate = $totalSlots > 0 ? round(($bookedSlots / $totalSlots) * 100) : 0;
        $totalRevenue = array_sum(array_map(fn($s) => $s['is_booked'] ? ($s['booking']['total_amount'] ?? 0) : 0, $slots));
        ?>
        <div class="stat-card">
            <div class="stat-icon available-color"><i class="fas fa-calendar-day"></i></div>
            <div><div class="stat-value"><?= $totalSlots ?></div><div class="stat-label">Total Slots</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon booked-color"><i class="fas fa-check-circle"></i></div>
            <div><div class="stat-value"><?= $bookedSlots ?></div><div class="stat-label">Booked</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon available-color"><i class="fas fa-circle"></i></div>
            <div><div class="stat-value"><?= $availableSlots ?></div><div class="stat-label">Available</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon occupancy-color"><i class="fas fa-chart-pie"></i></div>
            <div><div class="stat-value"><?= $occupancyRate ?>%</div><div class="stat-label">Occupancy</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon revenue-color"><i class="fas fa-rupee-sign"></i></div>
            <div><div class="stat-value">₹<?= number_format($totalRevenue) ?></div><div class="stat-label">Revenue</div></div>
        </div>
    </div>

    <!-- Venue Info -->
    <div class="venue-info-card">
        <div class="venue-info-header">
            <div>
                <h2><?= htmlspecialchars($venue['name'] ?? '') ?></h2>
                <p class="venue-location">
                    <i class="fas fa-map-marker-alt"></i>
                    <?= htmlspecialchars($venue['city'] ?? '') ?>, <?= htmlspecialchars($venue['state'] ?? '') ?>
                </p>
            </div>
            <div class="venue-info-meta">
                <span class="venue-date">
                    <i class="fas fa-calendar"></i>
                    <?= date('D, M d, Y', strtotime($date)) ?>
                </span>
            </div>
        </div>
        <div class="court-info-bar">
            <div class="court-detail">
                <i class="fas fa-tennis-ball"></i>
                <span><?= htmlspecialchars($court['name'] ?? '') ?></span>
            </div>
            <div class="court-detail">
                <i class="fas fa-volleyball-ball"></i>
                <span><?= htmlspecialchars($court['sport_name'] ?? 'N/A') ?></span>
            </div>
            <div class="court-detail">
                <i class="fas fa-tag"></i>
                <span>₹<?= number_format($court['price_per_hour'] ?? 0) ?>/hour</span>
            </div>
            <div class="court-detail">
                <i class="fas fa-users"></i>
                <span>Capacity: <?= $court['capacity'] ?? 'N/A' ?></span>
            </div>
        </div>
    </div>

    <!-- Time Slots -->
    <div class="slots-card">
        <div class="card-header">
            <h3><i class="fas fa-clock"></i> Time Slots</h3>
        </div>
        <div class="card-body">
            <?php if (empty($slots)): ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-calendar-times"></i></div>
                <h3>No Time Slots Available</h3>
                <p>The venue may be closed on this day.</p>
            </div>
            <?php else: ?>
            <div class="time-slots-grid">
                <?php foreach ($slots as $slot): ?>
                <?php
                    $mergedHours = (int) ($slot['merged_hours'] ?? 1);
                    $pricePerHour = (float) ($court['price_per_hour'] ?? 0);
                    $displayAmount = $slot['is_booked']
                        ? (float) ($slot['booking']['total_amount'] ?? ($pricePerHour * $mergedHours))
                        : ($pricePerHour * $mergedHours);
                ?>
                <div class="time-slot <?= $slot['is_booked'] ? 'booked' : 'available' ?><?= !empty($slot['is_merged_display']) ? ' merged-booked' : '' ?>"
                     <?php if (!$slot['is_booked']): ?>
                     data-start="<?= e($slot['start_time']) ?>"
                     data-end="<?= e($slot['end_time']) ?>"
                     onclick="toggleSlotSelection(this)"
                     <?php endif; ?>>
                    <div class="slot-time">
                        <?= date('g:i A', strtotime($slot['start_time'])) ?> – <?= date('g:i A', strtotime($slot['end_time'])) ?>
                    </div>
                    <?php if ($slot['is_booked']): ?>
                        <div class="slot-status booked-badge"><i class="fas fa-check-circle"></i> Booked</div>
                        <?php if ($mergedHours > 1): ?>
                        <div class="slot-duration-badge">
                            <i class="fas fa-hourglass-half"></i>
                            <?= $mergedHours ?> Hour<?= $mergedHours > 1 ? 's' : '' ?>
                        </div>
                        <?php endif; ?>
                        <div class="slot-info">
                            <div class="booking-ref">Ref: <?= htmlspecialchars($slot['booking']['booking_reference'] ?? 'N/A') ?></div>
                            <div class="booking-user">
                                <i class="fas fa-user"></i> <?= htmlspecialchars($slot['booking']['user_name'] ?? 'Unknown') ?>
                            </div>
                            <div class="booking-amount">
                                <i class="fas fa-rupee-sign"></i> ₹<?= number_format($displayAmount) ?>
                            </div>
                            <?php if (isset($slot['booking']['id'])): ?>
                            <div class="slot-actions" style="margin-top: 0.75rem;">
                                <a href="<?= url('/bookings/'.$slot['booking']['id']) ?>" class="btn-slot-action" title="View Details">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="slot-status available-badge"><i class="fas fa-circle"></i> Available</div>
                        <div class="slot-price">₹<?= number_format($pricePerHour) ?>/hr</div>
                        <button type="button" class="btn-book-slot" onclick="event.stopPropagation(); bookSlot('<?= $slot['start_time'] ?>', '<?= $slot['end_time'] ?>')">
                            <i class="fas fa-plus-circle"></i> Book 1 Hour
                        </button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="selected-slot-bar" id="selectedSlotBar">
                <div class="summary" id="selectedSlotSummary">Select consecutive slots</div>
                <button type="button" class="btn-merge-book" onclick="bookSelectedSlots()">
                    <i class="fas fa-calendar-plus"></i> Book Selected
                </button>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="slots-card">
        <div class="card-body">
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-map-marked-alt"></i></div>
                <h3>Select Venue & Court</h3>
                <p>Choose a venue and court from the options above to view booking slots.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>


<script>
const venueId = '<?= htmlspecialchars((string) $venue_id, ENT_QUOTES, 'UTF-8') ?>';
const courtId = '<?= htmlspecialchars((string) $court_id, ENT_QUOTES, 'UTF-8') ?>';
const currentDate = '<?= htmlspecialchars((string) $date, ENT_QUOTES, 'UTF-8') ?>';
const pricePerHour = <?= $pricePerHourJs ?>;
let selectedSlots = [];

function normalizeTime(value) {
    return (value || '').substring(0, 5);
}

function formatTime12(value) {
    const [h, m] = normalizeTime(value).split(':').map(Number);
    const ampm = h >= 12 ? 'PM' : 'AM';
    const hour = h % 12 || 12;
    return `${hour}:${String(m).padStart(2, '0')} ${ampm}`;
}

function toggleSlotSelection(el) {
    const start = el.dataset.start;
    const end = el.dataset.end;
    const key = `${start}|${end}`;
    const idx = selectedSlots.findIndex(s => s.key === key);

    if (idx >= 0) {
        selectedSlots.splice(idx, 1);
        el.classList.remove('selected');
    } else {
        selectedSlots.push({ key, start, end });
        el.classList.add('selected');
    }

    selectedSlots.sort((a, b) => normalizeTime(a.start).localeCompare(normalizeTime(b.start)));
    updateSelectedBar();
}

function updateSelectedBar() {
    const bar = document.getElementById('selectedSlotBar');
    const summary = document.getElementById('selectedSlotSummary');
    if (!bar || !summary) return;

    if (selectedSlots.length === 0) {
        bar.classList.remove('visible');
        return;
    }

    const first = selectedSlots[0];
    const last = selectedSlots[selectedSlots.length - 1];
    const hours = selectedSlots.length;
    const total = pricePerHour * hours;

    summary.textContent = `${formatTime12(first.start)} – ${formatTime12(last.end)} (${hours} Hour${hours > 1 ? 's' : ''}) · ₹${total.toLocaleString('en-IN')}`;
    bar.classList.add('visible');
}

function slotsAreConsecutive() {
    if (selectedSlots.length <= 1) return true;
    for (let i = 1; i < selectedSlots.length; i++) {
        if (normalizeTime(selectedSlots[i - 1].end) !== normalizeTime(selectedSlots[i].start)) {
            return false;
        }
    }
    return true;
}

function bookSelectedSlots() {
    if (!venueId || !courtId || !currentDate || selectedSlots.length === 0) {
        alert('Please select at least one available slot');
        return;
    }
    if (!slotsAreConsecutive()) {
        alert('Please select continuous time slots only (e.g. 9:00 AM – 1:00 PM)');
        return;
    }

    const sorted = [...selectedSlots].sort((a, b) => normalizeTime(a.start).localeCompare(normalizeTime(b.start)));
    const start = normalizeTime(sorted[0].start);
    const end = normalizeTime(sorted[sorted.length - 1].end);
    window.location.href = `<?= url('/bookings/offline/create') ?>?venue_id=${venueId}&court_id=${courtId}&date=${currentDate}&start_time=${start}&end_time=${end}`;
}

function bookSlot(startTime, endTime) {
    if (!venueId || !courtId || !currentDate) {
        alert('Please select venue and court first');
        return;
    }
    window.location.href = `<?= url('/bookings/offline/create') ?>?venue_id=${venueId}&court_id=${courtId}&date=${currentDate}&start_time=${startTime}&end_time=${endTime}`;
}

function loadCourts(venueId) {
    if (!venueId) {
        document.getElementById('court_id').innerHTML = '<option value="">Select Court</option>';
        return;
    }

    const courtSelect = document.getElementById('court_id');
    courtSelect.innerHTML = '<option value="">Loading...</option>';
    courtSelect.disabled = true;

    fetch(`<?= url('/api/courts') ?>?venue_id=${venueId}`)
        .then(r => r.json())
        .then(res => {
            const courts = res.courts || res;
            let options = '<option value="">Select Court</option>';
            if (Array.isArray(courts) && courts.length > 0) {
                courts.forEach(court => {
                    const selected = courtId == court.id ? 'selected' : '';
                    options += `<option value="${court.id}" ${selected}>${court.name} (Court #${court.court_number || '1'})</option>`;
                });
            } else {
                options = '<option value="">No courts available</option>';
            }
            courtSelect.innerHTML = options;
            courtSelect.disabled = false;
        })
        .catch(err => {
            console.error('Error:', err);
            courtSelect.innerHTML = '<option value="">Error loading courts</option>';
            courtSelect.disabled = false;
        });
}
</script>
