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
Route::get('/quick-login/Login', fn () => redirect()->route('quick-login')); // common typo/bookmark
Route::post('/quick-login/{user}', [App\Http\Controllers\QuickLoginController::class, 'login'])->name('quick-login.do');

// Home / Landing: show login form directly (no redirect to /login)
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return view('auth.login');
})->name('landing');

// Company Selection
Route::middleware(['auth'])->group(function () {
    Route::get('/company/select', [App\Http\Controllers\CompanyController::class, 'select'])->name('company.select');
    Route::post('/company/{company}/set', [App\Http\Controllers\CompanyController::class, 'set'])->name('company.set');
    Route::get('/company/create', [App\Http\Controllers\CompanyController::class, 'create'])->name('company.create');
    Route::post('/company/store', [App\Http\Controllers\CompanyController::class, 'store'])->name('company.store');
});

// All company-scoped routes
Route::middleware(['auth', 'company'])->group(function () {

    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');

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
    Route::match(['post', 'patch'], '/tickets/{ticket}/update-status', [App\Http\Controllers\TicketController::class, 'updateStatus'])->name('tickets.updateStatus');

    // Support Knowledge Base (admin CRUD)
    Route::resource('support-articles', App\Http\Controllers\SupportArticleController::class)->except(['show']);
    Route::resource('support-videos', App\Http\Controllers\SupportVideoController::class)->except(['show']);

    // Support Page (accessible to all logged-in users)
    Route::get('/support', [App\Http\Controllers\SupportPageController::class, 'index'])->name('support.index');

    // Combined Expenses page (admin, manager, accountant, technician)
    Route::get('/expenses', [App\Http\Controllers\ExpensesController::class, 'index'])->name('expenses.index')->middleware('role:company_admin,manager,accountant,technician');

// Estimate templates (was Quotations – merged into Estimates)
    Route::middleware(['role:company_admin,manager,accountant,technician'])->group(function () {
        Route::get('/quotations', fn () => redirect()->route('estimates.index', [], 301));
        Route::get('/estimates/templates', [App\Http\Controllers\QuotationTemplateController::class, 'index'])->name('quotation-templates.index');
        Route::get('/quotations/{quotation_template}', [App\Http\Controllers\QuotationTemplateController::class, 'show'])->name('quotation-templates.show');
        Route::get('/quotations/{quotation_template}/edit', [App\Http\Controllers\QuotationTemplateController::class, 'edit'])->name('quotation-templates.edit');
        Route::put('/quotations/{quotation_template}', [App\Http\Controllers\QuotationTemplateController::class, 'update'])->name('quotation-templates.update');
        Route::get('/quotations/{quotation_template}/pdf', [App\Http\Controllers\QuotationTemplateController::class, 'pdf'])->name('quotation-templates.pdf');
        Route::get('/quotations/{quotation_template}/download', [App\Http\Controllers\QuotationTemplateController::class, 'download'])->name('quotation-templates.download');
        Route::post('/quotations/{quotation_template}/to-estimate', [App\Http\Controllers\QuotationTemplateController::class, 'toEstimate'])->name('quotation-templates.to-estimate');
    });

    // Site Expenses - create/store accessible to technician too
    Route::middleware(['role:company_admin,manager,accountant,technician'])->group(function () {
        Route::get('/expenses/record', [App\Http\Controllers\ExpenseRecordController::class, 'choose'])->name('expenses.record');
        Route::get('/site-expenses/create', [App\Http\Controllers\SiteExpenseController::class, 'create'])->name('site-expenses.create');
        Route::post('/site-expenses', [App\Http\Controllers\SiteExpenseController::class, 'store'])->name('site-expenses.store');
        Route::get('/regular-expenses/create', [App\Http\Controllers\RegularExpenseController::class, 'create'])->name('regular-expenses.create');
        Route::post('/regular-expenses', [App\Http\Controllers\RegularExpenseController::class, 'store'])->name('regular-expenses.store');
    });

    // Site Expenses - index/edit/delete only for admin/manager/accountant
    Route::middleware(['role:company_admin,manager,accountant'])->group(function () {
        Route::get('/site-expenses', [App\Http\Controllers\SiteExpenseController::class, 'index'])->name('site-expenses.index');
        Route::get('/site-expenses/{site_expense}/edit', [App\Http\Controllers\SiteExpenseController::class, 'edit'])->name('site-expenses.edit');
        Route::put('/site-expenses/{site_expense}', [App\Http\Controllers\SiteExpenseController::class, 'update'])->name('site-expenses.update');
        Route::delete('/site-expenses/{site_expense}', [App\Http\Controllers\SiteExpenseController::class, 'destroy'])->name('site-expenses.destroy');
    });

    // Regular Expenses - index/edit/delete for admin/manager/accountant/technician
    Route::middleware(['role:company_admin,manager,accountant,technician'])->group(function () {
        Route::get('/regular-expenses', [App\Http\Controllers\RegularExpenseController::class, 'index'])->name('regular-expenses.index');
        Route::get('/regular-expenses/{regular_expense}/edit', [App\Http\Controllers\RegularExpenseController::class, 'edit'])->name('regular-expenses.edit');
        Route::put('/regular-expenses/{regular_expense}', [App\Http\Controllers\RegularExpenseController::class, 'update'])->name('regular-expenses.update');
        Route::delete('/regular-expenses/{regular_expense}', [App\Http\Controllers\RegularExpenseController::class, 'destroy'])->name('regular-expenses.destroy');
    });

    // Expense Categories (admin/manager/accountant)
    Route::middleware(['role:company_admin,manager,accountant'])->group(function () {
        Route::get('/expense-categories', [App\Http\Controllers\ExpenseCategoryController::class, 'index'])->name('expense-categories.index');
        Route::post('/expense-categories', [App\Http\Controllers\ExpenseCategoryController::class, 'store'])->name('expense-categories.store');
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

    // Estimates (admin, manager, accountant, technician)
    Route::middleware(['role:company_admin,manager,accountant,technician'])->group(function () {
        Route::resource('estimates', App\Http\Controllers\EstimateController::class);
        Route::post('/estimates/{estimate}/convert', [App\Http\Controllers\EstimateController::class, 'convertToInvoice'])->name('estimates.convert');
        Route::post('/estimates/{estimate}/purchase-order', [App\Http\Controllers\EstimateController::class, 'createPurchaseOrder'])->name('estimates.purchase-order');
        Route::get('/estimates/{estimate}/pdf', [App\Http\Controllers\EstimateController::class, 'pdf'])->name('estimates.pdf');
        Route::get('/estimates/{estimate}/download', [App\Http\Controllers\EstimateController::class, 'download'])->name('estimates.download');
    });

    // Users Management (admin only)
    Route::middleware(['role:company_admin,manager'])->group(function () {
        // Redirect wrong "Add User" / any non-numeric slug to create form — before resource so these win
        Route::get('/users/Add User', fn () => redirect('/users/create', 302))->name('users.add-user-redirect');
        Route::get('/users/Add%20User', fn () => redirect('/users/create', 302));
        Route::get('/users/Add+User', fn () => redirect('/users/create', 302));
        // Catch any other /users/{slug} that is not numeric and not 'create' -> redirect to create
        Route::get('/users/{slug}', fn () => redirect('/users/create', 302))
            ->where('slug', '^(?!create$)(?![0-9]+$).+$')
            ->name('users.create-redirect-fallback');
        // Only numeric IDs for {user} so "Add User" etc. never match edit/update/destroy
        Route::resource('users', App\Http\Controllers\UserController::class)->whereNumber('user')->except(['show']);
    });

    // Company Settings
    Route::middleware(['role:company_admin,manager'])->group(function () {
        Route::get('/company/settings', [App\Http\Controllers\CompanyController::class, 'settings'])->name('company.settings');
        Route::put('/company/settings', [App\Http\Controllers\CompanyController::class, 'updateSettings'])->name('company.settings.update');
    });

    // API-like routes for AJAX
    Route::get('/api/products/{product}/stock', [App\Http\Controllers\ProductController::class, 'getStock'])->name('api.product.stock');
    Route::get('/api/customers/{customer}/sites', [App\Http\Controllers\SiteController::class, 'getForCustomer'])->name('api.customer.sites');
    Route::post('/api/fcm-token', [App\Http\Controllers\Api\FcmTokenController::class, 'store'])->name('api.fcm-token.store');

    // FCM test page (admin/manager only)
    Route::middleware(['role:company_admin,manager'])->group(function () {
        Route::get('/fcm-test', [App\Http\Controllers\FcmTestController::class, 'index'])->name('fcm-test.index');
        Route::post('/fcm-test', [App\Http\Controllers\FcmTestController::class, 'send'])->name('fcm-test.send');
    });

    // Live Stream (technician, admin, manager)
    Route::middleware(['role:company_admin,manager,technician'])->group(function () {
        Route::resource('livestream', App\Http\Controllers\LiveStreamController::class)->only(['index', 'create', 'store', 'show']);
        Route::post('/livestream/{livestream}/stop', [App\Http\Controllers\LiveStreamController::class, 'stop'])->name('livestream.stop');
        Route::post('/livestream/{livestream}/signal', [App\Http\Controllers\LiveStreamController::class, 'storeSignal'])->name('livestream.signal');
        Route::get('/livestream/{livestream}/signals', [App\Http\Controllers\LiveStreamController::class, 'getSignals'])->name('livestream.signals');
        Route::get('/troubleshoot/connect', [App\Http\Controllers\TroubleshootController::class, 'connectForm'])->name('troubleshoot.connect');
        Route::post('/troubleshoot/verify', [App\Http\Controllers\TroubleshootController::class, 'verifyAndWatch'])->name('troubleshoot.verify');
        Route::get('/troubleshoot/watch/{code}', [App\Http\Controllers\TroubleshootController::class, 'technicianWatch'])->name('troubleshoot.watch');
        Route::post('/troubleshoot/{code}/signal', [App\Http\Controllers\TroubleshootController::class, 'technicianStoreSignal'])->name('troubleshoot.tech.signal');
        Route::get('/troubleshoot/{code}/signals', [App\Http\Controllers\TroubleshootController::class, 'technicianGetSignals'])->name('troubleshoot.tech.signals');
    });
});

// Live Stream public viewer (no auth required)
Route::get('/live/{token}', [App\Http\Controllers\LiveStreamController::class, 'viewer'])->name('livestream.viewer');
Route::post('/live/{token}/verify', [App\Http\Controllers\LiveStreamController::class, 'verifyPassword'])->name('livestream.verify');
Route::get('/live/{token}/watch', [App\Http\Controllers\LiveStreamController::class, 'watch'])->name('livestream.watch');
Route::post('/live/{token}/signal', [App\Http\Controllers\LiveStreamController::class, 'viewerSignal'])->name('livestream.viewer.signal');
Route::get('/live/{token}/signals', [App\Http\Controllers\LiveStreamController::class, 'viewerGetSignals'])->name('livestream.viewer.signals');
Route::get('/live/{token}/status', [App\Http\Controllers\LiveStreamController::class, 'status'])->name('livestream.status');

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
    Route::get('/troubleshoot', [App\Http\Controllers\TroubleshootController::class, 'customerPage'])->name('troubleshoot');
    Route::post('/troubleshoot/start', [App\Http\Controllers\TroubleshootController::class, 'start'])->name('troubleshoot.start');
    Route::post('/troubleshoot/end', [App\Http\Controllers\TroubleshootController::class, 'end'])->name('troubleshoot.end');
    Route::post('/troubleshoot/{troubleshoot}/signal', [App\Http\Controllers\TroubleshootController::class, 'customerStoreSignal'])->name('troubleshoot.signal');
    Route::get('/troubleshoot/{troubleshoot}/signals', [App\Http\Controllers\TroubleshootController::class, 'customerGetSignals'])->name('troubleshoot.signals');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
