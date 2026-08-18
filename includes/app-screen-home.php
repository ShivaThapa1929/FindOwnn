<?php
/** FindOwnn app home — HTML mockup inside phone frame (inner images use object-fit) */
/** @var string $asset_base Base path for assets — set by index.php / header.php */
if (!isset($asset_base)) {
    $script_dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
    $asset_base = ($script_dir === '') ? '/' : $script_dir . '/';
}
?>
<div class="findownn-app-screen findownn-app-screen--home" aria-hidden="true">
    <div class="fas-header">
        <div class="fas-header-text">
            <div class="fas-greeting">Hello, <span>Shiva</span></div>
            <div class="fas-tagline">Find &amp; book your favorite sport.</div>
            <div class="fas-location"><i class="bi bi-geo-alt-fill"></i> bhuj, Gujarat <i class="bi bi-chevron-down"></i></div>
        </div>
        <div class="fas-header-actions">
            <span class="fas-icon-btn"><i class="bi bi-bell"></i></span>
            <span class="fas-avatar">S</span>
        </div>
    </div>

    <div class="fas-search-row">
        <div class="fas-search"><i class="bi bi-search"></i> Search venues, sports, locat...</div>
        <span class="fas-filter-btn"><i class="bi bi-sliders"></i></span>
    </div>

    <div class="fas-promo">
        <img src="<?= $asset_base ?>assets/images/venue-cricket.jpg" alt="" loading="eager">
        <div class="fas-promo-overlay">
            <span class="fas-promo-badge">Box Cricket Offer</span>
            <strong>Play More, Pay Less!</strong>
        </div>
    </div>

    <div class="fas-section-head">
        <span>Popular Sports</span>
        <small>View All &gt;</small>
    </div>

    <div class="fas-sport-grid">
        <span class="fas-sport-tile fas-sport-tile--active">
            <i class="bi bi-grid-fill"></i>
            All
        </span>
        <span class="fas-sport-tile">
            <i class="bi bi-circle-fill"></i>
            Box Cricket
        </span>
        <span class="fas-sport-tile">
            <i class="bi bi-circle-fill"></i>
            Pickleball
        </span>
    </div>

    <div class="fas-section-head">
        <span>Featured Venues</span>
        <small>View All &gt;</small>
    </div>

    <article class="fas-featured-venue">
        <div class="fas-featured-venue-media">
            <img src="<?= $asset_base ?>assets/images/venue-cricket.jpg" alt="" loading="eager">
            <span class="fas-featured-badge">Popular</span>
            <span class="fas-featured-rating"><i class="bi bi-star-fill"></i> 4.8</span>
        </div>
        <div class="fas-featured-venue-body">
            <strong>Tiki Taka Arena</strong>
            <span>Box Cricket · bhuj</span>
            <em>₹800/hr</em>
        </div>
    </article>

    <div class="fas-bottom-nav">
        <span class="is-active"><i class="bi bi-house-door-fill"></i>Home</span>
        <span><i class="bi bi-building"></i>Venues</span>
        <span><i class="bi bi-calendar-check"></i>Bookings</span>
        <span><i class="bi bi-trophy"></i>Rewards</span>
        <span><i class="bi bi-person"></i>Profile</span>
    </div>
</div>
