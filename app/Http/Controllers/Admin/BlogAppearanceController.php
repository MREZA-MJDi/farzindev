<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StorefrontSetting;
use App\Services\Blog\BlogAppearance;
use App\Services\Media\ResponsiveImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class BlogAppearanceController extends Controller
{
    public function edit(BlogAppearance $appearance): View
    {
        return view('admin.blog-appearance.edit', [
            'appearance' => $appearance->all(),
        ]);
    }

    public function update(
        Request $request,
        ResponsiveImageService $images,
        BlogAppearance $appearance
    ): RedirectResponse {
        $validated = $request->validate([
            'hero_art_alt' => ['sometimes', 'nullable', 'string', 'max:255'],
            'hero_art_fit' => ['sometimes', Rule::in(['cover', 'contain'])],
            'hero_art_scale' => ['sometimes', 'integer', 'between:70,135'],
            'hero_art_position' => ['sometimes', Rule::in(['center', 'top', 'bottom', 'left', 'right'])],
            'hero_art_image_upload' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'dimensions:min_width=640,min_height=640', 'max:8192'],
            'hero_art_image_remove' => ['sometimes', 'boolean'],

            'closing_art_alt' => ['sometimes', 'nullable', 'string', 'max:255'],
            'closing_art_fit' => ['sometimes', Rule::in(['cover', 'contain'])],
            'closing_art_scale' => ['sometimes', 'integer', 'between:70,135'],
            'closing_art_position' => ['sometimes', Rule::in(['center', 'top', 'bottom', 'left', 'right'])],
            'closing_art_image_upload' => ['sometimes', 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'dimensions:min_width=640,min_height=640', 'max:8192'],
            'closing_art_image_remove' => ['sometimes', 'boolean'],

            'container_width' => ['sometimes', 'integer', 'between:1080,1440'],
        ], [
            'hero_art_image_upload.dimensions' => 'تصویر Hero پس از برش باید حداقل ۶۴۰ در ۶۴۰ پیکسل باشد.',
            'closing_art_image_upload.dimensions' => 'تصویر بخش پایانی پس از برش باید حداقل ۶۴۰ در ۶۴۰ پیکسل باشد.',
            'hero_art_image_upload.max' => 'حجم تصویر Hero نباید بیشتر از ۸ مگابایت باشد.',
            'closing_art_image_upload.max' => 'حجم تصویر بخش پایانی نباید بیشتر از ۸ مگابایت باشد.',
            'container_width.between' => 'عرض کانتینر باید بین ۱۰۸۰ تا ۱۴۴۰ پیکسل باشد.',
        ]);

        $updates = [];
        $oldPaths = [];
        $newPaths = [];

        foreach ([
            'hero_art_alt' => 'blog_hero_art_alt',
            'hero_art_fit' => 'blog_hero_art_fit',
            'hero_art_scale' => 'blog_hero_art_scale',
            'hero_art_position' => 'blog_hero_art_position',
            'closing_art_alt' => 'blog_closing_art_alt',
            'closing_art_fit' => 'blog_closing_art_fit',
            'closing_art_scale' => 'blog_closing_art_scale',
            'closing_art_position' => 'blog_closing_art_position',
            'container_width' => 'blog_container_width',
        ] as $input => $key) {
            if (array_key_exists($input, $validated)) {
                $updates[$key] = $validated[$input] === null ? null : (string) $validated[$input];
            }
        }

        foreach ([
            'hero_art' => ['upload' => 'hero_art_image_upload', 'remove' => 'hero_art_image_remove'],
            'closing_art' => ['upload' => 'closing_art_image_upload', 'remove' => 'closing_art_image_remove'],
        ] as $slot => $fields) {
            $settingKey = 'blog_' . $slot . '_image';
            $oldPath = StorefrontSetting::query()->where('key', $settingKey)->value('value');

            if ($request->hasFile($fields['upload'])) {
                $newPath = $images->store($request->file($fields['upload']), 'blog-design');

                if (! $newPath) {
                    foreach ($newPaths as $path) {
                        $images->delete($path);
                    }

                    return back()->withInput()->withErrors([
                        $fields['upload'] => 'ذخیره تصویر انجام نشد؛ تصویر فعلی تغییر نکرد.',
                    ]);
                }

                $newPaths[] = $newPath;
                $updates[$settingKey] = $newPath;

                if (filled($oldPath) && $oldPath !== $newPath) {
                    $oldPaths[] = $oldPath;
                }
            } elseif ($request->boolean($fields['remove'])) {
                $updates[$settingKey] = null;

                if (filled($oldPath)) {
                    $oldPaths[] = $oldPath;
                }
            }
        }

        if ($updates === []) {
            return back()->with('success', 'تغییری برای ذخیره انتخاب نشده است.');
        }

        try {
            DB::transaction(function () use ($updates): void {
                foreach ($updates as $key => $value) {
                    StorefrontSetting::query()->updateOrCreate(
                        ['key' => $key],
                        ['value' => $value]
                    );
                }
            });
        } catch (Throwable $exception) {
            foreach ($newPaths as $path) {
                $images->delete($path);
            }

            throw $exception;
        }

        foreach (array_unique($oldPaths) as $path) {
            if (
                filled($path)
                && ! filter_var($path, FILTER_VALIDATE_URL)
                && ! Str::startsWith($path, '/')
            ) {
                $images->delete($path);
            }
        }

        $appearance->clearCache();

        return redirect()
            ->route('admin.blog-appearance.edit')
            ->with('success', 'تصاویر و چیدمان مجله ذخیره شد؛ تغییرات در صفحه بلاگ اعمال شده‌اند.');
    }
}
