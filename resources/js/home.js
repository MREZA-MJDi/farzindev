const reduceMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
let reduceMotion = reduceMotionQuery.matches;
document.documentElement.classList.add('js');
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
const modalRoot = document.getElementById('quick-view-modal');
const toastRoot = document.getElementById('toast-root');
let lastTrigger = null;
let previousBodyOverflow = '';

function toast(message, type = 'info') {
    if (!toastRoot) return;

    const el = document.createElement('div');
    el.className = `toast toast--${type}`;
    el.textContent = message;
    toastRoot.appendChild(el);

    requestAnimationFrame(() => el.classList.add('toast--in'));

    window.setTimeout(() => {
        el.classList.remove('toast--in');
        window.setTimeout(() => el.remove(), 250);
    }, 2800);
}

async function addToCart(productId, btn, endpoint) {
    if (!productId || !endpoint) {
        toast('این محصول هنوز برای افزودن به سبد آماده نیست.', 'error');
        return;
    }

    if (btn?.dataset.busy === 'true') return;
    if (btn) {
        btn.dataset.busy = 'true';
        btn.disabled = true;
    }

    try {
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ quantity: 1 }),
            credentials: 'same-origin',
        });

        const payload = await response.json().catch(() => ({}));

        if (response.ok) {
            toast(payload.message || 'محصول به سبد خرید اضافه شد.', 'success');
            if (Number.isFinite(Number(payload.cart_count))) {
                const count = Number(payload.cart_count);
                document.querySelectorAll('[data-cart-count]').forEach((badge) => {
                    badge.textContent = String(count);
                    badge.classList.toggle('hidden', count < 1);
                });
            }
            return;
        }

        if (response.status === 419) {
            toast('نشست شما منقضی شده است؛ صفحه را تازه‌سازی کنید.', 'error');
        } else if (response.status === 422) {
            toast(payload.message || 'موجودی یا اطلاعات محصول نیاز به بررسی دارد.', 'error');
        } else {
            toast(payload.message || 'افزودن محصول به سبد انجام نشد.', 'error');
        }
    } catch {
        toast('اتصال برقرار نشد؛ دوباره تلاش کنید.', 'error');
    } finally {
        if (btn) {
            btn.dataset.busy = 'false';
            btn.disabled = false;
        }
    }
}

function setQuickViewImage(imageUrl) {
    const image = modalRoot?.querySelector('[data-qv-image]');
    if (!image) return;
    image.style.backgroundImage = imageUrl ? `url("${imageUrl.replace(/["\\]/g, '')}")` : '';
}

function openQuickView(data, trigger) {
    if (!modalRoot) return;

    lastTrigger = trigger || document.activeElement;
    modalRoot.querySelector('[data-qv-name]').textContent = data.name || '';
    modalRoot.querySelector('[data-qv-price]').textContent = data.price || '';
    modalRoot.querySelector('[data-qv-category]').textContent = data.category || 'محصول فرزین';

    const link = modalRoot.querySelector('[data-qv-link]');
    if (link) link.href = data.url || '#';

    setQuickViewImage(data.image);

    const addButton = modalRoot.querySelector('[data-qv-add]');
    if (addButton) {
        const canAdd = Number(data.stock) > 0 && Boolean(data.cartUrl);
        addButton.disabled = !canAdd;
        addButton.textContent = canAdd ? 'افزودن به سبد خرید' : 'فعلاً ناموجود';
        addButton.onclick = canAdd
            ? (event) => addToCart(data.id, event.currentTarget, data.cartUrl)
            : null;
    }

    modalRoot.classList.add('is-open');
    modalRoot.setAttribute('aria-hidden', 'false');
    document.body.dataset.quickViewOpen = 'true';
    previousBodyOverflow = document.body.style.overflow;
    document.body.style.overflow = 'hidden';

    modalRoot.querySelector('.qv__close')?.focus({ preventScroll: true });
}

function closeQuickView() {
    if (!modalRoot || !modalRoot.classList.contains('is-open')) return;

    modalRoot.classList.remove('is-open');
    modalRoot.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = previousBodyOverflow;
    delete document.body.dataset.quickViewOpen;

    if (lastTrigger instanceof HTMLElement && document.contains(lastTrigger)) {
        lastTrigger.focus({ preventScroll: true });
    }
}

document.querySelectorAll('.grid__item-inner[data-product-id]').forEach((link) => {
    link.addEventListener('click', (event) => {
        // Keep native new-tab / open-in-new-window behavior.
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || event.button === 1) return;
        event.preventDefault();

        openQuickView({
            id: link.dataset.productId,
            name: link.dataset.productName,
            price: link.dataset.productPrice,
            image: link.dataset.productImage,
            category: link.dataset.productCategory,
            stock: link.dataset.productStock,
            url: link.getAttribute('href'),
            cartUrl: link.dataset.cartUrl,
        }, link);
    });
});

modalRoot?.addEventListener('click', (event) => {
    if (event.target.closest('[data-qv-close]')) closeQuickView();
});

document.addEventListener('keydown', (event) => {
    if (!modalRoot?.classList.contains('is-open')) return;

    if (event.key === 'Escape') {
        event.preventDefault();
        closeQuickView();
        return;
    }

    if (event.key !== 'Tab') return;

    const focusable = [...modalRoot.querySelectorAll(
        'a[href], button:not(:disabled), input:not(:disabled), [tabindex]:not([tabindex="-1"])'
    )].filter((element) => element.offsetParent !== null);

    if (!focusable.length) return;

    const first = focusable[0];
    const last = focusable[focusable.length - 1];

    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
    } else if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
});

