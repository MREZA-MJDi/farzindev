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
                const nav = footer.querySelector('.farzin-footer-editorial__nav');
                const contact = footer.querySelector('.farzin-footer-editorial__contact');
                const footerRect = footer.getBoundingClientRect();
                const containerRect = container.getBoundingClientRect();
                const anchors = Array.from(footer.querySelectorAll('a'));

                return {
                    footerWidth: footerRect.width,
                    viewportWidth: document.documentElement.clientWidth,
                    containerWidth: containerRect.width,
                    containerLeft: containerRect.left,
                    containerRight: containerRect.right,
                    containerClientWidth: container.clientWidth,
                    containerScrollWidth: container.scrollWidth,
                    navLinks: nav.querySelectorAll('a').length,
                    contactExists: Boolean(contact),
                    hasBrand: Boolean(footer.querySelector('.farzin-footer-editorial__logo')),
                    hasNoPlaceholderLinks: anchors.every((anchor) => {
                        const href = anchor.getAttribute('href');
                        return Boolean(href) && href !== '#';
                    }),
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
                footerLayout.containerScrollWidth - footerLayout.containerClientWidth,
                path + ' footer content must not overflow its container'
            ).toBeLessThanOrEqual(1);
            expect(footerLayout.navLinks, path + ' footer must retain the main storefront links').toBeGreaterThanOrEqual(5);
            expect(footerLayout.contactExists, path + ' footer must expose its dashboard-driven contact area').toBeTruthy();
            expect(footerLayout.hasBrand, path + ' footer must expose the Farzin brand link').toBeTruthy();
            expect(footerLayout.hasNoPlaceholderLinks, path + ' footer must not contain empty or # social links').toBeTruthy();

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

