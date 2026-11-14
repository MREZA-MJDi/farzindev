const root = document.querySelector('[data-home-hero-preview]');

if (root) {
    const form = root.closest('form');

    const bindTextPreview = (fieldId, previewSelector, fallback) => {
        const field = form?.querySelector('#' + fieldId);
        const preview = root.querySelector(previewSelector);

        if (!field || !preview) return;

        const update = () => {
            const nextValue = field.value.trim();
            preview.textContent = nextValue || fallback;
        };

        field.addEventListener('input', update);
        field.addEventListener('change', update);
        update();
    };

    bindTextPreview(
        'page_home_hero_title',
        '[data-home-hero-preview-title]',
        'هود، اجاق گاز و سینک، آماده‌ی نصب در آشپزخانه‌ی شما',
    );
    bindTextPreview(
        'page_home_hero_description',
        '[data-home-hero-preview-description]',
        'مدل‌های موجود فرجی‌هوم را با تصویر، قیمت و مشخصات واقعی بررسی کنید.',
    );
    bindTextPreview(
        'page_home_hero_primary_label',
        '[data-home-hero-preview-cta]',
        'مشاهده محصولات ←',
    );
}
