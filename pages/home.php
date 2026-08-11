<?php include 'includes/header.php'; ?>

<!-- HERO SECTION -->
<section class="hero-section home-section home-section--hero" id="hero">
    <div class="hero-mesh"></div>
    <div class="hero-grid-pattern"></div>
    <div class="floating-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <div class="shape shape-3"></div>
    </div>

    <div class="container position-relative z-1">
        <div class="row align-items-center min-vh-100">

            <!-- Hero Content -->
            <div class="col-lg-6 text-center text-lg-start">
                <div class="hero-content animate-on-scroll">
                    <span class="badge-premium badge-animated mb-4">
                        <span class="badge-dot"></span>
                        <i class="bi bi-star-fill"></i> Bhuj's #1 Sports Booking App
                    </span>

                    <h1 class="hero-title mb-4">
                        Your Game.<br>
                        <span class="text-green-gradient">Your Time.</span><br>
                        <span class="hero-subtitle">Booked in seconds.</span>
                    </h1>

                    <p class="hero-text mb-5">
                        Tired of WhatsApp chains and missed calls just to book a slot? Findownn lets you see real availability, pick your time, and confirm instantly — no back-and-forth needed.
                    </p>

                    <div class="hero-buttons mb-5">
                        <a href="#download-cta" class="btn btn-premium btn-shimmer">
                            <i class="bi bi-download"></i>
                            <span>Download Free</span>
                        </a>
                        <a href="venues" class="btn btn-premium-outline">
                            <i class="bi bi-search"></i> Explore Playgrounds
                        </a>
                    </div>

                    <div class="hero-badges">
                        <div class="trust-badge">
                            <i class="bi bi-shield-fill-check"></i>
                            <span>Verified Playgrounds</span>
                        </div>
                        <div class="trust-badge">
                            <i class="bi bi-lightning-charge-fill"></i>
                            <span>Instant Booking</span>
                        </div>
                        <div class="trust-badge">
                            <i class="bi bi-lock-fill"></i>
                            <span>Secure Payments</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- App Mockup — real FindOwnn home screen -->
            <div class="col-lg-6 text-center mockup-wrapper mt-4 mt-lg-0 animate-on-scroll delay-200">
                <div class="hero-phone-showcase">
                    <div class="hero-phone-glow" aria-hidden="true"></div>
                    <div class="phone-frame hero-phone-frame">
                        <div class="phone-notch"></div>
                        <div class="phone-screen hero-phone-screen">
                            <?php include __DIR__ . '/../includes/app-screen-home.php'; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="scroll-indicator">
        <div class="scroll-mouse"><div class="scroll-wheel"></div></div>
        <span class="scroll-text">Scroll to explore</span>
    </div>
</section>

<!-- STATS -->
<section class="stats-section">
    <div class="container">
        <div class="stats-container animate-on-scroll">
            <div class="row g-0 text-center">
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <div class="stat-icon"><i class="bi bi-geo-alt-fill"></i></div>
                        <div class="stat-number" data-target="15" data-suffix="+">0</div>
                        <p class="stat-label">Premium Playgrounds</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <div class="stat-icon"><i class="bi bi-people-fill"></i></div>
                        <div class="stat-number" data-target="10000" data-suffix="+">0</div>
                        <p class="stat-label">Happy Players</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <div class="stat-icon"><i class="bi bi-calendar-check-fill"></i></div>
                        <div class="stat-number" data-target="25000" data-suffix="+">0</div>
                        <p class="stat-label">Slots Booked</p>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-item">
                        <div class="stat-icon"><i class="bi bi-star-fill"></i></div>
                        <div class="stat-number" data-target="4.9">0</div>
                        <p class="stat-label">Avg. Rating</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- HOW IT WORKS -->
