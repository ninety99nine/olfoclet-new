<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UssdSessionStepController;

Route::middleware(['auth:sanctum'])
    ->prefix('ussd-session-steps')
    ->controller(UssdSessionStepController::class)
    ->group(function () {
        Route::get('/', 'showUssdSessionSteps')->name('show.ussd.session.steps');

        Route::middleware(['app.permission'])
            ->prefix('{ussd_session_step}')
            ->group(function () {
                Route::get('/', 'showUssdSessionStep')->name('show.ussd.session.step');
            });
    });
