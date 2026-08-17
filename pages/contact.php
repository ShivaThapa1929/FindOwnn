<?php
require_once __DIR__ . '/../includes/site-contact.php';
require_once __DIR__ . '/../includes/user-auth.php';
include __DIR__ . '/../includes/header.php';
?>

<!-- Page Header -->
<header class="page-header">
    <div class="glow-orb glow-orb-bottom-left"></div>
    <div class="container text-center position-relative z-1 animate-on-scroll">
        <span class="badge-premium mb-3">Get in Touch</span>
        <h1 class="display-3 fw-bold text-white">Contact Findownn</h1>
        <p class="text-secondary lead mx-auto" style="max-width: 600px;">
            Have questions about bookings, playground listings, or custom tournament listings? Drop us a message.
        </p>
    </div>
</header>

<!-- Contact Form & Details -->
<section class="py-5 position-relative">
    <div class="container">
        <div class="row g-5">
            
            <!-- Contact Info Sidebar -->
            <div class="col-lg-5 animate-on-scroll">
                
                <div class="mb-4">
                    <span class="badge-premium mb-3">Bhuj Headquarters</span>
                    <h2 class="text-white fw-bold mb-4">Contact Information</h2>
                </div>

                <!-- Info Card 1: Office -->
                <div class="glass-card p-4 mb-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="step-icon-box m-0" style="width: 45px; height: 45px; flex-shrink: 0;">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div>
                            <h5 class="text-white mb-2" style="font-size: 1.1rem;">Our Office</h5>
                            <p class="text-secondary mb-0" style="font-size: 0.95rem; line-height: 1.6;">
                                102 Elite Business Hub, Sanskar Nagar,<br>Bhuj, Gujarat - 370001
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Info Card 2: Phone/Email -->
                <div class="glass-card p-4 mb-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="step-icon-box m-0" style="width: 45px; height: 45px; flex-shrink: 0;">
                            <i class="bi bi-chat-left-text-fill"></i>
                        </div>
                        <div>
                            <h5 class="text-white mb-2" style="font-size: 1.1rem;">Direct Contact</h5>
                            <p class="text-secondary mb-1" style="font-size: 0.95rem;">
                                <strong class="text-white">Phone:</strong> <a href="tel:<?= e($site_phone_tel) ?>" class="text-secondary text-decoration-none"><?= e($site_whatsapp_display) ?></a>
                            </p>
                            <p class="text-secondary mb-1" style="font-size: 0.95rem;">
                                <strong class="text-white">WhatsApp:</strong>
                                <a href="<?= e($site_whatsapp_url) ?>" class="text-success text-decoration-none" target="_blank" rel="noopener">
                                    <i class="bi bi-whatsapp me-1"></i><?= e($site_whatsapp_display) ?>
                                </a>
                            </p>
                            <p class="text-secondary mb-0" style="font-size: 0.95rem;">
                                <strong class="text-white">Email:</strong> <a href="mailto:<?= e($site_contact_email) ?>" class="text-secondary text-decoration-none"><?= e($site_contact_email) ?></a>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Info Card 3: Support Hours -->
                <div class="glass-card p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="step-icon-box m-0" style="width: 45px; height: 45px; flex-shrink: 0;">
                            <i class="bi bi-clock-fill"></i>
                        </div>
                        <div>
                            <h5 class="text-white mb-2" style="font-size: 1.1rem;">Support Hours</h5>
                            <p class="text-secondary mb-1" style="font-size: 0.95rem;">
                                <strong class="text-white">Monday - Saturday:</strong> 9:00 AM - 8:00 PM
                            </p>
                            <p class="text-secondary mb-0" style="font-size: 0.95rem;">
                                <strong class="text-white">Sunday Support:</strong> 10:00 AM - 4:00 PM
                            </p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Form -->
            <div class="col-lg-7 animate-on-scroll delay-200">
                <div class="glass-card p-5">
                    
                    <h3 class="text-white fw-bold mb-4">Send Us a Message</h3>
                    
                    <div id="contactAlert" class="alert d-none small py-2"></div>

                    <form id="contactForm" class="row g-4" novalidate>
                        <?= site_csrf_field() ?>
                        
                        <!-- Name -->
                        <div class="col-md-6">
                            <label class="glass-input-label" for="contact-name">Your Name</label>
                            <input type="text" id="contact-name" name="name" class="form-control glass-input" placeholder="Rajesh Patel" required maxlength="120">
                        </div>

                        <!-- Email -->
                        <div class="col-md-6">
                            <label class="glass-input-label" for="contact-email">Email Address</label>
                            <input type="email" id="contact-email" name="email" class="form-control glass-input" placeholder="rajesh@example.com" required>
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6">
                            <label class="glass-input-label" for="contact-phone">Phone Number (Optional)</label>
                            <input type="tel" id="contact-phone" name="phone" class="form-control glass-input" placeholder="<?= e($site_whatsapp_display) ?>" pattern="[0-9]{10}" maxlength="10">
                        </div>

                        <!-- Subject -->
                        <div class="col-md-6">
                            <label class="glass-input-label" for="contact-subject">Subject</label>
                            <select id="contact-subject" name="subject" class="form-select glass-input" style="cursor: pointer;" required>
                                <option value="" disabled selected>Select an option</option>
                                <option value="General Inquiry">General Inquiry</option>
                                <option value="Booking Assistance">Booking Assistance</option>
                                <option value="Playground Listing">Playground Listing Inquiry</option>
                                <option value="Careers">Careers / Hiring</option>
                            </select>
                        </div>

                        <!-- Message -->
                        <div class="col-12">
                            <label class="glass-input-label" for="contact-message">Message Details</label>
                            <textarea id="contact-message" name="message" class="form-control glass-input" rows="5" placeholder="Tell us how we can help you..." required minlength="10"></textarea>
                        </div>

                        <!-- Submit -->
                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-premium w-100 py-3">Send Message <i class="bi bi-send-fill ms-2"></i></button>
                        </div>

                    </form>

                </div>
            </div>

        </div>
    </div>
</section>

<!-- Maps Section with Premium Dark Filter -->
<section class="py-5 animate-on-scroll">
    <div class="container">
        <div class="glass-card p-2 rounded-4 overflow-hidden position-relative" style="height: 400px; border-color: rgba(34, 197, 94, 0.15);">
            <!-- Custom CSS Filter is applied inline to invert google map into dark theme -->
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d58546.03606992523!2d69.62932265000002!3d23.2566782!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39511e2f38d4f40f%3A0xe54d3725b74100fa!2sBhuj%2C%20Gujarat!5e0!3m2!1sen!2sin!4v1718714000000!5m2!1sen!2sin" 
                width="100%" 
                height="100%" 
                style="border:0; filter: invert(90%) hue-rotate(180deg) brightness(95%) contrast(90%); border-radius: 12px;" 
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </div>
</section>

<?php include __DIR__ . '/../includes/footer.php'; ?>
