# Farzin Public Storefront Design System

## Purpose and scope

This document defines the public-facing visual and component contract for Farzin's kitchen hood and sink store. It covers the home page, shop, category directory and category detail, product detail, cart, contact, blog archive/article, login and registration pages.

This is a functional and presentation standard. Security review is tracked separately and is intentionally outside this workstream.

## Visual direction

The storefront uses a restrained editorial / product-engineering direction:

- Square geometry for banners, cards, content panels, buttons, form controls and bordered utility panels. Do not add rounded corners to these surfaces.
- Product photography must show the complete hood or sink silhouette. Catalog and homepage product-card images use \`object-fit: contain\`; editorial and campaign photography may use \`cover\` when the crop is intentional and configured for that composition.
- The visual identity comes from proportion, typography, whitespace, framing, border hierarchy, controlled shadow, image scale and deliberate color contrast—not from rounded corners, pills or generic ecommerce gradients.
- Preserve the Farzin navy, burgundy, neutral metal and white/soft-gray palette.
- Keep product and editorial content readable in Persian RTL. Avoid decorative English labels where natural Persian wording communicates the same thing.
- Do not add unsupported warranty, installation, delivery, stock or payment claims. Marketing copy must be backed by real configured data.

## CSS ownership and load order

- \`resources/css/variables.css\`: shared public color, type, spacing, motion and surface tokens.
- \`resources/css/app.css\`: legacy/base storefront rules and shared component foundations. Avoid adding new page-specific styling here.
- \`resources/css/responsive.css\`: responsive layout corrections and catalog/pagination behavior.
- \`resources/css/farzin-storefront.css\`: Farzin public components and page-specific storefront patterns.
- \`resources/css/home.css\`: homepage-only layout and sections.
- \`resources/css/blog.css\`: blog archive and article typography/layout.
- `resources/css/product.css`: product slug gallery, detail layout and responsive product interactions; loaded only on product detail pages.
- \`resources/css/public-design-system.css\`: final public-only geometry, typography and component polish. It is loaded after the page style stack so local utilities cannot reintroduce rounded cards, banners or controls.
- \`resources/css/storefront.css\`: public CSS entry point; imports the shared public CSS layers.
- \`vite.config.js\`: registers independently referenced CSS entrypoints in the Vite manifest.

Do not place global public overrides in admin CSS. Admin UI retains its own design system.

## Component contract

Prefer the existing shared Blade components and extend them rather than duplicating markup:

- Layout: shared header, home header variant, mobile menu, bottom navigation, search overlay and footer.
- Catalog: \`x-product.card\`, product gallery, price, rating and variant selector.
- Home: category, featured product, article and carousel-section components.
- Shop/category: category cards, filters, sorting, filter drawer and pagination.
- UI: button, input, modal, badge and pagination components.

Page-specific sections may vary in composition, but shared behaviors (navigation, cart count, search, pagination, product card actions, image handling and form controls) must have one source of truth.

## Content and media contract

- A public page may only render published/active content that is enabled for that placement.
- Home page sections, products, banners, blog articles and category visuals are controlled by their intended dashboard settings. A saved record is not automatically a homepage placement.
- Product publishing requires a storefront-visible category and a usable primary image. Drafts remain editable without a public image.
- Blog articles may be published without a cover image; use the designed fallback. A missing physical file must never render as a broken image URL.
- Media selectors must not present missing files as available. Product, blog, category motion and SEO image pickers should only expose media whose file exists on its configured disk.
- Preserve source/original product images. Responsive variants are delivery derivatives; do not replace the original upload.
- Do not run production seeders or mutate customer content as part of a visual release.

## Dashboard-to-storefront publishing rules

The following rules are intentional and should be visible to the content team when creating or editing records:

### Products

- A product is publicly eligible when its status is active, its category is active, its price is greater than zero, and it has a usable primary image. Products without these requirements stay in the admin catalog but are not eligible for public category/shop/home listings.
- The homepage has separate placements: discounts require a real compare-at price above the current price; new arrivals require the `is_new` flag; best sellers require paid, non-cancelled order items; the selected-products shelf requires `show_in_home_gallery`.
- A product already placed in a higher-priority shelf is not repeated in the selected-products shelf. This is deliberate de-duplication, not a missing product.
- The create form preselects homepage placement. When editing, the saved `show_in_home_gallery` value is retained. The content team should confirm the placement flag before expecting a product in the selected shelf.
- Upload a JPG/JPEG/PNG/WEBP image of at least 800×600 px and no more than 20 MB, or select an existing file from the media library. The source image is retained; generated responsive variants are delivery assets.

### Blog articles

- New articles start as drafts. Drafts are saved in the admin and are intentionally absent from the public blog and homepage.
- To publish immediately, set status to published and leave the publication time empty or choose a time that has already passed. A future publication time schedules the article; it will appear only after that time.
- Published articles can appear in the homepage journal and public blog archive. The homepage journal shows up to three latest published articles.
- A cover image is optional. If no image is attached, or its file is missing, the public card/article uses the designed fallback instead of a broken image.
- SEO robots directives affect indexing, not whether a currently published article is visible to customers.

### Homepage content

- Hero banners, technology content, benefits, categories, product shelves and the journal have independent display controls or data requirements. Saving a setting does not imply every section is enabled.
- After editing a homepage image, confirm that the selected media file still exists on the public disk and that the section is enabled.
- When content is intentionally unpublished or a section has no eligible records, the page should omit that shelf cleanly rather than showing fixture content or an empty visual block.

## Responsive and interaction requirements

Check at 360, 390, 768, 1023, 1024 and 1440 CSS pixels:

- No horizontal page overflow.
- Header, search, cart, mobile menu, bottom navigation and footer remain reachable.
- Product images preserve their full silhouette; image frames maintain their intended aspect ratio.
- Product-card title, price, availability and CTA do not collide or get clipped.
- Category filters, sort controls, pagination and drawers remain usable.
- Blog article body, headings, tables, media and pagination remain readable.
- Forms keep labels, validation messages and submit actions visible.
- Interactive controls retain visible focus states and at least 44px touch targets where applicable.
- Motion respects \`prefers-reduced-motion\`; content must remain visible if reveal/slider JavaScript is delayed or unavailable.

## Verification commands

Run from the repository root:

\`\`\`bash
npm run build
php artisan test
npx playwright test tests/e2e/storefront-responsive.spec.js
\`\`\`

The responsive Playwright suite covers the public routes and representative interactions. Keep tests for:
- published content flowing from admin settings to the intended public section;
- missing media fallbacks and unavailable media selection;
- product create/update image workflows;
- product and category pagination;
- blog archive/category pagination and visible controls;
- responsive page overflow and shared navigation;
- uncropped catalog photography;
- square geometry for cards, panels, banners and buttons.

## Release acceptance

A public-facing change is not ready solely because it compiles. Require:

1. Laravel feature tests pass.
2. Vite production build and asset-budget checks pass.
3. Responsive browser tests pass at the configured viewports.
4. Public routes render without 5xx responses.
5. Admin content controls are verified against the exact public placement they manage.
6. No production customer data, content or database structure was changed unintentionally.
7. Security review is completed in its separate review phase.
