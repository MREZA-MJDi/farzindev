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

    public function test_placeholder_adapters_never_become_live_from_environment_flags_alone(): void
    {
        $manager = app(PaymentManager::class);

        foreach (['digipay', 'snappay', 'torobpay'] as $gateway) {
            config([
                "payment.{$gateway}.enabled" => true,
                "payment.{$gateway}.api_contract_confirmed" => true,
                "payment.{$gateway}.merchant_id" => 'merchant-test',
                "payment.{$gateway}.api_key" => 'api-key-test',
                "payment.{$gateway}.base_url" => 'https://gateway.example.test',
                "payment.{$gateway}.callback_url" => 'https://store.example.test/checkout/payment/callback/' . $gateway,
                "payment.{$gateway}.endpoints.create" => '/v1/payment',
                "payment.{$gateway}.endpoints.verify" => '/v1/verify',
            ]);

            $this->assertFalse(
                $manager->isReady($gateway),
                "{$gateway} must remain unavailable until its real merchant adapter is implemented."
            );
        }
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
