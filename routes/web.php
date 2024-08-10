<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ConsumptionController;

Route::get('/', [ConsumptionController::class, 'index'])->name('consumption.index');
Route::post('/calculate', [ConsumptionController::class, 'calculateUsage'])->name('calculate.usage');
