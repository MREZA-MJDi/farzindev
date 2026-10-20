import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

document.documentElement.classList.add('fz-js');

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
let pageMotionContext = null;

function initProductImages() {
    const images = document.querySelectorAll('[data-fz-product-image]');

    images.forEach((image) => {
        const frame = image.closest('[data-fz-image-frame]');
        if (!frame) return;

        const markLoaded = () => {
            frame.classList.remove('is-error');
            frame.classList.add('is-loaded');
        };

        const markFailed = () => {
            frame.classList.remove('is-loaded');
            frame.classList.add('is-error');
        };

        image.addEventListener('load', markLoaded, { once: true });
        image.addEventListener('error', markFailed, { once: true });

        if (image.complete) {
            if (image.naturalWidth > 0) {
                markLoaded();
            } else {
                markFailed();
            }
        }
    });
}

function initMobileNavigation() {
    const button = document.querySelector('[data-fz-menu-toggle]');
    const menu = document.querySelector('[data-fz-mobile-nav]');
    if (!button || !menu) return;

    const setOpen = (open) => {
        menu.hidden = !open;
        button.setAttribute('aria-expanded', String(open));
        button.setAttribute('aria-label', open ? 'بستن منو' : 'باز کردن منو');
    };

    button.addEventListener('click', () => {
        setOpen(menu.hidden);
    });

    menu.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', () => setOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setOpen(false);
    });

    document.addEventListener('click', (event) => {
        if (menu.hidden) return;
        if (menu.contains(event.target) || button.contains(event.target)) return;
        setOpen(false);
    });
}

function initScrollGrid() {
    const grid = document.querySelector('[data-fz-scroll-grid]');
    if (!grid || prefersReducedMotion.matches) return;

    const cards = gsap.utils.toArray('[data-fz-tile]', grid);
    if (cards.length < 2) return;

    pageMotionContext = gsap.context(() => {
        gsap.fromTo(
            cards,
            {
                y: (index) => 14 + (index % 4) * 5,
                rotateX: (index) => (index % 2 === 0 ? 3.5 : -3),
                rotateY: (index) => (index % 3 === 0 ? 1.8 : -1.2),
                scale: 0.988,
                transformOrigin: '50% 50%',
                force3D: true,
            },
            {
                y: 0,
                rotateX: 0,
                rotateY: 0,
                scale: 1,
                ease: 'none',
                stagger: { each: 0.012, from: 'center' },
                scrollTrigger: {
                    trigger: grid,
                    start: 'top 88%',
                    end: 'bottom 18%',
                    scrub: 0.6,
                    invalidateOnRefresh: true,
                },
            },
        );
    }, grid);

    window.addEventListener('load', () => ScrollTrigger.refresh(), { once: true });
}

function boot() {
    initProductImages();
    initMobileNavigation();
    initScrollGrid();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot, { once: true });
} else {
    boot();
}

document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        gsap.ticker.sleep();
        return;
    }

    gsap.ticker.wake();
    if (!prefersReducedMotion.matches) ScrollTrigger.refresh();
});

window.addEventListener('pagehide', () => {
    pageMotionContext?.revert();
    pageMotionContext = null;
}, { once: true });

prefersReducedMotion.addEventListener?.('change', (event) => {
    if (event.matches) {
        pageMotionContext?.revert();
        pageMotionContext = null;
    } else if (document.querySelector('[data-fz-scroll-grid]')) {
        initScrollGrid();
        ScrollTrigger.refresh();
    }
});
