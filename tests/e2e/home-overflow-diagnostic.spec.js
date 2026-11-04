import { expect, test } from '@playwright/test';

test('homepage technology media stays inside its responsive grid track', async ({ page }) => {
    for (const width of [360, 390, 768, 900, 1023, 1024, 1100, 1280, 1440, 1920]) {
        await page.setViewportSize({ width, height: 960 });
        await page.goto('/', { waitUntil: 'domcontentloaded' });

        const layout = await page.locator('.fz-home-technology__layout').evaluate((container) => {
            const visual = container.querySelector('.fz-home-technology__visual');
            const containerRect = container.getBoundingClientRect();
            const visualRect = visual.getBoundingClientRect();

            return {
                viewport: document.documentElement.clientWidth,
                documentWidth: document.documentElement.scrollWidth,
                bodyWidth: document.body.scrollWidth,
                containerLeft: containerRect.left,
                containerRight: containerRect.right,
                containerScrollWidth: container.scrollWidth,
                containerClientWidth: container.clientWidth,
                visualLeft: visualRect.left,
                visualRight: visualRect.right,
                visualWidth: visualRect.width,
                visualTrackWidth: visual.getBoundingClientRect().width,
            };
        });

        expect(
            layout.visualLeft,
            'technology media must not escape the left edge of its grid at ' + width + 'px: ' + JSON.stringify(layout)
        ).toBeGreaterThanOrEqual(layout.containerLeft - 1);

        expect(
            layout.visualRight,
            'technology media must not escape the right edge of its grid at ' + width + 'px: ' + JSON.stringify(layout)
        ).toBeLessThanOrEqual(layout.containerRight + 1);

        expect(
            layout.containerScrollWidth - layout.containerClientWidth,
            'technology grid must not have intrinsic horizontal overflow at ' + width + 'px: ' + JSON.stringify(layout)
        ).toBeLessThanOrEqual(1);

        expect(
            Math.max(layout.documentWidth, layout.bodyWidth) - layout.viewport,
            'homepage document must not overflow at ' + width + 'px: ' + JSON.stringify(layout)
        ).toBeLessThanOrEqual(1);
    }
});
