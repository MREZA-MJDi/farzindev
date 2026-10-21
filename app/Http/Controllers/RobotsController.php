<?php

namespace App\Http\Controllers;

use App\Services\Seo\SeoSettings;
use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(SeoSettings $seoSettings): Response
    {
        return response($seoSettings->robotsTxt())
            ->header('Content-Type', 'text/plain; charset=UTF-8')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
