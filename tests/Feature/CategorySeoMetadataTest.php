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
}
