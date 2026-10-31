<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\StorefrontSetting;
use App\Models\User;
use App\Services\Blog\BlogAppearance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogAppearanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_blog_art_direction_and_container_width(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('admin.blog-appearance.update'), [
                'hero_art_alt' => 'تصویر شاخص مجله',
                'hero_art_fit' => 'contain',
                'hero_art_scale' => 118,
                'hero_art_position' => 'right',
                'closing_art_alt' => 'تصویر پایانی',
                'closing_art_fit' => 'cover',
                'closing_art_scale' => 94,
                'closing_art_position' => 'top',
                'container_width' => 1360,
            ])
            ->assertRedirect(route('admin.blog-appearance.edit'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('storefront_settings', [
            'key' => 'blog_hero_art_alt',
            'value' => 'تصویر شاخص مجله',
        ]);
        $this->assertDatabaseHas('storefront_settings', [
            'key' => 'blog_hero_art_scale',
            'value' => '118',
        ]);
        $this->assertDatabaseHas('storefront_settings', [
            'key' => 'blog_container_width',
            'value' => '1360',
        ]);

        $appearance = app(BlogAppearance::class);
        $appearance->clearCache();

        $this->assertSame('contain', $appearance->all()['hero']['fit']);
        $this->assertSame('right center', $appearance->all()['hero']['object_position']);
        $this->assertSame(1360, $appearance->all()['container_width']);

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('--blog-container-width: 1360px', false);
    }

    public function test_blog_appearance_and_article_image_editor_are_admin_only_and_crop_ready(): void
    {
        $this->get(route('admin.blog-appearance.edit'))
            ->assertRedirect(route('login'));

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.blog-appearance.edit'))
            ->assertOk()
            ->assertSee('data-blog-appearance', false)
            ->assertSee('data-image-editor-input', false)
            ->assertSee('LIVE GRID PREVIEW');

        $this->get(route('admin.blog-posts.create'))
            ->assertOk()
            ->assertSee('data-image-editor-default-ratio="16:9"', false)
            ->assertSee('data-editor-media-picker', false)
            ->assertSee('data-image-editor-canvas', false);
    }

    public function test_blog_editor_can_select_recent_images_from_the_media_library(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Media::create([
            'user_id' => $admin->id,
            'disk' => 'public',
            'path' => 'media/editor-library-sample.webp',
            'filename' => 'editor-library-sample.webp',
            'original_name' => 'editor-library-sample.webp',
            'mime_type' => 'image/webp',
            'extension' => 'webp',
            'size' => 2048,
            'alt' => 'تصویر نمونه درون مقاله',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.blog-posts.create'))
            ->assertOk()
            ->assertSee('data-editor-media-image', false)
            ->assertSee('تصویر نمونه درون مقاله')
            ->assertSee('editor-library-sample.webp');
    }

    public function test_public_blog_keeps_rss_subscription_as_a_real_action_link(): void
    {
        $this->get(route('blog.index'))
            ->assertOk()
            ->assertSee('href="' . route('blog.feed') . '"', false)
            ->assertSee('دنبال‌کردن مقاله‌ها')
            ->assertSee('دریافت خودکار نوشته‌های تازه')
            ->assertSee('blog-rss-link__icon', false);

        $this->get(route('blog.feed'))
            ->assertOk();
    }
}
