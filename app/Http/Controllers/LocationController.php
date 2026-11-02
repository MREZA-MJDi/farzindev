<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Province;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function provinces(): JsonResponse
    {
        return response()
            ->json([
                'data' => Province::query()
                    ->orderBy('name')
                    ->get(['id', 'name']),
            ])
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function cities(Province $province): JsonResponse
    {
        return response()
            ->json([
                'data' => City::query()
                    ->where('province_id', $province->id)
                    ->orderBy('name')
                    ->get(['id', 'name', 'province_id']),
            ])
            ->header('Cache-Control', 'public, max-age=3600');
    }
}
