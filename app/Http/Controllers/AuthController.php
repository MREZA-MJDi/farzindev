<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Cart;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(Request $request): View
    {
        $intended = $request->query('intended');

        if (is_string($intended) && str_starts_with($intended, '/') && ! str_starts_with($intended, '//')) {
            $request->session()->put('url.intended', url($intended));
        }

        return view('auth.login');
    }

    public function login(
        LoginRequest $request
    ): RedirectResponse {
        $credentials = $request->validated();

        $identifier = $credentials['phone'];
        $loginField = str_contains($identifier, '@') ? 'email' : 'phone';

        $throttleKey = Str::transliterate(
            Str::lower($identifier) .
            '|' .
            $request->ip()
        );

        if (RateLimiter::tooManyAttempts(
            $throttleKey,
            5
        )) {
            $seconds = RateLimiter::availableIn(
                $throttleKey
            );

            return back()
                ->withInput($request->only('phone'))
                ->withErrors([
                    'phone' =>
                        "تعداد تلاش‌ها زیاد است. {$seconds} ثانیه دیگر دوباره تلاش کنید.",
                ]);
        }

        // Capture the guest cart key before the session guard rotates the session ID.
        $guestCartSessionId = $request->session()->getId();

        if (! Auth::attempt([
            $loginField => $identifier,
            'password' => $credentials['password'],
        ], $credentials['remember'] ?? false)) {

            RateLimiter::hit(
                $throttleKey,
                60
            );

            return back()
                ->withInput($request->only('phone', 'email'))
                ->withErrors([
                    'phone' =>
                        'شماره موبایل یا رمز عبور صحیح نیست.',
                ]);
        }

        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();

        /*
        |--------------------------------------------------------------------------
        | Role Based Redirect
        |--------------------------------------------------------------------------
        */

        if ($request->user()->isAdmin()) {
            return redirect()
                ->route('admin.dashboard')
                ->with(
                    'success',
                    'به پنل مدیریت خوش آمدید.'
                );
        }

        // Customer cart migration is unrelated to admin access; keep admin login independent of it.
        $this->mergeGuestCartIntoUserCart($request, $guestCartSessionId);

        return redirect()
            ->intended(route('account.index'))
            ->with(
                'success',
                'با موفقیت وارد شدید.'
            );
    }

    public function showRegister(Request $request): View
    {
        $intended = $request->query('intended');

        if (is_string($intended) && str_starts_with($intended, '/') && ! str_starts_with($intended, '//')) {
            $request->session()->put('url.intended', url($intended));
        }

        return view('auth.register');
    }

    public function register(
        RegisterRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        try {
            $user = DB::transaction(function () use (
                $validated
            ) {
                return User::create([
                'name' => trim(
                    $validated['first_name'] .
                    ' ' .
                    $validated['last_name']
                ),

                'email' => filled($validated['email'] ?? null)
                    ? Str::lower($validated['email'])
                    : null,

                'phone' => $validated['phone'],

                'password' => Hash::make(
                    $validated['password']
                ),

                /*
                |--------------------------------------------------------------------------
                | New users are always customers
                |--------------------------------------------------------------------------
                */
                    'role' => 'customer',
                ]);
            });
        } catch (\Illuminate\Database\QueryException $exception) {
            report($exception);

            // A concurrent duplicate signup or a storage constraint must never
            // surface as a raw 500 page; return the user to actionable fields.
            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors([
                    'phone' => 'حساب کاربری ایجاد نشد. شماره موبایل و ایمیل را بررسی کنید و دوباره تلاش کنید.',
                ]);
        }

        // Login rotates the session ID, so keep the guest cart's original key.
        $guestCartSessionId = $request->session()->getId();

        Auth::login($user);

        $request->session()->regenerate();

        $this->mergeGuestCartIntoUserCart($request, $guestCartSessionId);


        if ($request->user()->isAdmin()) {
            return redirect()
                ->route('admin.dashboard')
                ->with(
                    'success',
                    'به پنل مدیریت خوش آمدید.'
                );
        }

        return redirect()
            ->intended(route('account.index'))
            ->with(
                'success',
                'حساب کاربری با موفقیت ایجاد شد.'
            );
    }

    public function logout(
        Request $request
    ): RedirectResponse {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with(
                'success',
                'با موفقیت خارج شدید.'
            );
    }

    protected function mergeGuestCartIntoUserCart(
        Request $request,
        ?string $guestSessionId = null
    ): void {
        $sessionId = $guestSessionId ?? $request->session()->getId();

        $guestCart = Cart::query()
            ->where(
                'session_id',
                $sessionId
            )
            ->whereNull('user_id')
            ->where(
                'status',
                'active'
            )
            ->with('items')
            ->first();

        if (! $guestCart) {
            return;
        }

        $userCart = Cart::query()
            ->firstOrCreate(
                [
                    'user_id' => Auth::id(),
                    'status' => 'active',
                ],
                [
                    'session_id' => null,
                ]
            );

        foreach ($guestCart->items as $guestItem) {
            $existingItemQuery = $userCart
                ->items()
                ->where('product_id', $guestItem->product_id);

            if ($guestItem->variant_key !== null) {
                $existingItemQuery->where(function ($query) use ($guestItem) {
                    $query->where('variant_key', $guestItem->variant_key);

                    // Merge pre-snapshot, no-variant cart rows with the new "base" key.
                    if ($guestItem->variant_key === 'base') {
                        $query->orWhere(function ($legacy) {
                            $legacy
                                ->whereNull('variant_key')
                                ->whereNull('variant_options')
                                ->whereNull('product_variant_id');
                        });
                    }
                });
            } else {
                $existingItemQuery
                    ->whereNull('variant_key')
                    ->where('product_variant_id', $guestItem->product_variant_id);
            }

            $existingItem = $existingItemQuery->first();

            if ($existingItem) {
                $existingItem->increment(
                    'quantity',
                    $guestItem->quantity
                );

                continue;
            }

            $userCart->items()->create([
                'product_id' => $guestItem->product_id,
                'product_variant_id' => $guestItem->product_variant_id,
                'variant_key' => $guestItem->variant_key,
                'variant_options' => $guestItem->variant_options,
                'quantity' => $guestItem->quantity,
                'unit_price' => $guestItem->unit_price,
            ]);
        }

        $guestCart->items()->delete();

        $guestCart->update([
            'status' => 'converted',
        ]);
    }
}
