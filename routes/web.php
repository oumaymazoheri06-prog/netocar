<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AgenciesController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\BranchesController;
use App\Http\Controllers\ClientsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DataImportController;
use App\Http\Controllers\EmployeesController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReportExportController;
use App\Http\Controllers\ReservationsController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\TicketController;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureAgencyLicenseActive;
use App\Http\Middleware\EnsureTenantIsolation;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::middleware(['auth', '', EnsureTenantIsolation::class])->group(function () {
    Route::view('/guide', 'guide')->name('guide');
    Route::get('/dashboard', DashboardController::class)
        ->middleware(EnsureAgencyLicenseActive::class)
        ->name('dashboard');

    Route::middleware(EnsureAgencyLicenseActive::class)->group(function () {
        Route::get('/reports/{resource}/pdf', [ReportExportController::class, 'download'])->name('reports.download');
        Route::get('/reports/{resource}/print', [ReportExportController::class, 'print'])->name('reports.print');
        Route::get('/reports/{resource}/csv', [ReportExportController::class, 'csv'])->name('reports.csv');
    });

    Route::middleware(CheckRole::class.':admin')->group(function () {
        Route::resource('agencies', AgenciesController::class)->parameters(['agencies' => 'agency']);
        Route::get('/payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
        Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
    });

    Route::middleware([CheckRole::class.':admin,manager', EnsureAgencyLicenseActive::class])->group(function () {
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });

    Route::middleware(CheckRole::class.':admin,manager')->group(function () {
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
        Route::get('/payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
    });

    Route::middleware(CheckRole::class.':manager')->group(function () {
        Route::get('/payment/cashpluss', [PaymentController::class, 'createCashpluss'])->name('payment.cashpluss');
        Route::post('/payment/cashpluss', [PaymentController::class, 'store'])->name('payment.cashpluss.store');

        Route::get('/payment/virement', [PaymentController::class, 'createVirement'])->name('payment.virement');
        Route::post('/payment/virement', [PaymentController::class, 'store'])->name('payment.virement.store');
    });

    Route::middleware([CheckRole::class.':manager', EnsureAgencyLicenseActive::class])->group(function () {
        Route::get('/imports', [DataImportController::class, 'index'])->name('imports.index');
        Route::get('/imports/{resource}/template', [DataImportController::class, 'template'])->name('imports.template');
        Route::post('/imports/{resource}/preview', [DataImportController::class, 'preview'])->name('imports.preview');
        Route::post('/imports/{resource}', [DataImportController::class, 'store'])->name('imports.store');

        Route::resource('branches', BranchesController::class);
        Route::resource('clients', ClientsController::class);
        Route::resource('services', ServicesController::class);
        Route::resource('reservations', ReservationsController::class)->except(['index', 'show']);
        Route::resource('employees', EmployeesController::class);

        Route::resource('tickets', TicketController::class)->only(['create', 'store', 'destroy']);
    });

    Route::middleware([CheckRole::class.':manager,staff', EnsureAgencyLicenseActive::class])->group(function () {
        Route::get('/reservations', [ReservationsController::class, 'index'])->name('reservations.index');
        Route::get('/reservations/{reservation}', [ReservationsController::class, 'show'])->name('reservations.show');

        Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
        Route::get('/tickets/{ticket}', [TicketController::class, 'show'])->name('tickets.show');
        Route::put('/tickets/{ticket}', [TicketController::class, 'update'])->name('tickets.update');
    });
});

require __DIR__.'/settings.php';
