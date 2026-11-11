const select = document.querySelector('[data-home-technology-media-select]');

if (select) {
    const form = select.closest('form');
    const preview = form?.querySelector('[data-image-editor-existing-preview-for="page_home_technology_image"]');
    const placeholder = form?.querySelector('[data-image-editor-placeholder-for="page_home_technology_image"]');
    const fileInput = form?.querySelector('#page_home_technology_image');
    const cropPanel = form?.querySelector('[data-image-editor-panel-for="page_home_technology_image"]');

    const render = (url) => {
        if (url) {
            preview?.setAttribute('src', url);
            preview?.classList.remove('hidden');
            placeholder?.classList.add('hidden');
            placeholder?.classList.remove('flex');
        } else {
            preview?.removeAttribute('src');
            preview?.classList.add('hidden');
            placeholder?.classList.remove('hidden');
            placeholder?.classList.add('flex');
        }
    };

    select.addEventListener('change', () => {
        const option = select.selectedOptions[0];
        const url = option?.value ? option.dataset.imageUrl : select.dataset.currentImageUrl;

        if (select.value && fileInput) {
            fileInput.value = '';
            if (cropPanel) cropPanel.hidden = true;
        }

        render(url || '');
    });

    fileInput?.addEventListener('change', () => {
        if (fileInput.files?.length) {
            select.value = '';
        }
    });
}
