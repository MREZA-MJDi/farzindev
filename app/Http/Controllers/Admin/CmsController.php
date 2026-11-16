<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogTerm;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CmsController extends Controller
{
    public function index(Request $request): View
    {
        $tab = $request->query('tab', 'content');
        if (! in_array($tab, ['content', 'blog', 'seo', 'storefront', 'media'], true)) {
            $tab = 'content';
        }

        $posts = null;
        $terms = null;

        // Heavy CMS lists are fetched only when the blog sheet is active.
        // Each collection has its own bounded pagination window.
        if ($tab === 'blog') {
            $search = trim((string) $request->query('q', ''));

            $posts = BlogPost::query()
                ->with(['author:id,name', 'terms:id,type,name,slug'])
                ->when($search !== '', fn ($query) => $query->where(function ($builder) use ($search): void {
                    $builder->where('title', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%');
                }))
                ->where('status', '!=', 'trash')
                ->orderByRaw("CASE WHEN status = 'draft' THEN 0 WHEN status = 'published' THEN 1 ELSE 2 END")
                ->orderByDesc('updated_at')
                ->paginate(15, ['*'], 'posts_page')
                ->withQueryString();

            $terms = BlogTerm::query()
                ->withCount('posts')
                ->when($search !== '', fn ($query) => $query->where(function ($builder) use ($search): void {
                    $builder->where('name', 'like', '%' . $search . '%')
                        ->orWhere('slug', 'like', '%' . $search . '%');
                }))
                ->orderBy('type')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->paginate(15, ['*'], 'terms_page')
                ->withQueryString();
        }

        return view('admin.cms.index', compact('tab', 'posts', 'terms'));
    }
}
