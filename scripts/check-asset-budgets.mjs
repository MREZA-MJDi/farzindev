import { readFile, stat } from 'node:fs/promises';
import { gzipSync } from 'node:zlib';
import path from 'node:path';

const manifestPath = path.resolve('public/build/manifest.json');
const manifest = JSON.parse(await readFile(manifestPath, 'utf8'));

const budgets = [
    { key: 'resources/js/app.js', label: 'Shared application JavaScript', maxGzipKb: 45 },
    { key: 'resources/js/home.js', label: 'Homepage-only JavaScript', maxGzipKb: 8 },
    { key: 'resources/css/app.css', label: 'Shared storefront CSS', maxGzipKb: 32 },
    { key: 'resources/css/responsive.css', label: 'Shared responsive CSS', maxGzipKb: 8 },
    { key: 'resources/css/home.css', label: 'Homepage-only CSS', maxGzipKb: 18 },
    { key: 'resources/css/admin.css', label: 'Admin-only CSS', maxGzipKb: 24 },
    { key: 'resources/css/blog.css', label: 'Blog-only CSS', maxGzipKb: 5 },
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
