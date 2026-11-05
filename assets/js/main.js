/* AbsoluteSineMatik - small interface helpers */
(function () {
    'use strict';

    /* ---------- Poster fallback ----------
       If a poster URL fails to load, swap in the generated poster. */
    document.addEventListener('error', function (event) {
        var img = event.target;
        if (img.tagName === 'IMG' && img.dataset.fallback && img.getAttribute('src') !== img.dataset.fallback) {
            img.src = img.dataset.fallback;
        }
    }, true);

    /* ---------- Back button ----------
       Goes to the previous page if it was on this site, otherwise to the main page. */
    document.querySelectorAll('[data-back]').forEach(function (link) {
        link.addEventListener('click', function (event) {
            var cameFromSite = document.referrer && document.referrer.indexOf(location.origin) === 0;
            if (cameFromSite && history.length > 1) {
                event.preventDefault();
                history.back();
            }
        });
    });

    /* ---------- Search box ---------- */
    var searchForm = document.getElementById('searchForm');
    var searchInput = document.getElementById('searchInput');
    var searchToggle = document.getElementById('searchToggle');

    if (searchForm && searchInput && searchToggle) {
        if (searchInput.value.trim() !== '') {
            searchForm.classList.add('is-open');
        }

        searchToggle.addEventListener('click', function () {
            var isOpen = searchForm.classList.contains('is-open');
            if (isOpen && searchInput.value.trim() !== '') {
                searchForm.submit();
                return;
            }
            searchForm.classList.toggle('is-open');
            if (!isOpen) {
                searchInput.focus();
            }
        });
    }

    /* ---------- Filter dropdown ---------- */
    var filterToggle = document.getElementById('filterToggle');
    var filterPanel = document.getElementById('filterPanel');

    if (filterToggle && filterPanel) {
        filterToggle.addEventListener('click', function (event) {
            event.stopPropagation();
            var willOpen = filterPanel.hasAttribute('hidden');
            filterPanel.toggleAttribute('hidden', !willOpen);
            filterToggle.setAttribute('aria-expanded', String(willOpen));
        });

        document.addEventListener('click', function (event) {
            if (!filterPanel.contains(event.target)) {
                filterPanel.setAttribute('hidden', '');
                filterToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    /* ---------- Carousels ---------- */
    document.querySelectorAll('.carousel').forEach(function (carousel) {
        var track = carousel.querySelector('[data-carousel-track]');
        var prev = carousel.querySelector('[data-carousel-prev]');
        var next = carousel.querySelector('[data-carousel-next]');
        if (!track) { return; }

        function scrollByPage(direction) {
            track.scrollBy({ left: direction * track.clientWidth * 0.8, behavior: 'smooth' });
        }

        if (prev) { prev.addEventListener('click', function () { scrollByPage(-1); }); }
        if (next) { next.addEventListener('click', function () { scrollByPage(1); }); }
    });

    /* ---------- Show / hide password ---------- */
    document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.getElementById(button.dataset.togglePassword);
            if (!input) { return; }
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.textContent = show ? 'Hide' : 'Show';
        });
    });
})();
