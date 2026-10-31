/**
 * Lightweight storefront entrance enhancement.
 *
 * Content is never hidden while waiting for JavaScript or scroll events.
 * The old GSAP flow set sections to autoAlpha: 0 on every navigation, which
 * caused the page to appear empty and then rebuild itself as ScrollTrigger ran.
 * This version observes only explicitly marked elements and animates position,
 * never opacity or visibility. Normal document flow remains unchanged.
 */
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const selector = '[data-fz-reveal], [data-farzin-reveal], [data-shop-animate]';

if (!reduceMotion && 'IntersectionObserver' in window) {
    const targets = [...document.querySelectorAll(selector)];

    if (targets.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) {
                    return;
                }

                const element = entry.target;
                observer.unobserve(element);

                // Keep all text/images visible. Only add a small, compositor-
                // friendly translate when an element enters the viewport.
                if (typeof element.animate === 'function') {
                    try {
                        element.animate(
                            [
                                { translate: '0 8px' },
                                { translate: '0 0' },
                            ],
                            {
                                duration: 360,
                                easing: 'cubic-bezier(.22, 1, .36, 1)',
                                fill: 'none',
                            },
                        );
                    } catch {
                        // The content is already visible; animation is optional.
                    }
                }
            });
        }, {
            root: null,
            rootMargin: '0px 0px -5% 0px',
            threshold: 0.04,
        });

        targets.forEach((element) => observer.observe(element));
        window.addEventListener('pagehide', () => observer.disconnect(), { once: true });
    }
}
