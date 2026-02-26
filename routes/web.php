<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Simple ping — 403/404 debug: if this works, Laravel is running
Route::get('/ping', function () {
    return response('OK', 200, ['Content-Type' => 'text/plain']);
})->name('ping');

// Web installer: fill DB & app URL, one-click install (only when not yet installed)
Route::middleware(['installed'])->group(function () {
    Route::get('/install', [App\Http\Controllers\InstallController::class, 'index'])->name('install.index');
    Route::post('/install', [App\Http\Controllers\InstallController::class, 'store'])->name('install.store');
});

Auth::routes();

// Notification test (mail/notification setup)
Route::get('/notification-test', App\Http\Controllers\NotificationTestController::class)->name('notification-test');

// Server test (403 fix helper) — works even if public/server-test.php is missing on server
Route::get('/server-test', App\Http\Controllers\ServerTestController::class)->name('server-test');
Route::get('/server-test.php', App\Http\Controllers\ServerTestController::class);

// Full check — har cheez verify, SS bhejo to 403/missing fix karenge
Route::get('/full-check', App\Http\Controllers\FullCheckController::class)->name('full-check');
Route::get('/prerequisites', App\Http\Controllers\FullCheckController::class)->name('prerequisites');

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
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard.page');

    // Customers
    Route::resource('customers', App\Http\Controllers\CustomerController::class);

    // Sites (nested under customers)
    Route::resource('customers.sites', App\Http\Controllers\SiteController::class)->shallow();

    // Vendors
    Route::resource('vendors', App\Http\Controllers\VendorController::class);

    // Products
    Route::get('products/import/template', [App\Http\Controllers\ProductController::class, 'downloadImportTemplate'])->name('products.import.template');
    Route::get('products/import', [App\Http\Controllers\ProductController::class, 'import'])->name('products.import');
    Route::post('products/import', [App\Http\Controllers\ProductController::class, 'storeImport'])->name('products.import.store');
    Route::get('products/bulk-create', [App\Http\Controllers\ProductController::class, 'bulkCreate'])->name('products.bulk-create');
    Route::post('products/bulk-store', [App\Http\Controllers\ProductController::class, 'bulkStore'])->name('products.bulk-store');
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

    // Support Knowledge Base (admin CRUD)
    Route::resource('support-articles', App\Http\Controllers\SupportArticleController::class)->except(['show']);
    Route::resource('support-videos', App\Http\Controllers\SupportVideoController::class)->except(['show']);

    // Support Page (accessible to all logged-in users)
    Route::get('/support', [App\Http\Controllers\SupportPageController::class, 'index'])->name('support.index');

    // Site Expenses - create/store accessible to technician too
    Route::middleware(['role:company_admin,manager,accountant,technician'])->group(function () {
        Route::get('/site-expenses/create', [App\Http\Controllers\SiteExpenseController::class, 'create'])->name('site-expenses.create');
        Route::post('/site-expenses', [App\Http\Controllers\SiteExpenseController::class, 'store'])->name('site-expenses.store');
    });

    // Site Expenses - index/edit/delete only for admin/manager/accountant
    Route::middleware(['role:company_admin,manager,accountant'])->group(function () {
        Route::get('/site-expenses', [App\Http\Controllers\SiteExpenseController::class, 'index'])->name('site-expenses.index');
        Route::get('/site-expenses/{site_expense}/edit', [App\Http\Controllers\SiteExpenseController::class, 'edit'])->name('site-expenses.edit');
        Route::put('/site-expenses/{site_expense}', [App\Http\Controllers\SiteExpenseController::class, 'update'])->name('site-expenses.update');
        Route::delete('/site-expenses/{site_expense}', [App\Http\Controllers\SiteExpenseController::class, 'destroy'])->name('site-expenses.destroy');
    });

    // Customer Payment Approvals (admin, manager, accountant)
    Route::middleware(['role:company_admin,manager,accountant'])->group(function () {
        Route::get('/customer-payments', [App\Http\Controllers\CustomerPaymentController::class, 'index'])->name('customer-payments.index');
        Route::post('/customer-payments/{customer_payment}/approve', [App\Http\Controllers\CustomerPaymentController::class, 'approve'])->name('customer-payments.approve');
        Route::post('/customer-payments/{customer_payment}/reject', [App\Http\Controllers\CustomerPaymentController::class, 'reject'])->name('customer-payments.reject');
    });

    // Customer Advances (admin, manager, accountant)
    Route::middleware(['role:company_admin,manager,accountant'])->group(function () {
        Route::get('/customer-advances/{customer_advance}/receipt', [App\Http\Controllers\CustomerAdvanceController::class, 'receipt'])->name('customer-advances.receipt');
        Route::get('/customer-advances/{customer_advance}/download', [App\Http\Controllers\CustomerAdvanceController::class, 'download'])->name('customer-advances.download');
        Route::resource('customer-advances', App\Http\Controllers\CustomerAdvanceController::class)->except(['edit', 'update', 'destroy']);
    });

    // Estimates (admin, manager, accountant)
    Route::middleware(['role:company_admin,manager,accountant'])->group(function () {
        Route::resource('estimates', App\Http\Controllers\EstimateController::class);
        Route::post('/estimates/{estimate}/convert', [App\Http\Controllers\EstimateController::class, 'convertToInvoice'])->name('estimates.convert');
        Route::post('/estimates/{estimate}/purchase-order', [App\Http\Controllers\EstimateController::class, 'createPurchaseOrder'])->name('estimates.purchase-order');
        Route::get('/estimates/{estimate}/pdf', [App\Http\Controllers\EstimateController::class, 'pdf'])->name('estimates.pdf');
        Route::get('/estimates/{estimate}/download', [App\Http\Controllers\EstimateController::class, 'download'])->name('estimates.download');
    });

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
    Route::get('/payments', [App\Http\Controllers\CustomerPortalController::class, 'payments'])->name('payments');
    Route::post('/payments', [App\Http\Controllers\CustomerPortalController::class, 'storePayment'])->name('payments.store');
    Route::get('/profile', [App\Http\Controllers\CustomerPortalController::class, 'profile'])->name('profile');
    Route::put('/profile', [App\Http\Controllers\CustomerPortalController::class, 'updateProfile'])->name('profile.update');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
