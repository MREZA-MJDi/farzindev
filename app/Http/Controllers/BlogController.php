<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BlogTerm;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class BlogController extends Controller
{
    public function index(): View
    {
        $posts = $this->publishedPosts()->paginate(9)->withQueryString();

        return view('blog.index', [
            'posts' => $posts,
            'term' => null,
        ]);
    }

    public function category(string $slug): View
    {
        return $this->term(BlogTerm::TYPE_CATEGORY, $slug);
    }

    public function tag(string $slug): View
    {
        return $this->term(BlogTerm::TYPE_TAG, $slug);
    }

    public function term(string $type, string $slug): View
    {
        abort_unless(in_array($type, [BlogTerm::TYPE_CATEGORY, BlogTerm::TYPE_TAG], true), 404);

        $term = BlogTerm::query()
            ->active()
            ->ofType($type)
            ->where('slug', $slug)
            ->firstOrFail();

        $posts = $this->publishedPosts()
            ->whereHas('terms', fn ($query) => $query->whereKey($term->id))
            ->paginate(9)
            ->withQueryString();

        return view('blog.index', compact('posts', 'term'));
    }

    public function show(string $slug): View
    {
        $post = BlogPost::query()
            ->published()
            ->with(['author:id,name', 'terms:id,type,name,slug'])
            ->where('slug', $slug)
            ->firstOrFail();

        $termIds = $post->terms->modelKeys();
        $relatedQuery = BlogPost::query()
            ->published()
            ->whereKeyNot($post->id);

        if ($termIds !== []) {
            $relatedQuery->whereHas('terms', fn ($terms) => $terms->whereIn('blog_terms.id', $termIds));
        }

        $relatedPosts = $relatedQuery
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        if ($relatedPosts->isEmpty()) {
            $relatedPosts = BlogPost::query()
                ->published()
                ->whereKeyNot($post->id)
                ->orderByDesc('published_at')
                ->limit(3)
                ->get();
        }

        return view('blog.show', compact('post', 'relatedPosts'));
    }

    public function feed(): Response
    {
        $posts = BlogPost::query()
            ->published()
            ->whereIn('seo_robots', ['inherit', 'index,follow'])
            ->with(['author:id,name', 'terms:id,type,name,slug'])
            ->orderByDesc('published_at')
            ->limit(20)
            ->get();

        return response()
            ->view('blog.feed', compact('posts'))
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=900');
    }

    private function publishedPosts()
    {
        return BlogPost::query()
            ->published()
            ->with(['author:id,name', 'terms:id,type,name,slug'])
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }
}
