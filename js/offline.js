/**
 * Findownn — offline detection & graceful degradation
 */
(function () {
    'use strict';

    var banner = null;

    function getBase() {
        return document.documentElement.getAttribute('data-site-base') || '/';
    }

    function showBanner(message, type) {
        if (!banner) {
            banner = document.createElement('div');
            banner.id = 'offline-banner';
            banner.setAttribute('role', 'alert');
            banner.style.cssText = [
                'position:fixed',
                'top:0',
                'left:0',
                'right:0',
                'z-index:99999',
                'padding:10px 16px',
                'text-align:center',
                'font-size:0.875rem',
                'font-weight:600',
                'transform:translateY(-100%)',
                'transition:transform 0.3s ease',
            ].join(';');
            document.body.appendChild(banner);
        }

        banner.textContent = message;
        banner.style.background = type === 'offline' ? '#dc2626' : '#2a6ba0';
        banner.style.color = '#fff';
        requestAnimationFrame(function () {
            banner.style.transform = 'translateY(0)';
        });
    }

    function hideBanner() {
        if (banner) {
            banner.style.transform = 'translateY(-100%)';
        }
    }

    function updateOnlineStatus() {
        if (!navigator.onLine) {
            showBanner('You are offline — showing cached data where available', 'offline');
        } else {
            showBanner('Back online', 'online');
            setTimeout(hideBanner, 2500);
        }
    }

    window.addEventListener('offline', updateOnlineStatus);
    window.addEventListener('online', updateOnlineStatus);

    if (!navigator.onLine) {
        document.addEventListener('DOMContentLoaded', updateOnlineStatus);
    }

    // Register service worker (skip admin)
    if ('serviceWorker' in navigator && location.pathname.indexOf('/admin') === -1) {
        window.addEventListener('load', function () {
            var base = getBase();
            navigator.serviceWorker.register(base + 'sw.js', { scope: base })
                .catch(function () { /* SW registration failed — site still works */ });
        });
    }

    // Prevent unhandled promise rejections from crashing the page
    window.addEventListener('unhandledrejection', function (event) {
        var reason = event.reason;
        if (reason && (reason.offline || reason.code === 'OFFLINE')) {
            event.preventDefault();
        }
    });
})();
