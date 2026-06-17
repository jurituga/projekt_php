document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.getElementById('navToggle');
    var nav = document.getElementById('mainNav');
    var body = document.body;

    var overlay = document.getElementById('navOverlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'nav-overlay';
        overlay.id = 'navOverlay';
        body.appendChild(overlay);
    }

    var isOpen = false;

    function openMenu() {
        if (!nav || !toggle || isOpen) return;
        isOpen = true;
        nav.classList.add('open');
        toggle.classList.add('active');
        toggle.setAttribute('aria-expanded', 'true');
        overlay.classList.add('show');
        body.style.overflow = 'hidden';
    }

    function closeMenu() {
        if (!nav || !toggle || !isOpen) return;
        isOpen = false;
        nav.classList.remove('open');
        toggle.classList.remove('active');
        toggle.setAttribute('aria-expanded', 'false');
        overlay.classList.remove('show');
        body.style.overflow = '';
    }

    if (toggle) {
        toggle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (isOpen) { closeMenu(); } else { openMenu(); }
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function () { closeMenu(); });
    }

    if (nav) {
        nav.addEventListener('click', function (e) {
            if (e.target.closest('a') || (e.target.closest('button') && e.target.closest('button') !== toggle)) {
                closeMenu();
            }
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isOpen) { closeMenu(); }
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth > 640 && isOpen) { closeMenu(); }
    });

    // Header scroll shadow
    var header = document.getElementById('siteHeader');
    if (header) {
        var ticking = false;
        window.addEventListener('scroll', function () {
            if (!ticking) {
                window.requestAnimationFrame(function () {
                    header.classList.toggle('scrolled', window.scrollY > 10);
                    ticking = false;
                });
                ticking = true;
            }
        }, { passive: true });
    }

    // Auto-wrap standalone data-tables
    document.querySelectorAll('.data-table').forEach(function (table) {
        if (table.parentElement && table.parentElement.classList.contains('table-wrap')) return;
        var wrap = document.createElement('div');
        wrap.className = 'table-wrap';
        table.parentNode.insertBefore(wrap, table);
        wrap.appendChild(table);
    });
});
