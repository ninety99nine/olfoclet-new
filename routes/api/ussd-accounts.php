<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UssdAccountController;

Route::middleware(['auth:sanctum'])
    ->prefix('ussd-accounts')
    ->controller(UssdAccountController::class)
    ->group(function () {
        Route::get('/', 'showUssdAccounts')->name('show.ussd.accounts');
        Route::post('/', 'blockUssdAccounts')->name('block.ussd.accounts');
        Route::delete('/', 'deleteUssdAccounts')->name('delete.ussd.accounts');
        Route::get('/summary', 'showUssdAccountsSummary')->name('show.ussd.accounts.summary');

        Route::middleware(['app.permission'])
            ->prefix('{ussd_account}')
            ->group(function () {
                Route::get('/', 'showUssdAccount')->name('show.ussd.account');
                Route::delete('/', 'deleteUssdAccount')->name('delete.ussd.account');
                Route::post('/block', 'blockUssdAccount')->name('block.ussd.account');
                Route::post('/unblock', 'unblockUssdAccount')->name('unblock.ussd.account');
                Route::get('/summary', 'showUssdAccountSummary')->name('show.ussd.account.summary');
            });
    });
