<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StorefrontSetting;
use App\Services\Seo\SeoSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class SeoSettingsController extends Controller
{
    public function edit(SeoSettings $seoSettings): View
    {
        $settings = $seoSettings->all();

        return view('admin.seo-settings.edit', [
            'settings' => $settings,
            'ogImageUrl' => $seoSettings->resolvePublicAssetUrl($settings['seo_default_og_image'] ?? null),
        ]);
    }

    public function update(Request $request, SeoSettings $seoSettings): RedirectResponse
    {
        $validated = $request->validate([
            'seo_canonical_base_url' => ['required', 'url', 'regex:~^https?://[A-Za-z0-9.-]+(?::[0-9]{1,5})?/?$~i', 'max:255'],
            'seo_site_name' => ['required', 'string', 'max:100'],
            'seo_default_title' => ['required', 'string', 'max:255'],
            'seo_default_description' => ['required', 'string', 'max:320'],
            'seo_default_keywords' => ['nullable', 'string', 'max:1000'],
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
            $newImagePath = $request->file('seo_default_og_image_upload')->store('seo', 'public');
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
                Storage::disk('public')->delete($newImagePath);
            }
            throw $exception;
        }

        if (
            $oldImageValue
            && $oldImageValue !== ($validated['seo_default_og_image'] ?? null)
            && ! filter_var($oldImageValue, FILTER_VALIDATE_URL)
            && ! str_starts_with($oldImageValue, '/')
        ) {
            Storage::disk('public')->delete($oldImageValue);
        }

        $seoSettings->clearCache();

        return redirect()
            ->route('admin.seo-settings.edit')
            ->with('success', 'تنظیمات SEO ذخیره شد و روی خروجی عمومی سایت اعمال می‌شود.');
    }
}
