import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const reduceMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
let reduceMotion = reduceMotionQuery.matches;
document.documentElement.classList.add('js');
let gridMotionContext = null;
const lowPower = (navigator.connection?.saveData ?? false)
    || (navigator.hardwareConcurrency > 0 && navigator.hardwareConcurrency <= 2);

// The product mosaic uses native links: clicking a card opens its product slug.

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

function groupGridItems(items, axis) {
    const groups = [];

    items.forEach((element) => {
        const rect = element.getBoundingClientRect();
        const center = axis === 'y'
            ? rect.top + rect.height / 2
            : rect.left + rect.width / 2;
        const tolerance = Math.max(8, (axis === 'y' ? rect.height : rect.width) * 0.22);
        let group = groups.find((candidate) => Math.abs(candidate.center - center) < tolerance);

        if (!group) {
            group = { center, items: [] };
            groups.push(group);
        }

        group.items.push(element);
        group.center = group.items.reduce((sum, item) => {
            const itemRect = item.getBoundingClientRect();
            return sum + (axis === 'y'
                ? itemRect.top + itemRect.height / 2
                : itemRect.left + itemRect.width / 2);
        }, 0) / group.items.length;
    });

    return groups.sort((a, b) => a.center - b.center);
}

function initGridAnimations() {
    gridMotionContext?.revert();
    gridMotionContext = null;

    const grids = [...document.querySelectorAll('.home-body .grid')];
    if (!grids.length) return;

    if (reduceMotion || lowPower) {
        grids.forEach((grid) => grid.classList.add('grid--static'));
        return;
    }

    grids.forEach((grid) => grid.classList.remove('grid--static'));

    // Six scroll-driven 3D patterns adapted from the supplied Codrops demo.
    // The movement is deterministic, so a refresh or resize never randomizes
    // product positions. Product photos themselves remain ordinary <img> tags.
    const layouts = [
        { columns: 3, ratio: 1.08, perspective: 1450 },
        { columns: 4, ratio: 0.88, perspective: 1750 },
        { columns: 3, ratio: 1.00, perspective: 1550 },
        { columns: 4, ratio: 0.86, perspective: 1800 },
        { columns: 3, ratio: 1.04, perspective: 1550 },
        { columns: 3, ratio: 1.00, perspective: 1650 },
    ];

    gridMotionContext = gsap.context(() => {
        grids.forEach((grid, index) => {
            const wrap = grid.querySelector('.grid-wrap');
            const items = [...grid.querySelectorAll('.grid__item')];
            const inners = items.map((item) => item.querySelector('.grid__item-inner')).filter(Boolean);

            if (!wrap || !items.length) return;

            const type = index % layouts.length;
            const layout = layouts[type];
            grid.dataset.motionType = String(type + 1);
            grid.style.setProperty('--grid-columns', String(layout.columns));
            grid.style.setProperty('--grid-item-ratio', String(layout.ratio));
            grid.style.setProperty('--perspective', `${layout.perspective}px`);

            // Measure the rendered grid rather than assuming a fixed row/column
            // arrangement; mobile CSS can change the number of columns.
            const rows = groupGridItems(items, 'y');
            const columns = groupGridItems(items, 'x');
            const rowMap = new Map();
            const columnMap = new Map();
            rows.forEach((row, rowIndex) => row.items.forEach((item) => rowMap.set(item, rowIndex)));
            columns.forEach((column, columnIndex) => column.items.forEach((item) => columnMap.set(item, columnIndex)));

            const rowOf = (item) => rowMap.get(item) ?? 0;
            const columnOf = (item) => columnMap.get(item) ?? 0;
            const timeline = gsap.timeline({
                scrollTrigger: {
                    trigger: wrap,
                    start: 'top bottom+=5%',
                    end: 'bottom top-=5%',
                    scrub: true,
                    invalidateOnRefresh: true,
                    fastScrollEnd: true,
                },
            });

            if (type === 0) {
                // Pattern 1 — tiles cross the viewport from opposing sides in depth.
                timeline.fromTo(wrap, {
                    rotationY: 16,
                    rotationZ: -2,
                }, {
                    rotationY: 0,
                    rotationZ: 0,
                    ease: 'none',
                }, 0);
                timeline.fromTo(items, {
                    xPercent: (i) => -145 - (i % 3) * 22,
                    z: (i) => -650 - (i % 4) * 110,
                    rotateY: (i) => (i % 2 ? -1 : 1) * 24,
                    scale: 0.76,
                    opacity: 0.14,
                }, {
                    xPercent: (i) => 120 + (i % 3) * 18,
                    z: 0,
                    rotateY: 0,
                    scale: 1,
                    opacity: 1,
                    stagger: { each: 0.035, from: 'center' },
                    ease: 'none',
                }, 0);
                timeline.fromTo(inners, { scale: 1.14 }, { scale: 1, ease: 'none' }, 0);
            } else if (type === 1) {
                // Pattern 2 — vertical travel with a tilted six-dimensional panel.
                timeline.fromTo(wrap, {
                    rotationX: 15,
                    rotationZ: -5,
                }, {
                    rotationX: -12,
                    rotationZ: 4,
                    ease: 'none',
                }, 0);
                timeline.fromTo(items, {
                    yPercent: (i) => 155 + rowOf(items[i]) * 42,
                    z: (i) => -550 - (i % 4) * 95,
                    rotationY: (i) => (columnOf(items[i]) % 2 ? -1 : 1) * 34,
                    rotationX: (i) => (rowOf(items[i]) % 2 ? -1 : 1) * 12,
                    scale: 0.78,
                    opacity: 0.12,
                    filter: 'brightness(1.35)',
                }, {
                    yPercent: (i) => -95 - rowOf(items[i]) * 30,
                    z: 0,
                    rotationY: 0,
                    rotationX: 0,
                    scale: 1,
                    opacity: 1,
                    filter: 'brightness(1)',
                    stagger: { each: 0.025, from: 'end' },
                    ease: 'none',
                }, 0);
                timeline.fromTo(inners, { scale: 1.12 }, { scale: 1, ease: 'none' }, 0);
            } else if (type === 2) {
                // Pattern 3 — tiles rise from a remote depth and resolve into a clean grid.
                timeline.fromTo(wrap, {
                    z: -120,
                    rotationX: 9,
                }, {
                    z: 90,
                    rotationX: 0,
                    ease: 'none',
                }, 0);
                timeline.fromTo(items, {
                    z: (i) => -1150 - (i % 5) * 115,
                    rotationX: (i) => -42 + (i % 3) * 8,
                    xPercent: (i) => (columnOf(items[i]) - 1) * 48,
                    yPercent: (i) => 95 + rowOf(items[i]) * 55,
                    scale: 0.66,
                    opacity: 0.06,
                    filter: 'brightness(0.35)',
                }, {
                    z: 0,
                    rotationX: 0,
                    xPercent: 0,
                    yPercent: 0,
                    scale: 1,
                    opacity: 1,
                    filter: 'brightness(1)',
                    stagger: { each: 0.03, from: 'edges' },
                    ease: 'none',
                }, 0);
                timeline.fromTo(inners, { scale: 1.18 }, { scale: 1, ease: 'none' }, 0);
            } else if (type === 3) {
                // Pattern 4 — rotating tiles fold into view from a diagonal angle.
                timeline.fromTo(wrap, {
                    rotationY: 12,
                    rotationX: 5,
                }, {
                    rotationY: 0,
                    rotationX: 0,
                    ease: 'none',
                }, 0);
                timeline.fromTo(items, {
                    transformOrigin: '50% 0%',
                    rotationX: -62,
                    rotationY: (i) => (i % 2 ? 1 : -1) * 38,
                    rotationZ: (i) => (i % 2 ? -1 : 1) * 12,
                    z: -620,
                    scale: 0.7,
                    opacity: 0.08,
                    filter: 'brightness(1.3)',
                }, {
                    rotationX: 0,
                    rotationY: 0,
                    rotationZ: 0,
                    z: 0,
                    scale: 1,
                    opacity: 1,
                    filter: 'brightness(1)',
                    stagger: { each: 0.04, from: 'center' },
                    ease: 'none',
                }, 0);
                timeline.fromTo(inners, { scale: 1.16 }, { scale: 1, ease: 'none' }, 0);
            } else if (type === 4) {
                // Pattern 5 — alternate rows slide in opposite directions.
                timeline.fromTo(wrap, { rotationX: 17 }, { rotationX: 5, ease: 'none' }, 0);
                timeline.fromTo(items, {
                    xPercent: (i) => (rowOf(items[i]) % 2 ? 1 : -1) * 105,
                    yPercent: (i) => (rowOf(items[i]) % 2 ? 1 : -1) * 26,
                    z: -390,
                    rotationY: (i) => (rowOf(items[i]) % 2 ? -1 : 1) * 24,
                    scale: 0.8,
                    opacity: 0.16,
                    filter: 'brightness(0.55)',
                }, {
                    xPercent: 0,
                    yPercent: 0,
                    z: 0,
                    rotationY: 0,
                    scale: 1,
                    opacity: 1,
                    filter: 'brightness(1)',
                    stagger: { each: 0.025, from: 'edges' },
                    ease: 'none',
                }, 0);
            } else {
                // Pattern 6 — cards rotate upward into the viewer like a product deck.
                timeline.fromTo(items, {
                    transformOrigin: '50% 200%',
                    rotationX: 58,
                    rotationY: (i) => (columnOf(items[i]) - 1) * 14,
                    yPercent: (i) => 190 + rowOf(items[i]) * 50,
                    z: -850,
                    scale: 0.55,
                    opacity: 0.06,
                }, {
                    rotationX: 0,
                    rotationY: 360,
                    yPercent: 0,
                    z: 0,
                    scale: 1,
                    opacity: 1,
                    stagger: { each: 0.035, from: 'start' },
                    ease: 'none',
                });
                timeline.fromTo(wrap, { rotationX: 10 }, { rotationX: 0, ease: 'none' }, 0);
                timeline.fromTo(inners, { scale: 1.18 }, { scale: 1, ease: 'none' }, 0);
            }
        });
    }, document.querySelector('.home-body') || document);

    ScrollTrigger.refresh();
}

