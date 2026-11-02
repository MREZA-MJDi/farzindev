<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_sign_in_only_through_the_admin_guard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => 'password',
        ]);

        $response = $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertGuest('web');
    }

    public function test_admin_guard_does_not_leak_into_a_following_customer_request(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => 'password',
        ]);

        $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))->assertOk();

        $this->get(route('account.index'))
            ->assertRedirect(route('login'));

        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertGuest('web');
    }

    public function test_customer_guard_cannot_access_admin_routes(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($customer, 'web')
            ->get(route('admin.dashboard'));

        $response->assertRedirect(route('admin.login'));
        $this->assertGuest('admin');
    }

    public function test_admin_account_cannot_use_customer_login(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => 'password',
        ]);

        $response = $this->post(route('login.store'), [
            'phone' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertGuest('web');
        $this->assertGuest('admin');
    }
}
