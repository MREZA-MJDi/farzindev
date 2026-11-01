const root = document.querySelector('[data-blog-motion]');

if (root && 'IntersectionObserver' in window) {
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!reducedMotion) {
        const targets = root.querySelectorAll('[data-blog-reveal]');

        targets.forEach((target) => target.classList.add('blog-reveal-ready'));

        const observer = new IntersectionObserver((entries, activeObserver) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;

                entry.target.classList.add('is-visible');
                activeObserver.unobserve(entry.target);
            });
        }, {
            threshold: 0.12,
            rootMargin: '0px 0px -36px 0px',
        });

        targets.forEach((target) => observer.observe(target));
    }
}
