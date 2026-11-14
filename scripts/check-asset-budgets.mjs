import { readFile, stat } from 'node:fs/promises';
import { gzipSync } from 'node:zlib';
import path from 'node:path';

const manifestPath = path.resolve('public/build/manifest.json');
const manifest = JSON.parse(await readFile(manifestPath, 'utf8'));

const budgets = [
    { key: 'resources/js/app.js', label: 'Shared application JavaScript', maxGzipKb: 45 },
    { key: 'resources/js/home.js', label: 'Homepage-only JavaScript', maxGzipKb: 8 },
    { key: 'resources/js/admin-image-editor.js', label: 'Admin image editor chunk', maxGzipKb: 18 },
    { key: 'resources/js/admin-page-content.js', label: 'Admin page content feedback chunk', maxGzipKb: 5 },
    { key: 'resources/js/admin-blog-appearance.js', label: 'Admin blog appearance chunk', maxGzipKb: 7 },
    { key: 'resources/js/admin-blog-editor.js', label: 'Admin rich-text editor chunk', maxGzipKb: 16 },
    { key: 'resources/js/category-gif-preview.js', label: 'Category media preview chunk', maxGzipKb: 12 },
    // Public CSS is intentionally consolidated by storefront.css in Vite.
    // Budget the emitted production bundle, not its imported source fragments.
    { key: 'resources/css/storefront.css', label: 'Public storefront CSS', maxGzipKb: 60 },
    { key: 'resources/css/home.css', label: 'Homepage-only CSS', maxGzipKb: 15 },
    { key: 'resources/css/admin.css', label: 'Admin-only CSS', maxGzipKb: 24 },
    // The editorial artwork controls add two dashboard-managed image slots.
    // Keep the public magazine stylesheet tightly capped while accounting for them.
    { key: 'resources/css/blog.css', label: 'Blog-only CSS', maxGzipKb: 5.5 },
    { key: 'resources/css/product.css', label: 'Product-detail-only CSS', maxGzipKb: 3.5 },
];

let failed = false;

for (const budget of budgets) {
    const entry = manifest[budget.key];
    if (!entry?.file) {
        console.error(`Missing Vite manifest entry: ${budget.key}`);
        failed = true;
        continue;
    }

    const filePath = path.resolve('public/build', entry.file);
    const [source, metadata] = await Promise.all([
        readFile(filePath),
        stat(filePath),
    ]);
    const gzipKb = gzipSync(source, { level: 9 }).byteLength / 1024;
    const sourceKb = metadata.size / 1024;
    const passed = gzipKb <= budget.maxGzipKb;

    console.log(
        `${passed ? 'PASS' : 'FAIL'} ${budget.label}: ${gzipKb.toFixed(2)} KB gzip / ${sourceKb.toFixed(2)} KB raw (budget ${budget.maxGzipKb} KB gzip)`
    );

    if (!passed) failed = true;
}

if (failed) {
    console.error('Vite asset budget exceeded. Split optional code or reduce the relevant entry before release.');
    process.exit(1);
}
