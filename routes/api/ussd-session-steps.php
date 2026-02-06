<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UssdSessionStepController;

Route::prefix('apps/{app}/ussd-sessions/{ussd_session}/steps')
    ->controller(UssdSessionStepController::class)
    ->middleware(['auth:sanctum', 'app.permission'])
    ->group(function () {
        Route::get('/', 'showUssdSessionSteps')->name('show.ussd.session.steps');

        // Explicit route model binding versionlied: VersionServiceProvider.php
        Route::prefix('{ussd_session_step}')->group(function () {
            Route::get('/', 'showUssdSessionStep')->name('show.ussd.session.step');
        });
    });
