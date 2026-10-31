const slots = document.querySelectorAll('[data-blog-appearance-slot]');

const positions = {
    center: 'center center',
    top: 'center top',
    bottom: 'center bottom',
    left: 'left center',
    right: 'right center',
};

function updateSlotPreview(form) {
    const frame = form.querySelector('[data-blog-slot-live-frame]');
    const image = frame?.querySelector('[data-blog-slot-live-image]');
    const fallback = frame?.querySelector('[data-blog-slot-live-empty]');
    const fit = form.querySelector('[data-blog-preview-fit]')?.value || 'cover';
    const position = form.querySelector('[data-blog-preview-position]')?.value || 'center';
    const scaleInput = form.querySelector('[data-blog-preview-scale]');
    const scale = Math.min(135, Math.max(70, Number(scaleInput?.value) || 100));

    if (image) {
        image.style.objectFit = fit;
        image.style.objectPosition = positions[position] || positions.center;
        image.style.transform = `scale(${(scale / 100).toFixed(2)})`;
    }

    const scaleLabel = form.querySelector('[data-blog-preview-scale-label]');
    if (scaleLabel && scaleInput) scaleLabel.textContent = String(scale);

    return { frame, image, fallback };
}

slots.forEach((form) => {
    let previewUrl = null;
    const fileInput = form.querySelector('[data-image-editor-input]');
    const removeInput = form.querySelector('input[type="checkbox"][name$="_image_remove"]');

    const refresh = () => {
        const state = updateSlotPreview(form);
        if (!state.frame || !state.image) return;

        if (removeInput?.checked && !fileInput?.files?.length) {
            state.image.classList.add('hidden');
            state.fallback?.classList.remove('hidden');
            return;
        }

        if (state.image.getAttribute('src')) {
            state.image.classList.remove('hidden');
            state.fallback?.classList.add('hidden');
        }
    };

    form.querySelectorAll('[data-blog-preview-fit], [data-blog-preview-position], [data-blog-preview-scale]')
        .forEach((control) => {
            control.addEventListener('input', refresh, { passive: true });
            control.addEventListener('change', refresh);
        });

    fileInput?.addEventListener('change', () => {
        const file = fileInput.files?.[0];

        if (!file || !file.type.startsWith('image/')) return;

        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = URL.createObjectURL(file);

        const state = updateSlotPreview(form);
        if (state.image) {
            state.image.src = previewUrl;
            state.image.removeAttribute('srcset');
            state.image.classList.remove('hidden');
        }
        state.fallback?.classList.add('hidden');

        if (removeInput) removeInput.checked = false;
    });

    removeInput?.addEventListener('change', refresh);
    form.addEventListener('submit', () => {
        if (previewUrl) {
            URL.revokeObjectURL(previewUrl);
            previewUrl = null;
        }
    });

    refresh();
});

const containerWidth = document.querySelector('[data-blog-container-width]');
const containerOutput = document.querySelector('[data-blog-container-width-output]');
const containerPreview = document.querySelector('[data-blog-container-preview]');

function updateContainerPreview() {
    if (!containerWidth) return;

    const value = Math.min(1440, Math.max(1080, Number(containerWidth.value) || 1280));

    if (containerOutput) {
        containerOutput.textContent = `${value.toLocaleString('fa-IR')}px`;
    }

    if (containerPreview) {
        const visualWidth = 70 + ((value - 1080) / 360) * 30;
        containerPreview.style.width = `${visualWidth}%`;
        containerPreview.style.maxWidth = '100%';
        containerPreview.setAttribute('aria-label', `پیش‌نمایش عرض ${value} پیکسل`);
    }
}

containerWidth?.addEventListener('input', updateContainerPreview, { passive: true });
containerWidth?.addEventListener('change', updateContainerPreview);
updateContainerPreview();
