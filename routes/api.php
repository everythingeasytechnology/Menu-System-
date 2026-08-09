<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BusinessController;
use App\Http\Controllers\Api\V1\CouponController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\KitchenController;
use App\Http\Controllers\Api\V1\MenuCategoryController;
use App\Http\Controllers\Api\V1\MenuItemController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\OfferController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PublicMenuController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\RoomController;
use App\Http\Controllers\Api\V1\ServicePointController;
use App\Http\Controllers\Api\V1\StaffController;
use App\Http\Controllers\Api\V1\TableController;
use App\Http\Controllers\Api\V1\WaiterController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);
    Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('auth/reset-password', [AuthController::class, 'resetPassword']);

    Route::get('public/menu/{qr}', [PublicMenuController::class, 'menu']);
    Route::get('public/menu/{qr}/items/{menuItem}', [PublicMenuController::class, 'item']);
    Route::post('public/menu/{qr}/orders', [PublicMenuController::class, 'createOrder']);
    Route::get('public/orders/{orderNumber}', [PublicMenuController::class, 'orderStatus']);

    Route::middleware('api.token')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/change-password', [AuthController::class, 'changePassword']);
        Route::put('auth/profile', [AuthController::class, 'updateProfile']);

        Route::get('business', [BusinessController::class, 'show']);
        Route::put('business', [BusinessController::class, 'update']);
        Route::get('business/settings', [BusinessController::class, 'settings']);
        Route::put('business/settings', [BusinessController::class, 'updateSettings']);
        Route::patch('business/settings', [BusinessController::class, 'updateSettings']);
        Route::get('business/status', [BusinessController::class, 'status']);

        Route::get('staff/roles', [StaffController::class, 'roles']);
        Route::post('staff/{staff}/status', [StaffController::class, 'updateStatus']);
        Route::apiResource('staff', StaffController::class)->parameters(['staff' => 'staff']);

        Route::get('tables/{table}/qr', [TableController::class, 'qr']);
        Route::apiResource('tables', TableController::class)->parameters(['tables' => 'table']);

        Route::get('rooms/{room}/qr', [RoomController::class, 'qr']);
        Route::apiResource('rooms', RoomController::class);

        Route::get('service-points/{servicePoint}/qr', [ServicePointController::class, 'qr']);
        Route::get('service-points/{servicePoint}/scanner', [ServicePointController::class, 'downloadScanner']);
        Route::apiResource('service-points', ServicePointController::class)->parameters(['service-points' => 'servicePoint']);

        Route::post('categories/reorder', [MenuCategoryController::class, 'reorder']);
        Route::post('categories/{category}/toggle', [MenuCategoryController::class, 'toggle']);
        Route::apiResource('categories', MenuCategoryController::class);

        Route::get('menu-items/filter', [MenuItemController::class, 'filter']);
        Route::post('menu-items/{menuItem}/availability', [MenuItemController::class, 'toggleAvailability']);
        Route::apiResource('menu-items', MenuItemController::class)->parameters(['menu-items' => 'menuItem']);

        Route::get('orders/all', [OrderController::class, 'all']);
        Route::get('orders/active', [OrderController::class, 'active']);
        Route::get('orders/status/{status}', [OrderController::class, 'byStatus']);
        Route::post('orders/direct', [OrderController::class, 'directStore']);
        Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
        Route::post('orders/{order}/status', [OrderController::class, 'updateStatus']);
        Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show']);

        Route::get('kitchen/orders', [KitchenController::class, 'active']);
        Route::post('kitchen/orders/{order}/accept', [KitchenController::class, 'accept']);
        Route::post('kitchen/orders/{order}/preparing', [KitchenController::class, 'preparing']);
        Route::post('kitchen/orders/{order}/ready', [KitchenController::class, 'ready']);
        Route::post('kitchen/orders/{order}/completed', [KitchenController::class, 'completed']);

        Route::get('waiter/dashboard', [WaiterController::class, 'dashboard']);
        Route::post('waiter/orders/{order}/served', [WaiterController::class, 'markServed']);

        Route::apiResource('payments', PaymentController::class)->only(['index', 'store', 'show']);

        Route::apiResource('offers', OfferController::class);
        Route::post('coupons/validate', [CouponController::class, 'validateCoupon']);
        Route::post('coupons/apply', [CouponController::class, 'validateCoupon']);
        Route::apiResource('coupons', CouponController::class);

        Route::post('device-tokens', [NotificationController::class, 'storeDeviceToken']);
        Route::delete('device-tokens/{deviceToken}', [NotificationController::class, 'deleteDeviceToken']);
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead']);
        Route::post('notifications/read-all', [NotificationController::class, 'readAll']);

        Route::get('dashboard', [DashboardController::class, 'index']);

        Route::prefix('reports')->group(function () {
            Route::get('daily-sales', [ReportController::class, 'dailySales']);
            Route::get('monthly-sales', [ReportController::class, 'monthlySales']);
            Route::get('orders', [ReportController::class, 'orders']);
            Route::get('item-sales', [ReportController::class, 'itemSales']);
            Route::get('category-sales', [ReportController::class, 'itemSales']);
            Route::get('payments', [ReportController::class, 'paymentReport']);
            Route::get('cancelled-orders', [ReportController::class, 'cancelledOrders']);
            Route::get('top-selling-items', [ReportController::class, 'topSellingItems']);
        });
    });
});
