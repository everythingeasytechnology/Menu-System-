<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\MenuController;

// Executive Dashboard
Route::get('/', function () {
    return view('dashboard');
});

// Operations
Route::get('/orders', function () {
    return view('orders');
});

Route::get('/service-points', function () {
    return view('service-points');
});

// Management
Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
Route::get('/preset-images', [MenuController::class, 'presetImages'])->name('menu.preset-images');
Route::post('/menu', [MenuController::class, 'store'])->name('menu.store');
Route::put('/menu/{id}', [MenuController::class, 'update'])->name('menu.update');
Route::post('/menu/{id}/toggle-stock', [MenuController::class, 'toggleStock'])->name('menu.toggle-stock');
Route::delete('/menu/{id}', [MenuController::class, 'destroy'])->name('menu.destroy');

Route::get('/categories', function () {
    return view('categories');
});

Route::get('/staff', function () {
    return view('staff');
});

Route::get('/reports', function () {
    return view('reports');
});

// Standalone Public KFC-style Order Status Board
Route::get('/order-status', function () {
    return view('public-kds');
});

// Settings Management
Route::get('/settings', [SettingsController::class, 'index']);
Route::post('/settings/business', [SettingsController::class, 'updateBusiness'])->name('settings.business');
Route::post('/settings/razorpay', [SettingsController::class, 'updateRazorpay'])->name('settings.razorpay');
Route::post('/settings/cash', [SettingsController::class, 'updateCash'])->name('settings.cash');
Route::post('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
