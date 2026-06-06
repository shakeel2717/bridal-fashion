<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\VendorsController;
use App\Http\Controllers\ProductsController;

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Placeholder routes (we'll build each module next)
    Route::get('/customers', [CustomersController::class, 'index'])->name('customers.index');
    Route::get('/rentals', fn() => view('coming-soon'))->name('rentals.index');
    Route::get('/sales', fn() => view('coming-soon'))->name('sales.index');
    Route::get('/products', [ProductsController::class, 'index'])->name('products.index');
    Route::get('/categories', [CategoriesController::class, 'index'])->name('categories.index');
    Route::get('/vendors', [VendorsController::class, 'index'])->name('vendors.index');
    Route::get('/employees', fn() => view('coming-soon'))->name('employees.index');
    Route::get('/attendance', fn() => view('coming-soon'))->name('attendance.index');
    Route::get('/salary', fn() => view('coming-soon'))->name('salary.index');
    Route::get('/advances', fn() => view('coming-soon'))->name('advances.index');
    Route::get('/notifications', fn() => view('coming-soon'))->name('notifications.index');
    Route::get('/reports', fn() => view('coming-soon'))->name('reports.index');
    Route::get('/settings', fn() => view('coming-soon'))->name('settings.index');
    Route::get('/feature-toggles', fn() => view('coming-soon'))->name('feature-toggles.index');
});

require __DIR__.'/auth.php';
