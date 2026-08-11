<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SuperAdminController;
use App\Http\Controllers\Auth\BusinessOwnerLoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderOperationsController;
use App\Http\Controllers\ServicePointController;
use App\Http\Controllers\StaffController;

Route::middleware('guest')->group(function () {
    Route::get('/login', [BusinessOwnerLoginController::class, 'create'])->name('login');
    Route::post('/login', [BusinessOwnerLoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [BusinessOwnerLoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'super.admin'])
    ->group(function () {
        Route::get('/', [SuperAdminController::class, 'index'])->name('dashboard');
        Route::get('/businesses', [SuperAdminController::class, 'businesses'])->name('businesses.index');
        Route::get('/businesses/create', [SuperAdminController::class, 'createBusiness'])->name('businesses.create');
        Route::post('/businesses', [SuperAdminController::class, 'storeBusiness'])->name('businesses.store');
        Route::get('/businesses/{business}/edit', [SuperAdminController::class, 'editBusiness'])->name('businesses.edit');
        Route::put('/businesses/{business}', [SuperAdminController::class, 'updateBusiness'])->name('businesses.update');
    });

Route::middleware(['auth', 'business.owner'])->group(function () {
    // Executive Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Operations
    Route::get('/orders/history', [OrderOperationsController::class, 'history'])->name('dashboard.orders.history');
    Route::get('/orders', [OrderOperationsController::class, 'index'])->name('dashboard.orders.index');
    Route::get('/kitchen-display', [OrderOperationsController::class, 'kitchenDisplay'])->name('dashboard.kitchen-display');
    Route::get('/orders/live-feed', [OrderOperationsController::class, 'feed'])->name('dashboard.orders.feed');
    Route::post('/orders/{order}/status', [OrderOperationsController::class, 'updateStatus'])->name('dashboard.orders.status');
    Route::post('/orders/{order}/items', [OrderOperationsController::class, 'addItem'])->name('dashboard.orders.items.store');
    Route::post('/orders/{order}/items/{item}/status', [OrderOperationsController::class, 'updateItemStatus'])->name('dashboard.orders.items.status');
    Route::post('/orders/{order}/payment', [OrderOperationsController::class, 'updatePayment'])->name('dashboard.orders.payment');
    Route::post('/orders/{order}/cancel', [OrderOperationsController::class, 'cancel'])->name('dashboard.orders.cancel');

    Route::get('/service-points', [ServicePointController::class, 'index'])->name('service-points.index');
    Route::post('/service-points', [ServicePointController::class, 'store'])->name('service-points.store');
    Route::get('/service-points/{id}/scanner', [ServicePointController::class, 'scanner'])->name('service-points.scanner');
    Route::post('/service-points/{id}/settle', [ServicePointController::class, 'settle'])->name('service-points.settle');
    Route::put('/service-points/{id}', [ServicePointController::class, 'update'])->name('service-points.update');
    Route::delete('/service-points/{id}', [ServicePointController::class, 'destroy'])->name('service-points.destroy');

    // Management
    Route::get('/menu', [MenuController::class, 'index'])->name('menu.index');
    Route::get('/preset-images', [MenuController::class, 'presetImages'])->name('menu.preset-images');
    Route::post('/menu', [MenuController::class, 'store'])->name('menu.store');
    Route::put('/menu/{id}', [MenuController::class, 'update'])->name('menu.update');
    Route::post('/menu/{id}/toggle-stock', [MenuController::class, 'toggleStock'])->name('menu.toggle-stock');
    Route::delete('/menu/{id}', [MenuController::class, 'destroy'])->name('menu.destroy');

    Route::get('/coupons', [CouponController::class, 'index'])->name('dashboard.coupons.index');
    Route::post('/coupons', [CouponController::class, 'store'])->name('dashboard.coupons.store');
    Route::put('/coupons/{coupon}', [CouponController::class, 'update'])->name('dashboard.coupons.update');
    Route::post('/coupons/{coupon}/toggle', [CouponController::class, 'toggle'])->name('dashboard.coupons.toggle');
    Route::delete('/coupons/{coupon}', [CouponController::class, 'destroy'])->name('dashboard.coupons.destroy');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::post('/categories/{id}/toggle-active', [CategoryController::class, 'toggleActive'])->name('categories.toggle-active');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/staff', [StaffController::class, 'index'])->name('staff.index');
    Route::post('/staff', [StaffController::class, 'store'])->name('staff.store');
    Route::put('/staff/{staff}', [StaffController::class, 'update'])->name('staff.update');
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy'])->name('staff.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.read-all');

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
