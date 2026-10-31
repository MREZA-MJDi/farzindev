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

            const footerLayout = await page.locator('.farzin-site-footer').evaluate((footer) => {
                const container = footer.querySelector(':scope > .livora-container');
                const main = footer.querySelector('.farzin-site-footer__main');
                const footerRect = footer.getBoundingClientRect();
                const containerRect = container.getBoundingClientRect();
                const mainStyle = window.getComputedStyle(main);
                const footerHeadings = Array.from(footer.querySelectorAll('.farzin-site-footer__heading'));
                const headingStyles = footerHeadings.map((heading) => {
                    const style = window.getComputedStyle(heading);
                    return [style.fontSize, style.fontWeight, style.lineHeight, style.letterSpacing].join('|');
                });
                const mainHeadingTops = Array.from(main.querySelectorAll('.farzin-site-footer__heading'))
                    .map((heading) => heading.getBoundingClientRect().top);
                const trustHeadingTops = Array.from(footer.querySelectorAll('.farzin-site-footer__trust .farzin-site-footer__heading'))
                    .map((heading) => heading.getBoundingClientRect().top);

                return {
                    footerWidth: footerRect.width,
                    viewportWidth: document.documentElement.clientWidth,
                    containerWidth: containerRect.width,
                    containerLeft: containerRect.left,
                    containerRight: containerRect.right,
                    mainClientWidth: main.clientWidth,
                    mainScrollWidth: main.scrollWidth,
                    mainPaddingBlock: [Number.parseFloat(mainStyle.paddingTop), Number.parseFloat(mainStyle.paddingBottom)],
                    trustPaddingBlock: (() => {
                        const trust = footer.querySelector('.farzin-site-footer__trust');
                        const trustStyle = window.getComputedStyle(trust);
                        return [Number.parseFloat(trustStyle.paddingTop), Number.parseFloat(trustStyle.paddingBottom)];
                    })(),
                    columns: mainStyle.gridTemplateColumns.trim().split(' ').filter(Boolean).length,
                    footerHeadingCount: footerHeadings.length,
                    footerHeadingStyleCount: new Set(headingStyles).size,
                    mainHeadingTopSpread: mainHeadingTops.length ? Math.max(...mainHeadingTops) - Math.min(...mainHeadingTops) : 999,
                    trustHeadingTopSpread: trustHeadingTops.length ? Math.max(...trustHeadingTops) - Math.min(...trustHeadingTops) : 999,
                };
            });

            expect(
                Math.abs(footerLayout.footerWidth - footerLayout.viewportWidth),
                path + ' footer surface must span the viewport'
            ).toBeLessThanOrEqual(1);
            expect(
                footerLayout.containerWidth,
                path + ' footer content must stay within the shared 1280px grid'
            ).toBeLessThanOrEqual(1281);
            expect(
                Math.abs(footerLayout.containerLeft - (footerLayout.viewportWidth - footerLayout.containerRight)),
                path + ' footer content must remain horizontally centered'
            ).toBeLessThanOrEqual(1);
            expect(
                footerLayout.mainScrollWidth - footerLayout.mainClientWidth,
                path + ' footer columns must not overflow their grid'
            ).toBeLessThanOrEqual(1);

            const expectedFooterColumns = viewport.width < 768 ? 1 : (viewport.width < 1200 ? 2 : 4);
            expect(
                footerLayout.columns,
                path + ' footer must use the expected responsive column count'
            ).toBe(expectedFooterColumns);

            expect(footerLayout.footerHeadingCount, path + ' footer must expose all six aligned section headings').toBe(6);
            expect(Math.max(...footerLayout.mainPaddingBlock), path + ' footer main section must use the shared compact vertical scale').toBeLessThanOrEqual(52);
            expect(Math.max(...footerLayout.trustPaddingBlock), path + ' footer trust strip must not add excessive vertical space').toBeLessThanOrEqual(16);
            expect(footerLayout.footerHeadingStyleCount, path + ' footer headings must share one typography scale').toBe(1);
            if (viewport.width >= 1200) {
                expect(footerLayout.mainHeadingTopSpread, path + ' desktop main footer headings must share one row').toBeLessThanOrEqual(1);
                expect(footerLayout.trustHeadingTopSpread, path + ' desktop trust headings must share one row').toBeLessThanOrEqual(1);
            }

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

test('global mobile search is available and usable on every public page', async ({ page }) => {
    await page.setViewportSize({ width: 360, height: 640 });

    for (const path of publicRoutes) {
        await page.goto(path, { waitUntil: 'domcontentloaded' });

        const searchTrigger = page.locator('.farzin-mobile-nav button[aria-label="جستجو"]');
        await expect(searchTrigger, path + ' mobile search trigger').toBeVisible();
        await searchTrigger.click();

        await expect(page.locator('.farzin-search-overlay'), path + ' global search overlay').toBeVisible();
        await expect(page.locator('#global-search'), path + ' search input').toBeFocused();

        const searchOverflow = await page.locator('.farzin-search-overlay__panel').evaluate((panel) => (
            panel.scrollWidth - panel.clientWidth
        ));
        expect(searchOverflow, path + ' search panel must fit the mobile viewport').toBeLessThanOrEqual(1);

        await page.locator('.farzin-search-overlay__panel button[aria-label="بستن جستجو"]').click();
        await expect(page.locator('.farzin-search-overlay')).toBeHidden();
    }

    await page.goto('/', { waitUntil: 'domcontentloaded' });
    await page.locator('.farzin-site-header button[aria-label="باز کردن منو"]').click();
    await expect(page.locator('[data-farzin-mobile-menu-panel]')).toBeVisible();
});

