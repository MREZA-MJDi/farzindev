<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\StoreAddressRequest;
use App\Http\Requests\Account\UpdateAddressRequest;
use App\Http\Requests\Account\UpdateProfileRequest;
use App\Models\Address;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $orders = $user
            ->orders()
            ->with('items')
            ->latest()
            ->limit(5)
            ->get();

        $orderStats = $user->orders()
            ->selectRaw(
                'COUNT(*) AS total_orders,
                 COALESCE(SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END), 0) AS processing_orders,
                 COALESCE(SUM(CASE WHEN payment_status = ? THEN 1 ELSE 0 END), 0) AS paid_orders,
                 COALESCE(SUM(CASE WHEN payment_method = ? THEN 1 ELSE 0 END), 0) AS installment_orders',
                ['pending', 'processing', 'paid', 'installment']
            )
            ->first();

        $stats = [
            'orders' => (int) $orderStats->total_orders,
            'processing_orders' => (int) $orderStats->processing_orders,
            'wishlist' => $user->wishlists()->count(),
            'paid_orders' => (int) $orderStats->paid_orders,
            'installment_orders' => (int) $orderStats->installment_orders,
        ];

        return view('account.index', [
            'user' => $user,
            'orders' => $orders,
            'stats' => $stats,
        ]);
    }

    public function editProfile(): View
    {
        return view('account.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(
        UpdateProfileRequest $request
    ): RedirectResponse {
        $user = Auth::user();

        $user->update(
            $request->validated()
        );

        return back()->with(
            'success',
            'اطلاعات حساب با موفقیت به‌روزرسانی شد.'
        );
    }

    public function addresses(): View
    {
        $addresses = Auth::user()
            ->addresses()
            ->orderByDesc('is_default')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('account.addresses.index', [
            'addresses' => $addresses,
        ]);
    }

    public function storeAddress(
        StoreAddressRequest $request
    ): RedirectResponse {
        $user = Auth::user();

        $data = $request->validated();

        DB::transaction(function () use ($user, $data): void {
            // Serialize changes to a user's default-address invariant.
            $lockedUser = User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($data['is_default'] ?? false) {
                $lockedUser->addresses()->update([
                    'is_default' => false,
                ]);
            }

            $lockedUser->addresses()->create($data);
        });

        return back()->with(
            'success',
            'آدرس با موفقیت ثبت شد.'
        );
    }

    public function updateAddress(
        UpdateAddressRequest $request,
        Address $address
    ): RedirectResponse {
        $this->authorize('update', $address);

        $data = $request->validated();

        DB::transaction(function () use ($address, $data): void {
            $lockedUser = User::query()
                ->whereKey($address->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedAddress = $lockedUser->addresses()
                ->whereKey($address->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($data['is_default'] ?? false) {
                $lockedUser->addresses()
                    ->whereKeyNot($lockedAddress->id)
                    ->update(['is_default' => false]);
            }

            $lockedAddress->update($data);
        });

        return back()->with(
            'success',
            'آدرس با موفقیت به‌روزرسانی شد.'
        );
    }

    public function deleteAddress(
        Address $address
    ): RedirectResponse {
        $this->authorize('delete', $address);

        DB::transaction(function () use ($address): void {
            $lockedUser = User::query()
                ->whereKey($address->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedAddress = $lockedUser->addresses()
                ->whereKey($address->id)
                ->lockForUpdate()
                ->firstOrFail();

            $wasDefault = (bool) $lockedAddress->is_default;
            $lockedAddress->delete();

            if ($wasDefault) {
                $replacement = $lockedUser->addresses()
                    ->orderByDesc('id')
                    ->lockForUpdate()
                    ->first();

                $replacement?->update(['is_default' => true]);
            }
        });

        return back()->with(
            'success',
            'آدرس حذف شد.'
        );
    }

    public function setDefaultAddress(
        Address $address
    ): RedirectResponse {
        $this->authorize('update', $address);

        DB::transaction(function () use ($address): void {
            $lockedUser = User::query()
                ->whereKey($address->user_id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedAddress = $lockedUser->addresses()
                ->whereKey($address->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedUser->addresses()->update(['is_default' => false]);
            $lockedAddress->update(['is_default' => true]);
        });

        return back()->with(
            'success',
            'آدرس پیش‌فرض تغییر کرد.'
        );
    }
}
