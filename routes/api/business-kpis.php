<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BusinessKpiController;

Route::prefix('apps/{app}/business-kpis')
    ->controller(BusinessKpiController::class)
    ->middleware(['auth:sanctum', 'app.permission'])
    ->group(function () {
        Route::get('/', 'showBusinessKpis')->name('show.business.kpis');
        Route::post('/', 'createBusinessKpi')->name('create.business.kpi');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{business_kpi}')->group(function () {
            Route::get('/', 'showBusinessKpi')->name('show.business.kpi');
            Route::put('/', 'updateBusinessKpi')->name('update.business.kpi');
            Route::delete('/', 'deleteBusinessKpi')->name('delete.business.kpi');
        });
    });

