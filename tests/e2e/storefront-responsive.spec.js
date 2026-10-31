import { expect, test } from '@playwright/test';
import { resolve } from 'node:path';

const viewports = [
    { width: 360, height: 740 },
    { width: 390, height: 844 },
    { width: 768, height: 900 },
    { width: 1023, height: 900 },
    { width: 1024, height: 900 },
    { width: 1440, height: 960 },
];

const publicRoutes = [
    '/',
    '/shop',
    '/categories',
    '/categories/hoods',
    '/product/farzin-hood-preview-01',
    '/blog',
    '/blog/choosing-kitchen-hood',
    '/about',
    '/contact',
    '/cart',
    '/login',
    '/register',
];

for (const viewport of viewports) {
    test('public routes keep the shared layout usable at ' + viewport.width + 'px', async ({ page }) => {
        await page.setViewportSize(viewport);

        for (const path of publicRoutes) {
            const response = await page.goto(path, { waitUntil: 'domcontentloaded' });

            expect(response, path + ' must return a document response').not.toBeNull();
            expect(response.status(), path + ' must not return an HTTP error').toBeLessThan(400);

            await expect(page.locator('.farzin-site-header'), path + ' header').toBeVisible();
            await expect(page.locator('.farzin-site-footer'), path + ' footer').toBeAttached();

            if (viewport.width < 1024) {
                await expect(page.locator('.farzin-mobile-nav'), path + ' mobile navigation').toBeVisible();
            } else {
                await expect(page.locator('.farzin-mobile-nav'), path + ' desktop hides mobile navigation').toBeHidden();
            }

            const overflow = await page.evaluate(() => (
                Math.max(document.documentElement.scrollWidth, document.body.scrollWidth)
                - document.documentElement.clientWidth
            ));

            expect(
                overflow,
                path + ' must not overflow horizontally at ' + viewport.width + 'px'
            ).toBeLessThanOrEqual(1);
        }
    });
}

test('mobile search and navigation controls remain usable', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    await page.locator('.farzin-mobile-nav button[aria-label="جستجو"]').click();
    await expect(page.locator('.farzin-search-overlay')).toBeVisible();
    await expect(page.locator('#global-search')).toBeFocused();

    // Click the actual close control inside the panel; the full-screen
    // backdrop sits underneath the panel and can be occluded by its content.
    await page.locator('.farzin-search-overlay__panel button[aria-label="بستن جستجو"]').click();
    await expect(page.locator('.farzin-search-overlay')).toBeHidden();

    await page.locator('.farzin-site-header button[aria-label="باز کردن منو"]').click();
    await expect(page.locator('[data-farzin-mobile-menu-panel]')).toBeVisible();
});

test('homepage category GIF preview starts on hover and the first touch tap', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    // Deterministic, network-free animated-media fixture. Load the same preview
    // module used by Vite after the fixture exists so this test checks real
    // pointer/touch event behavior rather than only checking the Blade markup.
    await page.evaluate(() => {
        const card = document.createElement('a');
        card.id = 'category-preview-test-fixture';
        card.href = '/categories';
        card.className = 'fz-category-card';
        card.setAttribute('data-farzin-gif-card', '');
        card.innerHTML = `
            <span class="fz-category-card__art">
                <img class="fz-category-card__image" alt="Category cover">
                <img
                    data-farzin-category-gif
                    data-farzin-gif-src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs="
                    alt=""
                >
            </span>
        `;
        document.body.appendChild(card);
    });
    const fixture = page.locator('#category-preview-test-fixture');

    await page.addScriptTag({ path: resolve(process.cwd(), 'resources/js/category-gif-preview.js') });

    await page.evaluate(() => {
        document.querySelector('#category-preview-test-fixture').dispatchEvent(new PointerEvent('pointerenter', {
            bubbles: false,
            pointerType: 'mouse',
        }));
    });

    await expect(fixture).toHaveClass(/is-gif-previewing/);

    // Reset the preview and exercise the mobile interaction: first tap arms
    // the preview; a second tap is allowed to follow the category link.
    await page.evaluate(() => {
        const card = document.querySelector('#category-preview-test-fixture');
        card.dispatchEvent(new Event('pointerleave'));
        card.dataset.farzinPreviewTapped = '';
        card.dispatchEvent(new PointerEvent('pointerdown', {
            bubbles: true,
            pointerType: 'touch',
        }));
        card.dispatchEvent(new MouseEvent('click', {
            bubbles: true,
            cancelable: true,
            detail: 1,
        }));
    });

    await expect(fixture).toHaveClass(/is-gif-previewing/);
    await expect(fixture).toHaveAttribute('data-farzin-preview-tapped', '1');
});

test('customer and admin endpoints redirect guests to login', async ({ page }) => {
    for (const path of ['/checkout', '/account', '/admin', '/admin/blog-posts']) {
        await page.goto(path, { waitUntil: 'domcontentloaded' });
        await expect(page).toHaveURL(/\/login(?:\?.*)?$/);
    }
});


test('guest navigation uses account icons without login or register text buttons', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 960 });
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const desktopHeader = page.locator('.farzin-site-header');
    await expect(desktopHeader.getByText('ورود', { exact: true })).toHaveCount(0);
    await expect(desktopHeader.getByText('ثبت‌نام', { exact: true })).toHaveCount(0);
    await expect(desktopHeader.locator('a[aria-label="حساب کاربری"]')).toBeVisible();

    await page.setViewportSize({ width: 390, height: 844 });
    await page.reload({ waitUntil: 'domcontentloaded' });

    const mobileAccount = page.locator('.farzin-mobile-nav a[aria-label="حساب کاربری"]');
    await expect(mobileAccount).toBeVisible();
    await expect(mobileAccount.locator('span')).toHaveCount(0);
    await expect(page.locator('.farzin-site-header').getByText('ورود', { exact: true })).toHaveCount(0);
    await expect(page.locator('.farzin-site-header').getByText('ثبت‌نام', { exact: true })).toHaveCount(0);
});

test('homepage image and product sections stay within the loading budget', async ({ page }) => {
    for (const viewport of [
        { width: 390, height: 844 },
        { width: 1440, height: 960 },
    ]) {
        await page.setViewportSize(viewport);
        await page.goto('/', { waitUntil: 'domcontentloaded' });

        await expect(page.locator('.fz-category-card')).toHaveCountLessThanOrEqual(6);
        await expect(page.locator('.grid__image')).toHaveCountLessThanOrEqual(6);
        await expect(page.locator('.fz-product-section .fz-product-card')).toHaveCountLessThanOrEqual(4);
        await expect(page.locator('.fz-new-section .fz-product-card')).toHaveCountLessThanOrEqual(4);
    }
});
