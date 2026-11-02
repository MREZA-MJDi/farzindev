<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(Request $request): View
    {
        if ($request->boolean('session_expired')) {
            $request->session()->flash('warning', 'نشست مدیریت منقضی شده است؛ دوباره وارد شوید.');
        }

        return view('admin.auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->validated();

        $key = 'admin-login:' . Str::lower($credentials['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'تعداد تلاش‌ها بیش از حد مجاز است. کمی بعد دوباره تلاش کنید.']);
        }

        $authenticated = Auth::guard('admin')->attempt([
            'email' => Str::lower($credentials['email']),
            'password' => $credentials['password'],
            'role' => 'admin',
        ], $request->boolean('remember'));

        if (! $authenticated || ! $request->user('admin')?->isAdmin()) {
            Auth::guard('admin')->logout();
            RateLimiter::hit($key, 60);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'اطلاعات ورود صحیح نیست.']);
        }

        RateLimiter::clear($key);
        $request->session()->regenerate();

        return redirect()
            ->intended(route('admin.dashboard'))
            ->with('success', 'به پنل مدیریت خوش آمدید.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('admin.login')
            ->with('success', 'با موفقیت از پنل مدیریت خارج شدید.');
    }
}
