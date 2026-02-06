<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AppController;

Route::prefix('apps')
    ->middleware(['auth:sanctum'])
    ->controller(AppController::class)
    ->group(function () {
        Route::get('/', 'showApps')->name('show.apps');
        Route::post('/', 'createApp')->name('create.app');
        Route::delete('/', 'deleteApps')->name('delete.apps');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::middleware(['app.permission'])->prefix('{app}')->group(function () {
            Route::get('/', 'showApp')->middleware(['set.last.seen.on.app'])->name('show.app');
            Route::put('/', 'updateApp')->middleware(['set.last.seen.on.app'])->name('update.app');
            Route::delete('/', 'deleteApp')->name('delete.app');
        });
    });
