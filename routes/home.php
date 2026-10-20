<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Dedicated public homepage
|--------------------------------------------------------------------------
|
| This route stays separate from the general storefront routes while
| preserving the existing / URL and the "home" route name used by the app.
|
*/

Route::get('/', [HomeController::class, 'index'])
    ->name('home');
