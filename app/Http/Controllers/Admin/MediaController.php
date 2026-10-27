<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Media\MediaUploadRequest;
use App\Models\Media;
use App\Models\ProductImage;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class MediaController extends Controller
{
    public function index(): View
    {
        $media = Media::with('user')->latest()->paginate(24);

        return view('admin.media.index', compact('media'));
    }

    public function create(): View
    {
        return view('admin.media.create');
    }

    public function store(MediaUploadRequest $request): RedirectResponse
    {
        $file = $request->file('file');
        $disk = 'public';

        try {
            $path = $file->store('media', $disk);
        } catch (Throwable $exception) {
            Log::error('Media upload storage write failed.', [
                'user_id' => $request->user()?->id,
                'extension' => $file->getClientOriginalExtension(),
                'bytes' => $file->getSize(),
                'exception' => $exception,
            ]);

            return back()->withInput()->withErrors([
                'file' => 'ذخیره‌سازی رسانه ناموفق بود؛ فضای دیسک و دسترسی نوشتن پوشه storage را بررسی کنید.',
            ]);
        }

        if (! $path) {
            return back()->withInput()->withErrors([
                'file' => 'ذخیره فایل رسانه انجام نشد؛ دوباره تلاش کنید.',
            ]);
        }

        try {
            DB::transaction(function () use ($file, $disk, $path, $request): void {
                Media::create([
                    'user_id' => $request->user()?->id,
                    'disk' => $disk,
                    'path' => $path,
                    'filename' => basename($path),
                    'original_name' => Str::limit($file->getClientOriginalName(), 255, ''),
                    'mime_type' => $file->getMimeType() ?: $file->getClientMimeType(),
                    'extension' => strtolower($file->getClientOriginalExtension()),
                    'size' => $file->getSize(),
                    'alt' => $request->input('alt'),
                    'description' => $request->input('description'),
                ]);
            });
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($path);

            Log::error('Media upload database write failed.', [
                'user_id' => $request->user()?->id,
                'extension' => $file->getClientOriginalExtension(),
                'bytes' => $file->getSize(),
                'exception' => $exception,
            ]);

            return back()->withInput()->withErrors([
                'file' => 'فایل ذخیره شد، اما ثبت اطلاعات آن ناموفق بود؛ گزارش خطا ثبت شده است.',
            ]);
        }

        return redirect()->route('admin.media.index')->with('success', 'رسانه با موفقیت آپلود شد.');
    }

    public function destroy(Media $media): RedirectResponse
    {
        // A Media asset can be reused by multiple products and variants. Never
        // remove the underlying file while product_images still references it.
        $usedByProduct = $media->productImages()->exists()
            || ProductImage::query()->where('path', $media->path)->exists();
        $usedByCategory = Category::query()->where('hover_media_id', $media->id)->exists();

        if ($usedByProduct || $usedByCategory) {
            return redirect()
                ->route('admin.media.index')
                ->with('error', $usedByCategory
                    ? 'این رسانه برای پیش‌نمایش یک دسته‌بندی استفاده می‌شود؛ ابتدا آن اتصال را بردارید.'
                    : 'این رسانه به گالری یک یا چند محصول یا تنوع متصل است؛ ابتدا اتصال آن را از ویرایش محصولات بردارید.');
        }

        $disk = $media->disk;
        $path = $media->path;

        DB::transaction(function () use ($media): void {
            $media->delete();
        });

        if ($disk && $path) {
            Storage::disk($disk)->delete($path);
        }

        return redirect()->route('admin.media.index')->with('success', 'رسانه با موفقیت حذف شد.');
    }
}
