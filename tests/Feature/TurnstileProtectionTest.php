<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TurnstileProtectionTest extends TestCase
{
    use RefreshDatabase;

    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.turnstile.site_key' => 'test-site-key',
            'services.turnstile.secret' => 'test-only-value-not-a-secret',
            'services.turnstile.hostnames' => ['localhost'],
        ]);

        Http::fake(function ($request) {
            return match ($request['response'] ?? null) {
                'valid-test-token' => Http::response([
                    'success' => true,
                    'hostname' => 'localhost',
                    'action' => 'signup',
                ], 200),
                'wrong-action-token' => Http::response([
                    'success' => true,
                    'hostname' => 'localhost',
                    'action' => 'contact',
                ], 200),
                'wrong-host-token' => Http::response([
                    'success' => true,
                    'hostname' => 'attacker.example',
                    'action' => 'signup',
                ], 200),
                default => Http::response([
                    'success' => false,
                    'error-codes' => ['invalid-input-response'],
                ], 200),
            };
        });
    }

    public function test_customer_login_is_blocked_when_turnstile_fails(): void
    {
        User::factory()->create([
            'role' => 'customer',
            'phone' => '09123456789',
            'password' => 'StrongPass123',
        ]);

        $this->from(route('login'))
            ->post(route('login.store'), [
                'phone' => '09123456789',
                'password' => 'StrongPass123',
                'cf-turnstile-response' => 'invalid-token',
            ])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('captcha');

        $this->assertGuest('web');
        $this->assertTurnstileWasCalled('invalid-token');
    }

    public function test_registration_is_blocked_when_turnstile_fails(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'phone' => '09123456789',
                'email' => 'robot@example.test',
                'password' => 'StrongPass123',
                'password_confirmation' => 'StrongPass123',
                'terms' => '1',
                'cf-turnstile-response' => 'invalid-token',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('captcha');

        $this->assertDatabaseCount('users', 0);
        $this->assertTurnstileWasCalled('invalid-token');
    }

    public function test_registration_accepts_a_valid_signup_token_for_the_expected_hostname(): void
    {
        $this->post(route('register.store'), [
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'phone' => '09123456789',
            'email' => 'human@example.test',
            'password' => 'StrongPass123',
            'password_confirmation' => 'StrongPass123',
            'terms' => '1',
            'cf-turnstile-response' => 'valid-test-token',
        ])->assertRedirect(route('account.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'phone' => '09123456789',
            'email' => 'human@example.test',
            'role' => 'customer',
        ]);

        $this->assertTurnstileWasCalled('valid-test-token');
    }

    public function test_registration_rejects_a_token_generated_for_another_action(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'phone' => '09123456789',
                'password' => 'StrongPass123',
                'password_confirmation' => 'StrongPass123',
                'terms' => '1',
                'cf-turnstile-response' => 'wrong-action-token',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('captcha');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_admin_login_does_not_require_turnstile(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.test',
            'password' => 'StrongPass123',
        ]);

        $this->post(route('admin.login.store'), [
            'email' => 'admin@example.test',
            'password' => 'StrongPass123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin, 'admin');
        Http::assertNothingSent();
    }

    public function test_checkout_is_blocked_when_turnstile_fails(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);

        $this->actingAs($customer, 'web')
            ->from(route('checkout.index'))
            ->post(route('checkout.place'), [
                'cf-turnstile-response' => 'invalid-token',
            ])
            ->assertRedirect(route('checkout.index'))
            ->assertSessionHasErrors('captcha');

        $this->assertDatabaseCount('orders', 0);
        $this->assertTurnstileWasCalled('invalid-token');
    }

    public function test_contact_messages_are_blocked_when_turnstile_fails(): void
    {
        $this->from(route('contact'))
            ->post(route('contact.store'), [
                'cf-turnstile-response' => 'invalid-token',
            ])
            ->assertRedirect(route('contact'))
            ->assertSessionHasErrors('captcha');

        $this->assertDatabaseCount('contact_messages', 0);
        $this->assertTurnstileWasCalled('invalid-token');
    }

    public function test_wrong_siteverify_hostname_is_rejected(): void
    {
        $this->from(route('register'))
            ->post(route('register.store'), [
                'first_name' => 'Test',
                'last_name' => 'Customer',
                'phone' => '09123456789',
                'password' => 'StrongPass123',
                'password_confirmation' => 'StrongPass123',
                'terms' => '1',
                'cf-turnstile-response' => 'wrong-host-token',
            ])
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('captcha');

        $this->assertDatabaseCount('users', 0);
    }

    private function assertTurnstileWasCalled(string $token): void
    {
        Http::assertSent(fn ($request): bool =>
            $request->url() === self::VERIFY_URL
            && $request['secret'] === 'test-only-value-not-a-secret'
            && $request['response'] === $token
        );
    }
}
