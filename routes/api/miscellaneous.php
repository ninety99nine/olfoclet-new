<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MiscellaneousController;

Route::get('/filters', [MiscellaneousController::class, 'showFilters'])->name('show.filters');
