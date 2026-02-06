<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeploymentController;

Route::prefix('apps/{app}/deployments')
    ->controller(DeploymentController::class)
    ->middleware(['auth:sanctum', 'app.permission'])
    ->group(function () {
        Route::get('/', 'showDeployments')->name('show.deployments');
        Route::post('/', 'createDeployment')->name('create.deployment');
        Route::delete('/', 'deleteDeployments')->name('delete.deployments');

        // Explicit route model binding applied: AppServiceProvider.php
        Route::prefix('{deployment}')->group(function () {
            Route::get('/', 'showDeployment')->name('show.deployment');
            Route::put('/', 'updateDeployment')->name('update.deployment');
            Route::delete('/', 'deleteDeployment')->name('delete.deployment');
        });
    });