function findVisibleCartTarget() {
    const links = [...document.querySelectorAll('#app a[aria-label="سبد خرید"]')];
    return links.find((link) => link.getClientRects().length > 0
        && window.getComputedStyle(link).visibility !== 'hidden') || links[0] || null;
}

function updateCartCounters(count) {
    const total = Math.max(0, Number(count) || 0);
    const links = [...document.querySelectorAll('#app a[aria-label="سبد خرید"]')];

    links.forEach((link) => {
        let badge = link.querySelector('[data-cart-count-badge]');

        if (total < 1) {
            badge?.remove();
            return;
        }

        if (!badge) {
            badge = document.createElement('span');
            badge.dataset.cartCountBadge = '';
            badge.className = 'fz-cart-count-badge';
            badge.setAttribute('aria-label', `${new Intl.NumberFormat('fa-IR').format(total)} کالا در سبد`);
            link.appendChild(badge);
        }

        badge.textContent = new Intl.NumberFormat('fa-IR').format(total);
        badge.setAttribute('aria-label', `${new Intl.NumberFormat('fa-IR').format(total)} کالا در سبد`);
    });
}

function showCartMessage(form, text, isError = false) {
    const message = form.querySelector('[data-cart-message]');
    if (!message) return;

    message.textContent = text;
    message.dataset.state = isError ? 'error' : 'success';
    message.classList.add('is-visible');
}

