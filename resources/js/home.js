/*
 * Native, dependency-free homepage interactions.
 * Horizontal product rails use scroll snapping; slide navigation remains
 * accessible when JavaScript is unavailable because only the active slide
 * is exposed by the controls.
 */

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const revealTargets = document.querySelectorAll('.home-body .fz-reveal, .home-body [data-farzin-reveal]');
if (!reduceMotion && 'IntersectionObserver' in window && revealTargets.length) {
    const revealObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-entering');
            entry.target.addEventListener('animationend', () => {
                entry.target.classList.remove('is-entering');
            }, { once: true });
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.08, rootMargin: '0px 0px -7% 0px' });

    revealTargets.forEach((element) => revealObserver.observe(element));
}

/*
 * Product carousels are native overflow containers. scrollIntoView(inline:
 * start) handles RTL and LTR browser scrollLeft implementations consistently.
 */
document.querySelectorAll('[data-fz-carousel]').forEach((carousel) => {
    const track = carousel.querySelector('[data-fz-carousel-track]');
    if (!track) return;

    const items = Array.from(track.children).filter((item) => item.getBoundingClientRect().width > 0);
    const previous = carousel.querySelector('[data-fz-carousel-prev]');
    const next = carousel.querySelector('[data-fz-carousel-next]');
    if (!items.length) return;

    const visibleIndex = () => {
        const trackRect = track.getBoundingClientRect();
        const index = items.findIndex((item) => {
            const rect = item.getBoundingClientRect();
            return rect.width > 0 && rect.right > trackRect.left + 4 && rect.left < trackRect.right - 4;
        });
        return Math.max(0, index);
    };

    const moveTo = (delta) => {
        const index = visibleIndex();
        const target = items[Math.min(items.length - 1, Math.max(0, index + delta))];
        target?.scrollIntoView({
            behavior: reduceMotion ? 'auto' : 'smooth',
            block: 'nearest',
            inline: 'start',
        });
    };

    previous?.addEventListener('click', () => moveTo(-1));
    next?.addEventListener('click', () => moveTo(1));

    const updateControls = () => {
        const trackRect = track.getBoundingClientRect();
        const first = items[0].getBoundingClientRect();
        const last = items[items.length - 1].getBoundingClientRect();
        if (previous) previous.disabled = first.left >= trackRect.left - 2 && first.right <= trackRect.right + 2;
        if (next) next.disabled = last.left >= trackRect.left - 2 && last.right <= trackRect.right + 2;

        // In RTL, DOM-first is physically rightmost. Buttons remain useful
        // through native touch/trackpad scrolling when only one item fits.
        if (items.length <= 1) {
            if (previous) previous.disabled = true;
            if (next) next.disabled = true;
        }
    };

    track.addEventListener('scroll', updateControls, { passive: true });
    window.addEventListener('resize', updateControls, { passive: true });
    updateControls();
});

/* Admin-managed hero slides: keyboard-accessible arrows and pagination dots. */
document.querySelectorAll('[data-fz-hero-slider]').forEach((slider) => {
    const slides = Array.from(slider.querySelectorAll('[data-fz-hero-slide]'));
    const dots = Array.from(slider.querySelectorAll('[data-fz-hero-dot]'));
    const previous = slider.querySelector('[data-fz-hero-prev]');
    const next = slider.querySelector('[data-fz-hero-next]');
    if (slides.length <= 1) return;

    let activeIndex = Math.max(0, slides.findIndex((slide) => !slide.hidden));

    const showSlide = (nextIndex) => {
        activeIndex = (nextIndex + slides.length) % slides.length;
        slides.forEach((slide, index) => {
            const active = index === activeIndex;
            slide.hidden = !active;
            slide.classList.toggle('is-active', active);
            slide.setAttribute('aria-hidden', active ? 'false' : 'true');
        });
        dots.forEach((dot, index) => {
            if (index === activeIndex) dot.setAttribute('aria-current', 'true');
            else dot.removeAttribute('aria-current');
        });
    };

    previous?.addEventListener('click', () => showSlide(activeIndex - 1));
    next?.addEventListener('click', () => showSlide(activeIndex + 1));
    dots.forEach((dot) => {
        dot.addEventListener('click', () => {
            const index = Number(dot.dataset.fzHeroDot);
            if (Number.isInteger(index) && index >= 0 && index < slides.length) showSlide(index);
        });
    });

    slider.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowRight') {
            event.preventDefault();
            showSlide(activeIndex - 1);
        } else if (event.key === 'ArrowLeft') {
            event.preventDefault();
            showSlide(activeIndex + 1);
        }
    });
});
