<?php
/** Real FindOwnn app UI — Bookings screen (matches Flutter BookingsScreen) */
/** @var string $asset_base Base path for assets — set by index.php / header.php */
if (!isset($asset_base)) {
    $script_dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $asset_base = ($script_dir === '') ? '/' : $script_dir . '/';
}
?>
<div class="findownn-app-screen findownn-app-screen--bookings" aria-hidden="true">
    <div class="fas-book-top">
        <h3>My booking</h3>
        <span class="fas-icon-btn"><i class="bi bi-sliders"></i></span>
    </div>

    <div class="fas-book-tabs">
        <span class="is-active">Upcoming</span>
        <span>Pending</span>
        <span>Past</span>
        <span>Cancelled</span>
    </div>

    <div class="fas-book-list">
        <article class="fas-book-card">
            <img src="<?= $asset_base ?>assets/images/venue-cricket.jpg" alt="" loading="lazy">
            <div class="fas-book-card-body">
                <div class="fas-book-card-top">
                    <div>
                        <strong>Tiki Taka Arena</strong>
                        <span>Aug 11, 2026 · 6:00 PM – 7:00 PM</span>
                    </div>
                    <em class="fas-status fas-status--confirmed">Confirmed</em>
                </div>
                <div class="fas-book-card-meta">
                    <span>Box Cricket</span>
                    <strong>₹800</strong>
                </div>
            </div>
        </article>

        <article class="fas-book-card">
            <img src="<?= $asset_base ?>assets/images/venue-pickleball.jpg" alt="" loading="lazy">
            <div class="fas-book-card-body">
                <div class="fas-book-card-top">
                    <div>
                        <strong>Green Turf Hub</strong>
                        <span>Aug 13, 2026 · 7:00 PM – 8:00 PM</span>
                    </div>
                    <em class="fas-status fas-status--confirmed">Confirmed</em>
                </div>
                <div class="fas-book-card-meta">
                    <span>Pickleball</span>
                    <strong>₹650</strong>
                </div>
            </div>
        </article>
    </div>

    <div class="fas-bottom-nav">
        <span><i class="bi bi-house-door"></i>Home</span>
        <span><i class="bi bi-building"></i>Venues</span>
        <span class="is-active"><i class="bi bi-calendar-check-fill"></i>Bookings</span>
        <span><i class="bi bi-trophy"></i>Rewards</span>
        <span><i class="bi bi-person"></i>Profile</span>
    </div>
</div>
