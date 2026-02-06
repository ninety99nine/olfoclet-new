<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UssdSessionController;

Route::prefix('apps/{app}/ussd-sessions')
    ->controller(UssdSessionController::class)
    ->middleware(['auth:sanctum', 'app.permission'])
    ->group(function () {
        Route::get('/', 'showUssdSessions')->name('show.ussd.sessions');
        Route::get('/summary', 'showUssdSessionsSummary')->name('show.ussd.sessions.summary');

        // Explicit route model binding versionlied: VersionServiceProvider.php
        Route::prefix('{ussd_session}')->group(function () {
            Route::get('/', 'showUssdSession')->name('show.ussd.session');
        });
    });

