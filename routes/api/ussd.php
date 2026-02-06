<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UssdController;

Route::prefix('ussd')
    ->controller(UssdController::class)
    ->group(function () {

        // Internal Simulator
        Route::post('/simulate', 'simulateUssd')->name('ussd.simulate');

        // Live MNO Traffic
        Route::any('/launch/{deployment}', 'launchUssd')->name('ussd.launch');

    });
