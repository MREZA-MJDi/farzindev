<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Services\Seo\SeoSettings;
use App\Services\Media\ResponsiveImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class BlogPostController extends Controller
{
    public function index(Request $request): View
    {
        $query = BlogPost::query()->with('author:id,name');

        if ($request->filled('q')) {
            $term = trim((string) $request->query('q'));
            $query->where(function ($builder) use ($term): void {
                $builder->where('title', 'like', '%' . $term . '%')
                    ->orWhere('slug', 'like', '%' . $term . '%');
            });
        }

        if (in_array($request->query('status'), ['draft', 'published'], true)) {
            $query->where('status', $request->query('status'));
        }

        $posts = $query
            ->orderByRaw("CASE WHEN status = 'draft' THEN 0 ELSE 1 END")
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.blog-posts.index', compact('posts'));
    }

    public function create(): View
    {
        return view('admin.blog-posts.create', [
            'post' => new BlogPost(['status' => 'draft']),
        ]);
    }

    public function store(Request $request, SeoSettings $seoSettings, ResponsiveImageService $images): RedirectResponse
    {
        $validated = $this->validatePost($request);
        $uploadedPath = null;

        if ($request->hasFile('cover_image')) {
            $uploadedPath = $images->store($request->file('cover_image'), 'blog');
            if (! $uploadedPath) {
                return back()->withInput()->withErrors([
                    'cover_image' => 'ذخیره تصویر شاخص انجام نشد؛ دوباره تلاش کنید.',
                ]);
            }
        }

        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?? null, $validated['title']);
        $validated['cover_image'] = $uploadedPath;
        $validated['author_id'] = $request->user()?->id;
        $validated['published_at'] = $validated['status'] === 'published'
            ? (($validated['published_at'] ?? null) ?: now())
            : ($validated['published_at'] ?? null);

        try {
            $post = DB::transaction(fn (): BlogPost => BlogPost::create($validated));
        } catch (Throwable $exception) {
            if ($uploadedPath !== null) {
                $images->delete($uploadedPath);
            }
            throw $exception;
        }

        $seoSettings->clearCache();

        return redirect()
            ->route('admin.blog-posts.edit', $post)
            ->with('success', 'مقاله ذخیره شد.');
    }

    public function edit(BlogPost $blogPost): View
    {
        return view('admin.blog-posts.edit', ['post' => $blogPost]);
    }

    public function update(Request $request, BlogPost $blogPost, SeoSettings $seoSettings, ResponsiveImageService $images): RedirectResponse
    {
        $validated = $this->validatePost($request, $blogPost);
        $uploadedPath = null;

        if ($request->hasFile('cover_image')) {
            $uploadedPath = $images->store($request->file('cover_image'), 'blog');
            if (! $uploadedPath) {
                return back()->withInput()->withErrors([
                    'cover_image' => 'ذخیره تصویر جدید انجام نشد؛ تصویر قبلی حفظ شد.',
                ]);
            }
        }

        $oldImage = $blogPost->cover_image;
        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?? null, $validated['title'], $blogPost);
        if ($uploadedPath !== null) {
            $validated['cover_image'] = $uploadedPath;
        }
        $validated['published_at'] = $validated['status'] === 'published'
            ? (($validated['published_at'] ?? null) ?: ($blogPost->published_at ?? now()))
            : null;

        try {
            DB::transaction(function () use ($blogPost, $validated): void {
                $lockedPost = BlogPost::query()
                    ->whereKey($blogPost->id)
                    ->lockForUpdate()
                    ->firstOrFail();
                $lockedPost->update($validated);
            });
        } catch (Throwable $exception) {
            if ($uploadedPath !== null) {
                $images->delete($uploadedPath);
            }
            throw $exception;
        }

        if ($uploadedPath !== null && $oldImage && $oldImage !== $uploadedPath) {
            $images->delete($oldImage);
        }

        $seoSettings->clearCache();

        return redirect()
            ->route('admin.blog-posts.edit', $blogPost)
            ->with('success', 'تغییرات مقاله ذخیره شد.');
    }

    public function destroy(BlogPost $blogPost, SeoSettings $seoSettings, ResponsiveImageService $images): RedirectResponse
    {
        $image = $blogPost->cover_image;
        $blogPost->delete();

        if ($image) {
            $images->delete($image);
        }

        $seoSettings->clearCache();

        return redirect()
            ->route('admin.blog-posts.index')
            ->with('success', 'مقاله حذف شد.');
    }

    private function validatePost(Request $request, ?BlogPost $post = null): array
    {
        $slugRule = ['nullable', 'string', 'max:255', 'regex:/^[A-Za-z0-9-]+$/'];
        $slugRule[] = $post
            ? \Illuminate\Validation\Rule::unique('blog_posts', 'slug')->ignore($post->id)
            : \Illuminate\Validation\Rule::unique('blog_posts', 'slug');

        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => $slugRule,
            'excerpt' => ['nullable', 'string', 'max:600'],
            'body' => ['required', 'string'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'dimensions:min_width=800,min_height=450', 'max:8192'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'status' => ['required', 'in:draft,published'],
            'published_at' => ['nullable', 'date'],
        ], [
            'title.required' => 'عنوان مقاله الزامی است.',
            'slug.regex' => 'نشانی مقاله باید فقط شامل حروف انگلیسی، عدد و خط تیره باشد.',
            'slug.unique' => 'این نشانی قبلاً برای مقاله‌ای دیگر استفاده شده است.',
            'body.required' => 'متن مقاله را وارد کنید.',
            'cover_image.dimensions' => 'تصویر شاخص باید حداقل ۸۰۰ در ۴۵۰ پیکسل باشد.',
            'cover_image.max' => 'حجم تصویر شاخص نباید بیش از ۸ مگابایت باشد.',
            'meta_description.max' => 'توضیحات SEO حداکثر ۳۲۰ کاراکتر است.',
        ]);
    }

    private function uniqueSlug(?string $requestedSlug, string $title, ?BlogPost $current = null): string
    {
        $slug = Str::slug(trim((string) $requestedSlug));
        if ($slug === '' && $current && blank($requestedSlug)) {
            $slug = $current->slug;
        }
        if ($slug === '') {
            $slug = Str::slug($title);
        }
        if ($slug === '') {
            $slug = 'article-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(5));
        }

        $base = $slug;
        $suffix = 2;
        while (BlogPost::query()
            ->where('slug', $slug)
            ->when($current, fn ($query) => $query->whereKeyNot($current->id))
            ->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }
}
