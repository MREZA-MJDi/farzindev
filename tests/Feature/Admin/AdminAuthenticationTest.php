<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
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

        $customer = User::factory()->create(['role' => 'customer']);
        $this->actingAs($customer, 'web');

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

    public function test_customer_login_clears_an_existing_admin_guard(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => 'password',
        ]);
        $customer = User::factory()->create([
            'role' => 'customer',
            'password' => 'password',
        ]);

        $this->actingAs($admin, 'admin');

        $response = $this->post(route('login.store'), [
            'phone' => $customer->phone,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('account.index'));
        $this->assertAuthenticatedAs($customer, 'web');
        $this->assertGuest('admin');
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

    public function test_admin_login_normalizes_email_case_and_surrounding_whitespace(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin-normalized@example.test',
            'password' => 'StrongPass123',
        ]);

        $this->post(route('admin.login.store'), [
            'email' => '  ADMIN-NORMALIZED@EXAMPLE.TEST  ',
            'password' => 'StrongPass123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_admin_login_rejects_a_wrong_password_with_a_clear_persian_message(): void
    {
        User::factory()->create([
            'role' => 'admin',
            'email' => 'admin-wrong-password@example.test',
            'password' => 'StrongPass123',
        ]);

        $this->from(route('admin.login'))
            ->post(route('admin.login.store'), [
                'email' => 'admin-wrong-password@example.test',
                'password' => 'WrongPass123',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors([
                'email' => 'اطلاعات ورود صحیح نیست.',
            ]);

        $this->assertGuest('admin');
    }

    public function test_admin_login_throttle_reports_the_remaining_wait_time(): void
    {
        $email = 'admin-throttle@example.test';
        RateLimiter::clear('admin-login:' . $email . '|127.0.0.1');

        User::factory()->create([
            'role' => 'admin',
            'email' => $email,
            'password' => 'StrongPass123',
        ]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('admin.login.store'), [
                'email' => $email,
                'password' => 'WrongPass123',
            ])->assertRedirect();
        }

        $this->from(route('admin.login'))
            ->post(route('admin.login.store'), [
                'email' => $email,
                'password' => 'WrongPass123',
            ])
            ->assertRedirect(route('admin.login'))
            ->assertSessionHasErrors('email');

        $message = session('errors')->first('email');
        $this->assertStringContainsString('دقیقه دیگر دوباره تلاش کنید', $message);
        $this->assertGuest('admin');
    }

    public function test_admin_can_logout_and_the_admin_guard_is_cleared(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => 'StrongPass123',
        ]);

        $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'StrongPass123',
            'remember' => '1',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertNotEmpty($admin->fresh()->remember_token);

        $this->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest('admin');
        $this->get(route('admin.login'))->assertOk();
    }

    public function test_admin_login_form_is_not_cached_and_has_password_visibility_control(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee('autocomplete="username"', false)
            ->assertSee('autocomplete="current-password"', false)
            ->assertSee('id="admin-password-toggle"', false);
    }

    public function test_admin_sync_creates_and_updates_one_normalized_hashed_admin_idempotently(): void
    {
        config([
            'farzin.admin.name' => 'Configured Admin',
            'farzin.admin.email' => '  ADMIN-SYNC@EXAMPLE.TEST ',
            'farzin.admin.password' => 'StrongPass123',
        ]);

        $this->assertSame(0, Artisan::call('admin:sync'));

        $admin = User::query()->where('email', 'admin-sync@example.test')->firstOrFail();

        $this->assertSame('Configured Admin', $admin->name);
        $this->assertSame('admin', $admin->role);
        $this->assertTrue(Hash::check('StrongPass123', $admin->password));
        $this->assertDatabaseCount('users', 1);

        $firstHash = $admin->password;

        $this->assertSame(0, Artisan::call('admin:sync'));
        $this->assertDatabaseCount('users', 1);
        $this->assertSame($firstHash, $admin->fresh()->password);

        config(['farzin.admin.password' => 'ChangedStrongPass123']);
        $this->assertSame(0, Artisan::call('admin:sync'));

        $admin->refresh();
        $this->assertTrue(Hash::check('ChangedStrongPass123', $admin->password));
        $this->assertFalse(Hash::check('StrongPass123', $admin->password));

        config(['farzin.admin.email' => 'new-admin-sync@example.test']);
        $this->assertSame(0, Artisan::call('admin:sync'));
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'email' => 'new-admin-sync@example.test',
            'role' => 'admin',
        ]);
    }

    public function test_admin_sync_refuses_to_take_over_a_customer_account(): void
    {
        config([
            'farzin.admin.email' => 'existing-customer@example.test',
            'farzin.admin.password' => 'StrongPass123',
        ]);

        $customer = User::factory()->create([
            'role' => 'customer',
            'email' => 'existing-customer@example.test',
        ]);

        $this->assertSame(1, Artisan::call('admin:sync'));
        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'role' => 'customer',
            'email' => 'existing-customer@example.test',
        ]);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_admin_check_reports_only_boolean_results_without_credentials(): void
    {
        config([
            'farzin.admin.email' => 'admin-check@example.test',
            'farzin.admin.password' => 'StrongPass123',
        ]);

        User::factory()->create([
            'role' => 'admin',
            'email' => 'admin-check@example.test',
            'password' => 'StrongPass123',
        ]);

        $this->assertSame(0, Artisan::call('admin:check'));
        $output = Artisan::output();

        $this->assertStringContainsString('admin_exists=true', $output);
        $this->assertStringContainsString('email_matches=true', $output);
        $this->assertStringContainsString('password_matches=true', $output);
        $this->assertStringNotContainsString('StrongPass123', $output);
        $this->assertStringNotContainsString('admin-check@example.test', $output);
    }

    public function test_admin_login_redirects_to_the_intended_admin_page(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => 'StrongPass123',
        ]);

        $this->get(route('admin.products.create'))
            ->assertRedirect(route('admin.login'));

        $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'StrongPass123',
        ])->assertRedirect(route('admin.products.create'));
    }

    public function test_public_contact_form_response_is_not_cached(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_admin_dashboard_responses_are_not_cached(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private');
    }
}
