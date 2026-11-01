/*
 * Homepage enhancement only: reveal content as it enters the viewport.
 * Product selection and navigation remain native links. No carousel, quick
 * view, timer or extra interaction runtime is needed for the home page.
 */
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const revealTargets = document.querySelectorAll('.home-body .fz-reveal, .home-body [data-farzin-reveal]');

if (!reduceMotion && 'IntersectionObserver' in window && revealTargets.length) {
    const observer = new IntersectionObserver((entries, activeObserver) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;

            entry.target.classList.add('is-entering');
            entry.target.addEventListener('animationend', () => {
                entry.target.classList.remove('is-entering');
            }, { once: true });
            activeObserver.unobserve(entry.target);
        });
    }, {
        threshold: 0.08,
        rootMargin: '0px 0px -7% 0px',
    });

    revealTargets.forEach((element) => observer.observe(element));
}
