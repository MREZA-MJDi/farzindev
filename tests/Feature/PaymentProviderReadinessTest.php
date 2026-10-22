<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payments\PaymentManager;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class PaymentProviderReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_unconfirmed_gateway_does_not_create_a_payment_attempt_or_change_the_order(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = $this->makeOrder($customer);

        config([
            'payment.digipay.enabled' => true,
            'payment.digipay.api_contract_confirmed' => false,
        ]);

        try {
            app(PaymentService::class)->startInstallmentPayment($order, 'digipay');
            $this->fail('An unconfirmed gateway must refuse to create a payment attempt.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('پرداختی آغاز نشده', $exception->getMessage());
        }

        $this->assertDatabaseMissing('payments', ['order_id' => $order->id]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'pending',
            'payment_provider' => null,
        ]);
    }

    public function test_provider_is_ready_only_with_confirmed_contract_secure_urls_credentials_and_core_endpoints(): void
    {
        config([
            'payment.digipay.enabled' => true,
            'payment.digipay.api_contract_confirmed' => true,
            'payment.digipay.merchant_id' => 'merchant-test',
            'payment.digipay.api_key' => 'api-key-test',
            'payment.digipay.base_url' => 'https://gateway.example.test',
            'payment.digipay.callback_url' => 'https://store.example.test/checkout/payment/callback/digipay',
            'payment.digipay.endpoints.create' => '/v1/payment',
            'payment.digipay.endpoints.verify' => '/v1/verify',
        ]);

        $manager = app(PaymentManager::class);
        $this->assertTrue($manager->isReady('digipay'));

        config(['payment.digipay.base_url' => 'http://gateway.example.test']);
        $this->assertFalse($manager->isReady('digipay'));

        config(['payment.digipay.base_url' => 'https://gateway.example.test']);
        config(['payment.digipay.endpoints.verify' => '']);
        $this->assertFalse($manager->isReady('digipay'));

        config(['payment.digipay.endpoints.verify' => '/v1/verify']);
        config(['payment.digipay.api_contract_confirmed' => false]);
        $this->assertFalse($manager->isReady('digipay'));
    }

    public function test_shaparak_status_page_explains_pending_integration_without_claiming_payment(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = $this->makeOrder($customer);

        $this->actingAs($customer)
            ->get(route('checkout.provider-status', [
                'order' => $order,
                'gateway' => 'shaparak',
            ]))
            ->assertOk()
            ->assertSee('اتصال درگاه در حال آماده‌سازی است')
            ->assertSee('هیچ مبلغی از طریق این مسیر کسر نشده است');

        $this->assertDatabaseMissing('payments', ['order_id' => $order->id]);
    }

    private function makeOrder(User $customer): Order
    {
        return Order::create([
            'user_id' => $customer->id,
            'order_number' => 'FAR-PROVIDER-' . uniqid(),
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'online',
            'payment_provider' => null,
            'subtotal' => 1000000,
            'shipping_cost' => 0,
            'discount' => 0,
            'total' => 1000000,
            'first_name' => 'Buyer',
            'last_name' => 'Tester',
            'phone' => '09123456789',
            'email' => 'buyer@example.test',
            'province' => 'Tehran',
            'city' => 'Tehran',
            'address' => 'Test address',
            'postal_code' => '1234567890',
        ]);
    }
}
