<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AnalyticsController;

Route::prefix('analytics')
    ->controller(AnalyticsController::class)
    ->middleware(['auth:sanctum', 'app.permission'])
    ->group(function () {

        Route::get('/overview', 'overview');

        Route::get('/sessions-over-time',       'sessionsOverTime');
        Route::get('/new-users-over-time',      'newUsersOverTime');
        Route::get('/return-users-over-time',   'returnUsersOverTime');

        Route::get('/by-network',               'byNetwork');
        Route::get('/by-country',               'byCountry');
        Route::get('/by-network-and-country',   'byNetworkAndCountry');
        Route::get('/by-device',                'byDevice');

        Route::get('/failure-reasons',          'failureReasons');
        Route::get('/flags-summary',            'flagsSummary');
        Route::get('/flags-by-priority',        'flagsByPriority');
        Route::get('/flags-by-category',        'flagsByCategory');

        Route::get('/heatmap', 'heatmap');
        Route::get('/flags-status', 'flagsStatus');
        Route::get('/flags-by-priority', 'flagsByPriority');
        Route::get('/flags-by-category', 'flagsByCategory');

        Route::get('/failed-sessions-over-time', 'failedSessionsOverTime');

    });
