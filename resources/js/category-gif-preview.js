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

        const gif = card.querySelector('[data-farzin-category-gif]');
        if (gif) gif.removeAttribute('src');

        const video = card.querySelector('[data-farzin-category-video]');
        if (video) {
            video.pause();
            video.removeAttribute('src');
            video.load();
        }
    }

    function startPreview(card) {
        const gif = card.querySelector('[data-farzin-category-gif]');
        const video = card.querySelector('[data-farzin-category-video]');
        if (!gif && !video) return;

        cards.forEach((other) => {
            if (other === card) return;

            stopPreview(other);
            // A different card starts a fresh touch interaction next time.
            delete other.dataset.farzinPreviewTapped;
        });

        const existing = timers.get(card);
        if (existing) window.clearTimeout(existing);

        if (video) {
            if (!video.getAttribute('src')) {
                video.setAttribute('src', video.dataset.farzinVideoSrc || '');
                video.load();
            }
            const play = video.play();
            if (play && typeof play.catch === 'function') {
                play.catch(() => {
                    // A blocked preview must not block navigation to the category.
                });
            }
        } else if (gif && !gif.getAttribute('src')) {
            gif.setAttribute('src', gif.dataset.farzinGifSrc || '');
        }

        card.classList.add('is-gif-previewing');
        timers.set(card, window.setTimeout(() => stopPreview(card), 5000));
    }

    cards.forEach((card) => {
        if (!card.querySelector('[data-farzin-category-gif], [data-farzin-category-video]')) return;

        if (canHover) {
            card.addEventListener('pointerenter', () => startPreview(card));
            card.addEventListener('pointerleave', () => stopPreview(card));
            card.addEventListener('focus', () => startPreview(card));
            card.addEventListener('blur', () => stopPreview(card));
        }

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