function animateProductToCart(sourceImage, cartTarget) {
    if (!cartTarget) return;

    // The cart count and status update without decorative motion when requested.
    if (reduceMotion) return;

    if (!sourceImage) {
        gsap.fromTo(cartTarget, { scale: 1 }, {
            scale: 1.08,
            duration: 0.14,
            repeat: 1,
            yoyo: true,
            ease: 'power1.inOut',
            clearProps: 'scale',
        });
        return;
    }

    const sourceRect = sourceImage.getBoundingClientRect();
    const targetRect = cartTarget.getBoundingClientRect();

    if (sourceRect.width < 1 || sourceRect.height < 1 || targetRect.width < 1) return;

    const flyer = sourceImage.cloneNode(true);
    const imageStyle = window.getComputedStyle(sourceImage);
    Object.assign(flyer.style, {
        position: 'fixed',
        left: `${sourceRect.left}px`,
        top: `${sourceRect.top}px`,
        width: `${sourceRect.width}px`,
        height: `${sourceRect.height}px`,
        maxWidth: 'none',
        margin: '0',
        padding: imageStyle.padding,
        objectFit: imageStyle.objectFit,
        objectPosition: imageStyle.objectPosition,
        borderRadius: '1rem',
        background: '#fff',
        boxShadow: '0 14px 45px rgba(17, 19, 22, .2)',
        pointerEvents: 'none',
        zIndex: '4000',
        transformOrigin: 'center center',
    });
    flyer.setAttribute('aria-hidden', 'true');
    document.body.appendChild(flyer);

    const deltaX = targetRect.left + targetRect.width / 2 - (sourceRect.left + sourceRect.width / 2);
    const deltaY = targetRect.top + targetRect.height / 2 - (sourceRect.top + sourceRect.height / 2);
    const lift = Math.min(76, Math.max(28, sourceRect.height * 0.18));

    gsap.timeline({
        onComplete: () => flyer.remove(),
    })
        .to(flyer, {
            x: deltaX * 0.46,
            y: deltaY * 0.38 - lift,
            scale: 0.7,
            rotation: -5,
            duration: 0.28,
            ease: 'power2.out',
        })
        .to(flyer, {
            x: deltaX,
            y: deltaY,
            scale: 0.08,
            rotation: 8,
            opacity: 0.08,
            duration: 0.42,
            ease: 'power3.in',
        }, '-=0.02');

    gsap.fromTo(cartTarget, { scale: 1 }, {
        scale: 1.1,
        duration: 0.14,
        repeat: 1,
        yoyo: true,
        ease: 'power1.inOut',
        clearProps: 'scale',
    });
}

