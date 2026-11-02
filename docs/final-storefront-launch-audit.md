# Farzin storefront launch audit

This file tracks the implementation boundary for the public storefront. It separates code that is already present from work that requires verified commercial data, an external provider, or a production-side action.

## Implemented in the current launch-readiness branch

- Customer and administrator sign-in use separate routes and role checks. The administrator entry point is /admin/login; private authentication, account, cart and checkout paths are served with noindex.
- A shared floating contact action appears only when at least one real WhatsApp, telephone, Eitaa or Telegram channel is configured.
- Footer and contact page hide empty social, map and contact-channel sections. The footer no longer promotes generic application features as customer benefits.
- Contact form accepts optional product model, requested width, callback request and a photo of the installation area. Photo uploads are kept on Laravel's private local disk and are accessible only through the authenticated admin inbox; deleting the message removes its attachment.
- Optional warranty, shipping and installation summaries can be configured in Storefront Settings. The values are rendered only when an administrator has entered them.
- Eitaa is an optional contact channel in admin settings, the footer and the contact page.
- Known seed/demo/test/apparel/furniture catalogue rows are excluded from public product and category queries. Available products are prioritized on homepage, category and default shop listings.
- The home hero uses a real in-stock, image-backed catalogue product. The category directory omits empty categories and shows a computed starting price from purchasable products.
- Home journal is hidden until three published, indexable articles with their own cover-image records exist.
- Internal hood and sink category pages include model-specific practical buying guidance and visible FAQs. Guidance intentionally defers exact model measurements and technical values to the verified product specification.
- Product detail renders Product/Offer and BreadcrumbList schema. Duplicate product code/availability cards are removed; configurable service facts are displayed only when present.
- Blog archive pagination no longer repeats its featured article on later pages.
- Preview cleanup is non-destructive: the dry-run lists affected rows and order-item references; --apply archives rows and deactivates known demo categories without deleting products, media or order history.

## Requires real company data before it can be truthfully published

- Confirm the exact public brand/legal entity name (فرزین، فرزین فرجی or another registered name) and the correct telephone, WhatsApp, Eitaa, Telegram, Instagram, address and business hours.
- Enter only approved warranty duration/scope, shipping regions/fees/lead times, installation coverage/fees and return/refund terms. Empty settings intentionally hide the corresponding claims.
- Replace all legacy product placeholders with the commercial catalogue: official model names, SKUs, verified prices, stock by every required variant group, technical specification sheets, at least five useful real product photographs where available, and product-specific warranty/installation terms.
- Add actual workshop/factory photographs, founding date, materials/process information, project photographs and customer testimonials only after the company supplies and approves them. Contact form attachments are private customer submissions, not public project imagery.
- Publish the blog programme (8–10 substantive articles) after editorial review and real cover photography. The homepage currently requires at least three image-backed articles before showing its journal section.

## Requires external services or an approved policy decision

- SMS one-time passwords: no verified SMS provider, credentials, sender ID, delivery callbacks or rate-limit policy is configured. Customer sign-in remains the existing password flow; OTP is not simulated.
- Online payment: no provider adapter is currently approved for live payment. Do not enable a gateway until merchant documentation, authentication, create/verify payloads, signatures, amount units, redirect hosts and a successful sandbox callback have all been validated.
- Shipping calculation, province/city postal validation and estimated delivery dates need a real carrier/fulfilment agreement and rate table.
- Automated abandoned-cart reminders, SMS order updates, Analytics/Clarity and A/B testing need approved services, consent/cookie rules and credentials.
- Reviews and Q&A should remain hidden until genuine, moderated customer submissions and a verification process exist.

## Production-side actions after the branch is merged

1. Back up the database and uploads.
2. Deploy the new release while preserving .env and storage/app/public.
3. Run migrations through scripts/artisan-as-deploy.sh migrate --force (migrations add optional Eitaa/service summaries, structured product specifications, and contact-request details/private attachment references).
4. Run farzin:cleanup-preview-catalogue without --apply; review every listed product/category and its order-item count.
5. Only after reconciliation with the verified catalogue, run farzin:cleanup-preview-catalogue --apply.
6. Rebuild runtime caches with sudo bash scripts/restore-vps-runtime.sh.
7. Fill Storefront Settings with real contact/service details and verify /robots.txt, /sitemap.xml, canonical URLs and the rendered X-Robots-Tag before disabling the global SEO development lock.
8. Test customer login, separate admin login, guest cart merge, order placement, installment schedule, stock constraints and admin order management. Keep online payment marked as preparing until a real sandbox integration passes.

## Known feature boundaries

- Checkout supports authenticated order creation and an explicitly labelled guest preview/login continuation. It is not a verified guest OTP/online-payment flow yet.
- Account pages already provide profile, address, wishlist, order history and order details. Internal installment snapshots and due dates are represented in the order data; order-detail display formats due dates in Jalali.
- Shipping and free-shipping defaults must be reviewed in Admin → Storefront Settings; do not assume a zero fee or nationwide coverage is commercially correct.
- This code audit does not certify the live VPS, actual catalogue data, DNS/TLS, merchant payment credentials, SMTP delivery or company policies.