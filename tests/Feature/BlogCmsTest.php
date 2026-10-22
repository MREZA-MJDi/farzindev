<?php

namespace Tests\Feature;

use App\Models\BlogPost;
use App\Models\User;
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
            'slug' => 'choosing-kitchen-hood',
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
}
