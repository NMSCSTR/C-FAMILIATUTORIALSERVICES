(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState !== 'loading') fn();
        else document.addEventListener('DOMContentLoaded', fn);
    }

    window.AdminUI = {
        toast: function (message, icon) {
            if (!window.Swal) return;
            Swal.fire({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2800,
                timerProgressBar: true,
                background: '#0f172a',
                color: '#fff',
                icon: icon || 'success',
                title: message
            });
        },

        confirmForm: function (event, message) {
            event.preventDefault();
            var form = event.target;

            if (!window.Swal) {
                if (window.confirm(message)) form.submit();
                return false;
            }

            Swal.fire({
                title: 'Are you sure?',
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#1e293b'
            }).then(function (result) {
                if (result.isConfirmed) form.submit();
            });
            return false;
        },

        initSidebar: function () {
            var openBtn = document.getElementById('openMenu');
            var closeBtn = document.getElementById('closeMenu');
            var sidebar = document.getElementById('mobileSidebar');
            var overlay = document.getElementById('sidebarOverlay');
            if (!openBtn || !sidebar) return;

            function setState(open) {
                if (open) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                    setTimeout(function () { overlay.classList.add('opacity-100'); }, 10);
                } else {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.remove('opacity-100');
                    setTimeout(function () { overlay.classList.add('hidden'); }, 300);
                }
            }

            openBtn.addEventListener('click', function () { setState(true); });
            if (closeBtn) closeBtn.addEventListener('click', function () { setState(false); });
            if (overlay) overlay.addEventListener('click', function () { setState(false); });
        }
    };

    ready(function () {
        window.AdminUI.initSidebar();

        // URL-param toasts (?success=..., ?posted=1, ?approved=1, ?deleted=1 ...)
        var params = new URLSearchParams(window.location.search);
        var map = {
            success: 'Completed successfully',
            posted: 'Created successfully',
            approved: 'Enrollment approved',
            deleted: 'Deleted successfully',
            verified: 'Payment verified',
            refunded: 'Refund processed',
            logged: 'Walk-in payment saved'
        };
        for (var key in map) {
            if (params.has(key)) {
                var value = params.get(key);
                var msg = (value === '1' || value === null) ? map[key] : map[key];
                window.AdminUI.toast(msg, 'success');
                window.history.replaceState({}, document.title, window.location.pathname);
                break;
            }
        }

        var errorParam = params.get('error');
        if (errorParam) {
            window.AdminUI.toast('Something went wrong. Please review and retry.', 'error');
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    });
})();
