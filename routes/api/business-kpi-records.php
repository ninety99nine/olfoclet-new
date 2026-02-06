<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BusinessKpiRecordController;

Route::prefix('apps/{app}/business-kpis/{business_kpi}/records')
    ->controller(BusinessKpiRecordController::class)
    ->middleware(['auth:sanctum', 'app.permission'])
    ->group(function () {
        Route::get('/', 'showBusinessKpiRecords')->name('show.business.kpi.records');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{business_kpi_record}')->group(function () {
            Route::get('/', 'showBusinessKpiRecord')->name('show.business.kpi.record');
        });
    });