<section class="section-padding how-it-works-section" id="how-it-works">
    <div class="container">

        <div class="section-header animate-on-scroll">
            <span class="badge-premium mb-3">
                <i class="bi bi-lightbulb-fill"></i> How It Works
            </span>
            <h2 class="section-title mb-3">
                From couch to playground in <span class="text-green-gradient">3 steps</span>
            </h2>
            <p class="section-subtitle">No phone calls. No waiting. No double bookings. Just you and your game.</p>
        </div>

        <div class="steps-container">
            <div class="row g-4 position-relative">
                <div class="steps-connector d-none d-md-block"></div>

                <div class="col-md-4 animate-on-scroll">
                    <div class="step-card glass-card h-100">
                        <div class="step-number-circle">01</div>
                        <div class="step-icon-box"><i class="bi bi-search"></i></div>
                        <h4 class="step-title">Find Your Playground</h4>
                        <p class="step-text">Browse playgrounds near you and filter by sport, price, or rating. Every listing has real photos and honest reviews from actual players.</p>
                        <ul class="step-features">
                            <li><i class="bi bi-check-circle-fill"></i> Live availability, updated in real time</li>
                            <li><i class="bi bi-check-circle-fill"></i> Transparent pricing, zero hidden fees</li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-4 animate-on-scroll delay-150">
                    <div class="step-card glass-card h-100">
                        <div class="step-number-circle">02</div>
                        <div class="step-icon-box"><i class="bi bi-calendar-check"></i></div>
                        <h4 class="step-title">Pick Your Slot</h4>
                        <p class="step-text">Tap the time that works for you. Pay through UPI, cards, or wallets in seconds. Your slot is locked the moment payment clears.</p>
                        <ul class="step-features">
                            <li><i class="bi bi-check-circle-fill"></i> Instant confirmation, no waiting</li>
                            <li><i class="bi bi-check-circle-fill"></i> All major payment methods accepted</li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-4 animate-on-scroll delay-300">
                    <div class="step-card glass-card h-100">
                        <div class="step-number-circle">03</div>
                        <div class="step-icon-box"><i class="bi bi-trophy-fill"></i></div>
                        <h4 class="step-title">Show Up & Play</h4>
                        <p class="step-text">Walk in with your digital ticket. The court is yours. After your game, leave a rating to help others choose wisely.</p>
                        <ul class="step-features">
                            <li><i class="bi bi-check-circle-fill"></i> Digital ticket on your phone</li>
                            <li><i class="bi bi-check-circle-fill"></i> Zero paperwork at the playground</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- SPORTS -->
<section class="section-padding home-sports-section" id="sports">
    <div class="container">

        <div class="section-header section-header-split section-header-title-center animate-on-scroll">
            <h2 class="section-title mb-2">What do you play?</h2>
            <div class="section-header-split-row">
                <div>
                    <span class="badge-premium mb-3"><i class="bi bi-controller"></i> Sports</span>
                    <p class="section-subtitle mb-0">Live sports from Bhuj playgrounds — tap a sport to browse</p>
                </div>
                <div class="live-pulse-tag" aria-hidden="true">
                    <span class="live-pulse-dot"></span> Live in Bhuj
                </div>
            </div>
        </div>

        <div id="home-sports-container" class="home-sports-grid">
            <!-- Loading skeleton -->
            <div class="home-sports-skeleton">
                <div class="skeleton-sport-card"></div>
                <div class="skeleton-sport-card"></div>
                <div class="skeleton-sport-card"></div>
                <div class="skeleton-sport-card"></div>
            </div>
        </div>

    </div>
</section>

<!-- FEATURED PLAYGROUNDS -->
<section class="section-padding home-venues-section" id="venues">
    <div class="home-venues-glow"></div>
    <div class="container position-relative z-1">

        <div class="section-header section-header-split section-header-title-center animate-on-scroll mb-4 mb-md-5">
            <h2 class="section-title mb-2">Playgrounds people keep coming back to</h2>
            <div class="section-header-split-row">
                <div>
                    <span class="badge-premium mb-3"><i class="bi bi-patch-check-fill"></i> Top Rated</span>
                    <p class="section-subtitle mb-0">Hand-picked favourites — book in one tap</p>
                </div>
                <a href="venues" class="btn btn-premium-outline mt-3 mt-md-0 align-self-md-end">
                    View All <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <div id="featured-venues-container" class="home-featured-grid">
            <!-- Loading skeleton -->
            <div class="home-featured-skeleton">
                <div class="skeleton-sport-card"></div>
                <div class="skeleton-sport-card"></div>
                <div class="skeleton-sport-card"></div>
            </div>
        </div>

    </div>
