<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StorefrontSetting;
use App\Services\Seo\SeoSettings;
use App\Services\Media\ResponsiveImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class SeoSettingsController extends Controller
{
    private const CUSTOM_LINK_RELS = [
        'alternate', 'apple-touch-icon', 'author', 'dns-prefetch', 'help', 'icon',
        'license', 'manifest', 'mask-icon', 'modulepreload', 'preconnect', 'prefetch',
        'preload', 'stylesheet',
    ];

    private const PRELOAD_AS_VALUES = [
        'audio', 'document', 'embed', 'fetch', 'font', 'image', 'object', 'script',
        'style', 'track', 'video', 'worker',
    ];

    public function edit(SeoSettings $seoSettings): View
    {
        $settings = $seoSettings->all();

        return view('admin.seo-settings.edit', [
            'settings' => $settings,
            'ogImageUrl' => $seoSettings->resolvePublicAssetUrl($settings['seo_default_og_image'] ?? null),
            'customMetaTags' => $seoSettings->customMetaTags(),
            'customLinkTags' => $seoSettings->customLinkTags(),
        ]);
    }

    public function update(Request $request, SeoSettings $seoSettings, ResponsiveImageService $images): RedirectResponse
    {
        $validated = $request->validate([
            'seo_canonical_base_url' => ['required', 'url', 'regex:~^https?://[A-Za-z0-9.-]+(?::[0-9]{1,5})?/?$~i', 'max:255'],
            'seo_site_name' => ['required', 'string', 'max:100'],
            'seo_default_title' => ['required', 'string', 'max:255'],
            'seo_default_description' => ['required', 'string', 'max:320'],
            'seo_default_keywords' => ['nullable', 'string', 'max:1000'],
            'seo_custom_meta_tags' => ['nullable', 'array', 'max:30'],
            'seo_custom_meta_tags.*' => ['nullable', 'array'],
            'seo_custom_meta_tags.*.type' => ['nullable', 'string', 'in:name,property'],
            'seo_custom_meta_tags.*.key' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z][A-Za-z0-9:._-]*$/'],
            'seo_custom_meta_tags.*.content' => ['nullable', 'string', 'max:1000'],
            'seo_custom_link_tags' => ['nullable', 'array', 'max:30'],
            'seo_custom_link_tags.*' => ['nullable', 'array'],
            'seo_custom_link_tags.*.rel' => ['nullable', 'string', Rule::in(self::CUSTOM_LINK_RELS)],
            'seo_custom_link_tags.*.href' => ['nullable', 'string', 'max:2048'],
            'seo_custom_link_tags.*.hreflang' => ['nullable', 'string', 'max:35', 'regex:/^[A-Za-z]{2,3}(?:-[A-Za-z0-9]{2,8})*$/'],
            'seo_custom_link_tags.*.type' => ['nullable', 'string', 'max:100', 'regex:/^[A-Za-z0-9.+\/-]+$/'],
            'seo_custom_link_tags.*.as' => ['nullable', 'string', Rule::in(self::PRELOAD_AS_VALUES)],
            'seo_custom_link_tags.*.crossorigin' => ['nullable', 'string', Rule::in(['anonymous', 'use-credentials'])],
            'seo_default_og_image' => ['nullable', 'url', 'max:2048'],
            'seo_default_og_image_upload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'dimensions:min_width=1200,min_height=630', 'max:5120'],
            'seo_development_mode' => ['sometimes', 'boolean'],
            'seo_robots_noindex' => ['sometimes', 'boolean'],
            'seo_robots_nofollow' => ['sometimes', 'boolean'],
            'seo_robots_noarchive' => ['sometimes', 'boolean'],
            'seo_robots_nosnippet' => ['sometimes', 'boolean'],
            'seo_robots_noimageindex' => ['sometimes', 'boolean'],
            'seo_robots_txt_rules' => ['required', 'string', 'max:10000'],
            'seo_sitemap_enabled' => ['sometimes', 'boolean'],
            'seo_google_site_verification' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9_-]*$/'],
            'seo_bing_site_verification' => ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9_-]*$/'],
            'seo_twitter_site' => ['nullable', 'string', 'max:16', 'regex:/^@?[A-Za-z0-9_]{1,15}$/'],
            'seo_organization_name' => ['nullable', 'string', 'max:255'],
            'seo_organization_logo_url' => ['nullable', 'url', 'max:2048'],
            'seo_organization_phone' => ['nullable', 'string', 'max:50'],
            'seo_organization_email' => ['nullable', 'email:rfc', 'max:255'],
            'seo_organization_address' => ['nullable', 'string', 'max:500'],
        ], [
            'seo_canonical_base_url.regex' => 'دامنه canonical باید فقط ریشهٔ سایت باشد؛ برای مثال https://farajihome.com',
            'seo_default_description.max' => 'توضیحات پیش‌فرض حداکثر ۳۲۰ کاراکتر است.',
            'seo_default_og_image.url' => 'آدرس تصویر پیش‌فرض باید یک URL کامل باشد.',
            'seo_default_og_image_upload.dimensions' => 'تصویر Open Graph باید حداقل ۱۲۰۰ در ۶۳۰ پیکسل باشد.',
            'seo_default_og_image_upload.max' => 'حجم تصویر Open Graph نباید بیشتر از ۵ مگابایت باشد.',
            'seo_google_site_verification.regex' => 'کد تأیید گوگل فقط می‌تواند شامل حروف انگلیسی، عدد، خط تیره و زیرخط باشد.',
            'seo_bing_site_verification.regex' => 'کد تأیید بینگ فقط می‌تواند شامل حروف انگلیسی، عدد، خط تیره و زیرخط باشد.',
            'seo_twitter_site.regex' => 'نام کاربری X را مانند @farzin وارد کنید.',
        ]);

        $validated['seo_custom_meta_tags'] = json_encode(
            $this->normalizeCustomMetaTags($validated['seo_custom_meta_tags'] ?? []),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        $validated['seo_custom_link_tags'] = json_encode(
            $this->normalizeCustomLinkTags($validated['seo_custom_link_tags'] ?? []),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );

        $rules = str_replace(["\r\n", "\r"], "\n", trim($validated['seo_robots_txt_rules']));
        $hasUserAgent = false;
        foreach (explode("\n", $rules) as $lineNumber => $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (! preg_match('/^(User-agent|Allow|Disallow|Crawl-delay):[ \t]*[^\r\n]*$/i', $line)) {
                return back()->withInput()->withErrors([
                    'seo_robots_txt_rules' => 'خط ' . ($lineNumber + 1) . ' در robots.txt معتبر نیست. دستور Sitemap را وارد نکنید؛ آدرس نقشهٔ سایت خودکار درج می‌شود.',
                ]);
            }
            if (stripos($line, 'User-agent:') === 0) {
                $hasUserAgent = true;
            }
        }

        if (! $hasUserAgent) {
            return back()->withInput()->withErrors([
                'seo_robots_txt_rules' => 'قوانین robots.txt باید دست‌کم یک دستور User-agent داشته باشند.',
            ]);
        }

        $validated['seo_robots_txt_rules'] = $rules;
        foreach ([
            'seo_development_mode',
            'seo_robots_noindex',
            'seo_robots_nofollow',
            'seo_robots_noarchive',
            'seo_robots_nosnippet',
            'seo_robots_noimageindex',
            'seo_sitemap_enabled',
        ] as $booleanKey) {
            $validated[$booleanKey] = $request->boolean($booleanKey) ? '1' : '0';
        }

        $oldImageValue = StorefrontSetting::query()
            ->where('key', 'seo_default_og_image')
            ->value('value');

        $newImagePath = null;
        if ($request->hasFile('seo_default_og_image_upload')) {
            $newImagePath = $images->store($request->file('seo_default_og_image_upload'), 'seo');
            if (! $newImagePath) {
                return back()->withInput()->withErrors([
                    'seo_default_og_image_upload' => 'ذخیره تصویر انجام نشد؛ تنظیم قبلی حفظ شد.',
                ]);
            }
            $validated['seo_default_og_image'] = $newImagePath;
        } elseif (blank($validated['seo_default_og_image'] ?? null) && filled($oldImageValue)) {
            // A path-backed upload is intentionally not echoed into the URL field.
            // Preserve the existing image when other SEO settings are saved.
            $validated['seo_default_og_image'] = $oldImageValue;
        }

        unset($validated['seo_default_og_image_upload']);

        try {
            DB::transaction(function () use ($validated): void {
                foreach ($validated as $key => $value) {
                    StorefrontSetting::query()->updateOrCreate(
                        ['key' => $key],
                        ['value' => $value === null ? null : (string) $value]
                    );
                }
            });
        } catch (Throwable $exception) {
            if ($newImagePath !== null) {
                $images->delete($newImagePath);
            }
            throw $exception;
        }

        if (
            $oldImageValue
            && $oldImageValue !== ($validated['seo_default_og_image'] ?? null)
            && ! filter_var($oldImageValue, FILTER_VALIDATE_URL)
            && ! str_starts_with($oldImageValue, '/')
        ) {
            $images->delete($oldImageValue);
        }

        $seoSettings->clearCache();

        return redirect()
            ->route('admin.seo-settings.edit')
            ->with('success', 'تنظیمات SEO ذخیره شد و روی خروجی عمومی سایت اعمال می‌شود.');
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array{type: string, key: string, content: string}>
     */
    private function normalizeCustomMetaTags(array $rows): array
    {
        $normalized = [];
        $seen = [];
        $reserved = [
            'name' => [
                'description', 'keywords', 'robots', 'google-site-verification',
                'msvalidate.01', 'viewport', 'referrer', 'twitter:title', 'twitter:description',
                'twitter:card', 'twitter:image', 'twitter:site',
            ],
            'property' => [
                'og:type', 'og:site_name', 'og:title', 'og:description', 'og:url', 'og:image',
            ],
        ];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $type = strtolower(trim((string) ($row['type'] ?? '')));
            $key = trim((string) ($row['key'] ?? ''));
            $content = trim((string) ($row['content'] ?? ''));

            // The UI defaults the type to "name"; an otherwise empty row
            // should still be ignored when the user clicks Add and then Save.
            if ($key === '' && $content === '') {
                continue;
            }

            if (! in_array($type, ['name', 'property'], true) || $key === '' || $content === '') {
                throw ValidationException::withMessages([
                    'seo_custom_meta_tags' => 'برای هر تگ سفارشی، نوع تگ، نام ویژگی و محتوا را کامل کنید.',
                ]);
            }

            if (! preg_match('/^[A-Za-z][A-Za-z0-9:._-]{0,99}$/D', $key)) {
                throw ValidationException::withMessages([
                    'seo_custom_meta_tags' => 'نام تگ باید یک کلید معتبر HTML باشد.',
                ]);
            }

            $lowerKey = strtolower($key);
            if (in_array($lowerKey, $reserved[$type], true)) {
                throw ValidationException::withMessages([
                    'seo_custom_meta_tags' => 'تگ ' . $key . ' از قبل توسط سیستم SEO مدیریت می‌شود؛ برای جلوگیری از تگ تکراری آن را اینجا اضافه نکنید.',
                ]);
            }

            $identity = $type . ':' . $lowerKey;
            if (isset($seen[$identity])) {
                throw ValidationException::withMessages([
                    'seo_custom_meta_tags' => 'نوع و نام هر تگ سفارشی باید یکتا باشد.',
                ]);
            }

            $seen[$identity] = true;
            $normalized[] = ['type' => $type, 'key' => $key, 'content' => $content];
        }

        return $normalized;
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array<int, array{rel: string, href: string, hreflang: string, type: string, as: string, crossorigin: string}>
     */
    private function normalizeCustomLinkTags(array $rows): array
    {
        $normalized = [];
        $seen = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $rel = strtolower(trim((string) ($row['rel'] ?? '')));
            $href = trim((string) ($row['href'] ?? ''));
            $hreflang = trim((string) ($row['hreflang'] ?? ''));
            $type = trim((string) ($row['type'] ?? ''));
            $as = strtolower(trim((string) ($row['as'] ?? '')));
            $crossorigin = strtolower(trim((string) ($row['crossorigin'] ?? '')));

            // The UI defaults rel to "alternate"; ignore rows without an
            // href when every optional attribute is empty.
            if ($href === '' && $hreflang === '' && $type === '' && $as === '' && $crossorigin === '') {
                continue;
            }

            if (! in_array($rel, self::CUSTOM_LINK_RELS, true) || ! $this->isSafeCustomLinkHref($href)) {
                throw ValidationException::withMessages([
                    'seo_custom_link_tags' => 'برای هر لینک، یک رابطهٔ مجاز و یک URL کامل با http/https یا مسیر داخلی مانند /path وارد کنید. canonical در این بخش مجاز نیست.',
                ]);
            }

            if ($hreflang !== '' && ! preg_match('/^[A-Za-z]{2,3}(?:-[A-Za-z0-9]{2,8})*$/D', $hreflang)) {
                throw ValidationException::withMessages([
                    'seo_custom_link_tags' => 'مقدار hreflang معتبر نیست؛ نمونهٔ درست: fa-IR یا en.',
                ]);
            }

            if ($type !== '' && ! preg_match('/^[A-Za-z0-9.+\/-]{1,100}$/D', $type)) {
                throw ValidationException::withMessages([
                    'seo_custom_link_tags' => 'مقدار type برای تگ link معتبر نیست.',
                ]);
            }

            if ($as !== '' && (! in_array($as, self::PRELOAD_AS_VALUES, true) || ! in_array($rel, ['preload', 'modulepreload'], true))) {
                throw ValidationException::withMessages([
                    'seo_custom_link_tags' => 'ویژگی as فقط برای preload یا modulepreload و با نوع منبع معتبر استفاده می‌شود.',
                ]);
            }

            if ($rel === 'preload' && $as === '') {
                throw ValidationException::withMessages([
                    'seo_custom_link_tags' => 'برای rel=preload باید نوع منبع (as) را مشخص کنید.',
                ]);
            }

            if ($crossorigin !== '' && ! in_array($crossorigin, ['anonymous', 'use-credentials'], true)) {
                throw ValidationException::withMessages([
                    'seo_custom_link_tags' => 'مقدار crossorigin معتبر نیست.',
                ]);
            }

            $identity = $rel . '|' . strtolower($href);
            if (isset($seen[$identity])) {
                throw ValidationException::withMessages([
                    'seo_custom_link_tags' => 'ترکیب rel و href برای هر تگ link باید یکتا باشد.',
                ]);
            }

            $seen[$identity] = true;
            $normalized[] = [
                'rel' => $rel,
                'href' => $href,
                'hreflang' => $hreflang,
                'type' => $type,
                'as' => $as,
                'crossorigin' => $crossorigin,
            ];
        }

        return $normalized;
    }

    private function isSafeCustomLinkHref(string $href): bool
    {
        if ($href === '' || preg_match('/[\x00-\x20]/', $href) || str_contains($href, chr(92))) {
            return false;
        }

        if (filter_var($href, FILTER_VALIDATE_URL)) {
            return in_array(strtolower((string) parse_url($href, PHP_URL_SCHEME)), ['http', 'https'], true);
        }

        return str_starts_with($href, '/')
            && ! str_starts_with($href, '//')
            && ! preg_match('/[\x00-\x1F]/', $href);
    }

}
