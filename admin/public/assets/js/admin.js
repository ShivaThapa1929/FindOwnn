/**
 * Findownn Admin Dashboard — JS v2.0
 * Sidebar, validation, AJAX, toast notifications, form helpers
 */

(function () {
  'use strict';

  // ──────────────────────────────────────────────────────────────
  // 1. Sidebar toggle
  // ──────────────────────────────────────────────────────────────
  const sidebar     = document.getElementById('sidebar');
  const mainWrapper = document.getElementById('mainWrapper');
  const toggleBtn   = document.getElementById('sidebarToggle');

  // Persist state - expanded by default on desktop
  let collapsed = localStorage.getItem('sb_collapsed') === '1';

  function applySidebar() {
    if (!sidebar) return;
    if (window.innerWidth <= 991) {
      sidebar.classList.toggle('open', !collapsed);
      document.body.classList.remove('sidebar-collapsed');
    } else {
      sidebar.classList.remove('open');
      document.body.classList.toggle('sidebar-collapsed', collapsed);
    }
  }

  if (toggleBtn) {
    toggleBtn.addEventListener('click', (e) => {
      e.stopPropagation();
      collapsed = !collapsed;
      localStorage.setItem('sb_collapsed', collapsed ? '1' : '0');
      applySidebar();
    });
  }

  // Close sidebar on mobile when a nav link is tapped
  sidebar?.querySelectorAll('.sidebar-link').forEach(link => {
    link.addEventListener('click', () => {
      if (window.innerWidth <= 991) {
        collapsed = true;
        localStorage.setItem('sb_collapsed', '1');
        applySidebar();
      }
    });
  });

  // Tap outside closes on mobile only
  document.addEventListener('click', e => {
    if (window.innerWidth <= 991 && sidebar?.classList.contains('open')) {
      if (!sidebar.contains(e.target) && !toggleBtn?.contains(e.target)) {
        collapsed = true;
        localStorage.setItem('sb_collapsed', '1');
        applySidebar();
      }
    }
  });

  // Prevent sidebar clicks from closing it
  if (sidebar) {
    sidebar.addEventListener('click', e => {
      e.stopPropagation();
    });
  }

  window.addEventListener('resize', applySidebar);
  applySidebar();

  // ──────────────────────────────────────────────────────────────
  // 2. Auto-dismiss success alerts after 4s
  // ──────────────────────────────────────────────────────────────
  document.querySelectorAll('.alert.alert-success').forEach(el => {
    setTimeout(() => bootstrap.Alert.getOrCreateInstance(el)?.close(), 4000);
  });

  // ──────────────────────────────────────────────────────────────
  // 3. Scroll progress bar
  // ──────────────────────────────────────────────────────────────
  const progress = document.getElementById('scroll-progress');
  if (progress) {
    const onScroll = () => {
      const pct = window.scrollY / (document.documentElement.scrollHeight - window.innerHeight) * 100;
      progress.style.width = Math.min(pct, 100) + '%';
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  // ──────────────────────────────────────────────────────────────
  // 4. Navbar scroll state
  // ──────────────────────────────────────────────────────────────
  const navbar = document.getElementById('main-navbar');
  if (navbar) {
    const onNavScroll = () => navbar.classList.toggle('scrolled', window.scrollY > 50);
    window.addEventListener('scroll', onNavScroll, { passive: true });
    onNavScroll();
  }

  // ──────────────────────────────────────────────────────────────
  // 5. Bootstrap tooltip init
  // ──────────────────────────────────────────────────────────────
  document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
    new bootstrap.Tooltip(el, { trigger: 'hover' });
  });

  // ──────────────────────────────────────────────────────────────
  // 6. novalidate forms — custom validation UX
  // ──────────────────────────────────────────────────────────────
  document.querySelectorAll('form[novalidate]').forEach(form => {
    form.addEventListener('submit', e => {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();

        // Scroll to first invalid
        const first = form.querySelector(':invalid');
        if (first) {
          first.classList.add('is-invalid');
          first.scrollIntoView({ behavior: 'smooth', block: 'center' });
          first.focus();
        }

        // Mark all invalids
        form.querySelectorAll(':invalid').forEach(el => el.classList.add('is-invalid'));
      }
    });

    // Clear is-invalid on fix
    form.querySelectorAll('input, select, textarea').forEach(el => {
      el.addEventListener('input', () => {
        if (el.checkValidity()) el.classList.remove('is-invalid');
      });
    });
  });

  // ──────────────────────────────────────────────────────────────
  // 7. Confirm-submit buttons
  // ──────────────────────────────────────────────────────────────
  document.querySelectorAll('form[onsubmit]').forEach(() => {}); // handled inline

  // ──────────────────────────────────────────────────────────────
  // 8. AJAX helper (global)
  // ──────────────────────────────────────────────────────────────
  window.adminFetch = async (url, method = 'POST', body = {}) => {
    const csrf = document.querySelector('[name="_csrf"]')?.value ?? '';
    const res  = await fetch(url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-Token': csrf,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: method !== 'GET' ? JSON.stringify(body) : undefined,
    });
    return res.json();
  };

  // ──────────────────────────────────────────────────────────────
  // 9. Toast notification
  // ──────────────────────────────────────────────────────────────
  window.showToast = (message, type = 'success') => {
    let container = document.getElementById('_toastContainer');
    if (!container) {
      container = document.createElement('div');
      container.id = '_toastContainer';
      container.style.cssText = [
        'position:fixed', 'top:68px', 'right:18px', 'z-index:10000',
        'display:flex', 'flex-direction:column', 'gap:8px', 'pointer-events:none',
      ].join(';');
      document.body.appendChild(container);
    }

    const colors = { success:'#3887C6', danger:'#ef4444', warning:'#f59e0b', info:'#3b82f6' };
    const icons  = { success:'check-circle-fill', danger:'exclamation-triangle-fill', warning:'exclamation-circle-fill', info:'info-circle-fill' };
    const c = colors[type] ?? colors.info;
    const i = icons[type]  ?? icons.info;

    const el = document.createElement('div');
    el.style.cssText = [
      'background:rgba(9,16,11,0.97)',
      'border:1px solid rgba(255,255,255,0.07)',
      `border-left:3px solid ${c}`,
      'border-radius:10px',
      'padding:12px 18px',
      'color:#f0fdf4',
      'font-size:.84rem',
      'font-family:var(--font-h,sans-serif)',
      'font-weight:600',
      'box-shadow:0 8px 32px rgba(0,0,0,0.5)',
      'backdrop-filter:blur(12px)',
      'max-width:360px',
      'display:flex',
      'align-items:center',
      'gap:10px',
      'pointer-events:all',
      'opacity:0',
      'transform:translateX(20px)',
      'transition:all .22s ease',
    ].join(';');

    el.innerHTML = `<i class="bi bi-${i}" style="color:${c};font-size:1rem;flex-shrink:0;"></i>${message}`;
    container.appendChild(el);

    requestAnimationFrame(() => {
      el.style.opacity = '1';
      el.style.transform = 'translateX(0)';
    });

    setTimeout(() => {
      el.style.opacity = '0';
      el.style.transform = 'translateX(20px)';
      setTimeout(() => el.remove(), 250);
    }, 4200);
  };

  // ──────────────────────────────────────────────────────────────
  // 10. Active sidebar link detection
  // ──────────────────────────────────────────────────────────────
  const currentPath = window.location.pathname;
  document.querySelectorAll('.sidebar-link').forEach(link => {
    const href = link.getAttribute('href') || '';
    const hPath = href.split('?')[0];
    if (hPath.length > 1 && currentPath.startsWith(hPath)) {
      link.classList.add('active');
    }
  });

  // ──────────────────────────────────────────────────────────────
  // 11. Amenity tag input for venue forms
  // ──────────────────────────────────────────────────────────────
  function initAmenityInput() {
    const raw = document.querySelector('[data-amenity-input]');
    if (!raw) return;

    const wrap = document.createElement('div');
    wrap.className = 'amenity-tag-list';
    raw.parentNode.insertBefore(wrap, raw);
    raw.style.display = 'none';

    const input = document.createElement('input');
    input.type = 'text';
    input.placeholder = 'Type amenity, press Enter or comma…';
    input.style.cssText = 'flex:1;min-width:140px;background:none;border:none;outline:none;color:var(--text-primary);font-size:.84rem;padding:2px 4px;';
    wrap.appendChild(input);

    let tags = raw.value
      ? raw.value.split(',').map(t => t.trim()).filter(Boolean)
      : [];

    function renderTags() {
      wrap.querySelectorAll('.amenity-tag').forEach(el => el.remove());
      wrap.insertBefore(document.createElement('span'), input); // placeholder
      tags.forEach((tag, i) => {
        const span = document.createElement('span');
        span.className = 'amenity-tag';
        span.innerHTML = `${tag} <button type="button" onclick="this.parentElement.remove()" title="Remove">×</button>`;
        span.querySelector('button').addEventListener('click', () => {
          tags.splice(i, 1);
          renderTags();
          raw.value = tags.join(', ');
        });
        wrap.insertBefore(span, input);
      });
      raw.value = tags.join(', ');
    }

    function addTag(val) {
      const trimmed = val.trim().replace(/,$/, '');
      if (trimmed && !tags.includes(trimmed)) {
        tags.push(trimmed);
        renderTags();
      }
      input.value = '';
    }

    input.addEventListener('keydown', e => {
      if (e.key === 'Enter' || e.key === ',') {
        e.preventDefault();
        addTag(input.value);
      } else if (e.key === 'Backspace' && input.value === '' && tags.length) {
        tags.pop();
        renderTags();
      }
    });

    input.addEventListener('blur', () => { if (input.value) addTag(input.value); });
    wrap.addEventListener('click', () => input.focus());

    renderTags();
  }

  initAmenityInput();

  // ──────────────────────────────────────────────────────────────
  // 12. Background subscription expire check (1-in-15 chance)
  // ──────────────────────────────────────────────────────────────
  if (sidebar && Math.random() < 0.07) {
    const csrfToken = document.querySelector('[name="_csrf"]')?.value ?? '';
    fetch(window.location.origin + window.location.pathname.replace(/\/[^/]*$/, '') + '/subscriptions/expire-check', {
      method: 'POST',
      headers: { 'X-CSRF-Token': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
    }).catch(() => {});
  }

  // ──────────────────────────────────────────────────────────────
  // 13. Char counter for textareas with maxlength
  // ──────────────────────────────────────────────────────────────
  document.querySelectorAll('textarea[maxlength]').forEach(ta => {
    const max    = parseInt(ta.getAttribute('maxlength'));
    const helper = ta.nextElementSibling;
    if (!helper) return;

    ta.addEventListener('input', () => {
      const remaining = max - ta.value.length;
      helper.style.color = remaining < 50 ? '#f87171' : '';
      if (!helper.dataset.original) helper.dataset.original = helper.textContent;
      helper.textContent = `${ta.value.length} / ${max} characters`;
    });
  });

  // ──────────────────────────────────────────────────────────────
  // 14. Confirm dangerous forms (data-confirm attribute)
  // ──────────────────────────────────────────────────────────────
  document.querySelectorAll('form[data-confirm]').forEach(form => {
    form.addEventListener('submit', e => {
      if (!confirm(form.dataset.confirm)) e.preventDefault();
    });
  });

})();