test('homepage category cards link to their category without autoplay media', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const cards = page.locator('.fz-category-card--editorial');
    const count = await cards.count();

    expect(count).toBeLessThanOrEqual(3);
    expect(count).toBeGreaterThan(0);

    const categoryCards = await cards.evaluateAll((items) => items.map((card) => ({
        href: card.getAttribute('href'),
        hasVideo: Boolean(card.querySelector('video')),
        hasAnimatedImage: Boolean(card.querySelector('[data-farzin-category-gif], [data-farzin-category-video]')),
        hasLegacyPreviewHandler: card.hasAttribute('data-farzin-gif-card'),
    })));

    for (const card of categoryCards) {
        expect(card.href).toMatch(/^\/categories\/[^/]+$/);
        expect(card.hasVideo).toBe(false);
        expect(card.hasAnimatedImage).toBe(false);
        expect(card.hasLegacyPreviewHandler).toBe(false);
    }
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

test('shared footer keeps the same brand, navigation and dashboard-driven contacts on every storefront page', async ({ page }) => {
    const snapshots = [];

    for (const path of ['/', '/shop', '/categories/hoods', '/contact']) {
        await page.goto(path, { waitUntil: 'domcontentloaded' });

        snapshots.push(await page.locator('.farzin-site-footer').evaluate((footer) => {
            const logo = footer.querySelector('.farzin-footer-editorial__logo');
            const nav = footer.querySelector('.farzin-footer-editorial__nav');
            const contact = footer.querySelector('.farzin-footer-editorial__contact');
            const style = window.getComputedStyle(footer);
            const navStyle = window.getComputedStyle(nav);
            const logoStyle = window.getComputedStyle(logo);
            return {
                text: footer.innerText.replace(/\s+/g, ' ').trim(),
                direction: style.direction,
                fontFamily: style.fontFamily,
                fontSize: style.fontSize,
                lineHeight: style.lineHeight,
                color: style.color,
                backgroundColor: style.backgroundColor,
                navDisplay: navStyle.display,
                navLinks: nav.querySelectorAll('a').length,
                contactLinks: contact.querySelectorAll('a').length,
                logoFont: logoStyle.font,
                logoColor: logoStyle.color,
                hasPlaceholderLink: Array.from(footer.querySelectorAll('a')).some((anchor) => anchor.getAttribute('href') === '#'),
            };
        }));
    }

    for (const snapshot of snapshots.slice(1)) {
        expect(snapshot).toEqual(snapshots[0]);
    }
    expect(snapshots[0].navLinks).toBeGreaterThanOrEqual(5);
    expect(snapshots[0].logoFont).toBeTruthy();
    expect(snapshots[0].hasPlaceholderLink).toBe(false);
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

test('homepage product hero and distinct catalogue sections stay within the loading budget', async ({ page }) => {
    for (const viewport of [
        { width: 390, height: 844 },
        { width: 1440, height: 960 },
    ]) {
        await page.setViewportSize(viewport);
        await page.goto('/', { waitUntil: 'domcontentloaded' });

        expect(await page.locator('.fz-category-card').count()).toBeLessThanOrEqual(3);
        expect(await page.locator('.fz-home-hero [data-home-slide]').count()).toBeLessThanOrEqual(6);
        expect(await page.locator('.fz-product-section .fz-product-card--clickable').count()).toBeLessThanOrEqual(4);
        expect(await page.locator('.fz-new-section').count()).toBe(0);
        expect(await page.locator('.grid__image').count()).toBe(0);

        const heroProducts = await page.locator('.fz-home-hero [data-home-slide]').count();
        const bestsellerProducts = await page.locator('.fz-product-section .fz-product-card--clickable').count();
        expect(heroProducts + bestsellerProducts, 'homepage shows one bounded hero and one bounded product grid').toBeLessThanOrEqual(10);

        await expect(page.locator('.fz-category-section .fz-text-link')).toHaveAttribute('href', /\/categories(?:\?|$)/);
        if (bestsellerProducts > 0) {
            await expect(page.locator('.fz-product-section .fz-text-link')).toHaveAttribute('href', /\/shop(?:\?|$)/);
        }
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

test('blog archive renders the editorial magazine layout without horizontal overflow', async ({ page }) => {
    for (const viewport of [
        { width: 390, height: 844 },
        { width: 768, height: 900 },
        { width: 1440, height: 960 },
    ]) {
        await page.setViewportSize(viewport);
        await page.goto('/blog', { waitUntil: 'domcontentloaded' });

        await expect(page.locator('.blog-masthead')).toBeVisible();
        await expect(page.locator('.blog-masthead__title')).toBeVisible();
        await expect(page.locator('.blog-featured-card')).toBeVisible();
        await expect(page.locator('.blog-article-card').first()).toBeVisible();

        await page.locator('.blog-featured-card').scrollIntoViewIfNeeded();
        await expect(page.locator('.blog-featured-card')).toHaveClass(/is-visible/);

        const overflow = await page.evaluate(() => (
            Math.max(document.documentElement.scrollWidth, document.body.scrollWidth)
            - document.documentElement.clientWidth
        ));

        expect(overflow, 'blog magazine layout must not overflow at ' + viewport.width + 'px').toBeLessThanOrEqual(1);
    }
});

test('blog actions are real touch targets and the magazine stays free of heavy motion runtimes', async ({ page }) => {
    for (const viewport of [
        { width: 390, height: 844 },
        { width: 1440, height: 960 },
    ]) {
        await page.setViewportSize(viewport);
        await page.goto('/blog', { waitUntil: 'domcontentloaded' });

        const rss = page.getByRole('link', {
            name: 'دنبال‌کردن مقاله‌های مجله با فید RSS',
        });
        await expect(rss).toBeVisible();
        await expect(rss).toHaveAttribute('href', /\/blog\/feed\.xml$/);
        await expect(rss.locator('svg')).toHaveCount(1);

        const target = await rss.boundingBox();
        expect(target, 'RSS should have a measurable click target').not.toBeNull();
        expect(target.height, 'RSS click target must be at least 44px high').toBeGreaterThanOrEqual(44);
        expect(target.width, 'RSS pill must be wide enough to tap comfortably').toBeGreaterThanOrEqual(160);

        const motion = await page.evaluate(() => ({
            gsap: typeof window.gsap,
            intersectionObserver: typeof window.IntersectionObserver,
            loadedHeavyMotionFiles: performance.getEntriesByType('resource')
                .map((resource) => resource.name.toLowerCase())
                .filter((name) => name.includes('gsap') || name.includes('lenis')),
        }));

        expect(motion.gsap, 'blog must not load GSAP').toBe('undefined');
        expect(motion.intersectionObserver, 'blog should use the native observer API').toBe('function');
        expect(motion.loadedHeavyMotionFiles, 'blog must not request GSAP or Lenis assets').toEqual([]);

        const feedResponsePromise = page.waitForResponse((response) => (
            response.url().endsWith('/blog/feed.xml') && response.status() === 200
        ));
        await rss.click();

        const feedResponse = await feedResponsePromise;
        expect(feedResponse.headers()['content-type']).toContain('application/rss+xml');
        const feedXml = await feedResponse.text();
        expect(feedXml).toContain('<rss');
        expect(feedXml).toContain('<title>مجله فرزین</title>');
        await expect(page).toHaveURL(/\/blog\/feed\.xml$/);
    }
});

test('admin blog image workflows expose the cropper and saved media library', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/login', { waitUntil: 'domcontentloaded' });

    const loginForm = page.locator('form').filter({ has: page.locator('input[name="phone"]') });
    await loginForm.locator('input[name="phone"]').fill('browser-admin@example.test');
    await loginForm.locator('input[name="password"]').fill('browser-smoke-password');
    await loginForm.locator('button[type="submit"]').click();
    await expect(page).toHaveURL(/\/admin(?:\/dashboard)?(?:\?.*)?$/);

    await page.goto('/admin/blog-appearance', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-blog-appearance]')).toBeVisible();
    await expect(page.locator('.blog-design-preview')).toHaveCount(2);
    await expect(page.locator('[data-blog-container-width]')).toBeVisible();

    await page.goto('/admin/blog-posts/create', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('input[name="cover_image"][data-image-editor-input]')).toHaveAttribute(
        'data-image-editor-default-ratio',
        '16:9'
    );
    await expect(page.locator('[data-editor-media-picker] summary')).toBeVisible();
});
