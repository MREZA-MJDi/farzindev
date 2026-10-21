import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

// Admin navbar quick search is registered here instead of embedding a large
// JavaScript object in a Blade attribute. This avoids quote/HTML parser leaks
// and keeps the Blade template readable.
Alpine.data('adminQuickSearch', () => ({
    query: '',
    pages: [],

    init() {
        try {
            this.pages = JSON.parse(this.$el.dataset.pages || '[]');
        } catch (error) {
            console.error('Unable to initialize admin quick search.', error);
            this.pages = [];
        }
    },

    get matches() {
        const term = this.query.trim().toLocaleLowerCase();

        if (!term) {
            return [];
        }

        return this.pages
            .filter((page) => (
                (page.title + ' ' + page.terms).toLocaleLowerCase().includes(term)
            ))
            .slice(0, 6);
    },

    goToFirst() {
        if (this.matches.length > 0) {
            window.location.assign(this.matches[0].url);
        }
    },
}));

Alpine.start();

/**
 * Variant-backed products own stock at option level. Once one option from
 * every required type is selected, cap the quantity to the lowest selected
 * option stock instead of the unrelated parent product stock.
 */
window.addEventListener('product-variant-selected', () => {
    const form = document.querySelector('[data-product-cart-form]');

    if (!form) {
        return;
    }

    const options = [...form.querySelectorAll('input[name^="variants["]')];
    const requiredTypes = new Set(options.map((input) => input.dataset.variantType));
    const selectedByType = new Map();

    options.filter((input) => input.checked).forEach((input) => {
        selectedByType.set(
            input.dataset.variantType,
            Number(input.dataset.stock || 0),
        );
    });

    if (requiredTypes.size === 0 || selectedByType.size !== requiredTypes.size) {
        return;
    }

    const max = Math.min(...selectedByType.values());

    window.dispatchEvent(new CustomEvent('product-variant-availability-changed', {
        detail: { max },
    }));
});

/*
 * Load optional code only on pages that use it. Admin list pages no longer
 * download the image editor or GSAP animation runtime.
 */
if (document.querySelector('[data-image-editor-input]')) {
    import('./admin-image-editor.js').catch((error) => {
        console.error('Unable to load the product image editor.', error);
    });
}

if (document.querySelector('[data-farzin-gif-card]')) {
    import('./category-gif-preview.js').catch((error) => {
        console.error('Unable to initialize category GIF previews.', error);
    });
}

if (
    !document.body.classList.contains('home-body')
    && document.querySelector('.storefront-content')
) {
    import('./storefront-motion.js').catch((error) => {
        console.error('Unable to load optional storefront motion.', error);
    });
}
