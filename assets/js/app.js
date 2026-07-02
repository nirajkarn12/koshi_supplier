document.addEventListener('DOMContentLoaded', function () {
    const loader = document.getElementById('pageLoader');
    if (loader) {
        window.addEventListener('load', function () {
            loader.classList.add('is-hidden');
            setTimeout(function () { loader.remove(); }, 250);
        });
    }

    const header = document.querySelector('.site-header');
    if (header) {
        const toggleHeaderState = function () {
            header.classList.toggle('scrolled', window.scrollY > 18);
        };
        toggleHeaderState();
        window.addEventListener('scroll', toggleHeaderState, { passive: true });
    }

    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });

    if (window.Swiper) {
        new Swiper('.heroSwiper', {
            slidesPerView: 1,
            loop: true,
            autoplay: { delay: 5500 },
            pagination: { el: '.swiper-pagination', clickable: true },
            effect: 'fade'
        });
    }

    const revealItems = document.querySelectorAll('.reveal');
    const observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });
    revealItems.forEach(function (item) {
        observer.observe(item);
    });

    document.querySelectorAll('img').forEach(function (img) {
        if (!img.hasAttribute('loading')) {
            img.setAttribute('loading', 'lazy');
        }
    });

    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        const toggleBackToTop = function () {
            backToTop.classList.toggle('visible', window.scrollY > 480);
        };
        toggleBackToTop();
        window.addEventListener('scroll', toggleBackToTop, { passive: true });
        backToTop.addEventListener('click', function (e) {
            e.preventDefault();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    const showToast = function (message, type) {
        let toast = document.getElementById('siteToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'siteToast';
            toast.className = 'site-toast';
            document.body.appendChild(toast);
        }
        toast.className = 'site-toast show ' + (type || 'success');
        toast.textContent = message;
        clearTimeout(toast.timer);
        toast.timer = setTimeout(function () {
            toast.classList.remove('show');
        }, 2600);
    };

    document.querySelectorAll('[data-quick-view]').forEach(function (button) {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const productId = this.dataset.quickView;
            fetch('ajax/quick-view.php?id=' + productId)
                .then(function (r) { return r.text(); })
                .then(function (html) {
                    document.getElementById('quickViewBody').innerHTML = html;
                    new bootstrap.Modal(document.getElementById('quickViewModal')).show();
                });
        });
    });

    document.querySelectorAll('.add-to-cart').forEach(function (button) {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const productId = this.dataset.productId;
            fetch('ajax/cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=add&product_id=' + productId + '&quantity=1'
            }).then(function (r) { return r.json(); }).then(function (data) {
                if (data.success) {
                    const badge = document.querySelector('.site-header .badge, .navbar .badge');
                    if (badge) {
                        badge.textContent = data.count;
                    }
                    showToast(data.message || 'Added to cart', 'success');
                } else {
                    showToast(data.message || 'Unable to add item', 'danger');
                }
            });
        });
    });

    const searchInput = document.getElementById('headerSearchInput');
    const searchResults = document.getElementById('searchResults');
    if (searchInput && searchResults) {
        let timer;
        searchInput.addEventListener('input', function () {
            clearTimeout(timer);
            const term = this.value.trim();
            if (term.length < 2) {
                searchResults.style.display = 'none';
                return;
            }
            timer = setTimeout(function () {
                fetch('ajax/search.php?q=' + encodeURIComponent(term))
                    .then(function (r) { return r.text(); })
                    .then(function (html) {
                        searchResults.innerHTML = html;
                        searchResults.style.display = 'block';
                    });
            }, 200);
        });
        document.addEventListener('click', function () {
            searchResults.style.display = 'none';
        });
        searchInput.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    }
});