/*
 * Do not preload the gallery. Native lazy-loading lets the page render
 * immediately. IntersectionObserver triggers only small CSS entrance states;
 * native scrolling stays in control and no perpetual animation loop is used.
 */
function initRevealAnimations() {
    const elements = [...document.querySelectorAll('.fz-reveal')];
    if (!elements.length || reduceMotion || !('IntersectionObserver' in window)) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;

            const element = entry.target;
            element.classList.add('is-entering');
            element.addEventListener('animationend', () => {
                element.classList.remove('is-entering');
            }, { once: true });
            observer.unobserve(element);
        });
    }, {
        root: null,
        rootMargin: '0px 0px -7% 0px',
        threshold: 0.08,
    });

    elements.forEach((element) => observer.observe(element));
}

function initGridLayouts() {
    // Gallery sizing is handled by the responsive CSS grid contract.
    // Keep each gallery in a predictable reading order; do not vary columns per section.
    document.querySelectorAll('.home-body .grid').forEach((grid) => {
        grid.style.removeProperty('--grid-columns');
        grid.style.removeProperty('--grid-item-ratio');
        grid.classList.add('grid--static');
    });
}

/**
 * Draw the architectural hero from native scroll progress.
 * No ticker runs between scroll events; SVG pathLength=1 keeps dash offsets normalized.
 */
function initHeroScrollDrawing() {
    const hero = document.querySelector('[data-fz-scroll-hero]');
    const stage = hero?.querySelector('[data-hero-stage]');
    const drawing = hero?.querySelector('[data-hero-drawing]');

    if (!hero || !stage || !drawing) return;

    const groups = [...drawing.querySelectorAll('[data-hero-draw]')].map((group) => ({
        phase: Number(group.dataset.heroPhase || 0),
        paths: [...group.querySelectorAll('path')],
    }));
    const progressLabel = hero.querySelector('[data-hero-progress]');
    const motionPreference = window.matchMedia('(prefers-reduced-motion: reduce)');
    let motionReduced = motionPreference.matches;
    let rafId = 0;
    let disposed = false;

    const clamp = (value, min, max) => Math.min(max, Math.max(min, value));

    function paint() {
        rafId = 0;
        if (disposed) return;

        if (motionReduced) {
            groups.forEach(({ paths }) => {
                paths.forEach((path) => {
                    path.style.strokeDashoffset = '0';
                });
            });
            if (progressLabel) progressLabel.textContent = '100 / 100';
            drawing.style.transform = '';
            return;
        }

        const sectionTop = hero.getBoundingClientRect().top + window.scrollY;
        const scrollDistance = Math.max(1, hero.offsetHeight - stage.clientHeight);
        const progress = clamp((window.scrollY - sectionTop) / scrollDistance, 0, 1);

        groups.forEach(({ phase, paths }) => {
            const start = phase * 0.165;
            const phaseProgress = clamp((progress - start) / 0.235, 0, 1);
            const offset = String(1 - phaseProgress);

            paths.forEach((path) => {
                path.style.strokeDashoffset = offset;
            });
        });

        if (progressLabel) {
            progressLabel.textContent = String(Math.round(progress * 100)).padStart(2, '0') + ' / 100';
        }

        const scale = 0.985 + progress * 0.015;
        const lift = (1 - progress) * 5;
        drawing.style.transform = 'translate3d(0, ' + lift + 'px, 0) scale(' + scale + ')';
    }

    function schedulePaint() {
        if (disposed || rafId) return;
        rafId = window.requestAnimationFrame(paint);
    }

    function onMotionPreferenceChange(event) {
        motionReduced = event.matches;
        schedulePaint();
    }

    function onVisibilityChange() {
        if (document.hidden) {
            if (rafId) window.cancelAnimationFrame(rafId);
            rafId = 0;
            return;
        }

        schedulePaint();
    }

    function dispose() {
        disposed = true;
        if (rafId) window.cancelAnimationFrame(rafId);
        window.removeEventListener('scroll', schedulePaint);
        window.removeEventListener('resize', schedulePaint);
        document.removeEventListener('visibilitychange', onVisibilityChange);
        motionPreference.removeEventListener?.('change', onMotionPreferenceChange);
    }

    window.addEventListener('scroll', schedulePaint, { passive: true });
    window.addEventListener('resize', schedulePaint, { passive: true });
    document.addEventListener('visibilitychange', onVisibilityChange);
    motionPreference.addEventListener?.('change', onMotionPreferenceChange);
    window.addEventListener('pagehide', dispose, { once: true });

    schedulePaint();
}

function bootHomeMotion() {
    initHeroScrollDrawing();
    initRevealAnimations();
    initGridLayouts();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootHomeMotion, { once: true });
} else {
    bootHomeMotion();
}

// If images fail, keep their card surface and visible product name intact.
document.querySelectorAll('.grid__image, .fz-product-card__image img').forEach((image) => {
    image.addEventListener('error', () => {
        image.classList.add('image-unavailable');
        image.removeAttribute('src');
        image.alt = image.alt || 'تصویر محصول در دسترس نیست';
    }, { once: true });
});


// Keep the gallery layout static and responsive. Hero progress itself is driven
// by one passive scroll listener and a single coalesced animation frame.
reduceMotionQuery.addEventListener?.('change', (event) => {
    reduceMotion = event.matches;

    if (reduceMotion) {
        document.querySelectorAll('.grid').forEach((grid) => grid.classList.add('grid--static'));
        document.querySelectorAll('.fz-reveal.is-entering').forEach((element) => {
            element.classList.remove('is-entering');
        });
        return;
    }

    initGridLayouts();
});
