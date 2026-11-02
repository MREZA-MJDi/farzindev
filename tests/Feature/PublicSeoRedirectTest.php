<?php

namespace Tests\Feature;

use App\Models\SeoRedirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSeoRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_follow_an_active_cms_managed_redirect(): void
    {
        SeoRedirect::query()->create([
            'source_path' => '/legacy-kitchen-guide',
            'target_url' => '/blog/current-kitchen-guide',
            'status_code' => 301,
            'is_active' => true,
        ]);

        $this->get('/legacy-kitchen-guide')
            ->assertStatus(301)
            ->assertRedirect('/blog/current-kitchen-guide');
    }

    public function test_unknown_public_path_remains_a_real_not_found_response(): void
    {
        $this->get('/this-public-page-does-not-exist')
            ->assertNotFound();
    }
}
