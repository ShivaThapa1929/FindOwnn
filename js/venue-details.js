/**
 * Findownn Venue Details Page - 100% Dynamic Database Integration
 * All images, courts, amenities, location, reviews & multiple slots fetched strictly from MySQL DB
 */

document.addEventListener('DOMContentLoaded', async () => {
    
    const urlParams = new URLSearchParams(window.location.search);
    const venueId = urlParams.get('id') || '1';
    let selectedSlots = []; // Array for multiple slot selection
    let currentVenueData = null;
    
    // Set date input to today by default
    const dateInput = document.getElementById('booking-date');
    const todayStr = new Date().toISOString().split('T')[0];
    if (dateInput) {
        dateInput.value = todayStr;
        dateInput.min = todayStr;
    }
    
    // ==================== LOAD VENUE DATA FROM DB ====================
    async function loadVenueDetails() {
        showLoading();
        
        try {
            const response = await FindownnAPI.getVenue(venueId);
            
            if (response.success && response.data) {
                currentVenueData = response.data;
                renderVenueDetails(response.data);
                await loadVenueImages(venueId);
                await loadVenueReviews(venueId);
                await loadVenueAvailability(venueId, dateInput?.value || todayStr);
            } else {
                showError('Playground not found in database.');
            }
        } catch (error) {
            console.error('Error loading venue:', error);
            const msg = window.FindownnUI
                ? FindownnUI.friendlyApiMessage(error)
                : "We're unavailable right now. Please try again in a few minutes.";
            showError(msg);
        } finally {
            hideLoading();
        }
    }
    
    // ==================== RENDER DYNAMIC VENUE DETAILS ====================
    function renderVenueDetails(venue) {
        document.title = `${venue.name} - Findownn`;
        
        // Venue Name
        const nameElement = document.getElementById('venue-name');
        if (nameElement) nameElement.textContent = venue.name || 'Playground';
        
        // Verified Badge
        const badgeElem = document.getElementById('venue-verified-badge');
        if (badgeElem) {
            badgeElem.innerHTML = venue.is_verified
                ? `<span class="badge bg-success p-2 px-3"><i class="bi bi-patch-check-fill me-1"></i>Verified Playground</span>`
                : `<span class="badge bg-secondary p-2 px-3"><i class="bi bi-info-circle me-1"></i>Standard Playground</span>`;
        }
        
        // Rating & Reviews Count
        const ratingElement = document.getElementById('venue-rating');
        if (ratingElement) {
            const r = parseFloat(venue.rating || 0).toFixed(1);
            ratingElement.innerHTML = `
                <i class="bi bi-star-fill text-warning"></i> <span>${r}</span>
                <span class="text-muted ms-1">(${venue.total_reviews || 0} reviews)</span>
            `;
        }
        
        // Location (Address & City from DB)
        const locationElement = document.getElementById('venue-location');
        if (locationElement) {
            const locParts = [];
            if (venue.address) locParts.push(venue.address);
            if (venue.city) locParts.push(venue.city);
            const locStr = locParts.length > 0 ? locParts.join(', ') : 'Location not specified';
            locationElement.innerHTML = `<i class="bi bi-geo-alt-fill text-success"></i> ${locStr}`;
        }
        
        // Operating Hours
        const timingElement = document.getElementById('venue-timing');
        if (timingElement) {
            const open = venue.opening_time ? format12h(venue.opening_time) : '6:00 AM';
            const close = venue.closing_time ? format12h(venue.closing_time) : '11:00 PM';
            timingElement.innerHTML = `<i class="bi bi-clock-fill me-1"></i>Open: ${open} - ${close}`;
        }
        
        // Sports Tags (from venue_sports join in DB)
        const sportsContainer = document.getElementById('venue-sports-tags');
        if (sportsContainer) {
            const sportsList = Array.isArray(venue.sports) ? venue.sports : [];
            if (sportsList.length > 0) {
                sportsContainer.innerHTML = sportsList.map(s => {
                    const name = typeof s === 'string' ? s : (s.name || 'Sport');
                    return `<span class="badge rounded-pill bg-dark border border-success text-success px-3 py-2 fs-6"><i class="bi bi-trophy-fill me-1"></i>${name}</span>`;
                }).join('');
            } else {
                sportsContainer.innerHTML = `<span class="badge rounded-pill bg-dark border border-secondary text-muted px-3 py-2 fs-6">Multi-Sport</span>`;
            }
        }
        
        // Price per hour from DB
        const priceElement = document.getElementById('venue-price');
        if (priceElement) {
            const priceVal = parseInt(venue.price_per_hour || 0);
            priceElement.textContent = priceVal > 0 ? `₹${priceVal.toLocaleString('en-IN')}/hr` : 'Contact for Price';
        }
        
        // Description from DB
        const descElement = document.getElementById('venue-description');
        if (descElement) {
            descElement.textContent = venue.description || 'No description provided for this playground.';
        }
        
        // Full Address & Map Link from DB
        const addrElement = document.getElementById('venue-full-address');
        if (addrElement) {
            const addrArr = [];
            if (venue.address) addrArr.push(venue.address);
            if (venue.city) addrArr.push(venue.city);
            if (venue.state) addrArr.push(venue.state);
            if (venue.pincode) addrArr.push(venue.pincode);
            addrElement.textContent = addrArr.length > 0 ? addrArr.join(', ') : 'Full address not specified.';
        }
        
        const mapLink = document.getElementById('venue-map-link');
        if (mapLink) {
            if (venue.google_map_link) {
                mapLink.href = venue.google_map_link;
                mapLink.style.display = 'inline-flex';
            } else {
                mapLink.href = `https://maps.google.com/?q=${encodeURIComponent((venue.name || '') + ' ' + (venue.city || ''))}`;
                mapLink.style.display = 'inline-flex';
            }
        }
        
        // Courts list from DB
        renderCourts(venue.courts || []);
        
        // Amenities list from DB
        renderAmenities(venue);
        
        // Contact info from DB
        const phone = venue.contact_phone || venue.owner?.phone || '';
        const email = venue.contact_email || '';
        
        const phoneElem = document.getElementById('venue-phone');
        if (phoneElem) {
            if (phone) {
                phoneElem.querySelector('span').textContent = phone;
                phoneElem.href = `tel:${phone.replace(/\s+/g, '')}`;
                phoneElem.style.display = 'inline-flex';
            } else {
                phoneElem.style.display = 'none';
            }
        }
        
        const emailElem = document.getElementById('venue-email');
        if (emailElem) {
            if (email) {
                emailElem.querySelector('span').textContent = email;
                emailElem.href = `mailto:${email}`;
                emailElem.style.display = 'inline-flex';
            } else {
                emailElem.style.display = 'none';
            }
        }
        
        loadWhatsAppLink(venueId);
    }
    
    // ==================== RENDER COURTS FROM DB ====================
    function renderCourts(courts) {
        const container = document.getElementById('venue-courts');
        const badge = document.getElementById('courts-count-badge');
        if (!container) return;
        
        if (badge) badge.textContent = `${courts.length} Court${courts.length === 1 ? '' : 's'} Available`;
        
        if (!courts || courts.length === 0) {
            container.innerHTML = `
                <div class="col-12 text-center py-3">
                    <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i>No courts configured for this playground yet.</p>
                </div>
            `;
            return;
        }
        
        container.innerHTML = courts.map((c, idx) => {
            const courtImg = FindownnAPI.resolveImageUrl(c.image_url || c.featured_image);
            const imgHtml = courtImg ? `
                <div class="court-card-img-wrapper mb-2 rounded-3 overflow-hidden" style="height:120px; position:relative; background:#0d1510;">
                    <img src="${courtImg}" alt="${c.name || 'Court'}" style="width:100%; height:100%; object-fit:cover;" onerror="this.parentElement.style.display='none';">
                </div>
            ` : '';
            return `
            <div class="col-md-6">
                <div class="court-card h-100" data-court-index="${idx}">
                    ${imgHtml}
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="text-white fw-bold mb-0">${c.name || 'Court'}</h6>
                        <span class="badge bg-success">₹${parseInt(c.price_per_hour || 0).toLocaleString('en-IN')}/hr</span>
                    </div>
                    <div class="text-secondary small mb-2">
                        <span class="me-2"><i class="bi bi-layers text-success me-1"></i>${c.surface_type || 'Standard Surface'}</span>
                        ${c.capacity ? `<span><i class="bi bi-people text-success me-1"></i>Cap: ${c.capacity} players</span>` : ''}
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex gap-2">
                            ${c.is_indoor ? '<span class="badge bg-dark text-info border border-info">Indoor</span>' : '<span class="badge bg-dark text-warning border border-warning">Outdoor</span>'}
                            ${c.has_lighting ? '<span class="badge bg-dark text-success border border-success"><i class="bi bi-lightbulb-fill me-1"></i>Lighting</span>' : ''}
                        </div>
                        <small class="text-success fw-semibold" style="font-size:0.75rem;"><i class="bi bi-eye me-1"></i>View Details</small>
                    </div>
                </div>
            </div>
        `; }).join('');

        // Bind Court Details Click Event
        container.querySelectorAll('.court-card').forEach(card => {
            card.addEventListener('click', () => {
                const idx = parseInt(card.getAttribute('data-court-index'));
                const court = courts[idx];
                if (!court) return;

                document.getElementById('court-modal-title').textContent = court.name || 'Court Details';
                document.getElementById('court-modal-number').textContent = `Court #${court.court_number || (idx + 1)}`;
                document.getElementById('court-modal-price').textContent = `₹${parseInt(court.price_per_hour || 0).toLocaleString('en-IN')}/hr`;
                document.getElementById('court-modal-desc').textContent = court.description && court.description.trim() ? court.description : 'Professional court equipped with all modern amenities.';
                document.getElementById('court-modal-surface').textContent = court.surface_type || 'Artificial Turf';
                document.getElementById('court-modal-capacity').textContent = `${court.capacity || 14} Players`;
                document.getElementById('court-modal-lighting').textContent = court.has_lighting ? 'LED Floodlights' : 'Daylight Only';

                const imgContainer = document.getElementById('court-modal-img-container');
                const modalImg = document.getElementById('court-modal-img');
                const cImg = FindownnAPI.resolveImageUrl(court.image_url || court.featured_image);

                if (cImg && imgContainer && modalImg) {
                    modalImg.src = cImg;
                    imgContainer.style.display = 'block';
                } else if (imgContainer) {
                    imgContainer.style.display = 'none';
                }

                const modalEl = document.getElementById('courtDetailModal');
                if (modalEl) {
                    const modal = new bootstrap.Modal(modalEl);
                    modal.show();
                }
            });
        });
    }
    
    // ==================== RENDER AMENITIES FROM DB ====================
    function renderAmenities(venue) {
        const container = document.getElementById('venue-amenities');
        if (!container) return;
        
        const raw = Array.isArray(venue.amenities) ? venue.amenities : [];
        
        const amenityIconMap = {
            'floodlight': 'lightbulb-fill',
            'lighting': 'lightbulb-fill',
            'parking': 'p-circle-fill',
            'water': 'droplet-fill',
            'restroom': 'door-closed-fill',
            'washroom': 'door-closed-fill',
            'toilet': 'door-closed-fill',
            'changing': 'house-door-fill',
            'locker': 'house-door-fill',
            'first aid': 'heart-pulse-fill',
            'cafeteria': 'cup-hot-fill',
            'wifi': 'wifi'
        };

        if (raw.length === 0) {
            // Check boolean flags on venues row
            const list = [];
            if (venue.has_floodlights) list.push({ icon: 'lightbulb-fill', text: 'LED Floodlights' });
            if (venue.has_parking)     list.push({ icon: 'p-circle-fill', text: 'Parking' });
            if (venue.has_water)       list.push({ icon: 'droplet-fill', text: 'Drinking Water' });
            if (venue.has_restroom)    list.push({ icon: 'door-closed-fill', text: 'Washroom' });
            if (venue.has_changing_room) list.push({ icon: 'house-door-fill', text: 'Changing Room' });
            if (venue.has_first_aid)   list.push({ icon: 'heart-pulse-fill', text: 'First Aid' });

            if (list.length === 0) {
                container.innerHTML = `<p class="text-muted small mb-0"><i class="bi bi-info-circle me-1"></i>No specific amenities listed for this playground.</p>`;
                return;
            }

            container.innerHTML = list.map(a => `
                <div class="amenity-item">
                    <i class="bi bi-${a.icon}"></i>
                    <span>${a.text}</span>
                </div>
            `).join('');
            return;
        }

        container.innerHTML = raw.map(a => {
            const key = Object.keys(amenityIconMap).find(k => a.toLowerCase().includes(k));
            const icon = key ? amenityIconMap[key] : 'check-circle-fill';
            return `
                <div class="amenity-item">
                    <i class="bi bi-${icon}"></i>
                    <span>${a}</span>
                </div>
            `;
        }).join('');
    }
    
    // ==================== LOAD VENUE IMAGES FROM DB ====================
    async function loadVenueImages(venueId) {
        const galleryContainer = document.getElementById('venue-gallery');
        if (!galleryContainer) return;
        
        try {
            const response = await FindownnAPI.getVenueImages(venueId);
            const imageList = response?.data?.images || response?.data || [];
            renderImageGallery(imageList);
        } catch (error) {
            console.error('Error loading images:', error);
            renderImageGallery([]);
        }
    }
    
    function getAssetBase() {
        return FindownnAPI.getSiteBase();
    }

    const VENUE_IMAGE_FALLBACK = FindownnAPI.resolveImageUrl('assets/images/venue1.jpg');

    function renderImageGallery(images) {
        const galleryContainer = document.getElementById('venue-gallery');
        if (!galleryContainer) return;
        
        // If NO images exist in database, render placeholder UI card instead of fake pictures
        if (!images || images.length === 0) {
            galleryContainer.className = 'glass-card p-4 text-center mb-4 border-glass';
            galleryContainer.innerHTML = `
                <div class="py-4">
                    <i class="bi bi-image text-muted display-4 opacity-50 d-block mb-2"></i>
                    <h6 class="text-white fw-bold mb-1">No Gallery Photos Uploaded</h6>
                    <p class="text-muted small mb-0">Photos for this playground will be uploaded by the playground owner.</p>
                </div>
            `;
            return;
        }
        
        const displayImages = images.slice(0, 3);
        galleryContainer.className = 'gallery-grid mb-4';
        galleryContainer.innerHTML = displayImages.map((img, idx) => {
            const src = FindownnAPI.resolveImageUrl(img.url || img.image_path);
            return `
            <div class="gallery-item ${idx === 0 ? 'main' : ''}">
                <img src="${src}" alt="${img.caption || 'Playground Image'}" loading="lazy" onerror="this.onerror=null;this.src='${VENUE_IMAGE_FALLBACK}'">
            </div>`;
        }).join('');
    }
    
    // ==================== LOAD AVAILABILITY & OCCUPANCY FROM DB ====================
    async function loadVenueAvailability(venueId, date) {
        const availabilityContainer = document.getElementById('venue-availability');
        if (!availabilityContainer) return;
        
        availabilityContainer.innerHTML = `<p class="text-muted small">Checking slot availability...</p>`;
        selectedSlots = [];
        updateBookingButton();
        
        try {
            const response = await FindownnAPI.getVenueAvailability(venueId, date);
            
            if (response.success && response.data) {
                const slots = response.data.slots || [];
                const summary = response.data.summary || {};
                
                renderOccupancyBanner(summary, slots);
                renderSlots(slots);
            }
        } catch (error) {
            console.error('Error loading availability:', error);
            availabilityContainer.innerHTML = `<p class="text-danger small">Failed to load slots. Please refresh.</p>`;
        }
    }
    
    function renderOccupancyBanner(summary, slots) {
        const total = summary.total_slots || slots.length || 0;
        const booked = summary.booked_slots || slots.filter(s => s.is_booked).length || 0;
        const available = summary.available_slots || (total - booked);
        const percent = summary.occupancy_percentage !== undefined ? summary.occupancy_percentage : (total > 0 ? Math.round((booked / total) * 100) : 0);
        
        const percentElem = document.getElementById('occupancy-percentage');
        const fillElem = document.getElementById('occupancy-bar-fill');
        const totalElem = document.getElementById('stat-total-slots');
        const bookedElem = document.getElementById('stat-booked-slots');
        const availElem = document.getElementById('stat-available-slots');
        
        if (percentElem) percentElem.textContent = `${percent}% Occupied`;
        if (fillElem) fillElem.style.width = `${percent}%`;
        if (totalElem) totalElem.textContent = total;
        if (bookedElem) bookedElem.textContent = booked;
        if (availElem) availElem.textContent = available;
    }
    
    function renderSlots(slots) {
        const container = document.getElementById('venue-availability');
        if (!container) return;
        
        if (!slots || slots.length === 0) {
            container.innerHTML = `<p class="text-muted small">No slots configured for this date.</p>`;
            return;
        }
        
        container.innerHTML = slots.map(slot => {
            const isAvailable = slot.is_available && !slot.is_booked;
            const label = slot.start_label || format12h(slot.start_time);
            
            return `
                <button class="time-slot ${isAvailable ? '' : 'disabled'}"
                        ${isAvailable ? '' : 'disabled'}
                        data-start="${slot.start_time}"
                        data-end="${slot.end_time}"
                        data-label="${label}"
                        data-price="${slot.price || 1000}">
                    ${label}
                    <div style="font-size:0.68rem; opacity:0.8;">${isAvailable ? `₹${slot.price || 1000}` : 'Booked'}</div>
                </button>
            `;
        }).join('');
        
        // MULTIPLE slot selection handler
        container.querySelectorAll('.time-slot:not(.disabled)').forEach(btn => {
            btn.addEventListener('click', () => {
                const startTime = btn.getAttribute('data-start');
                const endTime = btn.getAttribute('data-end');
                const label = btn.getAttribute('data-label');
                const price = parseInt(btn.getAttribute('data-price') || '1000');
                
                const existingIdx = selectedSlots.findIndex(s => s.start_time === startTime);
                
                if (existingIdx !== -1) {
                    selectedSlots.splice(existingIdx, 1);
                    btn.classList.remove('selected');
                } else {
                    selectedSlots.push({ start_time: startTime, end_time: endTime, label, price });
                    btn.classList.add('selected');
                }
                
                selectedSlots.sort((a, b) => a.start_time.localeCompare(b.start_time));
                updateBookingButton();
            });
        });
    }
    
    function updateBookingButton() {
        const btn = document.getElementById('book-slot-btn');
        if (!btn) return;
        
        const count = selectedSlots.length;
        if (count > 0) {
            const totalPrice = selectedSlots.reduce((sum, s) => sum + s.price, 0);
            btn.disabled = false;
            btn.innerHTML = `<i class="bi bi-phone-vibrate me-2"></i>Book ${count} Slot${count > 1 ? 's' : ''} (₹${totalPrice.toLocaleString('en-IN')})`;
        } else {
            btn.disabled = true;
            btn.innerHTML = `<i class="bi bi-calendar-check me-2"></i>Select Slot(s) to Book`;
        }
    }

    // Book slot click handler -> Open App Download Modal
    const bookBtn = document.getElementById('book-slot-btn');
    if (bookBtn) {
        bookBtn.addEventListener('click', () => {
            if (selectedSlots.length === 0) return;

            const totalPrice = selectedSlots.reduce((sum, s) => sum + s.price, 0);
            const slotTimes = selectedSlots.map(s => s.label).join(', ');

            // Populate Modal Fields
            const modalTitle = document.getElementById('modal-venue-title');
            const modalDate  = document.getElementById('modal-slot-date');
            const modalTime  = document.getElementById('modal-slot-time');
            const modalPrice = document.getElementById('modal-slot-price');

            if (modalTitle) modalTitle.textContent = currentVenueData?.name || 'Playground';
            if (modalDate)  modalDate.textContent = dateInput?.value || todayStr;
            if (modalTime)  modalTime.textContent = `${slotTimes} (${selectedSlots.length} Hr${selectedSlots.length > 1 ? 's' : ''})`;
            if (modalPrice) modalPrice.textContent = `₹${totalPrice.toLocaleString('en-IN')}`;

            // Show Bootstrap Modal
            const modalEl = document.getElementById('appDownloadModal');
            if (modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        });
    }
    
    // ==================== LOAD REVIEWS FROM DB ====================
    async function loadVenueReviews(venueId) {
        const container = document.getElementById('venue-reviews');
        const summaryBadge = document.getElementById('reviews-summary-badge');
        if (!container) return;
        
        try {
            const response = await FindownnAPI.getVenueReviews(venueId);
            const reviews = response?.data?.reviews || [];
            const summary = response?.data?.summary || {};
            
            if (summaryBadge) {
                const avg = parseFloat(summary.average_rating || 0).toFixed(1);
                summaryBadge.innerHTML = `<i class="bi bi-star-fill"></i> ${avg} / 5.0 (${summary.total_reviews || reviews.length} reviews)`;
            }
            
            if (reviews.length === 0) {
                container.innerHTML = `<p class="text-muted small mb-0"><i class="bi bi-chat-left-text me-1"></i>No reviews submitted for this playground yet.</p>`;
                return;
            }
            
            container.innerHTML = reviews.map(r => `
                <div class="review-card">
                    <div class="review-header">
                        <div class="review-avatar">${(r.user_name || 'Player').charAt(0).toUpperCase()}</div>
                        <div>
                            <h6 class="review-author text-white mb-0" style="font-size:0.95rem;">${r.user_name || 'Player'}</h6>
                            <div class="review-rating text-warning" style="font-size:0.75rem;">${generateStars(r.rating || 5)}</div>
                        </div>
                        <span class="review-date ms-auto text-muted small">${formatDate(r.created_at)}</span>
                    </div>
                    <p class="review-text text-secondary mb-0" style="font-size:0.88rem; line-height:1.6;">${r.comment || r.review || ''}</p>
                </div>
            `).join('');
        } catch (error) {
            console.error('Error loading reviews:', error);
            container.innerHTML = `<p class="text-muted small mb-0">No reviews available.</p>`;
        }
    }
    
    // ==================== WHATSAPP LINK FROM DB ====================
    async function loadWhatsAppLink(venueId) {
        try {
            const response = await FindownnAPI.getVenueWhatsApp(venueId);
            const whatsappBtn = document.getElementById('whatsapp-btn');
            
            if (response.success && response.data?.whatsapp_link && whatsappBtn) {
                whatsappBtn.href = response.data.whatsapp_link;
                whatsappBtn.style.display = 'inline-flex';
            }
        } catch (error) {
            console.error('Error loading WhatsApp link:', error);
        }
    }
    
    // ==================== DATE PICKER LISTENERS ====================
    if (dateInput) {
        dateInput.addEventListener('change', () => {
            loadVenueAvailability(venueId, dateInput.value);
        });
    }
    
    // Helper function: Format 24h to 12h
    function format12h(timeStr) {
        if (!timeStr) return '';
        const parts = timeStr.split(':');
        let hour = parseInt(parts[0]);
        const min = parts[1] || '00';
        const ampm = hour >= 12 ? 'PM' : 'AM';
        hour = hour % 12;
        if (hour === 0) hour = 12;
        return `${hour}:${min} ${ampm}`;
    }
    
    function generateStars(rating) {
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= rating) stars += '<i class="bi bi-star-fill"></i> ';
            else stars += '<i class="bi bi-star"></i> ';
        }
        return stars;
    }
    
    function formatDate(dateStr) {
        if (!dateStr) return 'Recently';
        return new Date(dateStr).toLocaleDateString('en-IN', { day: 'numeric', month: 'short', year: 'numeric' });
    }
    
    // ==================== LOADING STATES ====================
    function showLoading() {
        const loader = document.getElementById('venue-loader');
        if (loader) loader.style.display = 'block';
    }
    
    function hideLoading() {
        const loader = document.getElementById('venue-loader');
        if (loader) loader.style.display = 'none';
        const content = document.getElementById('venue-content');
        if (content) content.style.display = 'block';
    }
    
    function showError(msg) {
        const loader = document.getElementById('venue-loader');
        if (loader) loader.style.display = 'none';
        const content = document.getElementById('venue-content');
        if (content) {
            content.style.display = 'block';
            content.innerHTML = `
                <div class="text-center py-5">
                    <i class="bi bi-exclamation-triangle display-3 text-danger mb-3 d-block"></i>
                    <h4 class="text-white">${msg}</h4>
                    <a href="venues" class="btn btn-premium mt-3"><i class="bi bi-arrow-left me-1"></i>Back to Playgrounds</a>
                </div>
            `;
        }
    }
    
    // Initialize
    await loadVenueDetails();
});
