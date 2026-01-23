<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UssdSessionController;

Route::middleware(['auth:sanctum'])
    ->prefix('ussd-sessions')
    ->controller(UssdSessionController::class)
    ->group(function () {
        Route::get('/', 'showUssdSessions')->name('show.ussd.sessions');
        Route::get('/summary', 'showUssdSessionsSummary')->name('show.ussd.sessions.summary');

        Route::middleware(['app.permission'])
            ->prefix('{ussd_session}')
            ->group(function () {
                Route::get('/', 'showUssdSession')->name('show.ussd.session');
            });
    });
