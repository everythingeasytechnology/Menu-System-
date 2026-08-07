<?php

use Illuminate\Support\Facades\Route;

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
Route::get('/menu', function () {
    return view('menu');
});

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
