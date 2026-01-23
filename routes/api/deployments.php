<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeploymentController;

Route::middleware(['auth:sanctum'])
    ->prefix('deployments')
    ->controller(DeploymentController::class)
    ->group(function () {
        Route::get('/', 'showDeployments')->name('show.deployments');
        Route::post('/', 'createDeployment')->name('create.deployment');
        Route::delete('/', 'deleteDeployments')->name('delete.deployments');

        Route::middleware(['app.permission'])
            ->prefix('{deployment}')
            ->group(function () {
                Route::get('/', 'showDeployment')->name('show.deployment');
                Route::put('/', 'updateDeployment')->name('update.deployment');
                Route::delete('/', 'deleteDeployment')->name('delete.deployment');
            });
    });
