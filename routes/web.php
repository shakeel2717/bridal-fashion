<?php

use App\Http\Controllers\AdvancesController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\NotificationsController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\PurchaseOrdersController;
use App\Http\Controllers\RentalsController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\VendorsController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Placeholder routes (we'll build each module next)
    Route::get('/customers', [CustomersController::class, 'index'])->name('customers.index');
    Route::get('/rentals', [RentalsController::class, 'index'])->name('rentals.index');
    Route::get('/rentals/create', [RentalsController::class, 'create'])->name('rentals.create');
    Route::get('/rentals/{rental}', [RentalsController::class, 'show'])->name('rentals.show');
    Route::get('/rentals/{rental}/edit', [RentalsController::class, 'edit'])->name('rentals.edit');
    Route::get('/sales', [SalesController::class, 'index'])->name('sales.index');
    Route::get('/sales/create', [SalesController::class, 'create'])->name('sales.create');
    Route::get('/sales/{sale}', [SalesController::class, 'show'])->name('sales.show');
    Route::get('/purchase-orders', [PurchaseOrdersController::class, 'index'])->name('purchase-orders.index');
    Route::get('/purchase-orders/create', [PurchaseOrdersController::class, 'create'])->name('purchase-orders.create');
    Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrdersController::class, 'show'])->name('purchase-orders.show');
    Route::get('/products', [ProductsController::class, 'index'])->name('products.index');
    Route::get('/categories', [CategoriesController::class, 'index'])->name('categories.index');
    Route::get('/vendors', [VendorsController::class, 'index'])->name('vendors.index');
    Route::get('/employees', [EmployeesController::class, 'index'])->name('employees.index');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/salary', [SalaryController::class, 'index'])->name('salary.index');
    Route::get('/advances', [AdvancesController::class, 'index'])->name('advances.index');
    Route::get('/notifications', [NotificationsController::class, 'index'])->name('notifications.index');
    Route::get('/reports', fn () => view('coming-soon'))->name('reports.index');
    Route::get('/settings', fn () => view('coming-soon'))->name('settings.index');
    Route::get('/feature-toggles', fn () => view('coming-soon'))->name('feature-toggles.index');
});

require __DIR__.'/auth.php';
