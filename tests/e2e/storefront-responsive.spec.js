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
    '/blog/category/browser-smoke-guides',
    '/blog/choosing-kitchen-hood',
    '/about',
    '/contact',
    '/cart',
    '/checkout',
    '/login',
    '/register',
    '/terms',
    '/privacy',
];

for (const viewport of viewports) {
    test('public routes keep the shared layout usable at ' + viewport.width + 'px', async ({ page }) => {
        await page.setViewportSize(viewport);

        for (const path of publicRoutes) {
            const response = await page.goto(path, { waitUntil: 'domcontentloaded' });

            expect(response, path + ' must return a document response').not.toBeNull();
            expect(response.status(), path + ' must not return an HTTP error').toBeLessThan(400);

            const header = path === '/'
                ? page.locator('.fz-home-header')
                : page.locator('.farzin-site-header');
            await expect(header, path + ' header').toBeVisible();
            await expect(header.locator('[data-farzin-header-brand]'), path + ' shared brand').toBeVisible();
            await expect(header.locator('[data-farzin-header-account]'), path + ' shared account action').toBeAttached();
            await expect(header.locator('[data-farzin-header-cart]'), path + ' shared cart action').toBeAttached();
            await expect(header.locator('[data-farzin-header-menu]'), path + ' shared menu action').toBeAttached();
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
    await page.locator('.fz-home-header [data-farzin-header-menu]').click();
    await expect(page.locator('[data-farzin-mobile-menu-panel]')).toBeVisible();

    await page.locator('[data-farzin-mobile-menu-panel] button[aria-label="بستن منو"]').click();
    await page.goto('/shop', { waitUntil: 'domcontentloaded' });
    await page.locator('.farzin-site-header [data-farzin-header-menu]').click();
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

test('admin login refreshes an expired CSRF session and never caches its form', async ({ page }) => {
    const login = await page.goto('/admin/login', { waitUntil: 'domcontentloaded' });
    expect(login?.status()).toBe(200);
    expect(login?.headers()['cache-control']).toContain('no-store');

    const expiredResponse = await page.request.post('/admin/login', {
        form: {
            _token: 'expired-csrf-token',
            email: 'browser-admin@example.test',
            password: 'browser-smoke-password',
        },
    });

    expect(expiredResponse.url()).toContain('/admin/login');
    expect(await expiredResponse.text()).toContain('نشست مدیریت منقضی شده است');
});

test('homepage category GIF preview starts on hover and the first touch tap', async ({ page }) => {
    // This test validates the interaction itself; other tests cover the
    // reduced-motion experience separately.
    await page.emulateMedia({ reducedMotion: 'no-preference' });
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    // Deterministic, network-free animated-media fixture. Load the same preview
    // module used by Vite after the fixture exists so this test checks real
    // pointer/touch event behavior rather than only checking the Blade markup.
    await page.evaluate(() => {
        const card = document.createElement('a');
        card.id = 'category-preview-test-fixture';
        card.href = '/categories';
        card.className = 'fz-category-card';
        // Keep the deterministic fixture inside the viewport so the real
        // IntersectionObserver cleanup does not immediately stop its preview.
        card.style.cssText = 'position:fixed;top:0;left:0;width:120px;height:120px;z-index:99999;';
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


test('homepage uses the sticky commerce header, a real banner fallback and the shared footer', async ({ page }) => {
    for (const viewport of [
        { width: 390, height: 844 },
        { width: 768, height: 900 },
        { width: 1440, height: 960 },
    ]) {
        await page.setViewportSize(viewport);
        await page.goto('/', { waitUntil: 'domcontentloaded' });

        await expect(page.locator('.fz-home-header')).toBeVisible();
        await expect(page.locator('.fz-home-header__search')).toBeVisible();
        await expect(page.locator('.fz-home-header__cart')).toBeVisible();
        await expect(page.locator('.fz-market-hero__slide').first()).toBeVisible();
        await expect(page.locator('.farzin-site-footer')).toBeVisible();

        const layout = await page.evaluate(() => {
            const header = document.querySelector('.fz-home-header');
            const hero = document.querySelector('.fz-market-hero');
            const footer = document.querySelector('.farzin-site-footer');
            return {
                headerPosition: window.getComputedStyle(header).position,
                heroTop: hero.getBoundingClientRect().top,
                footerTop: footer.getBoundingClientRect().top,
                overflow: Math.max(document.documentElement.scrollWidth, document.body.scrollWidth)
                    - document.documentElement.clientWidth,
            };
        });
        expect(layout.headerPosition).toBe('sticky');
        expect(layout.footerTop).toBeGreaterThan(layout.heroTop);
        expect(layout.overflow).toBeLessThanOrEqual(1);

        await page.goto('/shop', { waitUntil: 'domcontentloaded' });
        const shopSpacing = await page.evaluate(() => {
            const main = document.querySelector('#main-content');
            const footer = document.querySelector('.farzin-site-footer');
            return footer.getBoundingClientRect().top - main.getBoundingClientRect().bottom;
        });
        expect(shopSpacing).toBeGreaterThanOrEqual(24);
    }
});

test('shared footer keeps the same brand, navigation and dashboard-driven contacts on every storefront page', async ({ page }) => {
    const snapshots = [];

    await page.goto('/shop', { waitUntil: 'domcontentloaded' });
    const productHref = await page.locator('.farzin-product-card a[href*="/product/"]').first().getAttribute('href');
    const paths = ['/', '/shop', '/categories', '/categories/hoods', '/about', '/contact'];
    if (productHref) paths.push(productHref);

    for (const path of paths) {
        await page.goto(path, { waitUntil: 'domcontentloaded' });

        snapshots.push(await page.locator('.farzin-site-footer').evaluate((footer) => {
            const logo = footer.querySelector('.farzin-footer-editorial__logo');
            const nav = footer.querySelector('.farzin-footer-editorial__nav');
            const contact = footer.querySelector('.farzin-footer-editorial__contact');
            const style = window.getComputedStyle(footer);
            const navStyle = window.getComputedStyle(nav);
            const logoStyle = window.getComputedStyle(logo);
            return {
                direction: style.direction,
                fontFamily: style.fontFamily,
                color: style.color,
                backgroundColor: style.backgroundColor,
                navDisplay: navStyle.display,
                navLinks: nav.querySelectorAll('a').length,
                contactLinks: contact.querySelectorAll('a').length,
                logoFont: logoStyle.font,
                logoColor: logoStyle.color,
                logoWidth: Number(logo.getBoundingClientRect().width.toFixed(1)),
                logoHeight: Number(logo.getBoundingClientRect().height.toFixed(1)),
                logoImageFit: logo.querySelector('img') ? getComputedStyle(logo.querySelector('img')).objectFit : null,
                logoImageWidth: logo.querySelector('img') ? Number(logo.querySelector('img').getBoundingClientRect().width.toFixed(1)) : null,
                logoImageHeight: logo.querySelector('img') ? Number(logo.querySelector('img').getBoundingClientRect().height.toFixed(1)) : null,
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
    for (const path of ['/account', '/admin', '/admin/blog-posts']) {
        await page.goto(path, { waitUntil: 'domcontentloaded' });
        await expect(page).toHaveURL(/\/login(?:\?.*)?$/);
    }
});


test('homepage header exposes login, cart and accessible mobile navigation', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 960 });
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const desktopHeader = page.locator('.fz-home-header');
    await expect(desktopHeader.locator('.fz-home-header__account')).toBeVisible();
    await expect(desktopHeader.locator('.fz-home-header__account')).toContainText('ورود');
    await expect(desktopHeader.locator('.fz-home-header__search input')).toBeVisible();
    await expect(desktopHeader.locator('.fz-home-header__cart')).toBeVisible();

    await page.setViewportSize({ width: 390, height: 844 });
    await page.reload({ waitUntil: 'domcontentloaded' });

    const mobileHeader = page.locator('.fz-home-header');
    await expect(mobileHeader.locator('.fz-home-header__account')).toBeVisible();
    await expect(mobileHeader.locator('.fz-home-header__cart')).toBeVisible();
    await expect(mobileHeader.locator('.fz-home-header__search input')).toBeVisible();

    await mobileHeader.locator('.fz-home-header__menu').click();
    const mobileMenu = page.locator('[data-farzin-mobile-menu-panel]');
    await expect(mobileMenu).toBeVisible();
    await expect(mobileMenu.getByRole('link', { name: 'سبد خرید' })).toBeVisible();
    await expect(mobileMenu.getByRole('link', { name: /ورود|حساب من/ }).first()).toBeVisible();
});

test('homepage commerce shelves render only configured categories and genuine product records', async ({ page }) => {
    for (const viewport of [
        { width: 390, height: 844 },
        { width: 1440, height: 960 },
    ]) {
        await page.setViewportSize(viewport);
        await page.goto('/', { waitUntil: 'domcontentloaded' });

        const slideCount = await page.locator('.fz-market-hero__slide').count();
        expect(slideCount).toBeGreaterThanOrEqual(1);
        expect(slideCount).toBeLessThanOrEqual(4);

        const categories = page.locator('.fz-home-round-category');
        const hiddenCategories = await categories.evaluateAll((elements) => elements.filter((element) => {
            const style = window.getComputedStyle(element);
            return style.opacity !== '1' || style.visibility !== 'visible' || style.filter.includes('blur');
        }).length);
        expect(hiddenCategories).toBe(0);

        const cards = page.locator('.fz-home-shelf .farzin-product-card');
        const cardCount = await cards.count();
        expect(cardCount).toBeLessThanOrEqual(44);
        const invalidProductTitles = await cards.locator('.farzin-product-card__title').evaluateAll((elements) =>
            elements.filter((element) => /FZ-SEED|تست|آزمایشی|پیش‌نمایش|placeholder|preview|sample/i.test(element.textContent || '')).length
        );
        expect(invalidProductTitles).toBe(0);

        if (cardCount === 0) {
            await expect(page.locator('.fz-home-shelf')).toHaveCount(0);
        } else {
            await expect(page.locator('.fz-home-shelf__track').first()).toBeVisible();
            expect(await cards.locator('img').count()).toBe(cardCount);
        }

        await expect(page.locator('.fz-home-guide-card')).toHaveCount(0);
        await expect(page.locator('.fz-home-product-grid')).toHaveCount(0);

        const overflow = await page.evaluate(() => Math.max(document.documentElement.scrollWidth, document.body.scrollWidth) - document.documentElement.clientWidth);
        expect(overflow).toBeLessThanOrEqual(1);
    }
});

test('admin mobile quick search and list filters remain reachable without horizontal overflow', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/admin/login', { waitUntil: 'domcontentloaded' });

    const loginForm = page.locator('form').filter({ has: page.locator('input[name="email"]') });
    await loginForm.locator('input[name="email"]').fill('browser-admin@example.test');
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

test('homepage journal shows published articles with optional cover images', async ({ page }) => {
    for (const viewport of [
        { width: 390, height: 844 },
        { width: 768, height: 900 },
        { width: 1440, height: 960 },
    ]) {
        await page.setViewportSize(viewport);
        await page.goto('/', { waitUntil: 'domcontentloaded' });

        const journal = page.locator('.fz-home-journal');
        if (await journal.count() === 0) {
            await expect(page.locator('.fz-home-journal__grid')).toHaveCount(0);
            continue;
        }

        const grid = journal.locator('.fz-home-journal__grid');
        await expect(grid).toBeVisible();
        const articles = journal.locator('.fz-home-article-card');
        expect(await articles.count()).toBeGreaterThanOrEqual(1);
        expect(await articles.count()).toBeLessThanOrEqual(3);
        await expect(articles.first()).toBeVisible();
        await expect(articles.first().locator('h3 a')).toHaveAttribute('href', /\/blog\//);

        const layout = await journal.evaluate((section) => {
            const gridElement = section.querySelector('.fz-home-journal__grid');
            const card = section.querySelector('.fz-home-article-card');
            const style = window.getComputedStyle(gridElement);
            const cardStyle = window.getComputedStyle(card);
            return {
                direction: style.direction,
                columns: style.gridTemplateColumns.split(' ').length,
                radius: Number.parseFloat(cardStyle.borderTopLeftRadius),
                imageCount: section.querySelectorAll('.fz-home-article-card img').length,
                fallbackCount: section.querySelectorAll('.fz-home-article-card__media-fallback').length,
                imagesHaveAlt: Array.from(section.querySelectorAll('.fz-home-article-card img')).every((image) => image.getAttribute('alt')?.trim()),
                overflow: Math.max(document.documentElement.scrollWidth, document.body.scrollWidth)
                    - document.documentElement.clientWidth,
            };
        });

        expect(layout.direction).toBe('rtl');
        expect(layout.columns).toBe(viewport.width <= 767 ? 1 : 3);
        expect(layout.radius).toBeLessThanOrEqual(24);
        expect(layout.imageCount + layout.fallbackCount).toBe(await articles.count());
        expect(layout.imagesHaveAlt).toBeTruthy();
        expect(layout.overflow).toBeLessThanOrEqual(1);
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

        const hiddenBlogContent = await page.locator('[data-blog-reveal]').evaluateAll((elements) => (
            elements.filter((element) => {
                const style = window.getComputedStyle(element);
                return style.opacity !== '1' || style.visibility !== 'visible';
            }).length
        ));
        expect(hiddenBlogContent, 'blog copy and cards must be visible without waiting for scroll JavaScript').toBe(0);

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

        const rss = page.getByRole('link', { name: 'فید RSS' });
        await expect(rss).toBeVisible();
        await expect(rss).toHaveAttribute('href', /\/blog\/feed\.xml$/);

        const target = await rss.boundingBox();
        expect(target, 'RSS should have a measurable click target').not.toBeNull();
        expect(target.height, 'RSS click target must be at least 44px high').toBeGreaterThanOrEqual(44);
        expect(target.width, 'RSS click target must be at least 44px wide').toBeGreaterThanOrEqual(44);

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

test('desktop active navigation underline is centered and the shared palette stays neutral', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 960 });

    for (const path of ['/', '/shop', '/categories', '/blog', '/about', '/contact']) {
        await page.goto(path, { waitUntil: 'domcontentloaded' });

        if (path === '/') {
            await expect(page.locator('.fz-home-header')).toBeVisible();
            const homeShell = await page.evaluate(() => ({
                header: getComputedStyle(document.querySelector('.fz-home-header')).backgroundColor,
                footer: getComputedStyle(document.querySelector('.farzin-site-footer')).backgroundColor,
            }));
            expect(homeShell.header).toBe('rgba(255, 255, 255, 0.97)');
            expect(homeShell.footer).toBe('rgb(9, 46, 80)');
            continue;
        }

        const activeLink = page.locator('.farzin-site-header__nav-link[aria-current="page"]');
        await expect(activeLink, path + ' must expose one current desktop navigation item').toHaveCount(1);

        const indicator = await activeLink.evaluate((element) => {
            const line = window.getComputedStyle(element, '::after');
            const box = element.getBoundingClientRect();

            return {
                left: line.left,
                width: line.width,
                transform: line.transform,
                background: line.backgroundColor,
                linkWidth: box.width,
            };
        });

        expect(
            Math.abs(Number.parseFloat(indicator.left) - indicator.linkWidth / 2),
            path + ' underline anchor must be set at the link centre'
        ).toBeLessThanOrEqual(1);
        expect(indicator.width, path + ' active underline must have a stable width').toBe('20px');
        expect(indicator.transform, path + ' underline must compensate for exactly half its own width').toContain('-10');

        const shell = await page.evaluate(() => ({
            page: getComputedStyle(document.querySelector('#main-content')).backgroundColor,
            header: getComputedStyle(document.querySelector('.farzin-site-header')).backgroundColor,
            footer: getComputedStyle(document.querySelector('.farzin-site-footer')).backgroundColor,
        }));

        expect(shell.page, path + ' content shell must use the stronger neutral grey').toBe('rgb(200, 202, 200)');
        expect(shell.header, path + ' header must remain a white surface').toBe('rgba(255, 255, 255, 0.97)');
        expect(shell.footer, path + ' footer must use the editorial navy brand surface').toBe('rgb(9, 46, 80)');
    }
});

test('mobile navigation marks only the current destination and search state', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });

    for (const item of [
        { path: '/', label: 'خانه' },
        { path: '/shop', label: 'فروشگاه' },
        { path: '/cart', label: 'سبد' },
        { path: '/login', label: 'حساب' },
    ]) {
        await page.goto(item.path, { waitUntil: 'domcontentloaded' });

        const active = page.locator('.farzin-mobile-nav__item[aria-current="page"]');
        await expect(active, item.path + ' must mark its matching mobile tab as current').toHaveCount(1);
        await expect(active.locator('span').filter({ hasText: item.label })).toBeVisible();
    }

    await page.goto('/shop', { waitUntil: 'domcontentloaded' });
    const search = page.locator('.farzin-mobile-nav__item[aria-label="جستجو"]');
    await search.click();
    await expect(search).toHaveAttribute('aria-expanded', 'true');
    await expect(search).toHaveClass(/is-active/);
});

test('admin blog image workflows expose the cropper and saved media library', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/admin/login', { waitUntil: 'domcontentloaded' });

    const loginForm = page.locator('form').filter({ has: page.locator('input[name="email"]') });
    await loginForm.locator('input[name="email"]').fill('browser-admin@example.test');
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


test('admin homepage hero preview matches the fixed 16:10 crop and updates its copy live', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await page.goto('/admin/login', { waitUntil: 'domcontentloaded' });

    const loginForm = page.locator('form').filter({ has: page.locator('input[name="email"]') });
    await loginForm.locator('input[name="email"]').fill('browser-admin@example.test');
    await loginForm.locator('input[name="password"]').fill('browser-smoke-password');
    await loginForm.locator('button[type="submit"]').click();
    await expect(page).toHaveURL(/\/admin(?:\/dashboard)?(?:\?.*)?$/);

    await page.goto('/admin/page-content?tab=home', { waitUntil: 'domcontentloaded' });
    await expect(page.locator('[data-home-hero-preview]')).toBeVisible();

    const previewTitle = page.locator('[data-home-hero-preview-title]');
    await page.locator('#page_home_hero_title').fill('پیش‌نمایش زنده هود و سینک');
    await expect(previewTitle).toHaveText('پیش‌نمایش زنده هود و سینک');

    const imageBytes = await page.evaluate(async () => {
        const source = document.createElement('canvas');
        source.width = 1600;
        source.height = 1000;
        const context = source.getContext('2d');
        context.fillStyle = '#d9e2ea';
        context.fillRect(0, 0, source.width, source.height);
        context.fillStyle = '#163b5f';
        context.fillRect(140, 180, 760, 620);
        context.fillStyle = '#f4f5f3';
        context.fillRect(280, 270, 470, 300);
        context.fillStyle = '#667b8c';
        context.fillRect(1040, 250, 360, 520);
        const blob = await new Promise((resolve) => source.toBlob(resolve, 'image/png'));
        return Array.from(new Uint8Array(await blob.arrayBuffer()));
    });

    const upload = page.locator('input[name="page_home_hero_image"]');
    await expect(upload).toHaveAttribute('data-image-editor-ready', 'true');
    await upload.setInputFiles({
        name: 'farzin-hero-preview.png',
        mimeType: 'image/png',
        buffer: Buffer.from(imageBytes),
    });

    const cropPanel = page.locator('[data-image-editor-panel]');
    const cropCanvas = page.locator('[data-image-editor-canvas]');
    await expect(cropPanel).toBeVisible();
    await expect(cropCanvas).toBeVisible();
    await expect(page.locator('[data-image-editor-ratio]')).toHaveValue('16:10');

    const cropRatio = await cropCanvas.evaluate((canvas) => canvas.width / canvas.height);
    expect(cropRatio, 'crop preview must use the same 16:10 frame as the public homepage').toBeCloseTo(1.6, 2);
    await expect(cropPanel.locator('[data-image-editor-status]')).toContainText('پیکسل');

    await page.locator('[data-image-editor-zoom]').evaluate((input) => {
        input.value = '1.5';
        input.dispatchEvent(new Event('input', { bubbles: true }));
    });
    await expect(page.locator('[data-image-editor-zoom-label]')).toContainText('۱٫۵');

    await page.setViewportSize({ width: 390, height: 844 });
    const previewOverflow = await page.locator('[data-home-hero-preview]').evaluate((element) => (
        Math.max(element.scrollWidth, element.getBoundingClientRect().width)
        - element.getBoundingClientRect().width
    ));
    expect(previewOverflow, 'the hero crop preview must not overflow on mobile').toBeLessThanOrEqual(1);

    await upload.setInputFiles([]);
    await expect(cropPanel).toBeHidden();
    await expect(page.locator('[data-image-editor-placeholder]')).toBeVisible();
});

test('admin contact location preview updates the existing map frame when address and coordinates change', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 960 });
    await page.goto('/admin/login', { waitUntil: 'domcontentloaded' });

    const loginForm = page.locator('form').filter({ has: page.locator('input[name="email"]') });
    await loginForm.locator('input[name="email"]').fill('browser-admin@example.test');
    await loginForm.locator('input[name="password"]').fill('browser-smoke-password');
    await loginForm.locator('button[type="submit"]').click();
    await expect(page).toHaveURL(/\/admin(?:\/dashboard)?(?:\?.*)?$/);

    await page.goto('/admin/storefront-settings', { waitUntil: 'domcontentloaded' });

    const preview = page.locator('#contact-map-preview');
    const frame = page.locator('#contact-map-preview-frame');
    const map = page.locator('#contact-map-preview-iframe');
    const emptyState = page.locator('#contact-map-preview-empty');

    await expect(preview).toBeVisible();
    await expect(map).toBeAttached();
    await expect(emptyState).toBeVisible();

    await page.evaluate(() => {
        window.__contactMapPreviewBeforeEdit = document.querySelector('#contact-map-preview');
    });

    await page.locator('#address').fill('Tehran, Vanak Square, Unit 1');
    await expect(frame).toBeVisible();
    await expect(map).toHaveAttribute(
        'src',
        'https://www.google.com/maps?q=Tehran%2C%20Vanak%20Square%2C%20Unit%201&z=15&output=embed'
    );

    await page.locator('#latitude').fill('35.6892');
    await page.locator('#longitude').fill('51.3890');
    await expect(map).toHaveAttribute(
        'src',
        'https://www.google.com/maps?q=35.6892%2C51.3890&z=15&output=embed'
    );
    await expect(page.locator('#contact-map-preview-link')).toHaveAttribute(
        'href',
        'https://www.google.com/maps/search/?api=1&query=35.6892%2C51.3890'
    );
    await expect(page.locator('#contact-map-preview-status')).toContainText('مختصات دقیق');

    const samePreviewNode = await page.evaluate(() => (
        window.__contactMapPreviewBeforeEdit === document.querySelector('#contact-map-preview')
    ));
    expect(samePreviewNode, 'editing the location must update the existing preview instead of replacing its layout').toBe(true);

    await page.locator('#latitude').fill('91');
    await expect(frame).toBeHidden();
    await expect(emptyState).toBeVisible();
    await expect(page.locator('#contact-map-preview-empty-text')).toContainText('مختصات معتبر نیست');
});

test('shop product card actions stay aligned across different content heights', async ({ page }) => {
    for (const viewport of [
        { width: 390, height: 844 },
        { width: 1440, height: 960 },
    ]) {
        await page.setViewportSize(viewport);
        await page.goto('/shop', { waitUntil: 'domcontentloaded' });

        const cards = page.locator('.farzin-product-card');
        await expect(cards.nth(0)).toBeVisible();
        await expect(cards.nth(1)).toBeVisible();

        const ctaTops = await Promise.all([0, 1].map((index) => (
            cards.nth(index)
                .locator('.farzin-product-card__cta')
                .evaluate((element) => element.getBoundingClientRect().top)
        )));

        expect(
            Math.abs(ctaTops[0] - ctaTops[1]),
            'the first product row must keep purchase actions aligned'
        ).toBeLessThanOrEqual(2);
    }
});

test('homepage product cards use the shared carousel card and fixed image ratio', async ({ page }) => {
    for (const viewport of [
        { width: 390, height: 844 },
        { width: 1440, height: 960 },
    ]) {
        await page.setViewportSize(viewport);
        await page.goto('/', { waitUntil: 'domcontentloaded' });

        const cards = page.locator('.fz-home-shelf__item .farzin-product-card');
        const count = await cards.count();
        if (count === 0) {
            await expect(page.locator('.fz-home-shelf')).toHaveCount(0);
            continue;
        }

        const card = cards.first();
        await expect(card).toBeVisible();
        const geometry = await card.evaluate((element) => {
            const media = element.querySelector('.farzin-product-card__media');
            const imageLink = element.querySelector('.farzin-product-card__image-link');
            const image = imageLink.querySelector('img');
            const content = element.querySelector('.farzin-product-card__content');
            const cardRect = element.getBoundingClientRect();
            const mediaRect = media.getBoundingClientRect();
            const imageRect = imageLink.getBoundingClientRect();
            const contentRect = content.getBoundingClientRect();

            return {
                radius: Number.parseFloat(getComputedStyle(element).borderTopLeftRadius),
                overflow: getComputedStyle(element).overflow,
                imageRatio: imageRect.width / imageRect.height,
                installmentSlotHeight: element.querySelector('.farzin-product-card__installment-slot')?.getBoundingClientRect().height ?? 0,
                installmentSeal: Boolean(element.querySelector('.farzin-installment-seal')),
                imageAlt: image?.getAttribute('alt')?.trim(),
                mediaBottom: mediaRect.bottom,
                contentTop: contentRect.top,
                contentBottom: contentRect.bottom,
                cardBottom: cardRect.bottom,
                hasAction: Boolean(element.querySelector('.farzin-product-card__cta')),
            };
        });

        expect(geometry.radius).toBeGreaterThanOrEqual(10);
        expect(geometry.radius).toBeLessThanOrEqual(24);
        expect(geometry.overflow).toBe('hidden');
        expect(geometry.imageRatio).toBeGreaterThan(1.28);
        expect(geometry.imageRatio).toBeLessThan(1.38);
        expect(geometry.installmentSlotHeight).toBe(0);
        expect(geometry.imageAlt).toBeTruthy();
        expect(geometry.contentTop).toBeGreaterThanOrEqual(geometry.mediaBottom - 1);
        expect(geometry.contentBottom).toBeLessThanOrEqual(geometry.cardBottom + 1);
        expect(geometry.hasAction).toBeTruthy();
    }
});

test('category index cards keep their copy readable above the image treatment', async ({ page }) => {
    await page.setViewportSize({ width: 1440, height: 960 });
    await page.goto('/categories', { waitUntil: 'domcontentloaded' });

    const card = page.locator('.farzin-category-card').first();
    await expect(card).toBeVisible();
    const contrast = await card.evaluate((element) => {
        const title = element.querySelector('.farzin-category-card__content h3');
        const description = element.querySelector('.farzin-category-card__content p:not(:first-child)');
        const shade = element.querySelector('.farzin-category-card__shade');

        return {
            title: getComputedStyle(title).color,
            description: description ? getComputedStyle(description).color : null,
            shade: getComputedStyle(shade).backgroundImage,
        };
    });

    expect(contrast.title, 'category names must be white over the dark lower image overlay').toBe('rgb(255, 255, 255)');
    expect(contrast.description, 'category counts must stay legible on the same overlay').toBe('rgba(255, 255, 255, 0.82)');
    expect(contrast.shade, 'category cards must include a readable dark image gradient').toContain('linear-gradient');
});

test('product detail exposes one branded quantity stepper and preloads primary fonts', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await page.goto('/product/farzin-hood-preview-01', { waitUntil: 'domcontentloaded' });

    const stepper = page.locator('[data-quantity-control]');
    await expect(stepper).toHaveCount(1);
    await expect(stepper).toBeVisible();
    await expect(stepper.locator('input[name="quantity"]')).toBeVisible();
    await expect(stepper.getByRole('button', { name: 'کاهش تعداد' })).toBeVisible();
    await expect(stepper.getByRole('button', { name: 'افزایش تعداد' })).toBeVisible();

    const appearance = await stepper.evaluate((element) => ({
        background: getComputedStyle(element).backgroundColor,
        inputColor: getComputedStyle(element.querySelector('input')).color,
        gridColumns: getComputedStyle(element).gridTemplateColumns,
    }));
    expect(appearance.background).toBe('rgb(255, 255, 255)');
    expect(appearance.inputColor).toBe('rgb(6, 40, 79)');
    expect(appearance.gridColumns.split(' ').length).toBe(3);

    const fontPreloads = await page.locator('link[rel="preload"][as="font"]').count();
    expect(fontPreloads, 'regular and extra-bold Farzin font files must start before first paint').toBe(2);
});


test('public shop and product detail keep the same 4:3 image crop on mobile and desktop', async ({ page }) => {
    for (const viewport of [
        { width: 390, height: 844 },
        { width: 1440, height: 960 },
    ]) {
        await page.setViewportSize(viewport);
        await page.goto('/shop', { waitUntil: 'domcontentloaded' });

        const card = page.locator('.farzin-product-card').first();
        await expect(card).toBeVisible();
        const productHref = await card.locator('a[href*="/product/"]').first().getAttribute('href');
        expect(productHref).toBeTruthy();
        const cardRatio = await card.locator('.farzin-product-card__image-link').evaluate((element) => {
            const rect = element.getBoundingClientRect();
            return rect.width / rect.height;
        });
        expect(cardRatio).toBeGreaterThan(1.30);
        expect(cardRatio).toBeLessThan(1.36);

        await page.goto(productHref, { waitUntil: 'domcontentloaded' });
        const gallery = page.locator('.farzin-product-gallery .product-gallery-stage').first();
        await expect(gallery).toBeVisible();
        const detailRatio = await gallery.evaluate((element) => {
            const rect = element.getBoundingClientRect();
            return rect.width / rect.height;
        });
        expect(detailRatio).toBeGreaterThan(1.30);
        expect(detailRatio).toBeLessThan(1.36);
    }
});

test('shop catalogue search is a real GET form and preserves active filters', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/shop?sort=price_asc&featured=1', { waitUntil: 'domcontentloaded' });

    const search = page.locator('#shop-catalog-search');
    await expect(search).toBeVisible();
    await expect(search).toHaveAttribute('name', 'search');
    await search.fill('هود');
    await page.locator('.farzin-shop-search__submit').click();
    await expect(page).toHaveURL(/search=/);
    await expect(page).toHaveURL(/sort=price_asc/);
    await expect(page).toHaveURL(/featured=1/);
    await expect(page.locator('.farzin-shop-toolbar__sort')).toBeVisible();
});


test('homepage category thumbnails stay compact and the image fills a true circle', async ({ page }) => {
    for (const viewport of [
        { width: 390, height: 844 },
        { width: 768, height: 900 },
        { width: 1440, height: 960 },
    ]) {
        await page.setViewportSize(viewport);
        await page.goto('/', { waitUntil: 'domcontentloaded' });

        const categories = page.locator('.fz-home-category-rail .fz-home-round-category');
        const count = await categories.count();
        if (count === 0) {
            await expect(page.locator('.fz-home-category-rail')).toHaveCount(0);
            continue;
        }

        const category = categories.first();
        await expect(category).toBeVisible();
        const geometry = await category.evaluate((element) => {
            const frame = element.querySelector('.fz-home-round-category__image');
            const image = frame.querySelector('img');
            const frameRect = frame.getBoundingClientRect();
            const imageRect = image.getBoundingClientRect();
            return {
                frameWidth: frameRect.width,
                frameHeight: frameRect.height,
                frameRadius: getComputedStyle(frame).borderTopLeftRadius,
                imageWidth: imageRect.width,
                imageHeight: imageRect.height,
                imageFit: getComputedStyle(image).objectFit,
                titleVisible: Boolean(element.querySelector('strong')?.textContent.trim()),
            };
        });

        expect(geometry.frameWidth).toBeGreaterThanOrEqual(60);
        expect(geometry.frameWidth).toBeLessThanOrEqual(viewport.width < 768 ? 68 : 78);
        expect(Math.abs(geometry.frameWidth - geometry.frameHeight)).toBeLessThanOrEqual(1);
        expect(geometry.frameRadius).toBe('50%');
        expect(Math.abs(geometry.imageWidth - geometry.frameWidth)).toBeLessThanOrEqual(1);
        expect(Math.abs(geometry.imageHeight - geometry.frameHeight)).toBeLessThanOrEqual(1);
        expect(geometry.imageFit).toBe('cover');
        expect(geometry.titleVisible).toBeTruthy();
    }
});


test('catalog product cards preserve full product images without cropping', async ({ page }) => {
    await page.setViewportSize({ width: 1280, height: 900 });
    await page.goto('/shop', { waitUntil: 'domcontentloaded' });

    const image = page.locator('.farzin-shop-catalog .farzin-product-card__image').first();
    if (await image.count() === 0) {
        test.skip(true, 'The current browser fixture has no visible catalog product images.');
    }

    await expect(image).toBeVisible();
    const objectFit = await image.evaluate((element) => getComputedStyle(element).objectFit);
    expect(objectFit).toBe('contain');
});

test('hood and sink imagery remains fully visible in product detail galleries', async ({ page }) => {
    for (const viewport of [
        { width: 390, height: 844 },
        { width: 1440, height: 960 },
    ]) {
        await page.setViewportSize(viewport);
        await page.goto('/product/farzin-hood-preview-01', { waitUntil: 'domcontentloaded' });

        const image = page.locator('.farzin-product-gallery .product-gallery-stage img').first();
        await expect(image).toBeVisible();

        const imageStyle = await image.evaluate((element) => ({
            objectFit: getComputedStyle(element).objectFit,
            radius: getComputedStyle(element).borderTopLeftRadius,
        }));

        expect(imageStyle.objectFit).toBe('contain');
        expect(imageStyle.radius).toBe('0px');
    }
});

test('blog pagination layout and controls remain visible on archive pages', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto('/blog', { waitUntil: 'domcontentloaded' });

    const paginator = page.locator('.blog-pagination');
    if (await paginator.count() === 0) {
        test.skip(true, 'The current content fixture has fewer than two blog pages.');
    }

    const layout = paginator.locator('.farzin-paginator__layout');
    const controls = paginator.locator('.farzin-paginator__controls');

    await expect(layout).toBeVisible();
    await expect(controls).toBeVisible();

    const layoutDisplay = await layout.evaluate((element) => getComputedStyle(element).display);
    const controlsDisplay = await controls.evaluate((element) => getComputedStyle(element).display);

    expect(layoutDisplay).not.toBe('none');
    expect(controlsDisplay).not.toBe('none');

    const nextPage = controls.locator('a[rel="next"]');
    if (await nextPage.count()) {
        await nextPage.click();
        await expect(page).toHaveURL(/page=2/);
        await expect(page.locator('.blog-articles-section')).toBeVisible();
    }
});

test('public storefront uses consistent square geometry across all key page types', async ({ page }) => {
    await page.setViewportSize({ width: 390, height: 844 });

    const checks = [
        { path: '/', selectors: ['.fz-market-hero__slides', '.fz-market-hero__button', '.farzin-product-card', '.fz-home-article-card', '.fz-home-header__search', '.fz-home-header__account', '.fz-home-header__cart', '.farzin-site-footer a'] },
        { path: '/shop', selectors: ['.farzin-shop-banner__image', '.farzin-product-card', '.farzin-product-card__cta', '.farzin-shop-banner__action', '.farzin-shop-catalog aside', 'main button', 'main [class*="rounded-"]', 'main [class*="-card"]', 'main [class*="__card"]'] },
        { path: '/categories', selectors: ['.fz-category-card', '.fz-category-hero__product', '.farzin-shop-category-card', 'main button', 'main [class*="rounded-"]', 'main [class*="-card"]', 'main [class*="__card"]'] },
        { path: '/categories/hoods', selectors: ['.farzin-product-card', 'main button', 'main [class*="rounded-"]', 'main [class*="-card"]', 'main [class*="__card"]'] },
        { path: '/product/farzin-hood-preview-01', selectors: ['.fz-product-panel', '.fz-product-action', '.farzin-product-detail-info', '.farzin-product-facts__item', '.product-gallery-shell', '.product-gallery-stage', 'main button', 'main [class*="rounded-"]', 'main [class*="-card"]', 'main [class*="__card"]'] },
        { path: '/blog', selectors: ['.blog-featured-card', '.blog-article-card', '.blog-masthead__visual', '.blog-rss-link', 'main button', 'main [class*="rounded-"]', 'main [class*="-card"]', 'main [class*="__card"]'] },
        { path: '/blog/choosing-kitchen-hood', selectors: ['main button', 'main [class*="rounded-"]', 'main [class*="-card"]', 'main [class*="__card"]'] },
        { path: '/cart', selectors: ['main button', 'main [class*="rounded-"]', 'main [class*="-card"]', 'main [class*="__card"]'] },
        { path: '/contact', selectors: ['main button', 'main input', 'main textarea', 'main [class*="rounded-"]', 'main [class*="-card"]', 'main [class*="__card"]'] },
        { path: '/about', selectors: ['.fz-home-purchase-cta__inner', 'main button', 'main [class*="rounded-"]', 'main [class*="-card"]', 'main [class*="__card"]'] },
        { path: '/login', selectors: ['main button', 'main input', 'main [class*="rounded-"]', 'main [class*="-card"]', 'main [class*="__card"]'] },
        { path: '/register', selectors: ['main button', 'main input', 'main [class*="rounded-"]', 'main [class*="-card"]', 'main [class*="__card"]'] },
        { path: '/checkout', selectors: ['main button', 'main input', 'main [class*="rounded-"]', 'main [class*="-card"]', 'main [class*="__card"]'] },
    ];

    for (const check of checks) {
        const response = await page.goto(check.path, { waitUntil: 'domcontentloaded' });
        expect(response?.status(), check.path + ' should render without a server error').toBeLessThan(500);

        for (const selector of check.selectors) {
            const elements = page.locator(selector);
            const count = await elements.count();

            if (!count) continue;

            const radii = await elements.evaluateAll((nodes) => (
                nodes.map((node) => getComputedStyle(node).borderTopLeftRadius)
            ));

            expect(
                radii.every((radius) => radius === '0px'),
                check.path + ' must keep ' + selector + ' square',
            ).toBeTruthy();
        }
    }
});

