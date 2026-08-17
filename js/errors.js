/**
 * Findownn — user-friendly error toasts & API message helpers
 */
(function (global) {
    'use strict';

    var UNAVAILABLE = "We're unavailable right now. Please try again in a few minutes.";
    var OFFLINE     = 'You appear to be offline. Please check your connection and try again.';
    var NETWORK     = 'Network error. Please check your connection and try again.';

    var container = null;

    function ensureContainer() {
        if (container) return container;
        container = document.createElement('div');
        container.id = 'findownn-toast-stack';
        container.setAttribute('aria-live', 'polite');
        container.style.cssText = [
            'position:fixed',
            'bottom:24px',
            'right:16px',
            'left:16px',
            'z-index:100000',
            'display:flex',
            'flex-direction:column',
            'gap:10px',
            'align-items:flex-end',
            'pointer-events:none',
        ].join(';');
        document.body.appendChild(container);
        return container;
    }

    function toast(message, type, options) {
        options = options || {};
        var stack = ensureContainer();
        var el = document.createElement('div');
        el.setAttribute('role', 'alert');
        el.style.cssText = [
            'pointer-events:auto',
            'max-width:min(420px,100%)',
            'padding:14px 16px',
            'border-radius:12px',
            'font-size:0.875rem',
            'line-height:1.45',
            'box-shadow:0 8px 32px rgba(0,0,0,0.35)',
            'display:flex',
            'align-items:flex-start',
            'gap:10px',
            'animation:findownnToastIn 0.3s ease',
            'background:' + (type === 'success' ? 'rgba(22,163,74,0.95)' : type === 'warning' ? 'rgba(234,179,8,0.95)' : 'rgba(220,38,38,0.95)'),
            'color:#fff',
        ].join(';');

        var icon = type === 'success' ? 'check-circle-fill' : type === 'warning' ? 'exclamation-triangle-fill' : 'exclamation-octagon-fill';
        el.innerHTML = '<i class="bi bi-' + icon + '" style="flex-shrink:0;margin-top:2px;"></i><div style="flex:1;">' + escapeHtml(message) + '</div>';

        if (options.retry && typeof options.retry === 'function') {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.textContent = 'Retry';
            btn.style.cssText = 'background:rgba(255,255,255,0.2);border:none;color:#fff;border-radius:6px;padding:4px 10px;font-size:0.78rem;font-weight:600;cursor:pointer;margin-left:8px;flex-shrink:0;';
            btn.addEventListener('click', function () {
                el.remove();
                options.retry();
            });
            el.appendChild(btn);
        }

        stack.appendChild(el);

        var ttl = options.duration ?? 6000;
        setTimeout(function () {
            el.style.opacity = '0';
            el.style.transform = 'translateY(8px)';
            el.style.transition = 'opacity 0.25s, transform 0.25s';
            setTimeout(function () { el.remove(); }, 260);
        }, ttl);
    }

    function escapeHtml(str) {
        var d = document.createElement('div');
        d.textContent = String(str ?? '');
        return d.innerHTML;
    }

    function friendlyApiMessage(error) {
        if (!error) return UNAVAILABLE;
        if (error.offline || error.code === 'OFFLINE') return OFFLINE;
        if (error.name === 'TypeError' || error.message === 'Failed to fetch') return NETWORK;
        if (error.status >= 500 || error.code === 'SERVER_ERROR') return UNAVAILABLE;
        if (error.status === 429) return 'Too many requests. Please wait a moment and try again.';
        if (error.status === 401 || error.code === 'AUTH_REQUIRED') return 'Please sign in to continue.';
        if (error.status === 403) return 'You don\'t have permission to do that.';
        if (error.status === 404 || error.code === 'RESOURCE_NOT_FOUND') return 'The requested item could not be found.';
        if (error.message && error.message !== 'API request failed') return error.message;
        return UNAVAILABLE;
    }

    if (!document.getElementById('findownn-toast-styles')) {
        var style = document.createElement('style');
        style.id = 'findownn-toast-styles';
        style.textContent = '@keyframes findownnToastIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}';
        document.head.appendChild(style);
    }

    global.FindownnUI = global.FindownnUI || {};
    global.FindownnUI.showError = function (message, options) { toast(message, 'error', options); };
    global.FindownnUI.showSuccess = function (message, options) { toast(message, 'success', options); };
    global.FindownnUI.showWarning = function (message, options) { toast(message, 'warning', options); };
    global.FindownnUI.friendlyApiMessage = friendlyApiMessage;
    global.FindownnUI.UNAVAILABLE = UNAVAILABLE;
})(window);
