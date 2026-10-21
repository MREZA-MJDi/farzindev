const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
const touchLike = window.matchMedia('(hover: none), (pointer: coarse)').matches;

if (!reduceMotion) {
    const timers = new WeakMap();
    const cards = [...document.querySelectorAll('a[data-farzin-gif-card]')];

    function stopPreview(card) {
        const timer = timers.get(card);
        if (timer) {
            window.clearTimeout(timer);
            timers.delete(card);
        }
        card.classList.remove('is-gif-previewing');

        // Remove the animated source to stop decoding and release work when hidden.
        const gif = card.querySelector('[data-farzin-category-gif]');
        if (gif) gif.removeAttribute('src');
    }

    function startPreview(card) {
        const gif = card.querySelector('[data-farzin-category-gif]');
        if (!gif) return;

        cards.forEach((other) => {
            if (other !== card) stopPreview(other);
        });

        const existing = timers.get(card);
        if (existing) window.clearTimeout(existing);

        if (!gif.getAttribute('src')) {
            gif.setAttribute('src', gif.dataset.farzinGifSrc || '');
        }
        card.classList.add('is-gif-previewing');
        timers.set(card, window.setTimeout(() => stopPreview(card), 5000));
    }

    cards.forEach((card) => {
        if (!card.querySelector('[data-farzin-category-gif]')) return;

        if (canHover) {
            card.addEventListener('pointerenter', () => startPreview(card));
            card.addEventListener('pointerleave', () => stopPreview(card));
            card.addEventListener('focus', () => startPreview(card));
            card.addEventListener('blur', () => stopPreview(card));
        }

        // First tap previews for five seconds; a subsequent tap follows the category link.
        if (touchLike) {
            card.addEventListener('click', (event) => {
                if (card.dataset.farzinPreviewTapped === '1') return;
                event.preventDefault();
                card.dataset.farzinPreviewTapped = '1';
                startPreview(card);
            });
        }
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) cards.forEach(stopPreview);
    });
}