test('shop mobile filters apply together, scroll safely and preserve the selected sort', async ({ page }) => {
    await page.setViewportSize({ width: 360, height: 640 });
    await page.goto('/shop?sort=popular', { waitUntil: 'domcontentloaded' });

    await page.getByRole('button', { name: 'فیلترها' }).click();

    const drawer = page.locator('[data-farzin-filter-drawer]');
    await expect(drawer).toBeVisible();
    await expect(drawer).toHaveCount(1);

    const initialUrl = page.url();
    const category = drawer.locator('input[name="category"]').first();
    await expect(category).toBeVisible();
    await category.check();
    await drawer.locator('input[name="installment"]').check();
    await drawer.locator('input[name="search"]').fill('هود');

    // Mobile controls stay in the drawer until Apply is pressed.
    expect(page.url()).toBe(initialUrl);

    await drawer.getByRole('button', { name: 'اعمال فیلتر' }).click();
    await expect(page).toHaveURL(/\/shop\?/);
    await expect(page).toHaveURL(/sort=popular/);
    await expect(page).toHaveURL(/search=%D9%87%D9%88%D8%AF|search=هود/);
    await expect(page).toHaveURL(/category=/);
    await expect(page).toHaveURL(/installment=1/);
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

test('homepage CTA meets the shared footer at the same spacing used by interior pages', async ({ page }) => {
    for (const viewport of [
        { width: 390, height: 844 },
        { width: 768, height: 900 },
        { width: 1440, height: 960 },
    ]) {
        await page.setViewportSize(viewport);

        // On the homepage, the CTA must not add a second bottom gap on top
        // of the shared footer margin. Interior pages keep that same margin.
        await page.goto('/', { waitUntil: 'domcontentloaded' });
        await expect(page.locator('.farzin-site-footer')).toBeVisible();

        const homeSpacing = await page.evaluate(() => {
            const cta = document.querySelector('.fz-bottom-cta');
            const ctaPanel = document.querySelector('.fz-bottom-cta__inner');
            const footer = document.querySelector('.farzin-site-footer');
            const ctaStyle = window.getComputedStyle(cta);
            const footerStyle = window.getComputedStyle(footer);

            return {
                ctaPaddingBottom: Number.parseFloat(ctaStyle.paddingBlockEnd),
                panelToFooter: footer.getBoundingClientRect().top - ctaPanel.getBoundingClientRect().bottom,
                footerMarginTop: Number.parseFloat(footerStyle.marginBlockStart),
            };
        });

        expect(
            homeSpacing.ctaPaddingBottom,
            'homepage CTA must leave the final separation to the shared footer'
        ).toBeLessThanOrEqual(1);
        expect(
            Math.abs(homeSpacing.panelToFooter - homeSpacing.footerMarginTop),
            'homepage CTA-to-footer gap must equal the shared footer margin'
        ).toBeLessThanOrEqual(1);

        await page.goto('/shop', { waitUntil: 'domcontentloaded' });
        const shopSpacing = await page.evaluate(() => {
            const main = document.querySelector('#main-content');
            const footer = document.querySelector('.farzin-site-footer');
            const footerStyle = window.getComputedStyle(footer);

            return {
                mainToFooter: footer.getBoundingClientRect().top - main.getBoundingClientRect().bottom,
                footerMarginTop: Number.parseFloat(footerStyle.marginBlockStart),
            };
        });

        expect(
            Math.abs(shopSpacing.mainToFooter - shopSpacing.footerMarginTop),
            'interior page must use the same shared footer margin'
        ).toBeLessThanOrEqual(1);
        expect(shopSpacing.footerMarginTop).toBeGreaterThanOrEqual(24);
    }
});

test('shared footer keeps the same typography, content and grid on every storefront page', async ({ page }) => {
    const snapshots = [];

    for (const path of ['/', '/shop', '/categories/hoods', '/contact']) {
        await page.goto(path, { waitUntil: 'domcontentloaded' });

        snapshots.push(await page.locator('.farzin-site-footer').evaluate((footer) => {
            const main = footer.querySelector('.farzin-site-footer__main');
            const heading = footer.querySelector('.farzin-site-footer__heading');
            const style = window.getComputedStyle(footer);
            const headingStyle = window.getComputedStyle(heading);
            return {
                text: footer.innerText.replace(/\s+/g, ' ').trim(),
                direction: style.direction,
                fontFamily: style.fontFamily,
                fontSize: style.fontSize,
                lineHeight: style.lineHeight,
                color: style.color,
                backgroundColor: style.backgroundColor,
                columns: window.getComputedStyle(main).gridTemplateColumns,
                headingFont: headingStyle.font,
                headingColor: headingStyle.color,
                headingCount: footer.querySelectorAll('.farzin-site-footer__heading').length,
            };
        }));
    }

    for (const snapshot of snapshots.slice(1)) {
        expect(snapshot).toEqual(snapshots[0]);
    }
    expect(snapshots[0].headingCount).toBe(6);
});

test('storefront sections never disappear while entrance motion initializes', async ({ page }) => {
    for (const path of ['/categories/hoods', '/contact']) {
        await page.goto(path, { waitUntil: 'domcontentloaded' });

        const motionState = await page.locator(
            '.storefront-content [data-fz-reveal], .storefront-content [data-farzin-reveal], .storefront-content [data-shop-animate]'
        ).evaluateAll((elements) => elements.map((element) => {
            const style = window.getComputedStyle(element);
            return {
                opacity: style.opacity,
                visibility: style.visibility,
                display: style.display,
            };
        }));

        expect(motionState.length, path + ' should cover explicit motion targets').toBeGreaterThan(0);
        expect(
            motionState.filter((item) => item.opacity !== '1' || item.visibility !== 'visible' || item.display === 'none'),
            path + ' motion targets must stay visible during page entry'
        ).toEqual([]);
        expect(await page.evaluate(() => typeof window.gsap)).toBe('undefined');
    }
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

    await page.locator('.farzin-site-header button[aria-label="باز کردن منو"]').click();
    const mobileMenu = page.locator('[data-farzin-mobile-menu-panel]');
    await expect(mobileMenu).toBeVisible();
    await expect(mobileMenu.getByText('ورود', { exact: true })).toHaveCount(0);
    await expect(mobileMenu.getByText('ثبت‌نام', { exact: true })).toHaveCount(0);
    await expect(mobileMenu.getByRole('link', { name: 'سبد خرید' })).toBeVisible();
});

test('homepage image and product sections stay within the loading budget', async ({ page }) => {
    for (const viewport of [
        { width: 390, height: 844 },
        { width: 1440, height: 960 },
    ]) {
        await page.setViewportSize(viewport);
        await page.goto('/', { waitUntil: 'domcontentloaded' });

        expect(await page.locator('.fz-category-card').count()).toBeLessThanOrEqual(4);
        expect(await page.locator('.grid__image').count()).toBeLessThanOrEqual(4);
        expect(await page.locator('.fz-product-section .fz-product-card').count()).toBeLessThanOrEqual(4);
        expect(await page.locator('.fz-new-section .fz-product-card').count()).toBeLessThanOrEqual(4);

        const homepageProductCards = await page.locator('.fz-product-section .fz-product-card, .fz-new-section .fz-product-card').count();
        expect(homepageProductCards, 'homepage must never display more than eight full product cards').toBeLessThanOrEqual(8);
        await expect(page.locator('.fz-category-section .fz-text-link')).toHaveAttribute('href', /\/categories(?:\?|$)/);
        await expect(page.locator('.fz-product-section .fz-text-link')).toHaveAttribute('href', /\/shop(?:\?|$)/);
    }
});


test('admin mobile quick search and list filters remain reachable without horizontal overflow', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/login', { waitUntil: 'domcontentloaded' });

    const loginForm = page.locator('form').filter({ has: page.locator('input[name="phone"]') });
    await loginForm.locator('input[name="phone"]').fill('browser-admin@example.test');
    await loginForm.locator('input[name="password"]').fill('browser-smoke-password');
    await loginForm.locator('button[type="submit"]').click();
    await expect(page).toHaveURL(/\/admin(?:\/dashboard)?(?:\?.*)?$/);

    const mobileSearchTrigger = page.locator('.admin-navbar button[aria-label="جست‌وجوی پنل"]');
    await expect(mobileSearchTrigger).toBeVisible();
    await mobileSearchTrigger.click();

    const mobileSearch = page.locator('#admin-quick-search-mobile');
    await expect(mobileSearch).toBeVisible();
    await expect(mobileSearch).toBeFocused();
    await mobileSearch.fill('محصولات');
    await expect(page.locator('#admin-quick-search-mobile-results a').first()).toBeVisible();

    for (const route of [
        { path: '/admin/orders', input: 'input[name="search"]' },
        { path: '/admin/customers', input: 'input[name="search"]' },
        { path: '/admin/blog-posts', input: 'input[name="q"]' },
    ]) {
        await page.goto(route.path, { waitUntil: 'domcontentloaded' });
        const filterInput = page.locator('.admin-main ' + route.input);
        await expect(filterInput, route.path + ' mobile filter input').toBeVisible();

        const fontSize = await filterInput.evaluate((element) => Number.parseFloat(getComputedStyle(element).fontSize));
        expect(fontSize, route.path + ' mobile input must avoid automatic browser zoom').toBeGreaterThanOrEqual(16);

        const overflow = await page.evaluate(() => (
            Math.max(document.documentElement.scrollWidth, document.body.scrollWidth)
            - document.documentElement.clientWidth
        ));
        expect(overflow, route.path + ' must not overflow horizontally').toBeLessThanOrEqual(1);
    }
});
