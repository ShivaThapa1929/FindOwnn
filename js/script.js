/**
 * Findownn — Sports Booking Platform
 * Main JS v4.0 — Splash, Scroll, Counters, Interactions
 */

/* ============================================================
  SPLASH — runs immediately, before DOM ready
   ============================================================ */
(function () {
  // Detect if the page is being reloaded
  const isReload = (
    performance.getEntriesByType &&
    performance.getEntriesByType('navigation')[0] &&
    performance.getEntriesByType('navigation')[0].type === 'reload'
  ) || (window.performance && window.performance.navigation && window.performance.navigation.type === 1);

  if (isReload) {
    sessionStorage.removeItem('splashShown');
  }

  // Check if splash has been shown in this session
  const splashShown = sessionStorage.getItem('splashShown');
  
  // Only show splash if not shown before in this session
  if (!splashShown) {
    document.documentElement.classList.add('splash-active');
    
    window.addEventListener('load', function () {
      setTimeout(function () {
        const splash = document.getElementById('splash-screen');
        if (splash) {
          splash.remove();
        }
        document.documentElement.classList.remove('splash-active');
        document.body.classList.remove('splash-active');
        
        // Mark splash as shown for this session
        sessionStorage.setItem('splashShown', 'true');
      }, 2500);
    });
  } else {
    // Splash already shown, remove it immediately if it exists
    window.addEventListener('DOMContentLoaded', function() {
      const splash = document.getElementById('splash-screen');
      if (splash) {
        splash.style.display = 'none';
        splash.remove();
      }
      document.documentElement.classList.remove('splash-active');
      document.body.classList.remove('splash-active');
    });
  }
})();

