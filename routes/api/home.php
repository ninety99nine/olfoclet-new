<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::prefix('home')
    ->middleware(['auth:sanctum'])
    ->controller(HomeController::class)
    ->group(function () {
        Route::get('/summary', 'showHomeSummary')->name('show.home.summary');
    });
