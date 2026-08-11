<div class="content-header">
    <div class="content-header-left">
        <h1>
            <i class="fas fa-calendar-check"></i>
            Booking Slots
        </h1>
        <p class="text-muted">View and manage time slot bookings</p>
    </div>
    <div class="content-header-right">
        <?php if ($venue_id && $court_id && $venue && $court): ?>
        <a href="/admin/bookings" class="btn btn-secondary" style="margin-right: 0.5rem;">
            <i class="fas fa-list"></i>
            All Bookings
        </a>
        <?php endif; ?>
        <a href="/admin/bookings/offline/create" class="btn btn-primary">
            <i class="fas fa-plus"></i>
            New Booking
        </a>
    </div>
</div>

<div class="slot-booking-container">
    <!-- Venue Selection -->
    <div class="card slots-card">
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
                                <option value="<?= $v['id'] ?>" <?= $v['id'] == $venue_id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($v['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Court</label>
                        <select name="court_id" id="court_id" class="form-control" required>
                            <option value="">Select Court</option>
