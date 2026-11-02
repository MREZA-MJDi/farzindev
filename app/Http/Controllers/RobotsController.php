<?php

namespace App\Http\Controllers;

use App\Services\Seo\SeoSettings;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(SeoSettings $seoSettings): Response
    {
        $robots = $seoSettings->robotsTxt();
        $adminPath = trim((string) config('farzin.admin_path', 'admin'), '/') ?: 'admin';
        $adminDirective = 'Disallow: /' . $adminPath;

        if (! str_contains($robots, $adminDirective)) {
            $robots = rtrim($robots) . "\n" . $adminDirective . "\n";
        }

        return response($robots)
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
