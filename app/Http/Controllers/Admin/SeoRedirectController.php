<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SeoRedirectController extends Controller
{
    public function index(): View
    {
        $redirects = SeoRedirect::query()
            ->orderByDesc('is_active')
            ->orderByDesc('updated_at')
            ->paginate(25);

        return view('admin.seo-redirects.index', compact('redirects'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRedirect($request);
        $validated['source_path'] = $this->normalizePath($validated['source_path']);

        if ($validated['source_path'] === $this->localPath($validated['target_url'])) {
            return back()->withInput()->withErrors(['target_url' => 'مبدأ و مقصد Redirect نباید یکسان باشند.']);
        }

        SeoRedirect::create($validated);

        return redirect()->route('admin.seo-redirects.index')->with('success', 'ریدایرکت ذخیره شد.');
    }

    public function update(Request $request, SeoRedirect $seoRedirect): RedirectResponse
    {
        $validated = $this->validateRedirect($request, $seoRedirect);
        $validated['source_path'] = $this->normalizePath($validated['source_path']);

        if ($validated['source_path'] === $this->localPath($validated['target_url'])) {
            return back()->withInput()->withErrors(['target_url' => 'مبدأ و مقصد Redirect نباید یکسان باشند.']);
        }

        $seoRedirect->update($validated);

        return redirect()->route('admin.seo-redirects.index')->with('success', 'ریدایرکت به‌روزرسانی شد.');
    }

    public function destroy(SeoRedirect $seoRedirect): RedirectResponse
    {
        $seoRedirect->delete();

        return redirect()->route('admin.seo-redirects.index')->with('success', 'ریدایرکت حذف شد.');
    }

    private function validateRedirect(Request $request, ?SeoRedirect $redirect = null): array
    {
        return $request->validate([
            'source_path' => [
                'required',
                'string',
                'max:255',
                'regex:~^/(?!/)[A-Za-z0-9._~!$&\'()*+,;=:@%/-]*$~',
                Rule::unique('seo_redirects', 'source_path')->ignore($redirect?->id),
            ],
            'target_url' => ['required', 'string', 'max:2048', 'regex:~^(?:https://[^\s]+|/(?!/)[^\s]*)$~i'],
            'status_code' => ['required', Rule::in([301, 302])],
            'notes' => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'source_path.regex' => 'مبدأ باید یک مسیر داخلی مانند /old-page باشد.',
            'target_url.regex' => 'مقصد باید یک HTTPS URL یا مسیر داخلی مانند /new-page باشد.',
        ]);
    }

    private function normalizePath(string $path): string
    {
        return '/' . ltrim(trim($path), '/');
    }

    private function localPath(string $url): ?string
    {
        if (str_starts_with($url, '/')) {
            return $url;
        }

        $host = parse_url($url, PHP_URL_HOST);
        if ($host && strcasecmp($host, request()->getHost()) === 0) {
            return parse_url($url, PHP_URL_PATH) ?: '/';
        }

        return null;
    }
}
