const form = document.querySelector('[data-page-content-form]');

if (form) {
    const state = form.querySelector('[data-page-content-save-state]');
    const saveButton = form.querySelector('[data-page-content-save-button]');
    const initialLabel = saveButton?.textContent?.trim() || 'ذخیره تغییرات';
    let dirty = false;

    const markDirty = () => {
        dirty = true;
        if (state) {
            state.textContent = 'تغییرات ذخیره‌نشده داری؛ برای اعمال در سایت، ذخیره را بزن.';
            state.dataset.state = 'dirty';
        }
        if (saveButton) saveButton.textContent = initialLabel;
    };

    form.addEventListener('input', markDirty);
    form.addEventListener('change', markDirty);

    form.addEventListener('submit', () => {
        if (state) {
            state.textContent = 'در حال آماده‌سازی و ذخیره تغییرات…';
            state.dataset.state = 'saving';
        }
        if (saveButton) {
            saveButton.textContent = 'در حال ذخیره…';
            saveButton.setAttribute('aria-busy', 'true');
        }
    });

    window.addEventListener('page-content-save-error', (event) => {
        if (state) {
            state.textContent = event.detail?.message || 'ذخیره انجام نشد؛ خطا را بررسی و دوباره تلاش کن.';
            state.dataset.state = 'error';
        }
        if (saveButton) {
            saveButton.textContent = initialLabel;
            saveButton.removeAttribute('aria-busy');
        }
    });

    window.addEventListener('pageshow', () => {
        if (!dirty && state && state.dataset.state !== 'error') {
            state.textContent = 'وضعیت: بدون تغییر ذخیره‌نشده';
            state.dataset.state = 'clean';
        }
    });
}
