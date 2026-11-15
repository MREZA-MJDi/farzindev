# Admin CMS and dashboard standard

This document records the admin information architecture established in Farzindev for reuse in the next Laravel dashboards (including Livora). It is a convention, not a package.

## 1. Dashboard landing page

The dashboard landing page is a summary, not a second copy of management screens.

Keep only:
- A small number of operational charts (for example, revenue trend and order status).
- High-value links to canonical management workspaces.
- Bounded aggregate queries, preferably one grouped query per chart and short-lived cached aggregates where appropriate.

Do not load recent-order tables, customer lists, top-product collections, inventory detail lists, or blog lists on the landing page. Those belong in their own paginated workspaces.

## 2. Canonical workspaces

- Products: one canonical product editor contains product copy, specifications, gallery and variants.
- Product variants: render in the product editor with pagination; do not create parallel full-detail pages for images or variants.
- CMS: one tabbed hub for public page copy, blog posts plus taxonomy/slug management, SEO, banners/contact settings, and media.
- Blog posts and taxonomy lists share a CMS tab and use independent pagination windows.
- Legacy list URLs should redirect to the canonical workspace rather than maintain a second implementation.

## 3. Content versus behavior

CMS editors manage content and metadata. They should not expose arbitrary switches that hide core storefront sections or change global layout behavior. Keep banner management as the explicit exception. Keep operational controls (stock, order status, payment and access rules) in their proper operational workspaces.

## 4. SEO

Keep useful controls:
- Meta title and description
- Canonical URL
- Robots/indexing directives
- Open Graph
- Structured data
- Sitemap and redirect management

Do not add keyword fields, keyword-density scoring, or meta-keywords controls.

## 5. Data loading

- Fetch heavy data only for the active CMS tab.
- Paginate lists (default 15 rows) and preserve search parameters.
- Eager-load only the relations used by the current card/table.
- Bound gallery/variant image collections and avoid loading full variant trees on index pages.
- Avoid per-row queries and duplicate eager loads.
- Cache only small aggregate summaries with an explicit invalidation/version strategy.

## 6. Image upload contract

For product/category/CMS raster uploads:
- Accept JPG, PNG and WEBP only unless a specific workflow requires another type.
- Validate MIME/type, dimensions, individual size and batch count on the server.
- Client-side previews are advisory; server validation remains authoritative.
- Show an independent preview for every selected image and an editable alt description for each gallery item.
- Keep the previous stored image until the replacement upload and persistence both succeed.
- For the Farzindev product and variant gallery workflow: up to 5 images per batch, up to 10 MB per image, minimum 800×600 pixels. Keep PHP-FPM upload_max_filesize and post_max_size aligned with the selected batch limit and verify them on the actual VPS.

## 7. Release requirements

Before merging or deploying:
- Run Laravel feature tests and Vite production build.
- Run browser smoke tests at desktop and mobile widths.
- Verify the new migrations against a production-like database backup.
- Verify disk capacity, writable storage paths, PHP-FPM limits, queue behavior, database connections and Nginx upstream health.
- Test the public pages and admin save/edit flows against real records.
- Never treat a green code CI as proof that the VPS cannot return 504; production runtime and load checks are separate gates.
