<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\BusinessOwnerLoginController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ServicePointController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [BusinessOwnerLoginController::class, 'create'])->name('login');
    Route::post('/login', [BusinessOwnerLoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [BusinessOwnerLoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::middleware(['auth', 'business.owner'])->group(function () {
    // Executive Dashboard
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    // Operations
    Route::get('/orders', function () {
        $settings = \App\Models\BusinessSetting::first() ?? new \App\Models\BusinessSetting([
            'brand_name' => 'Everyday Eats',
            'gst_enabled' => false,
            'cgst' => 2.5,
            'sgst' => 2.5,
        ]);
        return view('orders', compact('settings'));
    });

    Route::get('/service-points', [ServicePointController::class, 'index'])->name('service-points.index');
    Route::post('/service-points', [ServicePointController::class, 'store'])->name('service-points.store');
    Route::put('/service-points/{id}', [ServicePointController::class, 'update'])->name('service-points.update');
    Route::delete('/service-points/{id}', [ServicePointController::class, 'destroy'])->name('service-points.destroy');

    // Management
    Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
    Route::get('/preset-images', [MenuController::class, 'presetImages'])->name('menu.preset-images');
    Route::post('/menu', [MenuController::class, 'store'])->name('menu.store');
    Route::put('/menu/{id}', [MenuController::class, 'update'])->name('menu.update');
    Route::post('/menu/{id}/toggle-stock', [MenuController::class, 'toggleStock'])->name('menu.toggle-stock');
    Route::delete('/menu/{id}', [MenuController::class, 'destroy'])->name('menu.destroy');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::post('/categories/{id}/toggle-active', [CategoryController::class, 'toggleActive'])->name('categories.toggle-active');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/staff', function () {
        return view('staff');
    });

    Route::get('/reports', function () {
        return view('reports');
    });

    // Settings Management
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::post('/settings/business', [SettingsController::class, 'updateBusiness'])->name('settings.business');
    Route::post('/settings/razorpay', [SettingsController::class, 'updateRazorpay'])->name('settings.razorpay');
    Route::post('/settings/cash', [SettingsController::class, 'updateCash'])->name('settings.cash');
    Route::post('/settings/gst', [SettingsController::class, 'updateGst'])->name('settings.gst');
    Route::post('/settings/password', [SettingsController::class, 'updatePassword'])->name('settings.password');
});

// Standalone Public KFC-style Order Status Board
Route::get('/order-status', function () {
    return view('public-kds');
});
