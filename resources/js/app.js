import './bootstrap';
import './admin-image-editor.js';
import Alpine from 'alpinejs';
import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

window.Alpine = Alpine;
gsap.registerPlugin(ScrollTrigger);
window.gsap = gsap;
window.ScrollTrigger = ScrollTrigger;

Alpine.start();

/**
 * Farzin storefront motion system.
 * Motion is progressive enhancement: content remains visible if JS is disabled,
 * and reduced-motion preferences disable scroll and ambient animations.
 */
const initFarzinMotion = () => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (reduceMotion) {
        return;
    }

    const shopHero = document.querySelector('[data-farzin-shop-hero]');

    if (shopHero) {
        const heroCopy = gsap.utils.toArray('[data-shop-animate]', shopHero);
        const heroArt = shopHero.querySelector('[data-shop-art]');

        gsap.set(heroCopy, { autoAlpha: 0, y: 22 });

        gsap.timeline({
            scrollTrigger: {
                trigger: shopHero,
                start: 'top 82%',
                toggleActions: 'play none none none',
                once: true,
            },
        }).to(heroCopy, {
            autoAlpha: 1,
            y: 0,
            duration: 0.78,
            stagger: 0.085,
            ease: 'power3.out',
            clearProps: 'transform',
        });

        if (heroArt) {
            gsap.fromTo(
                heroArt,
                { autoAlpha: 0, x: -18, scale: 0.965 },
                {
                    autoAlpha: 1,
                    x: 0,
                    scale: 1,
                    duration: 1.15,
                    ease: 'power3.out',
                    scrollTrigger: {
                        trigger: shopHero,
                        start: 'top 82%',
                        toggleActions: 'play none none none',
                        once: true,
                    },
                },
            );

            const hood = heroArt.querySelector('.farzin-shop-hero__hood');
            const orbit = heroArt.querySelector('.farzin-shop-hero__art-orbit--inner');
            const sinkCard = heroArt.querySelector('.farzin-shop-hero__sink-card');
            const artLine = heroArt.querySelector('.farzin-shop-hero__art-note-line');

            if (hood) {
                gsap.to(hood, {
                    y: -7,
                    duration: 3.4,
                    repeat: -1,
                    yoyo: true,
                    ease: 'sine.inOut',
                });
            }

            if (orbit) {
                gsap.to(orbit, {
                    rotation: 8,
                    duration: 12,
                    repeat: -1,
                    yoyo: true,
                    transformOrigin: '50% 50%',
                    ease: 'sine.inOut',
                });
            }

            if (sinkCard) {
                gsap.to(sinkCard, {
                    y: -8,
                    rotation: 0.8,
                    duration: 3.6,
                    repeat: -1,
                    yoyo: true,
                    transformOrigin: '50% 50%',
                    ease: 'sine.inOut',
                });
            }

            if (artLine) {
                gsap.fromTo(
                    artLine,
                    { scaleX: 0.55, transformOrigin: 'right center' },
                    {
                        scaleX: 1,
                        duration: 1.4,
                        repeat: -1,
                        yoyo: true,
                        ease: 'sine.inOut',
                    },
                );
            }
        }
    }

    gsap.utils.toArray('[data-farzin-reveal]').forEach((card) => {
        if (card.dataset.farzinMotionReady === 'true') {
            return;
        }

        card.dataset.farzinMotionReady = 'true';

        gsap.fromTo(
            card,
            { autoAlpha: 0, y: 24 },
            {
                autoAlpha: 1,
                y: 0,
                duration: 0.68,
                ease: 'power3.out',
                clearProps: 'transform',
                scrollTrigger: {
                    trigger: card,
                    start: 'top 93%',
                    toggleActions: 'play none none none',
                    once: true,
                },
            },
        );
    });

    ScrollTrigger.refresh();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFarzinMotion, { once: true });
} else {
    initFarzinMotion();
}
