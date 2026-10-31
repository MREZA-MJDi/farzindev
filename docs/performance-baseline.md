# Performance and production baseline

The target VPS is expected to host several independent projects. Farzin must therefore keep its browser payload page-scoped and avoid work that belongs to unrelated projects or routes.

## Runtime asset graph

- `resources/js/app.js` is the only shared browser entry. It owns Alpine and small common interactions.
- `resources/css/app.css` and `resources/css/responsive.css` are the common storefront styles. The admin uses a separate `resources/css/admin.css` entry.
- The homepage alone loads `resources/js/home.js` and `resources/css/home.css`; neither should be added to the shared app layout.
- The product-image editor and category GIF preview are loaded with dynamic imports only when their matching DOM controls are present.
- The optional storefront motion module uses native `IntersectionObserver` and the Web Animations API; it is not a GSAP/Lenis dependency and must not be described or bundled as one.
- Blog editor code is also a page-only dynamic chunk; ordinary storefront pages and unrelated admin screens must not download editor code.

The production build runs a gzip-size gate for the six intentional entrypoints. The initial limits are conservative budgets, not performance targets to spend unnecessarily: keep optional tools in their own chunks and reduce a shared entry before raising a budget.

| Entry | Gzip budget |
|---|---:|
| Shared application JavaScript | 45 KB |
| Homepage-only JavaScript | 8 KB |
| Shared storefront CSS | 32 KB |
| Shared responsive CSS | 8 KB |
| Homepage-only CSS | 18 KB |
| Admin-only CSS | 24 KB |

Build and verify the production asset graph:

```bash
npm ci
npm run build
```

Do not run the Vite development server in production. Prefer the CI-built `farzin-vps-release` artifact; do not install Node dependencies or rebuild assets on the production VPS unless the artifact deployment process explicitly requires it.

## Multiple projects on one VPS

A single Laravel installation is not a multi-tenant host. Run each unrelated domain/project as an isolated application release with its own document root, `.env`, `APP_KEY`, database and least-privilege database user, storage/uploads, logs, cache prefix, session cookie name, and deployment path. Do not point multiple domains at the same Laravel database or storage directory unless a deliberate multi-tenant design has been implemented and tested.

For projects that share a Redis server, use a unique `CACHE_PREFIX` and distinct queue names/Redis logical DBs per project. Keep `SESSION_DOMAIN` unset (host-only cookies) unless cross-subdomain login is an explicit requirement. Set a unique `SESSION_COOKIE` name per project to simplify isolation. Never share Laravel encryption keys between apps.

Use a separate Nginx server block and TLS certificate for each domain, each with its own `root /path/to/project/public`. For stronger CPU/RAM isolation, configure a dedicated PHP-FPM pool per project with a bounded `pm.max_children`, `pm.max_requests`, and process user/group; do not let four codebases share one unbounded pool. Bound queue workers per app and start with synchronous/database queues only where the workload permits. Do not start four always-on worker processes on a small VPS without measuring actual queue demand.

Measure the host before choosing pool sizes: inspect available RAM, CPU count, PHP-FPM peak worker memory, MySQL buffer pool, Redis memory, and p95 request duration under realistic concurrency. Leave headroom for MySQL, Nginx, OS cache, deploys, and traffic spikes. Avoid assigning the same CPU/RAM budget to each project when the VPS is small; a shared host still has one finite memory ceiling.

## Database query paths

The performance-index migration adds composite indexes for queries that commonly filter and order catalogue, product-image, variant-stock, and order records. It replaces the two narrower product indexes that are now covered by more useful composite indexes.

Adding indexes improves read paths at the cost of index storage and write work. Review production query plans and table sizes before adding more. Substring searches using leading wildcards (for example, `LIKE '%term%'`) generally do not benefit from ordinary B-tree indexes; changing search semantics requires a separate, database-specific design.

Keep homepage/catalogue queries bounded and eager-load only the columns/images that are actually rendered. Avoid unbounded `get()` on large datasets, N+1 relation reads, image galleries in list screens, and synchronous image transformations during ordinary page requests. Expensive image conversions, analytics, imports, mail, and sitemap rebuild work should use bounded jobs when real traffic warrants them.

## Release verification

Run the same checks as CI before deployment:

```bash
php artisan test
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

After deployment, measure real p50/p95 response time, slow queries, PHP-FPM queue length, CPU, memory, MySQL connections, cache hit ratio and asset transfer sizes. A successful build or CI run is not a substitute for measuring production traffic.