</section>

<!-- WHY FINDOWNN -->
<section class="section-padding" id="features">
    <div class="container">

        <div class="section-header animate-on-scroll">
            <span class="badge-premium mb-3"><i class="bi bi-stars"></i> Why Findownn</span>
            <h2 class="section-title mb-3">Everything that used to be annoying — fixed</h2>
            <p class="section-subtitle">We built the booking experience we always wished existed</p>
        </div>

        <div class="row g-3 g-lg-4">
            <div class="col-12 col-sm-6 col-lg-4 animate-on-scroll">
                <div class="glass-card feature-card h-100">
                    <div class="feature-icon"><i class="bi bi-lightning-charge-fill"></i></div>
                    <h4 class="feature-title">Book in 30 seconds</h4>
                    <p class="feature-text">No callbacks. No WhatsApp chains. Just pick a slot and you're done.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 animate-on-scroll delay-100">
                <div class="glass-card feature-card h-100">
                    <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                    <h4 class="feature-title">Every playground verified</h4>
                    <p class="feature-text">Our team personally visits each playground before it goes live on the app.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 animate-on-scroll delay-200">
                <div class="glass-card feature-card h-100">
                    <div class="feature-icon"><i class="bi bi-clock-history"></i></div>
                    <h4 class="feature-title">Always up to date</h4>
                    <p class="feature-text">Slots refresh live — if it shows available, it's available. No surprises.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 animate-on-scroll">
                <div class="glass-card feature-card h-100">
                    <div class="feature-icon"><i class="bi bi-wallet2"></i></div>
                    <h4 class="feature-title">Split with teammates</h4>
                    <p class="feature-text">Pay via UPI, cards, or wallets — and easily split the cost with your squad.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 animate-on-scroll delay-100">
                <div class="glass-card feature-card h-100">
                    <div class="feature-icon"><i class="bi bi-phone-vibrate"></i></div>
                    <h4 class="feature-title">Built for your phone</h4>
                    <p class="feature-text">The app is fast, light, and works on any network — even 4G in a pinch.</p>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-4 animate-on-scroll delay-200">
                <div class="glass-card feature-card h-100">
                    <div class="feature-icon"><i class="bi bi-people-fill"></i></div>
                    <h4 class="feature-title">Find players too</h4>
                    <p class="feature-text">Short a player? Connect with locals, join open sessions, build your crew.</p>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- APP SHOWCASE -->
<section class="section-padding bg-subtle home-section home-section--app" id="app">
    <div class="container">
        <div class="row align-items-center g-5">

            <div class="col-lg-6 animate-on-scroll order-2 order-lg-1 slide-left">
                <span class="badge-premium mb-3"><i class="bi bi-phone-fill"></i> Mobile App</span>
                <h2 class="section-title mb-4">Book from anywhere, anytime</h2>
                <p class="section-text mb-4">
                    Doesn't matter if you're planning a game a week ahead or booking one for tonight — the Findownn app makes it just as easy either way.
                </p>

                <div class="app-features">
                    <div class="app-feature-item">
                        <div class="app-feature-icon"><i class="bi bi-bell-fill"></i></div>
                        <div>
                            <h6 class="app-feature-title">Smart reminders</h6>
                            <p class="app-feature-text">Get a heads-up before your game so you're never late to the court</p>
                        </div>
                    </div>
                    <div class="app-feature-item">
                        <div class="app-feature-icon"><i class="bi bi-people-fill"></i></div>
                        <div>
                            <h6 class="app-feature-title">Find teammates nearby</h6>
                            <p class="app-feature-text">Connect with players in your area and fill those last open spots</p>
                        </div>
                    </div>
                    <div class="app-feature-item">
                        <div class="app-feature-icon"><i class="bi bi-wallet2"></i></div>
                        <div>
                            <h6 class="app-feature-title">Easy bill splitting</h6>
                            <p class="app-feature-text">Share the booking cost with your friends in one tap</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 text-center animate-on-scroll delay-200 order-1 order-lg-2 slide-right d-none d-lg-block">
                <div class="phone-frame">
                    <div class="phone-notch"></div>
                    <div class="phone-screen">
                        <?php include __DIR__ . '/../includes/app-screen-bookings.php'; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- TESTIMONIALS -->
