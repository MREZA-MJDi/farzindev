<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactSetting;
use App\Models\StorefrontSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;
use Illuminate\View\View;

class StorefrontSettingController extends Controller
{
    public function edit(): View
    {
        $settings = StorefrontSetting::query()
            ->whereIn('key', [
                'shop_banner_image',
                'shop_banner_title',
                'shop_banner_subtitle',
                'shipping_flat_rate',
                'shipping_free_threshold',
            ])
            ->pluck('value', 'key');

        $bannerPath = $settings->get('shop_banner_image');

        return view('admin.storefront-settings.edit', [
            'shopBannerTitle' => $settings->get('shop_banner_title') ?: 'فروشگاه فرزین',
            'shopBannerSubtitle' => $settings->get('shop_banner_subtitle') ?: 'هود و سینک آشپزخانه؛ انتخابی دقیق برای فضای شما.',
            'shopBannerPath' => $bannerPath,
            'shopBannerUrl' => $bannerPath ? Storage::disk('public')->url($bannerPath) : null,
            'shippingFlatRate' => (int) ($settings->get('shipping_flat_rate') ?? 0),
            'shippingFreeThreshold' => (int) ($settings->get('shipping_free_threshold') ?? 0),
            'contactSetting' => ContactSetting::query()->first(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'shop_banner_image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'dimensions:min_width=1200,min_height=600',
                'max:20480',
            ],
            'shop_banner_title' => ['required', 'string', 'max:255'],
            'shop_banner_subtitle' => ['nullable', 'string', 'max:500'],
            'shipping_flat_rate' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:1000000000000'],
            'shipping_free_threshold' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:1000000000000'],
            'remove_shop_banner' => ['nullable', 'boolean'],

            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'whatsapp' => ['nullable', 'string', 'max:30', 'regex:/^[+0-9()\-\s]+$/'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'address' => ['nullable', 'string', 'max:2000'],
            'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
            'hours' => ['nullable', 'string', 'max:255'],
            'phone_label' => ['nullable', 'string', 'max:100'],
            'email_label' => ['nullable', 'string', 'max:100'],
            'support_label' => ['nullable', 'string', 'max:100'],
            'support_description' => ['nullable', 'string', 'max:500'],
            'online_label' => ['nullable', 'string', 'max:100'],
            'online_description' => ['nullable', 'string', 'max:500'],
        ], [
            'shop_banner_image.image' => 'فایل بنر باید یک تصویر معتبر باشد.',
            'shop_banner_image.mimes' => 'فرمت بنر باید JPG، PNG یا WEBP باشد.',
            'shop_banner_image.max' => 'حجم تصویر بنر نباید بیشتر از ۲۰ مگابایت باشد.',
            'shop_banner_image.dimensions' => 'ابعاد بنر باید حداقل ۱۲۰۰ در ۶۰۰ پیکسل باشد.',
            'shop_banner_title.required' => 'عنوان بنر الزامی است.',
            'email.email' => 'فرمت ایمیل معتبر نیست.',
            'whatsapp.regex' => 'شماره واتساپ را فقط با رقم و علامت‌های متداول وارد کنید.',
            'instagram_url.url' => 'لینک اینستاگرام معتبر نیست.',
            'latitude.required_with' => 'برای ثبت مختصات نقشه، طول و عرض جغرافیایی را هر دو وارد کنید.',
            'longitude.required_with' => 'برای ثبت مختصات نقشه، طول و عرض جغرافیایی را هر دو وارد کنید.',
            'latitude.numeric' => 'عرض جغرافیایی باید عددی باشد.',
            'longitude.numeric' => 'طول جغرافیایی باید عددی باشد.',
            'latitude.between' => 'عرض جغرافیایی باید بین ۹۰- و ۹۰ باشد.',
            'longitude.between' => 'طول جغرافیایی باید بین ۱۸۰- و ۱۸۰ باشد.',
        ]);

        $oldPath = StorefrontSetting::query()
            ->where('key', 'shop_banner_image')
            ->value('value');

        $newPath = null;

        if ($request->hasFile('shop_banner_image')) {
            $newPath = $request->file('shop_banner_image')->store('banners', 'public');

            if (! $newPath) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'shop_banner_image' => 'ذخیره تصویر بنر انجام نشد؛ فایل قبلی حفظ شد.',
                    ]);
            }
        }

        $removeBanner = $newPath === null
            && $request->boolean('remove_shop_banner');

        try {
            DB::transaction(function () use ($data, $newPath, $removeBanner): void {
                if ($newPath !== null || $removeBanner) {
                    StorefrontSetting::query()->updateOrCreate(
                        ['key' => 'shop_banner_image'],
                        ['value' => $newPath]
                    );
                }

                StorefrontSetting::query()->updateOrCreate(
                    ['key' => 'shop_banner_title'],
                    ['value' => $data['shop_banner_title']]
                );

                StorefrontSetting::query()->updateOrCreate(
                    ['key' => 'shop_banner_subtitle'],
                    ['value' => $data['shop_banner_subtitle'] ?? null]
                );

                if (array_key_exists('shipping_flat_rate', $data)) {
                    StorefrontSetting::query()->updateOrCreate(
                        ['key' => 'shipping_flat_rate'],
                        ['value' => $data['shipping_flat_rate'] ?? 0]
                    );
                }

                if (array_key_exists('shipping_free_threshold', $data)) {
                    StorefrontSetting::query()->updateOrCreate(
                        ['key' => 'shipping_free_threshold'],
                        ['value' => $data['shipping_free_threshold'] ?: null]
                    );
                }

                $contact = ContactSetting::query()->first() ?? new ContactSetting();
                $contact->fill([
                    'phone' => $data['phone'] ?? null,
                    'email' => $data['email'] ?? null,
                    'whatsapp' => $data['whatsapp'] ?? null,
                    'instagram_url' => $data['instagram_url'] ?? null,
                    'address' => $data['address'] ?? null,
                    'latitude' => $data['latitude'] ?? null,
                    'longitude' => $data['longitude'] ?? null,
                    'hours' => $data['hours'] ?? null,
                    'phone_label' => $data['phone_label'] ?? 'تماس تلفنی',
                    'email_label' => $data['email_label'] ?? 'ایمیل',
                    'support_label' => $data['support_label'] ?? 'پشتیبانی',
                    'support_description' => $data['support_description'] ?? null,
                    'online_label' => $data['online_label'] ?? 'ارتباط آنلاین',
                    'online_description' => $data['online_description'] ?? null,
                    'is_active' => true,
                ]);
                $contact->save();
            });
        } catch (Throwable $e) {
            // Keep the currently published image if the database update fails.
            if ($newPath !== null) {
                Storage::disk('public')->delete($newPath);
            }

            throw $e;
        }

        // The new settings have committed; only now remove the superseded file.
        if (
            ($newPath !== null || $removeBanner)
            && $oldPath
            && $oldPath !== $newPath
        ) {
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()
            ->route('admin.storefront-settings.edit')
            ->with('success', 'تنظیمات فروشگاه با موفقیت ذخیره شد.');
    }
}
