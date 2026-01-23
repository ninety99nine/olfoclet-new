<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UssdSessionFlagController;

Route::middleware(['auth:sanctum'])
    ->prefix('ussd-session-flags')
    ->controller(UssdSessionFlagController::class)
    ->group(function () {
        Route::get('/', 'showUssdSessionFlags')->name('show.ussd.session.flags');
        Route::post('/', 'createUssdSessionFlag')->name('create.ussd.session.flag');
        Route::get('/summary', 'showUssdSessionFlagsSummary')->name('show.ussd.session.flags.summary');

        Route::middleware(['app.permission'])
            ->prefix('{ussd_session_flag}')
            ->group(function () {
                Route::get('/', 'showUssdSessionFlag')->name('show.ussd.session.flag');
                Route::put('/', 'updateUssdSessionFlag')->name('update.ussd.session.flag');
                Route::delete('/', 'deleteUssdSessionFlag')->name('delete.ussd.session.flag');
                Route::post('/resolve', 'resolveUssdSessionFlag')->name('resolve.ussd.session.flag');
                Route::post('/unresolve', 'unresolveUssdSessionFlag')->name('unresolve.ussd.session.flag');
            });
    });
