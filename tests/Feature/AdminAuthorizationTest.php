<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_enter_admin_dashboard(): void
    {
        $this->get('/admin')
            ->assertRedirect('/login');
    }

    public function test_valid_admin_can_open_the_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_authenticated_customer_cannot_open_admin_sections(): void
    {
        $customer = User::factory()->create([
            'role' => 'customer',
        ]);

        $this->actingAs($customer);

        foreach ([
            '/admin',
            '/admin/products',
            '/admin/categories',
            '/admin/orders',
            '/admin/customers',
            '/admin/media',
            '/admin/blog-posts',
            '/admin/seo-settings',
            '/admin/storefront-settings',
            '/admin/contact-messages',
        ] as $path) {
            $this->get($path)->assertForbidden();
        }
    }

    public function test_user_cannot_register_themselves_as_an_admin(): void
    {
        $this->post('/register', [
            'first_name' => 'Security',
            'last_name' => 'Test',
            'phone' => '09123456789',
            'email' => 'privilege-escalation@example.test',
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
            'terms' => '1',
            'role' => 'admin',
            'is_admin' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('users', [
            'email' => 'privilege-escalation@example.test',
            'role' => 'customer',
        ]);
    }

    public function test_each_response_has_a_server_generated_request_id(): void
    {
        $spoofedResponse = $this->withHeader('X-Request-ID', 'attacker-controlled-id')
            ->get('/login')
            ->assertOk();

        $this->assertNotSame(
            'attacker-controlled-id',
            $spoofedResponse->headers->get('X-Request-ID')
        );

        $first = $this->get('/login')->headers->get('X-Request-ID');
        $second = $this->get('/login')->headers->get('X-Request-ID');

        $this->assertNotEmpty($first);
        $this->assertNotSame('attacker-controlled-id', $first);
        $this->assertNotSame($first, $second);
    }

    public function test_json_server_errors_hide_internal_details_and_return_request_id(): void
    {
        config([
            'app.debug' => false,
        ]);

        Route::get('/__tests/server-error-json', function () {
            throw new RuntimeException('sensitive database connection detail');
        });

        $response = $this->getJson('/__tests/server-error-json');

        $response
            ->assertStatus(500)
            ->assertJsonStructure(['success', 'message', 'request_id'])
            ->assertJsonPath('success', false)
            ->assertDontSee('sensitive database connection detail');

        $this->assertNotEmpty($response->headers->get('X-Request-ID'));
    }
}
