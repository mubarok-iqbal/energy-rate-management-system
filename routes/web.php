<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ConsumptionController;

Route::get('/', [ConsumptionController::class, 'index'])->name('consumption.index');
Route::get('/calculate', [ConsumptionController::class, 'index'])->name('calculate.usage');