function initCartFlyActions() {
    document.querySelectorAll('[data-cart-add-form]').forEach((form) => {
        if (form.dataset.cartFlyBound === 'true') return;
        form.dataset.cartFlyBound = 'true';

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            if (form.dataset.pending === 'true') return;

            const button = form.querySelector('[data-cart-fly-button]');
            const card = form.closest('[data-product-card]');
            const sourceImage = card?.querySelector('.fz-product-card__image img');
            const cartTarget = findVisibleCartTarget();

            form.dataset.pending = 'true';
            if (button) button.disabled = true;

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: new FormData(form),
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const payload = await response.json().catch(() => ({}));
                const errorDetail = Object.values(payload.errors || {}).flat()[0];

                if (!response.ok || !response.headers.get('content-type')?.includes('application/json')) {
                    throw new Error(errorDetail || payload.message || 'افزودن محصول به سبد انجام نشد؛ دوباره تلاش کنید.');
                }

                updateCartCounters(payload.cart_count);
                showCartMessage(form, payload.message || 'محصول به سبد خرید اضافه شد.');
                animateProductToCart(sourceImage, cartTarget);
            } catch (error) {
                showCartMessage(form, error instanceof Error ? error.message : 'خطا در افزودن محصول به سبد.', true);
            } finally {
                form.dataset.pending = 'false';
                if (button) button.disabled = false;
            }
        });
    });
}

function bootHomeMotion() {
    initRevealAnimations();
    initGridAnimations();
    initCartFlyActions();
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

const gridBreakpointQuery = window.matchMedia('(max-width: 760px)');
gridBreakpointQuery.addEventListener?.('change', () => {
    initGridAnimations();
    ScrollTrigger.refresh();
});

window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        bootHomeMotion();
        ScrollTrigger.refresh();
    }
});

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
