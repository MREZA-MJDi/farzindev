<?php

namespace Tests\Feature;

use App\Models\StorefrontSetting;
use App\Models\User;
use App\Services\Seo\SeoSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SeoSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }

    public function test_public_layout_allows_indexing_and_following_by_default(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('name="robots" content="index, follow, max-snippet:-1, max-video-preview:-1, max-image-preview:large"', false)
            ->assertDontSee('name="robots" content="noindex', false)
            ->assertSee('rel="canonical" href="' . app(SeoSettings::class)->canonicalUrl('/', false) . '"', false)
            ->assertSee('property="og:title"', false)
            ->assertSee('application/ld+json', false);

        $this->assertSame(1, substr_count((string) $response->getContent(), '"@type":"WebSite"'));
    }

    public function test_robots_txt_is_generated_from_settings_and_keeps_crawling_allowed_for_meta_noindex(): void
    {
        $this->get(route('robots'))
            ->assertOk()
            ->assertHeader('content-type', 'text/plain; charset=UTF-8')
            ->assertSee('User-agent: *')
            ->assertSee('Allow: /')
            ->assertDontSee('Disallow: /')
            ->assertSee('Sitemap: ' . app(SeoSettings::class)->canonicalUrl('/sitemap.xml', false));
    }

    public function test_sitemap_uses_canonical_urls_and_can_be_disabled_in_settings(): void
    {
        $canonicalHome = app(SeoSettings::class)->canonicalUrl('/', false);

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('content-type', 'application/xml; charset=UTF-8')
            ->assertSee('<loc>' . $canonicalHome . '</loc>', false);

        StorefrontSetting::query()->updateOrCreate(
            ['key' => 'seo_sitemap_enabled'],
            ['value' => '0'],
        );
        app(SeoSettings::class)->clearCache();

        $this->get(route('sitemap'))->assertNotFound();
        $this->get(route('robots'))->assertDontSee('Sitemap:');
    }

    public function test_admin_seo_settings_are_protected_and_can_control_meta_robots(): void
    {
        $this->get(route('admin.seo-settings.edit'))
            ->assertRedirect(route('login'));

        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.seo-settings.edit'))
            ->assertOk()
            ->assertSee('تنظیمات سئو و ایندکس')
            ->assertSee(route('admin.seo-settings.update'), false);

        $this->actingAs($admin)
            ->put(route('admin.seo-settings.update'), [
                'seo_canonical_base_url' => 'https://farajihome.com',
                'seo_site_name' => 'فرزین',
                'seo_default_title' => 'عنوان SEO آزمایشی',
                'seo_default_description' => 'توضیحات SEO از پنل مدیریت',
                'seo_default_keywords' => 'هود, سینک',
                'seo_home_title' => 'عنوان صفحه اصلی از پنل',
                'seo_home_description' => 'توضیحات صفحه اصلی که از پنل SEO ذخیره شده است.',
                'seo_development_mode' => '0',
                'seo_robots_noindex' => '1',
                'seo_robots_nofollow' => '0',
                'seo_robots_noarchive' => '0',
                'seo_robots_nosnippet' => '0',
                'seo_robots_noimageindex' => '0',
                'seo_robots_txt_rules' => "User-agent: *\nAllow: /",
                'seo_sitemap_enabled' => '1',
            ])
            ->assertRedirect(route('admin.seo-settings.edit'));

        $this->assertDatabaseHas('storefront_settings', [
            'key' => 'seo_default_title',
            'value' => 'عنوان SEO آزمایشی',
        ]);

        $this->assertDatabaseHas('storefront_settings', [
            'key' => 'seo_home_title',
            'value' => 'عنوان صفحه اصلی از پنل',
        ]);

        app(SeoSettings::class)->clearCache();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<title>عنوان صفحه اصلی از پنل</title>', false)
            ->assertSee('name="description" content="توضیحات صفحه اصلی که از پنل SEO ذخیره شده است."', false)
            ->assertSee('name="robots" content="noindex, follow, max-snippet:-1, max-video-preview:-1, max-image-preview:large"', false);
    }

    public function test_admin_can_add_custom_meta_and_link_tags_and_render_them_on_the_storefront(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->put(route('admin.seo-settings.update'), [
                'seo_canonical_base_url' => 'https://farajihome.com',
                'seo_site_name' => 'فرزین',
                'seo_default_title' => 'عنوان SEO',
                'seo_default_description' => 'توضیحات سایت',
                'seo_default_keywords' => 'هود، سینک',
                'seo_robots_txt_rules' => "User-agent: *\nAllow: /",
                'seo_sitemap_enabled' => '1',
                'seo_custom_meta_tags' => [
                    ['type' => 'name', 'key' => 'theme-color', 'content' => '#f5f1e9'],
                    ['type' => 'property', 'key' => 'og:locale:alternate', 'content' => 'fa_IR'],
                    // Empty default rows should be ignored rather than blocking Save.
                    ['type' => 'name', 'key' => '', 'content' => ''],
                ],
                'seo_custom_link_tags' => [
                    [
                        'rel' => 'alternate',
                        'href' => 'https://farajihome.com/en',
                        'hreflang' => 'en',
                        'type' => '',
                        'as' => '',
                        'crossorigin' => '',
                    ],
                    [
                        'rel' => 'preconnect',
                        'href' => 'https://fonts.gstatic.com',
                        'hreflang' => '',
                        'type' => '',
                        'as' => '',
                        'crossorigin' => 'anonymous',
                    ],
                    [
                        'rel' => 'alternate',
                        'href' => '',
                        'hreflang' => '',
                        'type' => '',
                        'as' => '',
                        'crossorigin' => '',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.seo-settings.edit'))
            ->assertSessionHasNoErrors();

        $storedMetaTags = json_decode(
            (string) StorefrontSetting::query()->where('key', 'seo_custom_meta_tags')->value('value'),
            true
        );
        $storedLinkTags = json_decode(
            (string) StorefrontSetting::query()->where('key', 'seo_custom_link_tags')->value('value'),
            true
        );

        $this->assertSame([
            ['type' => 'name', 'key' => 'theme-color', 'content' => '#f5f1e9'],
            ['type' => 'property', 'key' => 'og:locale:alternate', 'content' => 'fa_IR'],
        ], $storedMetaTags);
        $this->assertCount(2, $storedLinkTags);

        app(SeoSettings::class)->clearCache();

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<meta name="theme-color" content="#f5f1e9">', false)
            ->assertSee('<meta property="og:locale:alternate" content="fa_IR">', false)
            ->assertSee('<link rel="alternate" href="https://farajihome.com/en"', false)
            ->assertSee('hreflang="en"', false)
            ->assertSee('<link rel="preconnect" href="https://fonts.gstatic.com"', false)
            ->assertSee('crossorigin="anonymous"', false);

        $this->actingAs($admin)
            ->get(route('admin.seo-settings.edit'))
            ->assertOk()
            ->assertSee('افزودن تگ')
            ->assertSee('افزودن لینک')
            ->assertSee('theme-color');
    }

    public function test_custom_link_tags_reject_javascript_urls(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.seo-settings.edit'))
            ->put(route('admin.seo-settings.update'), [
                'seo_canonical_base_url' => 'https://farajihome.com',
                'seo_site_name' => 'فرزین',
                'seo_default_title' => 'عنوان SEO',
                'seo_default_description' => 'توضیحات سایت',
                'seo_default_keywords' => '',
                'seo_robots_txt_rules' => "User-agent: *\nAllow: /",
                'seo_custom_link_tags' => [
                    ['rel' => 'alternate', 'href' => 'javascript:alert(1)'],
                ],
            ])
            ->assertRedirect(route('admin.seo-settings.edit'))
            ->assertSessionHasErrors('seo_custom_link_tags');

        $this->assertDatabaseMissing('storefront_settings', [
            'key' => 'seo_custom_link_tags',
        ]);
    }

    public function test_seo_boolean_settings_render_zero_values_as_unchecked(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        foreach ([
            'seo_development_mode',
            'seo_robots_noindex',
            'seo_robots_nofollow',
            'seo_robots_noarchive',
            'seo_robots_nosnippet',
            'seo_robots_noimageindex',
            'seo_sitemap_enabled',
        ] as $key) {
            \App\Models\StorefrontSetting::updateOrCreate(
                ['key' => $key],
                ['value' => '0']
            );
        }

        app(\App\Services\Seo\SeoSettings::class)->clearCache();

        $this->actingAs($admin)
            ->get(route('admin.seo-settings.edit'))
            ->assertOk()
            ->assertDontSee('name="seo_development_mode" value="1" checked', false)
            ->assertDontSee('name="seo_robots_noindex" value="1" checked', false)
            ->assertDontSee('name="seo_robots_nofollow" value="1" checked', false)
            ->assertDontSee('name="seo_robots_noarchive" value="1" checked', false)
            ->assertDontSee('name="seo_robots_nosnippet" value="1" checked', false)
            ->assertDontSee('name="seo_robots_noimageindex" value="1" checked', false)
            ->assertDontSee('name="seo_sitemap_enabled" value="1" checked', false);
    }

    public function test_error_pages_apply_full_noindex_policy(): void
    {
        $this->get('/__seo_nonexistent_path_for_test__')
            ->assertNotFound()
            ->assertSee('name="robots" content="noindex, nofollow, noarchive, nosnippet, noimageindex"', false);
    }
}
