<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Customer;

// ==================== Landing ====================
Route::get('/', [LandingController::class, 'index'])->name('landing');

// ==================== Authentication ====================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Email Verification
    Route::prefix('email')->name('verification.')->group(function () {
        Route::get('/verify', [AuthController::class, 'showVerifyEmail'])->name('notice');
        Route::get('/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->middleware('signed')->name('verify');
        Route::post('/verification-notification', [AuthController::class, 'resendVerification'])->middleware('throttle:6,1')->name('send');
    });
});

// ==================== Admin ====================
Route::middleware(['auth', 'role:admin,owner,mekanik,kasir'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [Admin\ProfileController::class, 'index'])->name('profile');
        Route::put('/', [Admin\ProfileController::class, 'update'])->name('profile.update');
        Route::put('/password', [Admin\ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::delete('/photo', [Admin\ProfileController::class, 'deletePhoto'])->name('profile.photo.destroy');
    });

    Route::middleware('role:admin,owner')->group(function () {
        Route::resource('heroes', Admin\HeroController::class)->except(['create', 'show', 'edit']);
    });

    Route::middleware('role:owner')->group(function () {
        Route::prefix('reports')->name('reports.')->group(function () {
            // Finance
            Route::get('/finance', [Admin\Reports\FinanceReportController::class, 'index'])->name('finance');
            Route::get('/finance/pdf', [Admin\Reports\FinanceReportController::class, 'exportPdf'])->name('finance.pdf');
            Route::get('/finance/excel', [Admin\Reports\FinanceReportController::class, 'exportExcel'])->name('finance.excel');
            
            // Sales
            Route::get('/sales', [Admin\Reports\SalesReportController::class, 'index'])->name('sales');
            Route::get('/sales/pdf', [Admin\Reports\SalesReportController::class, 'exportPdf'])->name('sales.pdf');
            Route::get('/sales/excel', [Admin\Reports\SalesReportController::class, 'exportExcel'])->name('sales.excel');
            
            // Purchases
            Route::get('/purchases', [Admin\Reports\PurchaseReportController::class, 'index'])->name('purchases');
            Route::get('/purchases/pdf', [Admin\Reports\PurchaseReportController::class, 'exportPdf'])->name('purchases.pdf');
            Route::get('/purchases/excel', [Admin\Reports\PurchaseReportController::class, 'exportExcel'])->name('purchases.excel');
        });
    });

    Route::middleware('role:admin,owner')->group(function () {
        // Users
        Route::resource('users', Admin\UserController::class)->except(['show']);
        Route::delete('users/{user}/photo', [Admin\UserController::class, 'deletePhoto'])->name('users.photo.destroy');
        Route::post('users/{id}/restore', [Admin\UserController::class, 'restore'])->name('users.restore');

        // Services
        Route::resource('services', Admin\ServiceController::class)->only(['index', 'store', 'update', 'destroy']);

        // Spareparts
        Route::resource('spareparts', Admin\SparepartController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::post('spareparts/{sparepart}/purchase', [Admin\SparepartController::class, 'storePurchase'])->name('spareparts.purchase');

        // Distributors
        Route::resource('distributors', Admin\DistributorController::class)->only(['index', 'store', 'update', 'destroy']);
    });

    // Bookings
    Route::prefix('bookings')->name('bookings.')->group(function () {
        Route::get('/', [Admin\BookingController::class, 'index'])->name('index');
        Route::get('/{booking}', [Admin\BookingController::class, 'show'])->name('show');
        Route::put('/{booking}/status', [Admin\BookingController::class, 'updateStatus'])->name('status');
        Route::post('/{booking}/services', [Admin\BookingController::class, 'addService'])->name('add-service');
        Route::delete('/{booking}/services/{bookingService}', [Admin\BookingController::class, 'removeService'])->name('remove-service');
        Route::post('/{booking}/spareparts', [Admin\BookingController::class, 'addSparepart'])->name('add-sparepart');
        Route::delete('/{booking}/spareparts/{bookingSparepart}', [Admin\BookingController::class, 'removeSparepart'])->name('remove-sparepart');
    });

    // Transactions
    Route::prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/', [Admin\TransactionController::class, 'index'])->name('index');
        Route::get('/create/{booking}', [Admin\TransactionController::class, 'create'])->name('create');
        Route::post('/', [Admin\TransactionController::class, 'store'])->name('store');
        Route::get('/{transaction}', [Admin\TransactionController::class, 'show'])->name('show');
    });

    Route::middleware('role:admin,owner,kasir')->group(function () {
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [Admin\PaymentController::class, 'index'])->name('index');
            Route::post('/{payment}/cash', [Admin\PaymentController::class, 'processCash'])->name('cash');
        });
    });

    Route::middleware('role:kasir')->group(function () {
        Route::prefix('cashier')->name('cashier.')->group(function () {
            Route::get('/', [Admin\CashierController::class, 'index'])->name('index');
            Route::post('/', [Admin\CashierController::class, 'store'])->name('store');
        });
    });
});

// ==================== Customer ====================
Route::middleware(['auth', 'verified', 'role:customer'])
    ->prefix('customer')
    ->name('customer.')
    ->group(function () {

    Route::get('/dashboard', [Customer\DashboardController::class, 'index'])->name('dashboard');

    // Profile
    Route::prefix('profile')->group(function () {
        Route::get('/', [Customer\ProfileController::class, 'index'])->name('profile');
        Route::put('/', [Customer\ProfileController::class, 'update'])->name('profile.update');
        Route::put('/password', [Customer\ProfileController::class, 'updatePassword'])->name('profile.password');
        Route::delete('/photo', [Customer\ProfileController::class, 'deletePhoto'])->name('profile.photo.destroy');
    });

    // Vehicles
    Route::resource('vehicles', Customer\VehicleController::class)->except(['create', 'show', 'edit']);

    // Bookings
    Route::resource('bookings', Customer\BookingController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/bookings/{booking}/invoice', [Customer\BookingController::class, 'invoice'])->name('bookings.invoice');
    Route::post('/bookings/{booking}/cancel', [Customer\BookingController::class, 'cancel'])->name('bookings.cancel');

    // Payments
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/{payment}', [Customer\PaymentController::class, 'show'])->name('show');
        Route::get('/{payment}/pay', [Customer\PaymentController::class, 'pay'])->name('pay');
        Route::post('/{payment}/digital', [Customer\PaymentController::class, 'initiateDigital'])->name('digital');
        Route::get('/{payment}/status', [Customer\PaymentController::class, 'checkStatus'])->name('status');
        Route::post('/{payment}/cancel', [Customer\PaymentController::class, 'cancelDigital'])->name('cancel');
    });
});