<section class="section-padding" id="testimonials">
    <div class="container">

        <div class="section-header animate-on-scroll">
            <span class="badge-premium mb-3"><i class="bi bi-chat-quote-fill"></i> Real Players</span>
            <h2 class="section-title mb-3">What the Bhuj community is saying</h2>
            <p class="section-subtitle">Don't take our word for it — here's what players across the city think</p>
        </div>

        <div class="testimonial-slider" id="testimonialSlider">
            <button type="button" class="testimonial-arrow testimonial-arrow-prev" aria-label="Previous review">
                <i class="bi bi-chevron-left"></i>
            </button>

            <div class="testimonial-viewport">
                <div class="testimonial-track" id="testimonialTrack">

                    <div class="testimonial-page">
                    <div class="testimonial-slide">
                        <div class="glass-card testimonial-card h-100">
                            <div class="testimonial-stars">
                                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                            </div>
                            <p class="testimonial-text">"Used to spend 20 minutes texting back and forth with playground owners. Now I book in under a minute. I don't know how I managed without this app."</p>
                            <div class="testimonial-author">
                                <div class="author-avatar">AR</div>
                                <div>
                                    <div class="author-name">Arjun Raval</div>
                                    <div class="author-desc">Box Cricket player · Bhuj</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="testimonial-slide">
                        <div class="glass-card testimonial-card h-100">
                            <div class="testimonial-stars">
                                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                            </div>
                            <p class="testimonial-text">"Pickleball is still new in Bhuj and finding good courts was such a headache. Findownn has all the verified spots in one place. Game changer."</p>
                            <div class="testimonial-author">
                                <div class="author-avatar">PD</div>
                                <div>
                                    <div class="author-name">Priya Desai</div>
                                    <div class="author-desc">Pickleball enthusiast · Bhuj</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="testimonial-slide">
                        <div class="glass-card testimonial-card h-100">
                            <div class="testimonial-stars">
                                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-half"></i>
                            </div>
                            <p class="testimonial-text">"Our whole group of 8 uses it. The split payment feature alone saved us from that awkward 'who pays what' situation every week."</p>
                            <div class="testimonial-author">
                                <div class="author-avatar">MK</div>
                                <div>
                                    <div class="author-name">Mihir Kothari</div>
                                    <div class="author-desc">Weekend cricketer · Bhuj</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>

                    <div class="testimonial-page">
                    <div class="testimonial-slide">
                        <div class="glass-card testimonial-card h-100">
                            <div class="testimonial-stars">
                                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                            </div>
                            <p class="testimonial-text">"Booked a 7 PM slot at 6:45 and was playing by 7:05. No calls, no confusion. This is how booking should always work."</p>
                            <div class="testimonial-author">
                                <div class="author-avatar">KS</div>
                                <div>
                                    <div class="author-name">Karan Shah</div>
                                    <div class="author-desc">Football player · Bhuj</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="testimonial-slide">
                        <div class="glass-card testimonial-card h-100">
                            <div class="testimonial-stars">
                                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i>
                            </div>
                            <p class="testimonial-text">"Badminton courts in Bhuj are always full on weekends. Findownn shows real-time availability — I booked Friday night slots for my whole group in seconds."</p>
                            <div class="testimonial-author">
                                <div class="author-avatar">NM</div>
                                <div>
                                    <div class="author-name">Neha Mehta</div>
                                    <div class="author-desc">Badminton player · Bhuj</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="testimonial-slide">
                        <div class="glass-card testimonial-card h-100">
                            <div class="testimonial-stars">
                                <i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-half"></i>
                            </div>
                            <p class="testimonial-text">"Listed my playground on Findownn and started getting bookings within a week. The dashboard makes it easy to manage slots without phone calls all day."</p>
                            <div class="testimonial-author">
                                <div class="author-avatar">RV</div>
                                <div>
                                    <div class="author-name">Rahul Vora</div>
                                    <div class="author-desc">Playground owner · Bhuj</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>

                </div>
            </div>

            <button type="button" class="testimonial-arrow testimonial-arrow-next" aria-label="Next review">
                <i class="bi bi-chevron-right"></i>
            </button>

            <div class="testimonial-dots" id="testimonialDots" role="tablist" aria-label="Testimonial slides"></div>
            <p class="testimonial-autoplay-label" aria-hidden="true">
                <span class="live-pulse-dot"></span> Auto-playing reviews
            </p>
        </div>

    </div>
