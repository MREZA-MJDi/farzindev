<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogTerm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlogTermController extends Controller
{
    public function index(): View
    {
        $terms = BlogTerm::query()
            ->withCount('posts')
            ->orderBy('type')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(30);

        return view('admin.blog-terms.index', compact('terms'));
    }

    public function create(): View
    {
        return view('admin.blog-terms.create', ['term' => new BlogTerm(['type' => 'category', 'is_active' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTerm($request);
        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?? null, $validated['name'], $validated['type']);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        $term = BlogTerm::create($validated);

        return redirect()->route('admin.blog-terms.edit', $term)->with('success', 'دسته یا برچسب ذخیره شد.');
    }

    public function edit(BlogTerm $blogTerm): View
    {
        return view('admin.blog-terms.edit', ['term' => $blogTerm]);
    }

    public function update(Request $request, BlogTerm $blogTerm): RedirectResponse
    {
        $validated = $this->validateTerm($request, $blogTerm);
        $validated['slug'] = $this->uniqueSlug($validated['slug'] ?? null, $validated['name'], $validated['type'], $blogTerm);
        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = (int) ($validated['sort_order'] ?? 0);

        $blogTerm->update($validated);

        return redirect()->route('admin.blog-terms.edit', $blogTerm)->with('success', 'تغییرات ذخیره شد.');
    }

    public function destroy(BlogTerm $blogTerm): RedirectResponse
    {
        $blogTerm->delete();

        return redirect()->route('admin.blog-terms.index')->with('success', 'دسته یا برچسب حذف شد؛ ارتباط آن از مقاله‌ها برداشته شد.');
    }

    private function validateTerm(Request $request, ?BlogTerm $term = null): array
    {
        $type = $request->input('type', $term?->type ?? BlogTerm::TYPE_CATEGORY);

        return $request->validate([
            'type' => ['required', Rule::in([BlogTerm::TYPE_CATEGORY, BlogTerm::TYPE_TAG])],
            'name' => ['required', 'string', 'max:160'],
            'slug' => ['nullable', 'string', 'max:180', 'regex:/^[A-Za-z0-9-]+$/'],
            'description' => ['nullable', 'string', 'max:2000'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'seo_robots' => ['nullable', Rule::in(['inherit', 'index,follow', 'noindex,follow', 'noindex,nofollow'])],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ], [
            'slug.regex' => 'Slug باید فقط شامل حروف انگلیسی، عدد و خط تیره باشد.',
        ]);
    }

    private function uniqueSlug(?string $requestedSlug, string $name, string $type, ?BlogTerm $current = null): string
    {
        $slug = Str::slug(trim((string) $requestedSlug)) ?: Str::slug($name);
        if ($slug === '') {
            $slug = $type . '-' . Str::lower(Str::random(8));
        }

        $base = $slug;
        $suffix = 2;
        while (BlogTerm::query()
            ->where('type', $type)
            ->where('slug', $slug)
            ->when($current, fn ($query) => $query->whereKeyNot($current->id))
            ->exists()) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }
}
