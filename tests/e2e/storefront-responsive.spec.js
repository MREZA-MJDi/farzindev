import { expect, test } from '@playwright/test';

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

test('customer and admin endpoints do not expose private pages to guests', async ({ page }) => {
    for (const path of ['/checkout', '/account', '/admin', '/admin/blog-posts']) {
        const response = await page.goto(path, { waitUntil: 'domcontentloaded' });

        // The auth middleware may redirect to login or return a denial response,
        // but the guest must never receive the private page itself.
        expect(response, path + ' must return a document response').not.toBeNull();
        if (/\/login(?:\?.*)?$/.test(new URL(page.url()).pathname + new URL(page.url()).search)) {
            expect(response.status()).toBeLessThan(400);
        } else {
            expect([401, 403, 404]).toContain(response.status());
        }
    }
});
