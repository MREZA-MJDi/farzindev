import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const reduceMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
let reduceMotion = reduceMotionQuery.matches;
document.documentElement.classList.add('js');
let gridMotionContext = null;
const lowPower = window.matchMedia('(max-width: 760px)').matches
    || (navigator.connection?.saveData ?? false)
    || (navigator.hardwareConcurrency > 0 && navigator.hardwareConcurrency <= 4);

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
            const cartCount = document.querySelector('[data-cart-count]');
            if (cartCount && Number.isFinite(Number(payload.cart_count))) {
                cartCount.textContent = String(payload.cart_count);
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
 * Do not preload the gallery. Native lazy-loading allows the page to render
 * immediately, while GSAP animates only small groups as they enter the
 * viewport. Native scrolling is intentionally preserved; Lenis used to run
 * a perpetual RAF and make scrolling feel disconnected on slower devices.
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

function initGridAnimations() {
    gridMotionContext?.revert();
    gridMotionContext = null;

    const grids = [...document.querySelectorAll('.grid')];
    if (!grids.length) return;

    if (reduceMotion || lowPower) {
        grids.forEach((grid) => grid.classList.add('grid--static'));
        return;
    }

    grids.forEach((grid) => grid.classList.remove('grid--static'));

    const layouts = [
        { columns: 3, ratio: 1.13, perspective: 1250 },
        { columns: 4, ratio: .92, perspective: 1450 },
        { columns: 3, ratio: 1.0, perspective: 1350 },
        { columns: 4, ratio: .88, perspective: 1550 },
        { columns: 3, ratio: 1.06, perspective: 1250 },
        { columns: 3, ratio: 1.0, perspective: 1400 },
    ];

    gridMotionContext = gsap.context(() => {
        grids.forEach((grid, index) => {
            const wrap = grid.querySelector('.grid-wrap');
            const items = [...grid.querySelectorAll('.grid__item')];
            if (!wrap || !items.length) return;

            const layout = layouts[index % layouts.length];
            grid.style.setProperty('--grid-columns', String(layout.columns));
            grid.style.setProperty('--grid-item-ratio', String(layout.ratio));
            grid.style.setProperty('--perspective', String(layout.perspective) + 'px');

            const timeline = gsap.timeline({
                defaults: { ease: 'power2.out' },
                scrollTrigger: {
                    trigger: grid,
                    start: 'top 88%',
                    end: 'top 44%',
                    scrub: 0.75,
                    invalidateOnRefresh: true,
                },
            });

            // Restrained 3D transforms; layout and native scrolling remain intact.
            timeline.fromTo(items, {
                y: 18,
                z: -55,
                rotateX: index % 2 === 0 ? 3 : -3,
                rotateY: index % 2 === 0 ? -2.5 : 2.5,
                scale: .965,
                opacity: .82,
                transformOrigin: '50% 50%',
                force3D: true,
            }, {
                y: 0,
                z: 0,
                rotateX: 0,
                rotateY: 0,
                scale: 1,
                opacity: 1,
                stagger: { each: .025, from: index % 2 === 0 ? 'start' : 'end' },
                duration: 1,
                immediateRender: false,
                clearProps: 'transform',
            });

            timeline.fromTo(wrap, {
                rotationX: index % 2 === 0 ? 1.4 : -1.4,
                transformOrigin: '50% 50%',
            }, {
                rotationX: 0,
                duration: 1,
                immediateRender: false,
                clearProps: 'transform',
            }, 0);
        });
    }, document.body);

    ScrollTrigger.refresh();
}

function bootHomeMotion() {
    initRevealAnimations();
    initGridAnimations();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootHomeMotion, { once: true });
} else {
    bootHomeMotion();
}

// If images fail, keep their card surface and visible product name intact.
document.querySelectorAll('.grid__image, .fz-product-card__image img, .fz-hero-product img').forEach((image) => {
    image.addEventListener('error', () => {
        image.classList.add('image-unavailable');
        image.removeAttribute('src');
        image.alt = image.alt || 'تصویر محصول در دسترس نیست';
    }, { once: true });
});


// Layouts can settle after fonts and lazy images are ready. Refresh triggers once,
// not on every animation frame, and sleep while the tab is in the background.
window.addEventListener('load', () => ScrollTrigger.refresh(), { once: true });
if (document.fonts?.ready) {
    document.fonts.ready.then(() => ScrollTrigger.refresh()).catch(() => {});
}

document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        gsap.ticker.sleep();
        return;
    }

    gsap.ticker.wake();
    if (!reduceMotion) ScrollTrigger.refresh();
});

window.addEventListener('pagehide', () => {
    gridMotionContext?.revert();
    gridMotionContext = null;
    ScrollTrigger.getAll().forEach((trigger) => trigger.kill());
}, { once: true });

reduceMotionQuery.addEventListener?.('change', (event) => {
    reduceMotion = event.matches;

    if (reduceMotion) {
        gridMotionContext?.revert();
        gridMotionContext = null;
        document.querySelectorAll('.grid').forEach((grid) => grid.classList.add('grid--static'));
        document.querySelectorAll('.fz-reveal.is-entering').forEach((element) => {
            element.classList.remove('is-entering');
        });
        return;
    }

    initGridAnimations();
    ScrollTrigger.refresh();
});
