const reduceMotionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
const PREVIEW_DURATION_MS = 4500;
const MEDIA_START_TIMEOUT_MS = 12000;
const cards = [...document.querySelectorAll('a[data-farzin-gif-card]')];

if (cards.length > 0) {
    let activePreviewCard = null;
    let activePreviewTimer = 0;
    let previewSequence = 0;
    let activePreviewToken = 0;

    function clearPreviewTimer() {
        if (activePreviewTimer) {
            window.clearTimeout(activePreviewTimer);
            activePreviewTimer = 0;
        }
    }

    function stopPreview(card, resetTouch = false) {
        const ownsPreview = activePreviewCard === card;
        const isVisible = card.classList.contains('is-gif-previewing');
        const gif = card.querySelector('[data-farzin-category-gif]');
        const video = card.querySelector('[data-farzin-category-video]');
        const hasGifSource = Boolean(gif?.getAttribute('src'));
        const hasVideoSource = Boolean(video?.getAttribute('src'));

        if (ownsPreview) {
            clearPreviewTimer();
            activePreviewCard = null;
            activePreviewToken = 0;
        }

        card.classList.remove('is-gif-previewing');

        // Do not call load() for every inactive video card. Release only media
        // that was actually requested, keeping scrolling across the grid cheap.
        if (gif && (ownsPreview || isVisible || hasGifSource)) {
            gif.onload = null;
            gif.onerror = null;
            gif.removeAttribute('src');
        }

        if (video && (ownsPreview || isVisible || hasVideoSource)) {
            video.onplaying = null;
            video.onerror = null;
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
        const source = video?.dataset.farzinVideoSrc || gif?.dataset.farzinGifSrc;

        if ((!gif && !video) || !source) return false;

        // Do not restart the same preview because both pointerenter and focus
        // can fire for one interaction.
        if (activePreviewCard === card) return true;

        cards.forEach((other) => {
            if (other !== card) stopPreview(other, true);
        });

        clearPreviewTimer();
        activePreviewCard = card;
        const token = ++previewSequence;
        activePreviewToken = token;

        // Fail safe if the media URL is unavailable or the network never
        // reports readiness. The 4.5-second viewing window starts only after
        // the GIF is loaded or the video actually begins playing.
        activePreviewTimer = window.setTimeout(() => {
            if (activePreviewCard === card && activePreviewToken === token) {
                stopPreview(card, false);
            }
        }, MEDIA_START_TIMEOUT_MS);

        const showReadyPreview = () => {
            if (activePreviewCard !== card || activePreviewToken !== token) return;
            if (card.classList.contains('is-gif-previewing')) return;

            clearPreviewTimer();
            card.classList.add('is-gif-previewing');

            activePreviewTimer = window.setTimeout(() => {
                // Keep the touch-armed state until the next tap so a second
                // tap navigates even if the 4.5-second preview has just ended.
                if (activePreviewCard === card && activePreviewToken === token) {
                    stopPreview(card, false);
                }
            }, PREVIEW_DURATION_MS);
        };

        const failPreview = () => {
            if (activePreviewCard === card && activePreviewToken === token) {
                // Keep the touch state armed; the next tap can still navigate.
                stopPreview(card, false);
            }
        };

        if (video) {
            video.muted = true;
            video.loop = true;
            video.playsInline = true;
            video.onplaying = showReadyPreview;
            video.onerror = failPreview;
            video.setAttribute('src', source);
            video.load();

            const playRequest = video.play();
            if (playRequest && typeof playRequest.then === 'function') {
                playRequest.then(showReadyPreview).catch(failPreview);
            }
        } else if (gif) {
            gif.onload = showReadyPreview;
            gif.onerror = failPreview;
            gif.setAttribute('src', source);

            // Cached GIFs can already be complete by the time src is assigned.
            if (gif.complete && gif.naturalWidth > 0) showReadyPreview();
        }

        return true;
    }

    cards.forEach((card) => {
        if (!card.querySelector('[data-farzin-category-gif], [data-farzin-category-video]')) return;

        card.addEventListener('pointerdown', (event) => {
            card.dataset.farzinPointerType = event.pointerType || 'mouse';
        });

        card.addEventListener('pointerenter', (event) => {
            // Avoid implicit motion for touch and reduced-motion users.
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

            // Mouse and keyboard activation keep normal link semantics.
            if (!touchActivation || event.detail === 0) return;

            // First tap previews. A second tap navigates, even after preview
            // playback has naturally finished.
            if (card.dataset.farzinPreviewTapped === '1') return;

            event.preventDefault();
            card.dataset.farzinPreviewTapped = '1';
            startPreview(card);
        });
    });

    // Release active media when the card scrolls mostly out of view.
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

    window.addEventListener('pagehide', () => {
        cards.forEach((card) => stopPreview(card, true));
    }, { once: true });
}
