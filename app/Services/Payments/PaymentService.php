<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Inventory\InventoryReservationService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class PaymentService
{
    public function __construct(
        protected PaymentManager $paymentManager,
        protected InventoryReservationService $inventoryReservationService
    ) {
    }

    /**
     * Start an external gateway payment.
     *
     * IMPORTANT:
     * External gateway payments are "online" payments.
     *
     * Livora internal cheque-based installment plans are handled
     * separately by InstallmentPlanService.
     */
    public function startInstallmentPayment(
        Order $order,
        string $gateway
    ): array {
        $this->validateOrderForPayment($order);

        $gateway = $this->normalizeGateway($gateway);

        $gatewayService = $this->paymentManager->driver(
            $gateway
        );

        /*
        |--------------------------------------------------------------------------
        | Create local payment attempt
        |--------------------------------------------------------------------------
        */

        $payment = DB::transaction(function () use (
            $order,
            $gateway
        ) {
            /*
             * Lock the order so two parallel requests cannot
             * create conflicting payment state.
             */
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Revalidate against the current locked order. The model passed
            // into this method may have been loaded before a cancellation or
            // another payment attempt completed.
            $this->validateOrderForPayment($lockedOrder);

            if ($lockedOrder->payments()->where('status', 'paid')->exists()) {
                throw new RuntimeException(
                    'برای این سفارش پرداخت موفق ثبت شده است.'
                );
            }

            // Never open two live gateway sessions for one order. Both could
            // succeed and charge the customer twice. Failed attempts can be
            // retried; stale attempts are closed by orders:expire-unpaid.
            if ($lockedOrder->payments()->whereIn('status', ['pending', 'initiated'])->exists()) {
                throw new RuntimeException(
                    'برای این سفارش یک پرداخت در حال انجام است. وضعیت همان پرداخت را بررسی کنید.'
                );
            }

            $amount = $this->normalizeAmount($lockedOrder->total);

            if ($amount <= 0) {
                throw new RuntimeException(
                    'مبلغ سفارش معتبر نیست.'
                );
            }

            // A previous failed/cancelled attempt may have released the
            // reservation. Re-reserve under row locks before opening a new
            // gateway attempt; failure prevents sending the customer to pay.
            $this->inventoryReservationService->reserveForOrder($lockedOrder);

            /*
             * External provider = online.
             *
             * Do NOT store "installment" here.
             * "installment" is reserved for Livora's own
             * cheque-based installment flow.
             */
            $lockedOrder->update([
                'payment_method' => 'online',
                'payment_provider' => $gateway,
                'payment_status' => 'pending',
            ]);

            return Payment::create([
                'order_id' => $lockedOrder->id,

                'user_id' => $lockedOrder->user_id,

                'gateway' => $gateway,

                'amount' => $amount,

                'status' => 'pending',

                'metadata' => [
                    'payment_method' => 'online',

                    'payment_flow' => 'external_gateway',

                    'order_number' =>
                        $lockedOrder->order_number,

                    'created_at' =>
                        now()->toISOString(),
                ],
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | Communicate with gateway
        |--------------------------------------------------------------------------
        */

        try {
            $result = $gatewayService->createPayment(
                $order->fresh(),
                $payment->fresh()
            );

            if (!($result['success'] ?? false)) {
                $this->markPaymentAsFailed(
                    $payment,
                    $result['message']
                    ?? 'Payment initialization failed.',
                    [
                        'gateway' => $gateway,
                        'response' => $result,
                    ]
                );

                return [
                    'success' => false,

                    'payment' =>
                        $payment->fresh(),

                    'gateway' => $gateway,

                    'redirect_url' => null,

                    'message' =>
                        $result['message']
                        ?? 'Payment initialization failed.',

                    'data' =>
                        $result['data'] ?? [],
                ];
            }

            $payment = $payment->fresh();

            $this->markPaymentAsInitiated(
                $payment,
                $result
            );

            $payment = $payment->fresh();

            $redirectUrl =
                $result['payment_url']
                ?? null;

            if (
                blank($redirectUrl)
            ) {
                $this->markPaymentAsFailed(
                    $payment,
                    'Gateway did not return a valid payment URL.',
                    [
                        'gateway' => $gateway,
                        'response' => $result,
                    ]
                );

                return [
                    'success' => false,

                    'payment' =>
                        $payment->fresh(),

                    'gateway' => $gateway,

                    'redirect_url' => null,

                    'message' =>
                        'درگاه پرداخت لینک انتقال معتبری برنگرداند.',

                    'data' =>
                        $result['data'] ?? [],
                ];
            }

            return [
                'success' => true,

                'payment' => $payment,

                'gateway' => $gateway,

                'redirect_url' => $redirectUrl,

                'message' =>
                    $result['message'] ?? null,

                'data' =>
                    $result['data'] ?? [],
            ];
        } catch (Throwable $e) {
            report($e);

            $this->markPaymentAsFailed(
                $payment,
                'Gateway communication failed.',
                [
                    'exception' =>
                        $e::class,
                ]
            );

            return [
                'success' => false,

                'payment' =>
                    $payment->fresh(),

                'gateway' => $gateway,

                'redirect_url' => null,

                'message' =>
                    'خطایی هنگام اتصال به درگاه پرداخت رخ داد. لطفاً دوباره تلاش کنید.',

                'data' => [],
            ];
        }
    }

    /**
     * Handle normalized external gateway callback.
     */
    public function handleCallback(
        string $gateway,
        array $callbackData
    ): array {
        $gateway = $this->normalizeGateway(
            $gateway
        );

        $gatewayService =
            $this->paymentManager->driver(
                $gateway
            );

        $payment =
            $this->findPaymentFromCallback(
                $gateway,
                $callbackData
            );

        if (!$payment) {
            return [
                'success' => false,

                'order_id' => null,

                'payment_id' => null,

                'transaction_id' => null,

                'message' =>
                    'رکورد پرداخت مربوط به این درخواست پیدا نشد.',

                'data' => [],
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Idempotency
        |--------------------------------------------------------------------------
        */

        if ($payment->isPaid()) {
            return [
                'success' => true,

                'order_id' =>
                    $payment->order_id,

                'payment_id' =>
                    $payment->id,

                'transaction_id' =>
                    $payment->transaction_id,

                'message' =>
                    'این پرداخت قبلاً با موفقیت ثبت شده است.',

                'data' => [
                    'already_paid' => true,
                ],
            ];
        }

        try {
            $result =
                $gatewayService->verifyPayment(
                    $payment
                );

            if (!($result['success'] ?? false)) {
                $this->markPaymentAsFailed(
                    $payment,
                    $result['message']
                    ?? 'Payment verification failed.',
                    [
                        'callback' =>
                            $callbackData,

                        'verify_response' =>
                            $result,
                    ]
                );

                return [
                    'success' => false,

                    'order_id' =>
                        $payment->order_id,

                    'payment_id' =>
                        $payment->id,

                    'transaction_id' =>
                        $payment->transaction_id,

                    'message' =>
                        'پرداخت تأیید نشد. در صورت کسر وجه، وضعیت تراکنش بررسی خواهد شد.',

                    'data' =>
                        $result['data'] ?? [],
                ];
            }

            /*
            |--------------------------------------------------------------------------
            | Amount validation
            |--------------------------------------------------------------------------
            */

            $this->validateVerifiedAmount(
                $payment,
                $result
            );

            $this->markPaymentAsPaid(
                $payment,
                $result['transaction_id']
                ?? null,
                [
                    'callback' =>
                        $callbackData,

                    'verify_response' =>
                        $result,
                ]
            );

            $freshPayment =
                $payment->fresh();

            return [
                'success' => true,

                'order_id' =>
                    $freshPayment->order_id,

                'payment_id' =>
                    $freshPayment->id,

                'transaction_id' =>
                    $freshPayment->transaction_id,

                'message' =>
                    'پرداخت با موفقیت تأیید شد.',

                'data' =>
                    $result['data'] ?? [],
            ];
        } catch (RuntimeException $e) {
            report($e);

            /*
             * Important:
             * If verification reached a business/security
             * mismatch, payment must not be marked as paid.
             */
            $this->markPaymentAsFailed(
                $payment,
                $e->getMessage(),
                [
                    'callback' =>
                        $callbackData,

                    'verification_error' =>
                        $e::class,
                ]
            );

            return [
                'success' => false,

                'order_id' =>
                    $payment->order_id,

                'payment_id' =>
                    $payment->id,

                'transaction_id' =>
                    $payment->transaction_id,

                'message' =>
                    'اطلاعات پرداخت با سفارش مطابقت ندارد.',

                'data' => [],
            ];
        } catch (Throwable $e) {
            report($e);

            return [
                'success' => false,

                'order_id' =>
                    $payment->order_id,

                'payment_id' =>
                    $payment->id,

                'transaction_id' =>
                    $payment->transaction_id,

                'message' =>
                    'خطایی هنگام تأیید پرداخت رخ داد.',

                'data' => [],
            ];
        }
    }

    /**
     * Verify an existing payment.
     */
    public function verifyPayment(
        Payment $payment
    ): array {
        if ($payment->isPaid()) {
            return [
                'success' => true,

                'payment' => $payment,

                'order' =>
                    $payment->order,

                'message' =>
                    'Payment is already paid.',
            ];
        }

        $gateway =
            $this->normalizeGateway(
                $payment->gateway
            );

        try {
            $gatewayService =
                $this->paymentManager->driver(
                    $gateway
                );

            $result =
                $gatewayService->verifyPayment(
                    $payment
                );

            if (!($result['success'] ?? false)) {
                $this->markPaymentAsFailed(
                    $payment,
                    $result['message']
                    ?? 'Payment verification failed.',
                    [
                        'verify_response' =>
                            $result,
                    ]
                );

                return [
                    'success' => false,

                    'payment' =>
                        $payment->fresh(),

                    'order' =>
                        $payment->order?->fresh(),

                    'message' =>
                        $result['message']
                        ?? 'Payment verification failed.',
                ];
            }

            $this->validateVerifiedAmount(
                $payment,
                $result
            );

            $this->markPaymentAsPaid(
                $payment,
                $result['transaction_id']
                ?? null,
                [
                    'verify_response' =>
                        $result,
                ]
            );

            return [
                'success' => true,

                'payment' =>
                    $payment->fresh(),

                'order' =>
                    $payment->order?->fresh(),

                'message' =>
                    $result['message']
                    ?? 'Payment verified successfully.',
            ];
        } catch (RuntimeException $e) {
            report($e);

            $this->markPaymentAsFailed(
                $payment,
                $e->getMessage(),
                [
                    'verification_error' =>
                        $e::class,
                ]
            );

            return [
                'success' => false,

                'payment' =>
                    $payment->fresh(),

                'order' =>
                    $payment->order?->fresh(),

                'message' =>
                    'اطلاعات پرداخت معتبر نیست.',
            ];
        } catch (Throwable $e) {
            report($e);

            return [
                'success' => false,

                'payment' =>
                    $payment->fresh(),

                'order' =>
                    $payment->order?->fresh(),

                'message' =>
                    'خطایی در تأیید پرداخت رخ داد.',
            ];
        }
    }

    /**
     * Get payment status from gateway.
     */
    public function getStatus(
        Payment $payment
    ): array {
        $gateway =
            $this->normalizeGateway(
                $payment->gateway
            );

        try {
            $gatewayService =
                $this->paymentManager->driver(
                    $gateway
                );

            return $gatewayService->getStatus(
                $payment
            );
        } catch (Throwable $e) {
            report($e);

            return [
                'success' => false,

                'status' => 'unknown',

                'message' =>
                    'امکان دریافت وضعیت پرداخت وجود ندارد.',

                'data' => [],
            ];
        }
    }

    /**
     * Cancel unpaid payment.
     */
    public function cancelPayment(
        Payment $payment
    ): array {
        if ($payment->isPaid()) {
            return [
                'success' => false,

                'message' =>
                    'پرداخت موفق را نمی‌توان لغو کرد.',
            ];
        }

        if (
            $payment->status === 'cancelled'
        ) {
            return [
                'success' => true,

                'message' =>
                    'این پرداخت قبلاً لغو شده است.',
            ];
        }

        $gateway =
            $this->normalizeGateway(
                $payment->gateway
            );

        try {
            $gatewayService =
                $this->paymentManager->driver(
                    $gateway
                );

            $result =
                $gatewayService->cancelPayment(
                    $payment
                );

            if ($result['success'] ?? false) {
                DB::transaction(
                    function () use (
                        $payment,
                        $result
                    ) {
                        $lockedPayment =
                            Payment::query()
                                ->whereKey(
                                    $payment->id
                                )
                                ->lockForUpdate()
                                ->firstOrFail();

                        if (
                            $lockedPayment->status === 'paid'
                        ) {
                            return;
                        }

                        $lockedPayment->update([
                            'status' =>
                                'cancelled',

                            'metadata' =>
                                array_merge(
                                    $lockedPayment->metadata ?? [],
                                    [
                                        'cancel_response' =>
                                            $result,

                                        'cancelled_at' =>
                                            now()->toISOString(),
                                    ]
                                ),
                        ]);

                        $this->syncOrderPaymentStatus(
                            $lockedPayment->order
                        );
                        $this->releaseInventoryIfNoActivePaymentAttempts(
                            $lockedPayment->order
                        );
                    }
                );
            }

            return $result;
        } catch (Throwable $e) {
            report($e);

            return [
                'success' => false,

                'message' =>
                    'لغو پرداخت انجام نشد.',

                'data' => [],
            ];
        }
    }

    /**
     * Refund a paid payment.
     */
    public function refundPayment(
        Payment $payment
    ): array {
        if (! $payment->isPaid()) {
            return [
                'success' => false,

                'message' =>
                    'فقط پرداخت موفق قابل استرداد است.',
            ];
        }

        $gateway =
            $this->normalizeGateway(
                $payment->gateway
            );

        try {
            $gatewayService =
                $this->paymentManager->driver(
                    $gateway
                );

            $result =
                $gatewayService->refundPayment(
                    $payment
                );

            if ($result['success'] ?? false) {
                DB::transaction(
                    function () use (
                        $payment,
                        $result
                    ) {
                        $lockedPayment =
                            Payment::query()
                                ->whereKey(
                                    $payment->id
                                )
                                ->lockForUpdate()
                                ->firstOrFail();

                        if (
                            $lockedPayment->status !== 'paid'
                        ) {
                            return;
                        }

                        $lockedPayment->update([
                            'status' =>
                                'refunded',

                            'metadata' =>
                                array_merge(
                                    $lockedPayment->metadata ?? [],
                                    [
                                        'refund_response' =>
                                            $result,

                                        'refunded_at' =>
                                            now()->toISOString(),
                                    ]
                                ),
                        ]);

                        $this->syncOrderPaymentStatus(
                            $lockedPayment->order
                        );
                    }
                );
            }

            return $result;
        } catch (Throwable $e) {
            report($e);

            return [
                'success' => false,

                'message' =>
                    'استرداد وجه انجام نشد.',

                'data' => [],
            ];
        }
    }

    /**
     * Mark payment as initiated.
     */
    protected function markPaymentAsInitiated(
        Payment $payment,
        array $result
    ): void {
        DB::transaction(
            function () use (
                $payment,
                $result
            ) {
                $lockedPayment =
                    Payment::query()
                        ->whereKey(
                            $payment->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                /*
                 * Never change a paid payment back to initiated.
                 */
                if (
                    $lockedPayment->status === 'paid'
                ) {
                    return;
                }

                $lockedPayment->update([
                    'status' =>
                        'initiated',

                    'authority' =>
                        $result['authority']
                        ?? $lockedPayment->authority,

                    'transaction_id' =>
                        $result['transaction_id']
                        ?? $lockedPayment->transaction_id,

                    'metadata' =>
                        array_merge(
                            $lockedPayment->metadata ?? [],
                            [
                                'create_response' =>
                                    $result,

                                'initiated_at' =>
                                    now()->toISOString(),
                            ]
                        ),
                ]);
            }
        );
    }

    /**
     * Mark payment as paid.
     */
    protected function markPaymentAsPaid(
        Payment $payment,
        ?string $transactionId = null,
        array $extraMetadata = []
    ): void {
        DB::transaction(
            function () use (
                $payment,
                $transactionId,
                $extraMetadata
            ) {
                $lockedPayment =
                    Payment::query()
                        ->whereKey(
                            $payment->id
                        )
                        ->lockForUpdate()
                        ->firstOrFail();

                /*
                 * Idempotency.
                 */
                if (
                    $lockedPayment->status === 'paid'
                ) {
                    return;
                }

                /*
                 * A refunded/cancelled payment must not
                 * magically become paid without a new attempt.
                 */
                if (
                    in_array(
                        $lockedPayment->status,
                        [
                            'cancelled',
                            'refunded',
                        ],
                        true
                    )
                ) {
                    throw new RuntimeException(
                        'این تلاش پرداخت دیگر قابل تأیید نیست.'
                    );
                }

                $lockedPayment->update([
                    'status' =>
                        'paid',

                    'transaction_id' =>
                        $transactionId
                        ?? $lockedPayment->transaction_id,

                    'paid_at' =>
                        now(),

                    'metadata' =>
                        array_merge(
                            $lockedPayment->metadata ?? [],
                            $extraMetadata,
                            [
                                'paid_at' =>
                                    now()->toISOString(),
                            ]
                        ),
                ]);

                $order = Order::query()
                    ->whereKey($lockedPayment->order_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                /*
                 * External gateways represent an online payment.
                 * A successful late callback reopens an expired/cancelled
                 * order, while InventoryReservationService either safely
                 * commits the held stock or raises an explicit admin issue.
                 */
                $order->update([
                    'payment_status' => 'paid',

                    'payment_method' => 'online',

                    'payment_provider' => $lockedPayment->gateway,

                    'status' => $order->status === 'cancelled'
                        ? 'pending'
                        : $order->status,
                ]);

                $this->inventoryReservationService->commitForPaidOrder(
                    $order->fresh()
                );
            }
        );
    }

    /**
     * Mark payment as failed and synchronize order state.
     */
    protected function markPaymentAsFailed(
        Payment $payment,
        ?string $message = null,
        array $data = []
    ): void {
        DB::transaction(
            function () use (
                $payment,
                $message,
                $data
            ) {
                $lockedPayment =
                    Payment::query()
                        ->whereKey(
                            $payment->id
                        )
                        ->lockForUpdate()
                        ->first();

                if (! $lockedPayment) {
                    return;
                }

                /*
                 * Do not overwrite a successful payment.
                 */
                if (
                    $lockedPayment->status === 'paid'
                ) {
                    return;
                }

                /*
                 * A refunded payment stays refunded.
                 */
                if (
                    $lockedPayment->status === 'refunded'
                ) {
                    return;
                }

                $lockedPayment->update([
                    'status' =>
                        'failed',

                    'metadata' =>
                        array_merge(
                            $lockedPayment->metadata ?? [],
                            [
                                'failure_message' =>
                                    $message,

                                'failure_response' =>
                                    $data,

                                'failed_at' =>
                                    now()->toISOString(),
                            ]
                        ),
                ]);

                $this->syncOrderPaymentStatus(
                    $lockedPayment->order
                );
                $this->releaseInventoryIfNoActivePaymentAttempts(
                    $lockedPayment->order
                );
            }
        );
    }

    /**
     * Synchronize order payment status from its attempts.
     */
    protected function syncOrderPaymentStatus(
        ?Order $order
    ): void {
        if (! $order) {
            return;
        }

        /*
         * A paid attempt always wins.
         */
        $hasPaid =
            $order->payments()
                ->where(
                    'status',
                    'paid'
                )
                ->exists();

        if ($hasPaid) {
            $order->update([
                'payment_status' => 'paid',
            ]);

            return;
        }

        /*
         * If another attempt is still active,
         * the order remains payable.
         */
        $hasActive =
            $order->payments()
                ->whereIn(
                    'status',
                    [
                        'pending',
                        'initiated',
                    ]
                )
                ->exists();

        if ($hasActive) {
            $order->update([
                'payment_status' => 'pending',
            ]);

            return;
        }

        /*
         * No paid or active attempt remains.
         */
        $hasRefunded =
            $order->payments()
                ->where(
                    'status',
                    'refunded'
                )
                ->exists();

        if ($hasRefunded) {
            $order->update([
                'payment_status' => 'refunded',
            ]);

            return;
        }

        $order->update([
            'payment_status' => 'failed',
        ]);
    }

    /**
     * Release stock only when no successful or in-flight payment attempt
     * remains. This lets customers retry a failed gateway attempt safely.
     */
    protected function releaseInventoryIfNoActivePaymentAttempts(
        ?Order $order
    ): void {
        if (! $order) {
            return;
        }

        $order = $order->fresh();

        if (! $order || $order->payment_status === 'paid') {
            return;
        }

        if ($order->payments()->where('status', 'paid')->exists()) {
            return;
        }

        if ($order->payments()->whereIn('status', ['pending', 'initiated'])->exists()) {
            return;
        }

        $this->inventoryReservationService->releaseForOrder($order);
    }

    /**
     * Find local payment from callback data.
     */
    protected function findPaymentFromCallback(
        string $gateway,
        array $callbackData
    ): ?Payment {
        /*
        |--------------------------------------------------------------------------
        | 1. Local payment ID
        |--------------------------------------------------------------------------
        */

        foreach ([
                     'payment_id',
                     'paymentId',
                 ] as $key) {
            if (
                isset($callbackData[$key])
                && is_numeric($callbackData[$key])
            ) {
                $payment =
                    Payment::query()
                        ->whereKey(
                            (int) $callbackData[$key]
                        )
                        ->where(
                            'gateway',
                            $gateway
                        )
                        ->first();

                if ($payment) {
                    return $payment;
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Authority / Transaction / Provider reference
        |--------------------------------------------------------------------------
        */

        foreach ([
                     'authority',
                     'Authority',
                     'transaction_id',
                     'transactionId',
                     'tracking_code',
                     'trackingCode',
                     'token',
                     'Token',
                     'reference_id',
                     'referenceId',
                     'invoice_id',
                     'invoiceId',
                 ] as $key) {
            if (
                ! array_key_exists(
                    $key,
                    $callbackData
                )
                || blank($callbackData[$key])
            ) {
                continue;
            }

            $value =
                (string) $callbackData[$key];

            $payment =
                Payment::query()
                    ->where(
                        'gateway',
                        $gateway
                    )
                    ->where(
                        function (Builder $query) use (
                            $value
                        ) {
                            $query
                                ->where(
                                    'authority',
                                    $value
                                )
                                ->orWhere(
                                    'transaction_id',
                                    $value
                                );
                        }
                    )
                    ->latest('id')
                    ->first();

            if ($payment) {
                return $payment;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | 3. Internal order number
        |--------------------------------------------------------------------------
        |
        | This is only a fallback.
        | Final authenticity still depends on gateway verification.
        |
        */

        foreach ([
                     'order_number',
                     'orderNumber',
                     'merchant_order_id',
                     'merchantOrderId',
                 ] as $key) {
            if (
                ! array_key_exists(
                    $key,
                    $callbackData
                )
                || blank($callbackData[$key])
            ) {
                continue;
            }

            $order =
                Order::query()
                    ->where(
                        'order_number',
                        $callbackData[$key]
                    )
                    ->first();

            if (! $order) {
                continue;
            }

            return $order
                ->payments()
                ->where(
                    'gateway',
                    $gateway
                )
                ->whereIn(
                    'status',
                    [
                        'pending',
                        'initiated',
                    ]
                )
                ->latest('id')
                ->first();
        }

        return null;
    }

    /**
     * Validate verified gateway amount.
     */
    protected function validateVerifiedAmount(
        Payment $payment,
        array $result
    ): void {
        $order =
            $payment->order;

        if (! $order) {
            throw new RuntimeException(
                'سفارش مربوط به پرداخت پیدا نشد.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Our local payment amount must be positive.
        |--------------------------------------------------------------------------
        */

        $expectedPaymentAmount =
            $this->normalizeAmount(
                $payment->amount
            );

        if ($expectedPaymentAmount <= 0) {
            throw new RuntimeException(
                'مبلغ پرداخت معتبر نیست.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | External gateway returned amount
        |--------------------------------------------------------------------------
        */

        if (
            array_key_exists(
                'amount',
                $result
            )
            && $result['amount'] !== null
        ) {
            $receivedAmount =
                $this->normalizeAmount(
                    $result['amount']
                );

            if (
                $expectedPaymentAmount !==
                $receivedAmount
            ) {
                throw new RuntimeException(
                    'Payment amount mismatch.'
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | External online payment must equal current order total.
        |--------------------------------------------------------------------------
        |
        | Internal Livora installment payments should NOT enter this
        | method because they are a separate flow.
        |
        */

        if (($payment->metadata['payment_method'] ?? null) === 'online') {
            $orderAmount =
                $this->normalizeAmount(
                    $order->total
                );

            if (
                $expectedPaymentAmount !==
                $orderAmount
            ) {
                throw new RuntimeException(
                    'Payment amount does not match order total.'
                );
            }
        }
    }

    /**
     * Validate order state before initiating online payment.
     */
    protected function validateOrderForPayment(
        Order $order
    ): void {
        if (
            $order->payment_status === 'paid'
        ) {
            throw new RuntimeException(
                'این سفارش قبلاً پرداخت شده است.'
            );
        }

        if (
            $order->status === 'cancelled'
        ) {
            throw new RuntimeException(
                'سفارش لغو شده قابل پرداخت نیست.'
            );
        }

        if (
            (float) $order->total <= 0
        ) {
            throw new RuntimeException(
                'مبلغ سفارش معتبر نیست.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Installment-created order is still payable only
        | through its correct installment flow.
        |--------------------------------------------------------------------------
        */

        if (
            $order->payment_method === 'installment'
            && $order->installment_enabled
        ) {
            throw new RuntimeException(
                'برای این سفارش ابتدا باید فرآیند پرداخت اقساطی ادامه پیدا کند.'
            );
        }
    }

    /**
     * Normalize gateway.
     */
    protected function normalizeGateway(
        string $gateway
    ): string {
        return strtolower(
            trim($gateway)
        );
    }

    /**
     * Normalize monetary values.
     *
     * Current database stores money with decimals, but
     * the project works with whole تومان for gateway comparison.
     */
    protected function normalizeAmount(
        mixed $amount
    ): int {
        return (int) round(
            (float) $amount
        );
    }
}