</section>

<!-- PARTNER CTA -->
<section class="section-padding bg-subtle">
    <div class="container">
        <div class="partner-cta-card animate-on-scroll">
            <div class="row align-items-center g-4">
                <div class="col-lg-8 text-center text-lg-start position-relative z-1">
                    <span class="badge-premium mb-3"><i class="bi bi-building"></i> For Playground Owners</span>
                    <h2 class="section-title mb-3">Got a sports playground in Bhuj?</h2>
                    <p class="section-text mb-0">
                        Fill your empty slots, ditch the manual scheduling, and grow your bookings — we handle the tech side so you can focus on running a great playground.
                    </p>
                </div>
                <div class="col-lg-4 text-center text-lg-end position-relative z-1">
                    <a href="partner" class="btn btn-premium btn-lg">
                        List Your Playground <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ -->
<section class="section-padding" id="faq">
    <div class="container">

        <div class="section-header animate-on-scroll">
            <span class="badge-premium mb-3"><i class="bi bi-question-circle-fill"></i> FAQ</span>
            <h2 class="section-title mb-3">Got questions? We've got you.</h2>
            <p class="section-subtitle">The things people ask us most — answered honestly</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8 animate-on-scroll">
                <div class="accordion" id="faqAccordion">

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How does Findownn work?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Simple: download the app, search for a sport, pick a playground, choose your time, and pay. That's it. We handle the confirmation and send you a digital ticket. No calls, no waiting.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                What if I need to cancel?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Plans change — we get it. Cancellation and refund policies vary by playground, and you'll see the exact policy before you confirm any booking. No nasty surprises.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Is it safe to pay on the app?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes. We use industry-standard payment gateways with bank-level encryption. You can pay with UPI (GPay, PhonePe), debit/credit cards, or digital wallets — all fully secured.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                Which sports can I book right now?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Box Cricket and Pickleball are live across multiple playgrounds in Bhuj. Football and Badminton are on the way — follow us on Instagram for launch updates!
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                I own a playground — how do I list it?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Head over to our <a href="partner" class="text-success fw-semibold">List your playground page</a> and fill in a quick form. We'll reach out within 24 hours to get your playground verified and live on the app.
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</section>

<!-- FINAL CTA -->
<section class="cta-section" id="download-cta">
    <div class="container">
        <div class="cta-block text-center animate-on-scroll scale-in">
            <span class="badge-premium mb-4"><i class="bi bi-rocket-takeoff-fill"></i> Get Started</span>
            <h2 class="cta-title mb-4">Ready to stop waiting and start playing?</h2>
            <p class="cta-text mb-5">
                Download the app and book your first slot in under a minute. It's free to download, and your first game is waiting.
            </p>
            <div class="cta-buttons">
                <a href="#" class="btn btn-premium btn-lg btn-shimmer" onclick="alert('App coming soon on Play Store! Stay tuned.'); return false;">
                    <i class="bi bi-google-play"></i> Get on Android
                </a>
                <a href="#" class="btn btn-premium-outline btn-lg" onclick="alert('App coming soon on App Store! Stay tuned.'); return false;">
                    <i class="bi bi-apple"></i> Get on iOS
                </a>
            </div>
            <p class="mt-4" style="font-size:0.82rem;color:var(--text-muted);">
                <i class="bi bi-star-fill text-warning me-1" style="font-size:0.75rem;"></i> Rated 4.9 by 10,000+ players in Bhuj
            </p>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
