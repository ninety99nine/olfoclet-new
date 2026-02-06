<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VersionController;

Route::prefix('apps/{app}/versions')
    ->controller(VersionController::class)
    ->middleware(['auth:sanctum', 'app.permission'])
    ->group(function () {
        Route::get('/', 'showVersions')->name('show.versions');
        Route::post('/', 'createVersion')->name('create.version');
        Route::delete('/', 'deleteVersions')->name('delete.versions');

        // Explicit route model binding versionlied: VersionServiceProvider.php
        Route::prefix('{version}')->group(function () {
            Route::get('/', 'showVersion')->name('show.version');
            Route::put('/', 'updateVersion')->name('update.version');
            Route::delete('/', 'deleteVersion')->name('delete.version');
        });
    });
