<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategorySeoMetadataTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_category_specific_seo_and_it_is_rendered_publicly(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.categories.store'), [
                'name' => 'هود مخفی',
                'slug' => 'built-in-hoods',
                'description' => 'راهنمای انتخاب هود مخفی',
                'meta_title' => 'خرید هود مخفی آشپزخانه | فرزین',
                'meta_description' => 'مدل‌های هود مخفی فرزین را با مشخصات و قیمت بررسی کنید.',
                'is_active' => '1',
                'sort_order' => '3',
            ])
            ->assertRedirect(route('admin.categories.index'))
            ->assertSessionHasNoErrors();

        $category = Category::query()->where('slug', 'built-in-hoods')->firstOrFail();

        $this->assertSame('خرید هود مخفی آشپزخانه | فرزین', $category->meta_title);
        $this->assertSame('مدل‌های هود مخفی فرزین را با مشخصات و قیمت بررسی کنید.', $category->meta_description);

        $this->get(route('categories.show', $category->slug))
            ->assertOk()
            ->assertSee('<title>خرید هود مخفی آشپزخانه | فرزین</title>', false)
            ->assertSee('name="description" content="مدل‌های هود مخفی فرزین را با مشخصات و قیمت بررسی کنید."', false);
    }

    public function test_category_image_forms_use_the_shared_preview_and_crop_editor(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::create([
            'name' => 'دسته تست کراپ',
            'slug' => 'category-crop-test',
            'description' => 'آزمایش ویرایش تصویر دسته‌بندی',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        foreach ([
            route('admin.categories.create'),
            route('admin.categories.edit', $category),
        ] as $url) {
            $this->actingAs($admin)
                ->get($url)
                ->assertOk()
                ->assertSee('data-image-editor-input', false)
                ->assertSee('data-image-editor-existing-preview', false)
                ->assertSee('data-image-editor-placeholder', false)
                ->assertSee('data-image-editor-panel', false)
                ->assertSee('data-image-editor-canvas', false)
                ->assertSee('data-image-editor-ratio', false)
                ->assertSee('data-image-editor-zoom', false)
                ->assertSee('data-image-editor-rotate', false);
        }
    }

}
