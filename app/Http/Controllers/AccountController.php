<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\StoreAddressRequest;
use App\Http\Requests\Account\UpdateAddressRequest;
use App\Http\Requests\Account\UpdateProfileRequest;
use App\Models\Address;
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

        $stats = [
            'orders' => $user->orders()->count(),

            'processing_orders' => $user
                ->orders()
                ->whereIn('status', [
                    'pending',
                    'processing',
                ])
                ->count(),

            'wishlist' => $user
                ->wishlists()
                ->count(),

            'paid_orders' => $user->orders()
                ->where('payment_status', 'paid')
                ->count(),

            'installment_orders' => $user->orders()
                ->where('payment_method', 'installment')
                ->count(),
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
            ->latest()
            ->get();

        return view('account.addresses.index', [
            'addresses' => $addresses,
        ]);
    }

    public function storeAddress(
        StoreAddressRequest $request
    ): RedirectResponse {
        $user = Auth::user();

        $data = $request->validated();

        DB::transaction(function () use (
            $user,
            &$data
        ) {
            if ($data['is_default'] ?? false) {
                $user->addresses()
                    ->update([
                        'is_default' => false,
                    ]);
            }

            $user->addresses()->create($data);
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

        DB::transaction(function () use (
            $address,
            &$data
        ) {
            if ($data['is_default'] ?? false) {
                $address->user
                    ->addresses()
                    ->whereKeyNot($address->id)
                    ->update([
                        'is_default' => false,
                    ]);
            }

            $address->update($data);
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

        $wasDefault = $address->is_default;

        $user = $address->user;

        $address->delete();

        if ($wasDefault) {
            $user->addresses()
                ->latest('id')
                ->first()
                ?->update([
                    'is_default' => true,
                ]);
        }

        return back()->with(
            'success',
            'آدرس حذف شد.'
        );
    }

    public function setDefaultAddress(
        Address $address
    ): RedirectResponse {
        $this->authorize('update', $address);

        DB::transaction(function () use ($address) {

            $address->user
                ->addresses()
                ->update([
                    'is_default' => false,
                ]);

            $address->update([
                'is_default' => true,
            ]);
        });

        return back()->with(
            'success',
            'آدرس پیش‌فرض تغییر کرد.'
        );
    }
}
