<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Placeholder routes (we'll build each module next)
    Route::get('/customers', fn() => view('coming-soon'))->name('customers.index');
    Route::get('/rentals', fn() => view('coming-soon'))->name('rentals.index');
    Route::get('/sales', fn() => view('coming-soon'))->name('sales.index');
    Route::get('/products', fn() => view('coming-soon'))->name('products.index');
    Route::get('/categories', fn() => view('coming-soon'))->name('categories.index');
    Route::get('/vendors', fn() => view('coming-soon'))->name('vendors.index');
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
