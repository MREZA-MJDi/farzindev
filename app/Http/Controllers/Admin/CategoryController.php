<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Category\StoreCategoryRequest;
use App\Http\Requests\Admin\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\Media;
use App\Services\Media\ResponsiveImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Throwable;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(): View
    {
        $categories = Category::query()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        $motionMedia = $this->selectableMotionMedia();

        return view('admin.categories.create', compact('motionMedia'));
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request, ResponsiveImageService $images): RedirectResponse
    {
        $data = $request->validated();
        $uploadedImage = $request->file('image');
        unset($data['image']);

        $newImage = null;

        if ($uploadedImage) {
            $newImage = $images->store($uploadedImage, 'categories');

            if (! $newImage) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'image' => 'ذخیره تصویر دسته‌بندی انجام نشد؛ دوباره تلاش کنید.',
                    ]);
            }
        }

        try {
            DB::transaction(function () use ($data, $newImage): void {
                $payload = $data;

                if ($newImage !== null) {
                    $payload['image'] = $newImage;
                }

                Category::create($payload);
            });
        } catch (Throwable $e) {
            if ($newImage !== null) {
                $images->delete($newImage);
            }

            throw $e;
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'دسته‌بندی با موفقیت ایجاد شد.');
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category): View
    {
        return view('admin.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): View
    {
        $motionMedia = $this->selectableMotionMedia();

        return view('admin.categories.edit', compact('category', 'motionMedia'));
    }

    /**
     * Update the specified category.
     */
    public function update(
        UpdateCategoryRequest $request,
        Category $category,
        ResponsiveImageService $images
    ): RedirectResponse {
        $data = $request->validated();
        $uploadedImage = $request->file('image');
        unset($data['image']);

        $newImage = null;

        if ($uploadedImage) {
            $newImage = $images->store($uploadedImage, 'categories');

            if (! $newImage) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'image' => 'ذخیره تصویر دسته‌بندی انجام نشد؛ تصویر قبلی حفظ شد.',
                    ]);
            }
        }

        $oldImage = null;

        try {
            DB::transaction(function () use ($category, $data, $newImage, &$oldImage): void {
                $lockedCategory = Category::query()
                    ->whereKey($category->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $oldImage = $lockedCategory->image;
                $payload = $data;

                if ($newImage !== null) {
                    $payload['image'] = $newImage;
                }

                $lockedCategory->update($payload);
            });
        } catch (Throwable $e) {
            if ($newImage !== null) {
                Storage::disk('public')->delete($newImage);
            }

            throw $e;
        }

        if (
            $newImage !== null
            && $oldImage
            && ! str_starts_with($oldImage, 'http://')
            && ! str_starts_with($oldImage, 'https://')
            && ! str_starts_with($oldImage, '//')
        ) {
            $images->delete($oldImage);
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'دسته‌بندی با موفقیت بروزرسانی شد.');
    }

    /**
     * Media suitable for short category previews.
     * GIFs are capped at 10 MiB and MP4/WebM clips at 30 MiB.
     */
    private function selectableMotionMedia()
    {
        return Media::query()
            ->where(function ($query): void {
                $query->where(function ($gif): void {
                    $gif->where('mime_type', 'image/gif')
                        ->where('size', '<=', 10 * 1024 * 1024);
                })->orWhere(function ($video): void {
                    $video->whereIn('mime_type', ['video/mp4', 'video/webm'])
                        ->where('size', '<=', 30 * 1024 * 1024);
                });
            })
            ->latest()
            ->get(['id', 'original_name', 'mime_type', 'extension', 'size']);
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category, ResponsiveImageService $images): RedirectResponse
    {
        // Soft-deleted products still reference this category through
        // a restrictive foreign key, so they must be considered too.
        if ($category->products()->withTrashed()->exists()) {
            return redirect()
                ->route('admin.categories.index')
                ->with(
                    'error',
                    'این دسته‌بندی هنوز محصول دارد و تا حذف یا انتقال محصولات قابل حذف نیست.'
                );
        }

        $image = $category->image;

        $category->delete();

        if (
            $image
            && ! str_starts_with($image, 'http://')
            && ! str_starts_with($image, 'https://')
            && ! str_starts_with($image, '//')
        ) {
            $images->delete($image);
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'دسته‌بندی با موفقیت حذف شد.');
    }
}
