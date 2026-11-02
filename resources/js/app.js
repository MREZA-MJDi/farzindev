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

    // Reflect the exact combined variant adjustment in the visible price.
    const priceSummary = document.querySelector('[data-product-price-summary]');
    const currentPrice = priceSummary?.querySelector('[data-product-current-price]');
    const priceRange = priceSummary?.querySelector('[data-product-price-range]');

    if (priceSummary && currentPrice && priceRange) {
        const basePrice = Number(priceSummary.dataset.basePrice || 0);
        const adjustment = options
            .filter((input) => input.checked)
            .reduce((total, input) => total + Number(input.dataset.priceAdjustment || 0), 0);
        const nextPrice = Math.max(0, basePrice + adjustment);

        currentPrice.textContent = new Intl.NumberFormat('fa-IR', {
            maximumFractionDigits: 0,
        }).format(nextPrice);
        currentPrice.hidden = false;
        priceRange.hidden = true;
    }
});

/*
 * Load only functional modules on pages that need them. Storefront and blog
 * content is server-rendered and never waits for reveal/scroll JavaScript.
 */
if (document.querySelector('[data-image-editor-input]')) {
    import('./admin-image-editor.js').catch((error) => {
        console.error('Unable to load the product image editor.', error);
    });
}

if (document.querySelector('[data-blog-appearance]')) {
    import('./admin-blog-appearance.js').catch((error) => {
        console.error('Unable to initialize blog appearance previews.', error);
    });
}

if (document.querySelector('[data-blog-rich-editor]')) {
    import('./admin-blog-editor.js').catch((error) => {
        console.error('Unable to load the blog editor.', error);
    });
}

if (document.querySelector('[data-farzin-gif-card]')) {
    import('./category-gif-preview.js').catch((error) => {
        console.error('Unable to initialize category GIF previews.', error);
    });
}

