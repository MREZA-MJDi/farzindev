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

    public function test_fully_configured_placeholder_gateway_still_cannot_start_payment(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = $this->makeOrder($customer);

        config([
            'payment.digipay.enabled' => true,
            'payment.digipay.api_contract_confirmed' => true,
            'payment.digipay.merchant_id' => 'merchant-test',
            'payment.digipay.client_id' => 'client-test',
            'payment.digipay.client_secret' => 'secret-test',
            'payment.digipay.base_url' => 'https://gateway.example.test',
            'payment.digipay.callback_url' => 'https://store.example.test/checkout/payment/callback/digipay',
            'payment.digipay.endpoints.create' => '/v1/payment',
            'payment.digipay.endpoints.verify' => '/v1/verify',
        ]);

        try {
            app(PaymentService::class)->startInstallmentPayment($order, 'digipay');
            $this->fail('Placeholder adapter must never initiate a payment.');
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

    public function test_callback_and_manual_verification_leave_pending_payment_untouched_when_adapter_is_not_ready(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $order = $this->makeOrder($customer);
        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $customer->id,
            'gateway' => 'digipay',
            'amount' => $order->total,
            'status' => 'pending',
            'metadata' => ['test' => true],
        ]);

        $service = app(PaymentService::class);

        $callback = $service->handleCallback('digipay', [
            'payment_id' => $payment->id,
            'verified' => true,
            'amount' => $order->total,
            'transaction_id' => 'UNTRUSTED-CALLBACK-ID',
        ]);

        $this->assertFalse($callback['success']);
        $this->assertTrue($callback['data']['verification_skipped']);
        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertNull($payment->fresh()->transaction_id);
        $this->assertSame('pending', $order->fresh()->payment_status);

        $verified = $service->verifyPayment($payment->fresh());
        $this->assertFalse($verified['success']);
        $this->assertSame('pending', $payment->fresh()->status);

        $status = $service->getStatus($payment->fresh());
        $this->assertFalse($status['success']);
        $this->assertSame('unknown', $status['status']);
        $this->assertSame('pending', $payment->fresh()->status);

        $cancel = $service->cancelPayment($payment->fresh());
        $this->assertFalse($cancel['success']);
        $this->assertSame('pending', $payment->fresh()->status);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'pending',
            'payment_provider' => null,
        ]);
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
