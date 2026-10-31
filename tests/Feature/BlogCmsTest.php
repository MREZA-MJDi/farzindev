<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\BlogTerm;
use App\Models\SeoRedirect;
use App\Models\StorefrontSetting;
use App\Models\User;
use App\Services\Content\RichTextSanitizer;
use App\Services\Seo\SeoSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogCmsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_blog_lists_only_currently_published_articles_and_sitemap_tracks_them(): void
    {
        $published = BlogPost::create([
            'title' => 'راهنمای انتخاب هود',
            'slug' => 'published-article-test',
            'excerpt' => 'راهنمای بررسی ابعاد و صدای هود',
            'body' => 'متن کامل مقاله منتشرشده.',
            'meta_title' => 'خرید و انتخاب هود | فرزین',
            'meta_description' => 'راهنمای انتخاب هود برای آشپزخانه.',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $draft = BlogPost::create([
            'title' => 'پیش‌نویس داخلی',
            'slug' => 'internal-draft',
            'body' => 'این متن نباید عمومی شود.',
            'status' => 'draft',
        ]);

        $scheduled = BlogPost::create([
            'title' => 'مقاله زمان‌بندی‌شده',
            'slug' => 'scheduled-article',
            'body' => 'این متن هنوز نباید منتشر شود.',
            'status' => 'published',
            'published_at' => now()->addDay(),
        ]);

        app(SeoSettings::class)->clearCache();

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee($draft->title)
            ->assertDontSee($scheduled->title);

        $this->get(route('blog.show', $published->slug))
            ->assertOk()
            ->assertSee('خرید و انتخاب هود | فرزین')
            ->assertSee(route('blog.show', $published->slug), false)
            ->assertSee('BlogPosting', false)
            ->assertSee('راهنمای انتخاب هود برای آشپزخانه.');

        $this->get(route('blog.show', $draft->slug))->assertNotFound();
        $this->get(route('blog.show', $scheduled->slug))->assertNotFound();

        StorefrontSetting::query()->updateOrCreate(
            ['key' => 'seo_development_mode'],
            ['value' => '0'],
        );
        app(SeoSettings::class)->clearCache();

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee(route('blog.show', $published->slug), false)
            ->assertDontSee(route('blog.show', $draft->slug), false)
            ->assertDontSee(route('blog.show', $scheduled->slug), false);
    }

    public function test_admin_can_create_edit_and_publish_a_blog_article(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.blog-posts.index'))
            ->assertOk()
            ->assertSee('مقالات بلاگ');

        $this->actingAs($admin)
            ->post(route('admin.blog-posts.store'), [
                'title' => 'انتخاب سینک مناسب',
                'slug' => 'choosing-a-sink',
                'excerpt' => 'نکات کاربردی انتخاب سینک.',
                'body' => 'بدنه مقاله برای تست.',
                'meta_title' => 'انتخاب سینک | فرزین',
                'meta_description' => 'راهنمای انتخاب سینک.',
                'status' => 'draft',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $post = BlogPost::query()->where('slug', 'choosing-a-sink')->firstOrFail();
        $this->assertSame('draft', $post->status);
        $this->get(route('blog.show', $post->slug))->assertNotFound();

        $this->actingAs($admin)
            ->put(route('admin.blog-posts.update', $post), [
                'title' => 'انتخاب سینک مناسب',
                'slug' => 'choosing-a-sink',
                'excerpt' => 'نکات کاربردی انتخاب سینک.',
                'body' => 'بدنه مقاله برای تست.',
                'meta_title' => 'انتخاب سینک | فرزین',
                'meta_description' => 'راهنمای انتخاب سینک.',
                'status' => 'published',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $post->refresh();
        $this->assertSame('published', $post->status);
        $this->assertNotNull($post->published_at);

        $this->get(route('blog.show', $post->slug))
            ->assertOk()
            ->assertSee('بدنه مقاله برای تست.')
            ->assertSee('BlogPosting', false);
    }

    public function test_admin_blog_slug_must_be_unique_and_url_safe(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        BlogPost::create([
            'title' => 'مقاله اول',
            'slug' => 'same-slug',
            'body' => 'بدنه',
            'status' => 'draft',
        ]);

        $this->actingAs($admin)
            ->from(route('admin.blog-posts.create'))
            ->post(route('admin.blog-posts.store'), [
                'title' => 'مقاله تکراری',
                'slug' => 'same-slug',
                'body' => 'بدنه دوم',
                'status' => 'draft',
            ])
            ->assertRedirect(route('admin.blog-posts.create'))
            ->assertSessionHasErrors('slug');

        $this->actingAs($admin)
            ->from(route('admin.blog-posts.create'))
            ->post(route('admin.blog-posts.store'), [
                'title' => 'مقاله بد',
                'slug' => 'slug has spaces',
                'body' => 'بدنه سوم',
                'status' => 'draft',
            ])
            ->assertRedirect(route('admin.blog-posts.create'))
            ->assertSessionHasErrors('slug');
    }

    public function test_rich_text_sanitizer_removes_unsafe_markup_and_keeps_supported_content(): void
    {
        $html = app(RichTextSanitizer::class)->sanitize(
            '<h2 onclick="alert(1)">راهنمای انتخاب</h2><p>متن <strong>مهم</strong>.</p>'
            . '<script>alert(document.cookie)</script>'
            . '<a href="javascript:alert(1)" onmouseover="alert(1)">لینک ناامن</a>'
            . '<img src="/storage/blog/cover.webp" alt="تصویر" onerror="alert(1)">'
            . '<iframe src="https://attacker.example"></iframe>'
        );

        $this->assertStringContainsString('<h2>راهنمای انتخاب</h2>', $html);
        $this->assertStringContainsString('<strong>مهم</strong>', $html);
        $this->assertStringContainsString('/storage/blog/cover.webp', $html);
        $this->assertStringNotContainsString('onclick=', $html);
        $this->assertStringNotContainsString('onerror=', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('<iframe', $html);
    }

    public function test_admin_can_save_rich_article_seo_fields_and_taxonomies(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = BlogTerm::create(['type' => 'category', 'name' => 'راهنمای خرید', 'slug' => 'buying-guides', 'is_active' => true]);
        $tag = BlogTerm::create(['type' => 'tag', 'name' => 'هود', 'slug' => 'hood', 'is_active' => true]);

        $this->actingAs($admin)->post(route('admin.blog-posts.store'), [
            'title' => 'راهنمای انتخاب هود آشپزخانه',
            'slug' => 'kitchen-hood-buying-guide',
            'excerpt' => 'راهنمای انتخاب هود آشپزخانه برای فضای پخت‌وپز.',
            'body' => '<h2>راهنمای انتخاب هود</h2><p>نکته <strong>مهم</strong>.</p><script>alert(1)</script>',
            'meta_title' => 'راهنمای انتخاب هود آشپزخانه | فرزین',
            'meta_description' => 'نکات مهم برای انتخاب هود آشپزخانه متناسب با ابعاد و فضای نصب.',
            'focus_keyword' => 'انتخاب هود آشپزخانه',
            'canonical_url' => 'https://farajihome.com/blog/kitchen-hood-buying-guide',
            'og_title' => 'راهنمای انتخاب هود',
            'og_description' => 'راهنمای فرزین برای انتخاب هود مناسب.',
            'seo_robots' => 'noindex,follow',
            'schema_type' => 'Article',
            'is_featured' => '1',
            'term_ids' => [$category->id, $tag->id],
            'status' => 'published',
        ])->assertRedirect()->assertSessionHas('success');

        $post = BlogPost::query()->where('slug', 'kitchen-hood-buying-guide')->firstOrFail();
        $this->assertSame('انتخاب هود آشپزخانه', $post->focus_keyword);
        $this->assertSame('Article', $post->schema_type);
        $this->assertSame('noindex,follow', $post->seo_robots);
        $this->assertStringContainsString('<h2>', $post->body_html);
        $this->assertStringNotContainsString('<script', $post->body_html);
        $this->assertCount(2, $post->terms);

        $this->get(route('blog.show', $post->slug))
            ->assertOk()
            ->assertSee('name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex"', false)
            ->assertSee('<link rel="canonical" href="https://farajihome.com/blog/kitchen-hood-buying-guide">', false)
            ->assertSee('Article', false)
            ->assertSee('<h2>راهنمای انتخاب هود</h2>', false);
    }

    public function test_blog_taxonomy_archives_and_rss_feed_show_only_published_articles(): void
    {
        $category = BlogTerm::create(['type' => 'category', 'name' => 'راهنمای هود', 'slug' => 'hood-guides', 'meta_title' => 'راهنمای هود | مجله فرزین', 'is_active' => true]);
        $tag = BlogTerm::create(['type' => 'tag', 'name' => 'نصب', 'slug' => 'installation', 'is_active' => true]);
        $published = BlogPost::create([
            'title' => 'نکات نصب هود',
            'slug' => 'hood-installation-guide',
            'excerpt' => 'چک‌لیست نصب هود.',
            'body' => 'متن مقاله درباره نصب هود.',
            'status' => 'published',
            'published_at' => now()->subHour(),
        ]);
        $published->terms()->sync([$category->id, $tag->id]);
        $draft = BlogPost::create([
            'title' => 'پیش‌نویس نصب',
            'slug' => 'draft-installation',
            'body' => 'این نباید عمومی باشد.',
            'status' => 'draft',
        ]);

        $this->get(route('blog.category', $category->slug))
            ->assertOk()->assertSee($published->title)->assertDontSee($draft->title)
            ->assertSee('<title>راهنمای هود | مجله فرزین</title>', false);

        $this->get(route('blog.tag', $tag->slug))
            ->assertOk()->assertSee($published->title)->assertDontSee($draft->title);

        $this->get(route('blog.feed'))
            ->assertOk()
            ->assertHeader('content-type', 'application/rss+xml; charset=UTF-8')
            ->assertSee('<rss version="2.0">', false)
            ->assertSee($published->title)
            ->assertDontSee($draft->title);
    }

    public function test_sitemap_includes_indexable_blog_taxonomies_and_excludes_noindex_articles(): void
    {
        $category = BlogTerm::create(['type' => 'category', 'name' => 'مقاله‌های آشپزخانه', 'slug' => 'kitchen-articles', 'is_active' => true]);
        $tag = BlogTerm::create(['type' => 'tag', 'name' => 'سینک', 'slug' => 'sink', 'is_active' => true]);
        $indexable = BlogPost::create([
            'title' => 'راهنمای انتخاب سینک',
            'slug' => 'sink-guide-for-sitemap',
            'body' => 'راهنمای انتخاب سینک آشپزخانه.',
            'status' => 'published',
            'published_at' => now()->subHour(),
            'seo_robots' => 'inherit',
        ]);
        $indexable->terms()->sync([$category->id, $tag->id]);
        $noindex = BlogPost::create([
            'title' => 'مقاله خصوصی سئو',
            'slug' => 'noindex-sitemap-article',
            'body' => 'نباید در نقشه سایت باشد.',
            'status' => 'published',
            'published_at' => now()->subHour(),
            'seo_robots' => 'noindex,follow',
        ]);

        StorefrontSetting::query()->updateOrCreate(['key' => 'seo_development_mode'], ['value' => '0']);
        app(SeoSettings::class)->clearCache();

        $this->get(route('sitemap'))->assertOk()
            ->assertSee(route('blog.show', $indexable->slug), false)
            ->assertSee(route('blog.category', $category->slug), false)
            ->assertSee(route('blog.tag', $tag->slug), false)
            ->assertDontSee(route('blog.show', $noindex->slug), false);
    }

    public function test_redirect_manager_serves_safe_method_redirects_without_database_write_per_hit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.seo-redirects.store'), [
            'source_path' => '/old-kitchen-guide',
            'target_url' => '/blog/new-kitchen-guide',
            'status_code' => '301',
            'notes' => 'Old guide URL',
            'is_active' => '1',
        ])->assertRedirect(route('admin.seo-redirects.index'))->assertSessionHas('success');

        $redirect = SeoRedirect::query()->where('source_path', '/old-kitchen-guide')->firstOrFail();
        $this->get('/old-kitchen-guide')->assertRedirect('/blog/new-kitchen-guide');
        $this->assertSame(0, $redirect->fresh()->hits);
        $this->post('/old-kitchen-guide')->assertStatus(405);
    }

    public function test_changing_published_blog_slug_creates_a_301_redirect(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $post = BlogPost::create([
            'title' => 'راهنمای اولیه',
            'slug' => 'old-guide-slug',
            'body' => 'متن مقاله.',
            'status' => 'published',
            'published_at' => now()->subDay(),
        ]);

        $this->actingAs($admin)->put(route('admin.blog-posts.update', $post), [
            'title' => 'راهنمای به‌روزشده',
            'slug' => 'new-guide-slug',
            'excerpt' => '',
            'body' => '<h2>نسخه جدید</h2><p>متن به‌روزشده.</p>',
            'meta_title' => 'راهنمای به‌روزشده',
            'meta_description' => 'توضیحات به‌روزشده برای مقاله.',
            'status' => 'published',
        ])->assertRedirect()->assertSessionHas('success');

        $this->get('/blog/old-guide-slug')->assertRedirect('/blog/new-guide-slug');
        $this->assertDatabaseHas('seo_redirects', [
            'source_path' => '/blog/old-guide-slug',
            'target_url' => '/blog/new-guide-slug',
            'status_code' => 301,
        ]);
    }

}
