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
/* The homepage hero is now a static, product-led composition.
 * No scroll-bound SVG drawing or animation loop is needed.
 */


function initHomeProductSlider() {
    const slider = document.querySelector('[data-farzin-home-slider]');
    if (!slider) return;

    const slides = [...slider.querySelectorAll('[data-home-slide]')];
    if (slides.length < 2) return;

    const dots = [...slider.querySelectorAll('[data-home-slide-to]')];
    const count = slider.querySelector('[data-home-slide-count]');
    const previous = slider.querySelector('[data-home-slide-prev]');
    const next = slider.querySelector('[data-home-slide-next]');
    const toggle = slider.querySelector('[data-home-slide-toggle]');
    const toggleLabel = slider.querySelector('[data-home-slide-toggle-label]');
    const interval = Math.max(3000, Number(slider.dataset.autoplayMs) || 4000);

    let activeIndex = 0;
    let timer = null;
    let hovered = false;
    let focused = false;
    let inViewport = true;
    let userPaused = false;
    let motionAllowed = !reduceMotion;

    const formatNumber = (value) => new Intl.NumberFormat('fa-IR', {
        minimumIntegerDigits: 2,
        useGrouping: false,
    }).format(value);

    const stopTimer = () => {
        if (timer !== null) {
            window.clearTimeout(timer);
            timer = null;
        }
    };

    const canAutoplay = () => (
        motionAllowed
        && !userPaused
        && !hovered
        && !focused
        && inViewport
        && document.visibilityState === 'visible'
    );

    const updateToggle = () => {
        if (!toggle) return;
        toggle.classList.toggle('is-paused', userPaused || !motionAllowed);
        const label = userPaused ? 'ادامه پخش' : 'مکث';
        if (toggleLabel) toggleLabel.textContent = label;
        toggle.setAttribute('aria-label', userPaused ? 'ادامه پخش خودکار' : 'توقف پخش خودکار');
        toggle.title = userPaused ? 'ادامه پخش خودکار' : 'توقف پخش خودکار';
        const icon = toggle.querySelector('svg');
        if (icon) {
            icon.replaceChildren();
            const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
            path.setAttribute('d', userPaused ? 'M8 5l11 7-11 7z' : 'M8 5h3v14H8zM14 5h3v14h-3z');
            icon.append(path);
        }
    };

    const render = (index) => {
        activeIndex = (index + slides.length) % slides.length;

        slides.forEach((slide, slideIndex) => {
            const active = slideIndex === activeIndex;
            slide.hidden = !active;
            slide.setAttribute('aria-hidden', active ? 'false' : 'true');
        });

        dots.forEach((dot, dotIndex) => {
            const active = dotIndex === activeIndex;
            dot.classList.toggle('is-active', active);
            dot.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        if (count) {
            count.textContent = formatNumber(activeIndex + 1) + ' / ' + formatNumber(slides.length);
        }
    };

    const schedule = () => {
        stopTimer();
        if (!canAutoplay()) return;

        timer = window.setTimeout(() => {
            render(activeIndex + 1);
            schedule();
        }, interval);
    };

    const navigate = (index) => {
        render(index);
        schedule();
    };

    previous?.addEventListener('click', () => navigate(activeIndex - 1));
    next?.addEventListener('click', () => navigate(activeIndex + 1));
    dots.forEach((dot) => {
        dot.addEventListener('click', () => navigate(Number(dot.dataset.homeSlideTo) || 0));
    });

    toggle?.addEventListener('click', () => {
        userPaused = !userPaused;
        updateToggle();
        schedule();
    });

    slider.addEventListener('mouseenter', () => {
        hovered = true;
        stopTimer();
    });
    slider.addEventListener('mouseleave', () => {
        hovered = false;
        schedule();
    });
    slider.addEventListener('focusin', () => {
        focused = true;
        stopTimer();
    });
    slider.addEventListener('focusout', (event) => {
        if (event.relatedTarget instanceof Node && slider.contains(event.relatedTarget)) return;
        focused = false;
        schedule();
    });

    document.addEventListener('visibilitychange', schedule);

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(([entry]) => {
            inViewport = Boolean(entry?.isIntersecting);
            schedule();
        }, { threshold: 0.15 });
        observer.observe(slider);
    }

    reduceMotionQuery.addEventListener?.('change', (event) => {
        motionAllowed = !event.matches;
        if (!motionAllowed) stopTimer();
        updateToggle();
        schedule();
    });

    updateToggle();
    schedule();
}

function bootHomeMotion() {
    initHomeProductSlider();
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
