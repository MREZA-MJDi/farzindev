const reduceMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
const cards = [...document.querySelectorAll('a[data-farzin-gif-card]')];

if (cards.length > 0) {
    function stopPreview(card, resetTouch = false) {
        card.classList.remove('is-gif-previewing');

        const gif = card.querySelector('[data-farzin-category-gif]');
        if (gif) gif.removeAttribute('src');

        const video = card.querySelector('[data-farzin-category-video]');
        if (video) {
            video.pause();
            video.removeAttribute('src');
            video.load();
        }

        if (resetTouch) {
            delete card.dataset.farzinPreviewTapped;
            delete card.dataset.farzinPointerType;
        }
    }

    function startPreview(card) {
        const gif = card.querySelector('[data-farzin-category-gif]');
        const video = card.querySelector('[data-farzin-category-video]');

        if (!gif && !video) return;

        // Only one preview can consume bandwidth and decode video at a time.
        cards.forEach((other) => {
            if (other !== card) stopPreview(other, true);
        });

        if (video) {
            if (!video.getAttribute('src')) {
                video.setAttribute('src', video.dataset.farzinVideoSrc || '');
                video.load();
            }

            const playRequest = video.play();
            if (playRequest && typeof playRequest.catch === 'function') {
                playRequest.catch(() => {
                    // Autoplay policies may reject hover playback; the card remains navigable.
                });
            }
        } else if (gif && !gif.getAttribute('src')) {
            gif.setAttribute('src', gif.dataset.farzinGifSrc || '');
        }

        card.classList.add('is-gif-previewing');
    }

    cards.forEach((card) => {
        if (!card.querySelector('[data-farzin-category-gif], [data-farzin-category-video]')) return;

        card.addEventListener('pointerdown', (event) => {
            card.dataset.farzinPointerType = event.pointerType || 'mouse';
        });

        card.addEventListener('pointerenter', (event) => {
            // Intentional touch playback is handled by click; never auto-play on touch hover.
            if (event.pointerType === 'touch' || reduceMotionQuery.matches) return;
            startPreview(card);
        });

        card.addEventListener('pointerleave', (event) => {
            if (event.pointerType === 'touch' || card.dataset.farzinPreviewTapped === '1') return;
            stopPreview(card);
        });

        card.addEventListener('focus', () => {
            if (!reduceMotionQuery.matches) startPreview(card);
        });

        card.addEventListener('blur', () => {
            if (card.dataset.farzinPreviewTapped !== '1') stopPreview(card);
        });

        card.addEventListener('click', (event) => {
            const pointerType = card.dataset.farzinPointerType || 'mouse';
            const touchActivation = pointerType === 'touch'
                || (pointerType === 'pen' && window.matchMedia('(pointer: coarse)').matches);

            // Keyboard and mouse clicks navigate normally; desktop hover has already shown the motion.
            if (!touchActivation || event.detail === 0) return;

            // First tap plays the attached media; a second tap follows the category link.
            if (card.dataset.farzinPreviewTapped === '1') return;

            event.preventDefault();
            card.dataset.farzinPreviewTapped = '1';
            startPreview(card);
        });
    });

    // Release media on scroll-away, backgrounding, or interaction with another area.
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting || entry.intersectionRatio < 0.12) {
                    stopPreview(entry.target, true);
                }
            });
        }, { threshold: [0, 0.12] });

        cards.forEach((card) => observer.observe(card));
    }

    document.addEventListener('pointerdown', (event) => {
        if (event.target.closest('a[data-farzin-gif-card]')) return;

        cards.forEach((card) => {
            if (card.dataset.farzinPreviewTapped === '1') {
                stopPreview(card, true);
            }
        });
    });

    document.addEventListener('visibilitychange', () => {
        if (document.hidden) cards.forEach((card) => stopPreview(card, true));
    });
}
