<?php

use App\Http\Controllers\Api\UsageController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PlanChangeController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\CustomerController;


Route::post('/usage', [UsageController::class, 'store'])
    ->middleware('throttle:usage');

Route::post('/plan-changes', [
    PlanChangeController::class,
    'store',
]);


Route::get(
    '/merchants/{merchant}/dashboard',
    [DashboardController::class, 'show']
);


Route::post('/customers', [CustomerController::class, 'store']);