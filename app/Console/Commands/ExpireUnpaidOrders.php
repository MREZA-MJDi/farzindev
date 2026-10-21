<?php

namespace App\Console\Commands;

use App\Models\Order;
use App\Services\Inventory\InventoryReservationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireUnpaidOrders extends Command
{
    protected $signature = 'orders:expire-unpaid {--minutes=1440 : Minutes to keep an unpaid stock reservation}';

    protected $description = 'Release stock held by stale, unpaid orders and cancel their pending payment attempts';

    public function handle(InventoryReservationService $inventory): int
    {
        $minutes = max(60, (int) $this->option('minutes'));
        $cutoff = now()->subMinutes($minutes);
        $expired = 0;

        Order::query()
            ->whereNotNull('inventory_reserved_at')
            ->whereNull('inventory_committed_at')
            ->where('payment_status', '!=', 'paid')
            ->whereIn('status', ['pending', 'processing'])
            ->where('inventory_reserved_at', '<=', $cutoff)
            ->orderBy('id')
            ->select('id')
            ->chunkById(100, function ($orders) use ($inventory, &$expired): void {
                foreach ($orders as $candidate) {
                    $wasExpired = DB::transaction(function () use ($candidate, $inventory): bool {
                        $order = Order::query()
                            ->whereKey($candidate->id)
                            ->lockForUpdate()
                            ->first();

                        if (
                            ! $order
                            || $order->inventory_reserved_at === null
                            || $order->inventory_committed_at !== null
                            || $order->payment_status === 'paid'
                            || ! in_array($order->status, ['pending', 'processing'], true)
                            || $order->inventory_reserved_at->gt(now()->subMinutes(max(60, (int) $this->option('minutes'))))
                        ) {
                            return false;
                        }

                        // Do not expire an order if any installment has already
                        // been marked paid, even if the order-level status is partial.
                        if ($order->installments()->where('status', 'paid')->exists()) {
                            return false;
                        }

                        // A paid provider attempt always wins over stale order state.
                        if ($order->payments()->where('status', 'paid')->exists()) {
                            $inventory->commitForPaidOrder($order);

                            return false;
                        }

                        $order->payments()
                            ->whereIn('status', ['pending', 'initiated'])
                            ->update(['status' => 'failed']);

                        $order->update([
                            'status' => 'cancelled',
                            'payment_status' => 'failed',
                        ]);

                        $inventory->releaseForOrder($order);

                        return true;
                    });

                    if ($wasExpired) {
                        $expired++;
                    }
                }
            });

        $this->info("Released inventory for {$expired} stale unpaid order(s).");

        return self::SUCCESS;
    }
}
