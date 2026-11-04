import { test } from '@playwright/test';

test('diagnose homepage horizontal overflow by reporting actual overflowing elements', async ({ page }) => {
    for (const width of [1023, 1024, 1440]) {
        await page.setViewportSize({ width, height: 960 });
        await page.goto('/', { waitUntil: 'domcontentloaded' });

        const report = await page.evaluate(() => {
            const viewport = document.documentElement.clientWidth;
            const elements = Array.from(document.querySelectorAll('body *')).map((element) => {
                const rect = element.getBoundingClientRect();
                const style = getComputedStyle(element);
                return {
                    tag: element.tagName.toLowerCase(),
                    id: element.id || null,
                    className: typeof element.className === 'string' ? element.className.slice(0, 180) : '',
                    left: Math.round(rect.left * 10) / 10,
                    right: Math.round(rect.right * 10) / 10,
                    width: Math.round(rect.width * 10) / 10,
                    scrollWidth: element.scrollWidth,
                    clientWidth: element.clientWidth,
                    position: style.position,
                    transform: style.transform,
                    overflowX: style.overflowX,
                };
            }).filter((item) => item.width > 0 && (
                item.right > viewport + 1 || item.left < -1 ||
                item.scrollWidth > item.clientWidth + 2
            ));

            return {
                viewport,
                documentWidth: document.documentElement.scrollWidth,
                bodyWidth: document.body.scrollWidth,
                scrollX: window.scrollX,
                offenders: elements
                    .sort((a, b) => (b.right - viewport) - (a.right - viewport))
                    .slice(0, 35),
            };
        });

        console.log('FARZIN_OVERFLOW_DIAGNOSTIC ' + JSON.stringify(report));
    }
});
