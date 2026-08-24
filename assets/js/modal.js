(function () {
    'use strict';

    var FOCUSABLE = 'a[href], button:not([disabled]), textarea:not([disabled]), input:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])';
    var lastFocused = null;

    function currentOverlay() {
        return document.querySelector('[data-modal-root]:not(.hidden)');
    }

    window.AdminModal = {
        open: function (id) {
            var modal = document.getElementById(id);
            if (!modal || !modal.classList.contains('hidden')) return;
            if (!currentOverlay()) lastFocused = document.activeElement;
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
            var target = modal.querySelector('[data-autofocus]') || modal.querySelector(FOCUSABLE);
            if (target) target.focus();
        },
        close: function (id) {
            var modal = document.getElementById(id);
            if (!modal || modal.classList.contains('hidden')) return;
            modal.classList.add('hidden');
            if (!currentOverlay()) {
                document.body.style.overflow = '';
                if (lastFocused && document.contains(lastFocused)) lastFocused.focus();
                lastFocused = null;
            }
        }
    };

    document.addEventListener('keydown', function (e) {
        var open = currentOverlay();
        if (!open) return;

        if (e.key === 'Escape') {
            e.preventDefault();
            window.AdminModal.close(open.id);
            return;
        }

        if (e.key === 'Tab') {
            var items = open.querySelectorAll(FOCUSABLE);
            if (!items.length) return;
            var first = items[0];
            var last = items[items.length - 1];
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    });

    document.addEventListener('mousedown', function (e) {
        if (e.target.matches && e.target.matches('[data-modal-dismiss]')) {
            window.AdminModal.close(e.target.id);
        }
    });
})();
