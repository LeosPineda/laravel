<?php

use App\Http\Controllers\Superadmin\DashboardController as SuperadminDashboardController;
use App\Http\Controllers\Superadmin\VendorController as SuperadminVendorController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public route - redirect to login
Route::get('/', function () {
    return redirect()->route('login');
});

// Auth routes are handled by Fortify

// Dashboard redirect based on role
Route::get('/dashboard', function () {
    $user = auth()->user();

    return match ($user->role) {
        'superadmin' => redirect()->route('superadmin.dashboard'),
        'vendor' => redirect()->route('vendor.dashboard'),
        'customer' => redirect()->route('customer.home'),
        default => redirect()->route('login'),
    };
})->middleware('auth', 'verified')->name('dashboard');

// Superadmin routes (no email verification needed)
Route::middleware(['auth', 'role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [SuperadminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/vendors', [SuperadminVendorController::class, 'index'])->name('vendors.index');
    Route::get('/vendors/create', [SuperadminVendorController::class, 'create'])->name('vendors.create');
    Route::post('/vendors', [SuperadminVendorController::class, 'store'])->name('vendors.store');
    Route::get('/vendors/{vendor}/edit', [SuperadminVendorController::class, 'edit'])->name('vendors.edit');
    Route::post('/vendors/{vendor}', [SuperadminVendorController::class, 'update'])->name('vendors.update');
    Route::patch('/vendors/{vendor}/toggle-active', [SuperadminVendorController::class, 'toggleActive'])->name('vendors.toggle-active');
    Route::delete('/vendors/{vendor}', [SuperadminVendorController::class, 'destroy'])->name('vendors.destroy');
});

// Vendor routes (requires email verification)
Route::middleware(['auth', 'role:vendor', 'verified'])->prefix('vendor')->name('vendor.')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('vendor/Dashboard');
    })->name('dashboard');
});

// Customer routes (requires email verification)
Route::middleware(['auth', 'role:customer', 'verified'])->prefix('customer')->name('customer.')->group(function () {
    Route::get('/home', function () {
        return Inertia::render('customer/Home');
    })->name('home');
});

require __DIR__.'/settings.php';
