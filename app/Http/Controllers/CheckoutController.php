<?php

namespace App\Http\Controllers;

use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Models\Cart;
use App\Services\Checkout\CheckoutOrderService;
use App\Models\Order;
use App\Services\Installments\InstallmentPlanService;
use App\Services\Payments\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class CheckoutController extends Controller
{
    /**
     * Show checkout page.
     */
    public function index(): View
    {
        $cart = $this->getActiveCart();

        $cart->load([
            'items.product.category',
            'items.product.images',
            'items.variant',
        ]);

        abort_if(
            $cart->items->isEmpty(),
            404,
            'سبد خرید خالی است.'
        );

        $defaultAddress = Auth::user()
            ->defaultAddress()
            ->first();

        return view('checkout.index', [
            'cart' => $cart,
            'defaultAddress' => $defaultAddress,
        ]);
    }

    /**
     * @param Order $order
     * @return View
     */
    public function installment(
        Order $order
    ): View {
        abort_unless(
            $order->user_id === Auth::id(),
            403
        );

        $order->load([
            'items.product.images',
            'installments',
        ]);

        /*
        |--------------------------------------------------------------------------
        | The installment plan must already exist.
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $order->payment_method === 'installment'
            && $order->installment_enabled === true,
            404
        );

        /*
        |--------------------------------------------------------------------------
        | Make sure the order actually has installment rows.
        |--------------------------------------------------------------------------
        */

        abort_unless(
            $order->installments->isNotEmpty(),
            404
        );

        return view(
            'checkout.installment',
            [
                'order' => $order,
            ]
        );
    }
    /**
     * Create an order from the active cart.
     */
    public function placeOrder(
        PlaceOrderRequest $request,
        CheckoutOrderService $checkoutOrderService
    ): RedirectResponse {
        $validated = $request->validated();

        try {
            $order = $checkoutOrderService->placeOrder(
                $this->getActiveCart(),
                (int) Auth::id(),
                $validated
            );

            if (! $order) {
                return redirect()
                    ->route('cart.index')
                    ->with('error', 'سبد خرید شما خالی است.');
            }

            return redirect()
                ->route('checkout.payment', $order)
                ->with('success', 'سفارش شما با موفقیت ثبت شد.');
        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'ثبت سفارش انجام نشد. لطفاً دوباره تلاش کنید.');
        }
    }


    /**
     * Show payment selection page.
     */
    public function payment(
        Order $order
    ): View {
        abort_unless(
            $order->user_id === Auth::id(),
            403
        );

        $order->load([
            'items.product.images',
            'latestPayment',
            'installments',
        ]);

        return view('checkout.payment', [
            'order' => $order,
        ]);
    }

    /**
     * Explain a provider's availability without creating an order payment
     * or pretending that an external checkout has started.
     */
    public function providerStatus(
        Order $order,
        string $gateway,
        \App\Services\Payments\PaymentManager $paymentManager
    ): View {
        abort_unless($order->user_id === Auth::id(), 403);

        $providers = [
            'shaparak' => [
                'name' => 'پرداخت آنلاین بانکی',
                'network' => 'شبکه شاپرک',
                'detail' => 'برای اتصال پرداخت بانکی، PSP یا پرداخت‌یار و مستندات رسمی آن باید مشخص شود.',
            ],
            'digipay' => [
                'name' => 'دیجی‌پی',
                'network' => 'DigiPay',
                'detail' => 'اتصال منتظر مستندات رسمی پذیرنده، مشخصات احراز هویت و endpointهای تأیید تراکنش است.',
            ],
            'snappay' => [
                'name' => 'اسنپ‌پی',
                'network' => 'SnappPay',
                'detail' => 'اتصال منتظر مستندات رسمی پذیرنده، مشخصات احراز هویت و endpointهای تأیید تراکنش است.',
            ],
            'torobpay' => [
                'name' => 'ترب‌پی',
                'network' => 'TorobPay',
                'detail' => 'اتصال منتظر مستندات رسمی پذیرنده، مشخصات احراز هویت و endpointهای تأیید تراکنش است.',
            ],
        ];

        abort_unless(isset($providers[$gateway]), 404);

        return view('checkout.provider-pending', [
            'order' => $order,
            'gateway' => $gateway,
            'provider' => $providers[$gateway],
            'isReady' => $gateway !== 'shaparak' && $paymentManager->isReady($gateway),
        ]);
    }

    /**
     * Start DigiPay / SnapPay / TorobPay installment payment.
     */
    public function startInstallmentPayment(
        Request $request,
        Order $order,
        PaymentService $paymentService
    ): RedirectResponse {
        abort_unless(
            $order->user_id === Auth::id(),
            403
        );

        $validated = $request->validate([
            'gateway' => [
                'required',
                'string',
                'in:digipay,snappay,torobpay',
            ],
        ]);

        try {
            $result = $paymentService
                ->startInstallmentPayment(
                    $order,
                    $validated['gateway']
                );

            if ($result['already_paid'] ?? false) {
                return redirect()
                    ->route('account.orders.show', $order)
                    ->with('success', 'پرداخت سفارش در همین فاصله تأیید شد.');
            }

            if (!($result['success'] ?? false)) {
                return back()->with(
                    'error',
                    $result['message']
                    ?? 'امکان شروع پرداخت وجود ندارد.'
                );
            }

            if (
                empty(
                $result['redirect_url']
                )
            ) {
                return back()->with(
                    'error',
                    'درگاه پرداخت لینک انتقال معتبری برنگرداند.'
                );
            }

            return redirect()->away(
                $result['redirect_url']
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            report($e);

            return back()->with(
                'error',
                'خطایی هنگام شروع پرداخت رخ داد.'
            );
        }
    }

    /**
     * Start Livora internal installment plan.
     *
     * Example:
     * 50% cash + remaining amount by cheque.
     */
    public function startInternalInstallment(
        Order $order,
        InstallmentPlanService $installmentPlanService
    ): RedirectResponse {
        abort_unless(
            $order->user_id === Auth::id(),
            403
        );

        try {
            $installmentPlanService->create($order);

            return redirect()
                ->route(
                    'checkout.installment',
                    $order
                )
                ->with(
                    'success',
                    'طرح خرید اقساطی برای سفارش ایجاد شد.'
                );

        } catch (Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                        ?: 'امکان ایجاد طرح اقساطی وجود ندارد.'
                );
        }
    }

    /**
     * Preview Livora installment plan.
     *
     * This endpoint will be useful later for
     * AJAX/calculator UI.
     */
    public function installmentPreview(
        Order $order,
        InstallmentPlanService $installmentPlanService
    ): RedirectResponse|array {
        abort_unless(
            $order->user_id === Auth::id(),
            403
        );

        try {
            return $installmentPlanService
                ->preview($order);
        } catch (Throwable $e) {
            report($e);

            return [
                'enabled' => false,
                'message' =>
                    $e->getMessage()
                        ?: 'طرح اقساطی قابل محاسبه نیست.',
            ];
        }
    }

    /**
     * Handle external payment callback.
     */
    public function paymentCallback(
        Request $request,
        string $gateway,
        PaymentService $paymentService
    ): RedirectResponse {
        abort_unless(
            in_array(
                $gateway,
                [
                    'digipay',
                    'snappay',
                    'torobpay',
                ],
                true
            ),
            404
        );

        try {
            $result = $paymentService
                ->handleCallback(
                    $gateway,
                    $request->all()
                );

            if (!($result['success'] ?? false)) {
                return redirect()
                    ->route('home')
                    ->with(
                        'error',
                        $result['message']
                        ?? 'پرداخت انجام نشد.'
                    );
            }

            $orderId =
                $result['order_id']
                ?? null;

            if ($orderId) {
                return redirect()
                    ->route(
                        'account.orders.show',
                        $orderId
                    )
                    ->with(
                        'success',
                        'پرداخت با موفقیت تأیید شد.'
                    );
            }

            return redirect()
                ->route('home')
                ->with(
                    'success',
                    'پرداخت با موفقیت تأیید شد.'
                );
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('home')
                ->with(
                    'error',
                    'خطایی در تأیید پرداخت رخ داد.'
                );
        }
    }

    /**
     * Get active customer cart.
     */
    protected function getActiveCart(): Cart
    {
        return Cart::query()
            ->where(
                'user_id',
                Auth::id()
            )
            ->where(
                'status',
                'active'
            )
            ->firstOrCreate(
                [
                    'user_id' =>
                        Auth::id(),

                    'status' =>
                        'active',
                ],
                [
                    'session_id' =>
                        null,
                ]
            );
    }

}
