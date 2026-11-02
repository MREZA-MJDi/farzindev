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


test('homepage final CTA meets the shared footer without a duplicate contact bridge', async ({ page }) => {
    for (const viewport of [
        { width: 390, height: 844 },
        { width: 768, height: 900 },
        { width: 1440, height: 960 },
    ]) {
        await page.setViewportSize(viewport);
        await page.goto('/', { waitUntil: 'domcontentloaded' });
        await expect(page.locator('.farzin-site-footer')).toBeVisible();
        await expect(page.locator('.fz-home-final__panel')).toBeVisible();

        const homeSpacing = await page.evaluate(() => {
            const ctaPanel = document.querySelector('.fz-home-final__panel');
            const finalSection = document.querySelector('.fz-home-final');
            const footer = document.querySelector('.farzin-site-footer');

            return {
                panelToFooter: footer.getBoundingClientRect().top - ctaPanel.getBoundingClientRect().bottom,
                sectionBottom: finalSection.getBoundingClientRect().bottom,
                footerTop: footer.getBoundingClientRect().top,
                hasOldContactBridge: Boolean(document.querySelector('.fz-home-contact-bridge')),
            };
        });

        expect(homeSpacing.panelToFooter, 'homepage CTA must have deliberate bottom breathing room').toBeGreaterThanOrEqual(40);
        expect(homeSpacing.sectionBottom).toBeLessThanOrEqual(homeSpacing.footerTop + 1);
        expect(homeSpacing.hasOldContactBridge, 'the old duplicate contact bridge must stay removed').toBe(false);

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
            'interior page must use the shared footer margin'
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
                direction: style.direction,
                fontFamily: style.fontFamily,
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
    for (const path of ['/account', '/admin', '/admin/blog-posts']) {
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

        if (viewport.width >= 1024) {
            const imageIsLeftOfCopy = await page.evaluate(() => {
                const image = document.querySelector('.fz-sales-hero__visual');
                const copy = document.querySelector('.fz-sales-hero__copy');
                if (!image || !copy) return false;
                const imageRect = image.getBoundingClientRect();
                const copyRect = copy.getBoundingClientRect();
                return imageRect.left < copyRect.left && imageRect.right <= copyRect.left + 1;
            });
            expect(imageIsLeftOfCopy, 'desktop kitchen hero image must sit physically to the left of the sales copy').toBeTruthy();
        }

        const hiddenCategoryCards = await page.locator('.fz-category-card').evaluateAll((elements) => (
            elements.filter((element) => {
                const style = window.getComputedStyle(element);
                return style.opacity !== '1' || style.visibility !== 'visible' || style.filter.includes('blur');
            }).length
        ));
        expect(hiddenCategoryCards, 'homepage category cards must render without a delayed blur or fade').toBe(0);

        expect(await page.locator('.fz-category-card').count()).toBeLessThanOrEqual(4);
        expect(await page.locator('.fz-home-guide-card').count()).toBe(2);
        expect(await page.locator('.fz-home-product-grid .farzin-product-card').count()).toBeLessThanOrEqual(8);
        await expect(page.locator('.fz-new-section')).toHaveCount(0);
        await expect(page.locator('.fz-mosaic-intro')).toHaveCount(0);
        await expect(page.locator('[data-farzin-home-slider]')).toHaveCount(0);

        const homepageProductCards = await page.locator('.fz-home-product-grid .farzin-product-card').count();
        expect(homepageProductCards, 'homepage shows one compact grid of up to eight selected products').toBeLessThanOrEqual(8);
        const homepageProductEntries = await page.locator('.fz-sales-hero__visual, .fz-home-product-grid .farzin-product-card').count();
        expect(homepageProductEntries, 'homepage shows one kitchen hero and no more than eight featured cards').toBeLessThanOrEqual(9);
        await expect(page.locator('.fz-home-categories .fz-home-text-link')).toHaveAttribute('href', /\/categories(?:\?|$)/);
        await expect(page.locator('.fz-home-products .fz-home-text-link')).toHaveAttribute('href', /\/shop(?:\?|$)/);
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

test('homepage journal uses published articles, responsive RTL columns and restrained card radii', async ({ page }) => {
    for (const viewport of [
        { width: 390, height: 844 },
        { width: 768, height: 900 },
        { width: 1440, height: 960 },
    ]) {
        await page.setViewportSize(viewport);
        await page.goto('/', { waitUntil: 'domcontentloaded' });

        const journal = page.locator('.fz-home-journal');
        await expect(journal).toBeVisible();
        const grid = journal.locator('.fz-home-journal-grid');
        await expect(grid).toBeVisible();
        const articles = journal.locator('.fz-home-article-card');
        await expect(articles.first()).toBeVisible();
        expect(await articles.count()).toBeLessThanOrEqual(3);
        await expect(journal.locator('.fz-home-article-card h3 a').first()).toHaveAttribute('href', /\/blog\//);
        await expect(journal.locator('.fz-home-text-link')).toHaveAttribute('href', /\/blog(?:\?|$)/);

        const layout = await journal.evaluate((section) => {
            const grid = section.querySelector('.fz-home-journal-grid');
            const card = section.querySelector('.fz-home-article-card');
            const style = window.getComputedStyle(grid);
            const cardStyle = window.getComputedStyle(card);
            return {
                direction: style.direction,
                columns: style.gridTemplateColumns.split(' ').length,
                radius: Number.parseFloat(cardStyle.borderTopLeftRadius),
                overflow: Math.max(document.documentElement.scrollWidth, document.body.scrollWidth)
                    - document.documentElement.clientWidth,
            };
        });

        expect(layout.direction, 'homepage journal must keep RTL reading order').toBe('rtl');
        expect(layout.columns, 'journal becomes one column on mobile and three columns on tablet/desktop').toBe(viewport.width <= 767 ? 1 : 3);
        expect(layout.radius, 'journal card corner radius must remain restrained and consistent').toBeLessThanOrEqual(24);
        expect(layout.overflow, 'homepage journal must not cause horizontal overflow').toBeLessThanOrEqual(1);
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

test('homepage product cards use equal responsive image ratios and readable actions', async ({ page }) => {
    for (const viewport of [
        { width: 390, height: 844 },
        { width: 1440, height: 960 },
    ]) {
        await page.setViewportSize(viewport);
        await page.goto('/', { waitUntil: 'domcontentloaded' });

        const card = page.locator('.fz-home-product-grid .farzin-product-card').first();
        await expect(card, 'homepage must render a selected product card').toBeVisible();

        const geometry = await card.evaluate((element) => {
            const media = element.querySelector('.farzin-product-card__media');
            const imageLink = element.querySelector('.farzin-product-card__image-link');
            const infoPanel = element.querySelector('.farzin-product-card__content');
            const cardRect = element.getBoundingClientRect();
            const mediaRect = media.getBoundingClientRect();
            const linkRect = imageLink.getBoundingClientRect();
            const infoRect = infoPanel.getBoundingClientRect();

            return {
                width: cardRect.width,
                height: cardRect.height,
                radius: Number.parseFloat(getComputedStyle(element).borderTopLeftRadius),
                overflow: getComputedStyle(element).overflow,
                mediaWidth: mediaRect.width,
                mediaHeight: mediaRect.height,
                linkWidth: linkRect.width,
                linkHeight: linkRect.height,
                mediaBottom: mediaRect.bottom,
                infoTop: infoRect.top,
                infoBottom: infoRect.bottom,
                cardBottom: cardRect.bottom,
            };
        });

        expect(geometry.radius, 'product cards should keep the shared rounded-card treatment').toBeGreaterThanOrEqual(12);
        expect(geometry.radius, 'product cards should not become circular/pill-shaped').toBeLessThanOrEqual(24);
        expect(geometry.overflow, 'product card content must stay within its rounded frame').toBe('hidden');
        expect(geometry.mediaWidth).toBeGreaterThan(0);
        expect(geometry.mediaHeight).toBeGreaterThan(0);
        expect(geometry.mediaWidth / geometry.mediaHeight, 'all selected product images use one fixed landscape ratio').toBeGreaterThan(1.05);
        expect(geometry.mediaWidth / geometry.mediaHeight).toBeLessThan(1.2);
        expect(Math.abs(geometry.linkWidth - geometry.mediaWidth), 'the product image link fills the shared media frame').toBeLessThanOrEqual(1);
        expect(Math.abs(geometry.linkHeight - geometry.mediaHeight), 'the product image link fills the shared media frame').toBeLessThanOrEqual(1);
        expect(geometry.infoTop, 'product details start after the image rather than overlaying it').toBeGreaterThanOrEqual(geometry.mediaBottom - 1);
        expect(geometry.infoBottom, 'product details stay inside the card frame').toBeLessThanOrEqual(geometry.cardBottom + 1);
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
