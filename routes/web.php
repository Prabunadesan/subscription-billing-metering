<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\web\DashboardPageController;

Route::get('/', function () {
    return view('welcome');
});


Route::get(
    '/dashboard/{merchant}',
    [DashboardPageController::class, 'show']
)->name('dashboard');