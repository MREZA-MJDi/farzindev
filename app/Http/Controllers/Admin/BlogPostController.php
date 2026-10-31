<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogTerm;
use App\Models\SeoRedirect;
use App\Services\Content\RichTextSanitizer;
use App\Services\Media\ResponsiveImageService;
use App\Services\Seo\BlogSeoAnalyzer;
use App\Services\Seo\SeoSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class BlogPostController extends Controller
{
    public function index(Request $request): View
    {
        $query = BlogPost::query()->with(['author:id,name', 'terms:id,type,name,slug']);

        if ($request->filled('q')) {
            $term = trim((string) $request->query('q'));
            $query->where(function ($builder) use ($term): void {
                $builder->where('title', 'like', '%' . $term . '%')
                    ->orWhere('slug', 'like', '%' . $term . '%');
            });
        }

        if (in_array($request->query('status'), ['draft', 'published', 'trash'], true)) {
            $query->where('status', $request->query('status'));
        } else {
            $query->where('status', '!=', 'trash');
        }

        $posts = $query
            ->orderByRaw("CASE WHEN status = 'draft' THEN 0 WHEN status = 'published' THEN 1 ELSE 2 END")
            ->orderByDesc('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.blog-posts.index', compact('posts'));
    }

    public function create(BlogSeoAnalyzer $analyzer): View
    {
        $post = new BlogPost([
            'status' => 'draft',
            'seo_robots' => 'inherit',
            'schema_type' => 'BlogPosting',
        ]);

        return view('admin.blog-posts.create', [
            'post' => $post,
            'termOptions' => $this->termOptions(),
            'selectedTermIds' => [],
            'seoReport' => $analyzer->analyze($post),
        ]);
    }

    public function store(
        Request $request,
        SeoSettings $seoSettings,
        ResponsiveImageService $images,
        RichTextSanitizer $sanitizer
    ): RedirectResponse {
        $validated = $this->validatePost($request);
        $termIds = $validated['term_ids'] ?? [];
        unset($validated['term_ids']);

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
        $validated['body_html'] = $sanitizer->sanitize($validated['body']);
        $validated['body'] = $this->plainText($validated['body_html']);
        $validated['cover_image'] = $uploadedPath;
        $validated['author_id'] = $request->user()?->id;
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['seo_robots'] = $validated['seo_robots'] ?? 'inherit';
        $validated['schema_type'] = $validated['schema_type'] ?? 'BlogPosting';
        $validated['published_at'] = $validated['status'] === 'published'
            ? (($validated['published_at'] ?? null) ?: now())
            : null;

        try {
            $post = DB::transaction(function () use ($validated, $termIds): BlogPost {
                $post = BlogPost::create($validated);
                $post->terms()->sync($termIds);

                return $post;
            });
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

    public function edit(
        BlogPost $blogPost,
        BlogSeoAnalyzer $analyzer
    ): View {
        $blogPost->load('terms:id,type,name,slug');

        return view('admin.blog-posts.edit', [
            'post' => $blogPost,
            'termOptions' => $this->termOptions(),
            'selectedTermIds' => $blogPost->terms->modelKeys(),
            'seoReport' => $analyzer->analyze($blogPost),
        ]);
    }

    public function update(
        Request $request,
        BlogPost $blogPost,
        SeoSettings $seoSettings,
        ResponsiveImageService $images,
        RichTextSanitizer $sanitizer
    ): RedirectResponse {
        $validated = $this->validatePost($request, $blogPost);
        $termIds = $validated['term_ids'] ?? [];
        unset($validated['term_ids']);

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
        $oldSlug = $blogPost->slug;
        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?? null, $validated['title'], $blogPost);
        $validated['body_html'] = $sanitizer->sanitize($validated['body']);
        $validated['body'] = $this->plainText($validated['body_html']);

        if ($uploadedPath !== null) {
            $validated['cover_image'] = $uploadedPath;
        }

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['seo_robots'] = $validated['seo_robots'] ?? 'inherit';
        $validated['schema_type'] = $validated['schema_type'] ?? 'BlogPosting';
        $validated['published_at'] = $validated['status'] === 'published'
            ? (($validated['published_at'] ?? null) ?: ($blogPost->published_at ?? now()))
            : null;

        try {
            DB::transaction(function () use ($blogPost, $validated, $termIds): void {
                $lockedPost = BlogPost::query()
                    ->whereKey($blogPost->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $lockedPost->update($validated);
                $lockedPost->terms()->sync($termIds);
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

        if ($oldSlug !== $validated['slug']) {
            SeoRedirect::query()->updateOrCreate(
                ['source_path' => route('blog.show', $oldSlug, false)],
                [
                    'target_url' => route('blog.show', $validated['slug'], false),
                    'status_code' => 301,
                    'is_active' => true,
                    'notes' => 'تغییر خودکار نشانی مقاله ' . $blogPost->id,
                ]
            );
        }

        $seoSettings->clearCache();

        return redirect()
            ->route('admin.blog-posts.edit', $blogPost)
            ->with('success', 'تغییرات مقاله ذخیره شد.');
    }

    public function destroy(
        BlogPost $blogPost,
        SeoSettings $seoSettings
    ): RedirectResponse {
        // WordPress-style Trash: keep editorial history and media recoverable.
        $blogPost->update([
            'status' => 'trash',
            'published_at' => null,
        ]);

        $seoSettings->clearCache();

        return redirect()
            ->route('admin.blog-posts.index')
            ->with('success', 'مقاله به زباله‌دان منتقل شد؛ محتوا و تصویر آن حذف نشده‌اند.');
    }

    /**
     * @return array<int, BlogTerm>
     */
    private function termOptions(): array
    {
        return BlogTerm::query()
            ->active()
            ->orderBy('type')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function validatePost(Request $request, ?BlogPost $post = null): array
    {
        $slugRule = ['nullable', 'string', 'max:180', 'regex:/^[A-Za-z0-9-]+$/'];
        $slugRule[] = $post
            ? Rule::unique('blog_posts', 'slug')->ignore($post->id)
            : Rule::unique('blog_posts', 'slug');

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => $slugRule,
            'excerpt' => ['nullable', 'string', 'max:600'],
            'body' => ['required', 'string', 'max:2000000'],
            'cover_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'dimensions:min_width=800,min_height=450', 'max:8192'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'focus_keyword' => ['nullable', 'string', 'max:160'],
            'canonical_url' => ['nullable', 'url:https', 'max:2048'],
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:320'],
            'seo_robots' => ['nullable', Rule::in(['inherit', 'index,follow', 'noindex,follow', 'noindex,nofollow'])],
            'schema_type' => ['nullable', Rule::in(['BlogPosting', 'Article', 'NewsArticle'])],
            'is_featured' => ['sometimes', 'boolean'],
            'term_ids' => ['nullable', 'array', 'max:30'],
            'term_ids.*' => ['integer', 'distinct', 'exists:blog_terms,id'],
            'status' => ['required', Rule::in(['draft', 'published', 'trash'])],
            'published_at' => ['nullable', 'date'],
        ], [
            'title.required' => 'عنوان مقاله الزامی است.',
            'slug.regex' => 'نشانی مقاله باید فقط شامل حروف انگلیسی، عدد و خط تیره باشد.',
            'slug.unique' => 'این نشانی قبلاً برای مقاله‌ای دیگر استفاده شده است.',
            'body.required' => 'متن مقاله را وارد کنید.',
            'canonical_url.url' => 'Canonical باید یک آدرس HTTPS معتبر باشد.',
            'cover_image.dimensions' => 'تصویر شاخص باید حداقل ۸۰۰ در ۴۵۰ پیکسل باشد.',
            'cover_image.max' => 'حجم تصویر شاخص نباید بیش از ۸ مگابایت باشد.',
            'meta_description.max' => 'توضیحات SEO حداکثر ۳۲۰ کاراکتر است.',
        ]);

        $termIds = array_map('intval', $validated['term_ids'] ?? []);
        if ($termIds !== []) {
            $activeCount = BlogTerm::query()
                ->whereIn('id', $termIds)
                ->where('is_active', true)
                ->count();

            if ($activeCount !== count($termIds)) {
                abort(422, 'یکی از دسته‌بندی‌ها یا برچسب‌های انتخاب‌شده غیرفعال یا نامعتبر است.');
            }
        }

        return $validated;
    }

    private function plainText(string $html): string
    {
        return trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');
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
