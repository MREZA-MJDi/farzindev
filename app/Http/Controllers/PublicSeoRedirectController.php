<?php

namespace App\Http\Controllers;

use App\Models\SeoRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PublicSeoRedirectController extends Controller
{
    public function handle(Request $request): RedirectResponse
    {
        if (! $request->isMethodSafe()) {
            throw new NotFoundHttpException();
        }

        $sourcePath = '/' . ltrim($request->getPathInfo(), '/');
        if (mb_strlen($sourcePath) > 255) {
            throw new NotFoundHttpException();
        }

        $redirect = SeoRedirect::query()
            ->where('source_path', $sourcePath)
            ->where('is_active', true)
            ->first();

        if (! $redirect) {
            throw new NotFoundHttpException();
        }

        $redirect->increment('hits', 1, ['last_hit_at' => now()]);

        return redirect()->to($redirect->target_url, $redirect->status_code);
    }
}