document.addEventListener('DOMContentLoaded', () => {

  // ============================================================
  // 0. Smooth page enter + scroll restore
  // ============================================================
  if (!window.location.hash) {
    window.scrollTo(0, 0);
  }
  requestAnimationFrame(() => {
    document.body.classList.add('page-ready');
  });

  // ============================================================
  // 1. Scroll progress bar
  // ============================================================
  const progressBar = document.createElement('div');
  progressBar.id = 'scroll-progress';
  document.body.prepend(progressBar);

  const backToTop = document.createElement('button');
  backToTop.id = 'back-to-top';
  backToTop.type = 'button';
  backToTop.setAttribute('aria-label', 'Back to top');
  backToTop.innerHTML = '<i class="bi bi-arrow-up"></i>';
  document.body.appendChild(backToTop);
  backToTop.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  });

  // ============================================================
  // 2. Navbar scroll behaviour
  // ============================================================
  const navbar = document.querySelector('#main-navbar');

  const onScroll = () => {
    const scrolled = window.scrollY;
    const total = document.documentElement.scrollHeight - window.innerHeight;
    if (total > 0) progressBar.style.width = `${(scrolled / total) * 100}%`;
    if (navbar) navbar.classList.toggle('scrolled', scrolled > 60);
    backToTop.classList.toggle('visible', scrolled > 420);
  };

  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // Close mobile nav after tapping a link
  const navCollapse = document.getElementById('navbarNav');
  if (navCollapse && navbar) {
    navCollapse.querySelectorAll('.nav-link, .btn').forEach(link => {
      link.addEventListener('click', () => {
        if (window.innerWidth < 992 && navCollapse.classList.contains('show')) {
          const instance = bootstrap.Collapse.getOrCreateInstance(navCollapse);
          instance.hide();
        }
      });
    });
  }

  // ============================================================
  // 3. Scroll-reveal animations
  // ============================================================
  const revealObserver = new IntersectionObserver((entries, obs) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('appear');
        obs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

  window.refreshScrollAnimations = function (root = document) {
    root.querySelectorAll('.animate-on-scroll:not(.appear)').forEach(el => revealObserver.observe(el));
  };

  window.refreshScrollAnimations();

  // ============================================================
  // 4. Animated counters
  // ============================================================
  const easeOut = t => 1 - Math.pow(1 - t, 3);

  const animateCounter = (el, target, duration = 1800) => {
    const isFloat  = String(target).includes('.');
    const suffix   = el.dataset.suffix || '';
    const start    = performance.now();

    const fmt = v => {
      if (isFloat) return v.toFixed(1) + suffix;
      const n = Math.floor(v);
      return (n >= 1000 ? n.toLocaleString('en-IN') : n) + suffix;
    };

    const tick = now => {
      const progress = Math.min((now - start) / duration, 1);
      el.textContent = fmt(easeOut(progress) * target);
      if (progress < 1) requestAnimationFrame(tick);
      else el.textContent = fmt(target);
    };

    requestAnimationFrame(tick);
  };

  const counterObserver = new IntersectionObserver((entries, obs) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el = entry.target;
        if (el.dataset.counted === '1') return;
        el.dataset.counted = '1';
        animateCounter(el, parseFloat(el.dataset.target));
        obs.unobserve(el);
      }
    });
  }, { threshold: 0.15, rootMargin: '0px 0px -20px 0px' });

  document.querySelectorAll('.stat-number[data-target]').forEach(el => counterObserver.observe(el));

  // ============================================================
  // 4b. Home stats strip — reveal + counter (index page)
  // ============================================================
  const initHomeStatsReveal = () => {
    const section = document.querySelector('.home-section--stats');
    if (!section || section.dataset.statsReady === '1') return;

    const reveal = () => {
      if (section.classList.contains('is-visible')) return;
      section.classList.add('is-visible');
      section.dataset.statsReady = '1';

      section.querySelectorAll('.stat-number[data-target]').forEach(el => {
        if (el.dataset.counted === '1') return;
        el.dataset.counted = '1';
        animateCounter(el, parseFloat(el.dataset.target), 1400);
      });
    };

    section.classList.add('home-stats-js');

    const inView = () => {
      const rect = section.getBoundingClientRect();
      return rect.top < window.innerHeight * 0.92 && rect.bottom > 0;
    };

    if (inView()) {
      requestAnimationFrame(() => reveal());
      return;
    }

    if (!('IntersectionObserver' in window)) {
      reveal();
      return;
    }

    const observer = new IntersectionObserver((entries, obs) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        reveal();
        obs.disconnect();
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -20px 0px' });

    observer.observe(section);
  };

  initHomeStatsReveal();
  window.addEventListener('load', initHomeStatsReveal, { once: true });

  // Phone mockup — static real app booking screen (rendered in home.php)
  // Legacy carousel removed; screen HTML lives in includes/app-screen-bookings.php

  // ============================================================
  // 6. Partner earnings calculator
  // ============================================================
  const courtsSlider = document.getElementById('num-courts');
  const hoursSlider  = document.getElementById('booked-hours');

  if (courtsSlider && hoursSlider) {
    const valCourts  = document.getElementById('val-courts');
    const valHours   = document.getElementById('val-hours');
    const estRevenue = document.getElementById('est-revenue');

    const calcRevenue = () => {
      const courts = parseInt(courtsSlider.value);
      const hours  = parseInt(hoursSlider.value);
      valCourts.textContent = courts;
      valHours.textContent  = hours;
      const total = courts * hours * 1000 * 30 * 0.85;
      estRevenue.textContent = new Intl.NumberFormat('en-IN', {
        style: 'currency', currency: 'INR', maximumFractionDigits: 0
      }).format(total);
    };

    courtsSlider.addEventListener('input', calcRevenue);
    hoursSlider.addEventListener('input', calcRevenue);
    calcRevenue();
  }

  // ============================================================
  // 7. Venue search + filter
  // ============================================================
  const searchInput = document.getElementById('venue-search');
  const filterBtns  = document.querySelectorAll('.filter-btn');
  const venueItems  = document.querySelectorAll('.venue-item');

  if (venueItems.length > 0) {
    let activeFilter = 'all';
    let query = '';

    const applyFilter = () => {
      venueItems.forEach(card => {
        const name  = (card.dataset.name || '').toLowerCase();
        const loc   = (card.dataset.location || '').toLowerCase();
        const sport = (card.dataset.sport || '').toLowerCase();
        const matchQ = name.includes(query) || loc.includes(query);
        const matchF = activeFilter === 'all' || sport === activeFilter.toLowerCase();
        card.style.display = (matchQ && matchF) ? '' : 'none';
      });
    };

    if (searchInput) {
      searchInput.addEventListener('input', e => {
        query = e.target.value.toLowerCase().trim();
        applyFilter();
      });
    }

    filterBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        filterBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        activeFilter = btn.dataset.filter;
        applyFilter();
      });
    });
  }

  // ============================================================
  // 8. Booking modal
  // ============================================================
  const bookBtns       = document.querySelectorAll('.btn-book-trigger');
  const modalVenueName = document.getElementById('modal-venue-name');

  if (bookBtns.length && modalVenueName) {
    const bookingModalEl = document.getElementById('bookingModal');
    if (bookingModalEl) {
      const modal = new bootstrap.Modal(bookingModalEl);
      bookBtns.forEach(btn => {
        btn.addEventListener('click', () => {
          modalVenueName.textContent = btn.dataset.venueName;
          modal.show();
        });
      });
    }
  }

  // ============================================================
  // 9. Subtle 3-D card tilt on desktop
  // ============================================================
  if (window.matchMedia('(min-width: 992px) and (hover: hover)').matches) {
    document.querySelectorAll('.feature-card, .step-card').forEach(card => {
      card.addEventListener('mousemove', e => {
        const r = card.getBoundingClientRect();
        const x = ((e.clientX - r.left) / r.width  - 0.5) * 6;
        const y = ((e.clientY - r.top)  / r.height - 0.5) * 6;
        card.style.transform = `translateY(-4px) rotateX(${-y}deg) rotateY(${x}deg)`;
        card.style.transition = 'transform 0.08s ease';
      });
      card.addEventListener('mouseleave', () => {
        card.style.transform = '';
        card.style.transition = '';
      });
    });
  }

  // ============================================================
  // 10. Smooth internal navigation + link prefetch
  // ============================================================
  const prefetched = new Set();

  const isInternalNavLink = (link) => {
    const href = link.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('mailto:') ||
        href.startsWith('tel:') || href.startsWith('javascript:')) return false;
    if (link.target === '_blank' || link.hasAttribute('download')) return false;
    try {
      const url = new URL(link.href, window.location.href);
      return url.origin === window.location.origin && !url.pathname.includes('/admin');
    } catch {
      return false;
    }
  };

  const prefetchPage = (url) => {
    if (!url || prefetched.has(url)) return;
    prefetched.add(url);
    const hint = document.createElement('link');
    hint.rel = 'prefetch';
    hint.href = url;
    hint.as = 'document';
    document.head.appendChild(hint);
  };

  document.querySelectorAll('a[href]').forEach(link => {
    if (!isInternalNavLink(link)) return;

    link.addEventListener('mouseenter', () => prefetchPage(link.href), { passive: true });
    link.addEventListener('focus', () => prefetchPage(link.href), { passive: true });

    link.addEventListener('click', (e) => {
      if (e.defaultPrevented || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      if (link.getAttribute('href').startsWith('#')) return;

      e.preventDefault();
      document.body.classList.remove('page-ready');
      document.body.classList.add('page-leaving');
      setTimeout(() => {
        window.location.href = link.href;
      }, 220);
    });
  });

  // ============================================================
  // 11. Smooth anchor scroll
  // ============================================================
  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener('click', e => {
      const target = document.querySelector(link.getAttribute('href'));
      if (target) {
        e.preventDefault();
        const offset = navbar ? navbar.offsetHeight + 20 : 80;
        window.scrollTo({ top: target.offsetTop - offset, behavior: 'smooth' });
      }
    });
  });

  // ============================================================
  // 12. Active nav highlighting on scroll
  // ============================================================
  const sections = document.querySelectorAll('section[id]');
  if (sections.length > 0) {
    const navLinks = document.querySelectorAll('.nav-link');
    const sectionObs = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const id = entry.target.id;
          navLinks.forEach(link => {
            link.classList.toggle('active', link.getAttribute('href') === `#${id}`);
          });
        }
      });
    }, { threshold: 0.45 });
    sections.forEach(s => sectionObs.observe(s));
  }

  // ============================================================
  // 13. Testimonial slider — see standalone init below DOMContentLoaded
  // ============================================================

});

