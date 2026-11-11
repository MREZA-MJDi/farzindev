function setupBlogCover(root) {
    const fileInput = root.querySelector('[data-blog-cover-upload]');
    const mediaSelect = root.querySelector('[data-blog-cover-media-select]');
    const removeInput = root.querySelector('[data-blog-cover-remove]');
    const preview = root.querySelector('[data-image-editor-existing-preview]');
    const placeholder = root.querySelector('[data-image-editor-placeholder]');

    if (!preview || !placeholder) return;

    let objectUrl = null;

    const render = (url, alt = '') => {
        if (!url) {
            preview.removeAttribute('src');
            preview.alt = '';
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
            placeholder.classList.add('flex');
            return;
        }

        preview.src = url;
        preview.alt = alt;
        preview.classList.remove('hidden');
        placeholder.classList.add('hidden');
        placeholder.classList.remove('flex');
    };

    fileInput?.addEventListener('change', () => {
        const file = fileInput.files?.[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            fileInput.value = '';
            render('', '');
            return;
        }

        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = URL.createObjectURL(file);
        render(objectUrl, root.querySelector('input[name="cover_image_alt"]')?.value || file.name);

        if (mediaSelect) mediaSelect.value = '';
        if (removeInput) removeInput.checked = false;
    });

    mediaSelect?.addEventListener('change', () => {
        const option = mediaSelect.selectedOptions[0];
        if (!option?.value) return;

        if (fileInput) fileInput.value = '';
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = null;
        if (removeInput) removeInput.checked = false;
        render(option.dataset.mediaUrl || '', root.querySelector('input[name="cover_image_alt"]')?.value || option.textContent.trim());
    });

    removeInput?.addEventListener('change', () => {
        if (!removeInput.checked) return;

        if (fileInput) fileInput.value = '';
        if (mediaSelect) mediaSelect.value = '';
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = null;
        render('', '');
    });

    root.querySelector('input[name="cover_image_alt"]')?.addEventListener('input', (event) => {
        if (preview.getAttribute('src')) preview.alt = event.currentTarget.value;
    });

    window.addEventListener('beforeunload', () => {
        if (objectUrl) URL.revokeObjectURL(objectUrl);
    });

    if (mediaSelect?.value) {
        const selected = mediaSelect.selectedOptions[0];
        if (selected?.dataset.mediaUrl) {
            render(selected.dataset.mediaUrl, root.querySelector('input[name="cover_image_alt"]')?.value || selected.textContent.trim());
        }
    }
}

document.querySelectorAll('[data-blog-cover-editor]').forEach(setupBlogCover);
