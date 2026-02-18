<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Auth::routes();

// Quick Login (Demo)
Route::get('/quick-login', [App\Http\Controllers\QuickLoginController::class, 'index'])->name('quick-login');
Route::post('/quick-login/{user}', [App\Http\Controllers\QuickLoginController::class, 'login'])->name('quick-login.do');

// Company Selection
Route::middleware(['auth'])->group(function () {
    Route::get('/company/select', [App\Http\Controllers\CompanyController::class, 'select'])->name('company.select');
    Route::post('/company/{company}/set', [App\Http\Controllers\CompanyController::class, 'set'])->name('company.set');
});

// All company-scoped routes
Route::middleware(['auth', 'company'])->group(function () {

    Route::get('/', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('home');

    // Customers
    Route::resource('customers', App\Http\Controllers\CustomerController::class);

    // Sites (nested under customers)
    Route::resource('customers.sites', App\Http\Controllers\SiteController::class)->shallow();

    // Vendors
    Route::resource('vendors', App\Http\Controllers\VendorController::class);

    // Products
    Route::resource('products', App\Http\Controllers\ProductController::class);

    // Purchases
    Route::resource('purchases', App\Http\Controllers\PurchaseController::class);

    // Invoices
    Route::resource('invoices', App\Http\Controllers\InvoiceController::class);
    Route::get('/invoices/{invoice}/pdf', [App\Http\Controllers\InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::get('/invoices/{invoice}/download', [App\Http\Controllers\InvoiceController::class, 'download'])->name('invoices.download');

    // Payments
    Route::post('/invoices/{invoice}/payments', [App\Http\Controllers\PaymentController::class, 'store'])->name('payments.store');

    // Warranties
    Route::get('/warranties', [App\Http\Controllers\WarrantyController::class, 'index'])->name('warranties.index');
    Route::get('/warranties/search', [App\Http\Controllers\WarrantyController::class, 'search'])->name('warranties.search');
    Route::patch('/warranties/{warranty}', [App\Http\Controllers\WarrantyController::class, 'update'])->name('warranties.update');

    // Serial Number Search
    Route::get('/serials/search', [App\Http\Controllers\SerialNumberController::class, 'search'])->name('serials.search');
    Route::get('/serials/{serial}', [App\Http\Controllers\SerialNumberController::class, 'show'])->name('serials.show');

    // Tickets
    Route::resource('tickets', App\Http\Controllers\TicketController::class);
    Route::post('/tickets/{ticket}/assign', [App\Http\Controllers\TicketController::class, 'assign'])->name('tickets.assign');
    Route::post('/tickets/{ticket}/update-status', [App\Http\Controllers\TicketController::class, 'updateStatus'])->name('tickets.updateStatus');

    // Users Management (admin only)
    Route::middleware(['role:company_admin,manager'])->group(function () {
        Route::resource('users', App\Http\Controllers\UserController::class);
    });

    // Company Settings
    Route::middleware(['role:company_admin,manager'])->group(function () {
        Route::get('/company/settings', [App\Http\Controllers\CompanyController::class, 'settings'])->name('company.settings');
        Route::put('/company/settings', [App\Http\Controllers\CompanyController::class, 'updateSettings'])->name('company.settings.update');
    });

    // API-like routes for AJAX
    Route::get('/api/products/{product}/stock', [App\Http\Controllers\ProductController::class, 'getStock'])->name('api.product.stock');
    Route::get('/api/customers/{customer}/sites', [App\Http\Controllers\SiteController::class, 'getForCustomer'])->name('api.customer.sites');
});

// Customer Portal (separate layout/limited access)
Route::middleware(['auth', 'company', 'role:customer'])->prefix('portal')->name('portal.')->group(function () {
    Route::get('/', [App\Http\Controllers\CustomerPortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/invoices', [App\Http\Controllers\CustomerPortalController::class, 'invoices'])->name('invoices');
    Route::get('/invoices/{invoice}', [App\Http\Controllers\CustomerPortalController::class, 'showInvoice'])->name('invoices.show');
    Route::get('/warranties', [App\Http\Controllers\CustomerPortalController::class, 'warranties'])->name('warranties');
    Route::get('/complaints', [App\Http\Controllers\CustomerPortalController::class, 'complaints'])->name('complaints');
    Route::post('/complaints', [App\Http\Controllers\CustomerPortalController::class, 'storeComplaint'])->name('complaints.store');
});