/* ============================================================
   Testimonial slider — standalone (always runs)
   ============================================================ */
(function () {
  let initialized = false;

  function initTestimonialSlider() {
    const testimonialSlider = document.getElementById('testimonialSlider');
    const testimonialTrack  = document.getElementById('testimonialTrack');
    const testimonialDots   = document.getElementById('testimonialDots');

    if (!testimonialSlider || !testimonialTrack || !testimonialDots) return;
    if (initialized) return;

    const viewport = testimonialSlider.querySelector('.testimonial-viewport');
    const pages = Array.from(testimonialTrack.querySelectorAll('.testimonial-page'));
    if (!viewport || pages.length === 0) return;

    initialized = true;

    const prevBtn = testimonialSlider.querySelector('.testimonial-arrow-prev');
    const nextBtn = testimonialSlider.querySelector('.testimonial-arrow-next');
    let currentIndex = 0;
    let autoplayTimer = null;
    const AUTOPLAY_MS = 5000;

    const getPageWidth = () => viewport.getBoundingClientRect().width;

    const updateSlider = () => {
      const w = getPageWidth();
      currentIndex = Math.max(0, Math.min(currentIndex, pages.length - 1));
      testimonialTrack.style.transform = w > 0
        ? `translate3d(-${currentIndex * w}px, 0, 0)`
        : 'translate3d(0, 0, 0)';

      testimonialDots.querySelectorAll('.testimonial-dot').forEach((dot, i) => {
        dot.classList.toggle('active', i === currentIndex);
        dot.setAttribute('aria-selected', i === currentIndex ? 'true' : 'false');
      });
    };

    if (!testimonialDots.childElementCount) {
      pages.forEach((_, i) => {
        const dot = document.createElement('button');
        dot.type = 'button';
        dot.className = 'testimonial-dot' + (i === 0 ? ' active' : '');
        dot.setAttribute('role', 'tab');
        dot.setAttribute('aria-label', `Show reviews page ${i + 1}`);
        dot.setAttribute('aria-selected', i === 0 ? 'true' : 'false');
        dot.addEventListener('click', () => {
          currentIndex = i;
          updateSlider();
          restartAutoplay();
        });
        testimonialDots.appendChild(dot);
      });
    }

    const maxIndex = () => pages.length - 1;

    const goNext = () => {
      currentIndex = currentIndex >= maxIndex() ? 0 : currentIndex + 1;
      updateSlider();
    };

    const goPrev = () => {
      currentIndex = currentIndex <= 0 ? maxIndex() : currentIndex - 1;
      updateSlider();
    };

    const stopAutoplay = () => {
      if (autoplayTimer) {
        clearInterval(autoplayTimer);
        autoplayTimer = null;
      }
    };

    const startAutoplay = () => {
      stopAutoplay();
      if (pages.length <= 1) return;
      autoplayTimer = setInterval(goNext, AUTOPLAY_MS);
    };

    const restartAutoplay = () => {
      stopAutoplay();
      startAutoplay();
    };

    prevBtn?.addEventListener('click', () => { goPrev(); restartAutoplay(); });
    nextBtn?.addEventListener('click', () => { goNext(); restartAutoplay(); });

    document.addEventListener('visibilitychange', () => {
      if (document.hidden) stopAutoplay();
      else startAutoplay();
    });

    window.addEventListener('resize', updateSlider);

    if (typeof ResizeObserver !== 'undefined') {
      new ResizeObserver(updateSlider).observe(viewport);
    }

    let touchStartX = 0;
    viewport.addEventListener('touchstart', (e) => {
      touchStartX = e.changedTouches[0].screenX;
    }, { passive: true });
    viewport.addEventListener('touchend', (e) => {
      const diff = touchStartX - e.changedTouches[0].screenX;
      if (Math.abs(diff) > 48) {
        if (diff > 0) goNext();
        else goPrev();
        restartAutoplay();
      }
    }, { passive: true });

    updateSlider();
    startAutoplay();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTestimonialSlider);
  } else {
    initTestimonialSlider();
  }
  window.addEventListener('load', initTestimonialSlider);
  setTimeout(initTestimonialSlider, 600);
  setTimeout(initTestimonialSlider, 1500);
})();
