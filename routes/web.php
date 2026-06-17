<?php

use App\Http\Controllers\Admin\CloudflareAccountController;
use App\Http\Controllers\Admin\DomainController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Admin\CargoReconciliationController;
use App\Http\Controllers\Admin\CargoSettingController;
use App\Http\Controllers\Admin\CdnProviderController;
use App\Http\Controllers\Admin\SystemAlertController;
use App\Http\Controllers\Admin\BrandController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FunnelController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FunnelController::class, 'show'])->name('funnel.landing');
Route::post('/order/submit', [FunnelController::class, 'submit'])->name('funnel.order.submit');
Route::post('/payment/callback', [FunnelController::class, 'paymentCallback'])->name('payment.callback');
Route::get('/order/success/{order}', [FunnelController::class, 'success'])->name('funnel.success');
Route::post('/order/{order}/upsell/accept', [FunnelController::class, 'acceptUpsell'])->name('funnel.upsell.accept');
Route::post('/order/{order}/upsell/reject', [FunnelController::class, 'rejectUpsell'])->name('funnel.upsell.reject');

Route::get('/preview-dashboard', function () {
    return view('admin.dashboard');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', function () {
        return redirect()->route('admin.dashboard');
    })->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

Route::post('/contact-submit', [\App\Http\Controllers\LandingContactController::class, 'submit'])->name('contact.submit');

Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function (): void {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/orders', [OrderController::class, 'index'])->name('orders');
    Route::get('/orders/export', [OrderController::class, 'export'])->name('orders.export');
    Route::get('/orders/bulk-print', [OrderController::class, 'bulkPrint'])->name('orders.bulk-print');
    Route::get('/orders/{order}/barcode', [OrderController::class, 'printBarcode'])->name('orders.barcode');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('/orders/{order}/api-approve', [OrderController::class, 'approveApiOrder'])->name('orders.api-approve');
    Route::post('/orders/{order}/update-address', [OrderController::class, 'updateAddress'])->name('orders.update-address');
    Route::post('/orders/{order}/upsell/add', [OrderController::class, 'addUpsell'])->name('orders.upsell.add');
    Route::post('/orders/{order}/send-cargo', [OrderController::class, 'sendToCargo'])->name('orders.send-cargo');
    Route::post('/orders/{order}/cancel-cargo', [OrderController::class, 'cancelCargo'])->name('orders.cancel-cargo');
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');

    // Cargo Settings & Reconciliation
    Route::get('/cargo-settings', [CargoSettingController::class, 'index'])->name('cargo-settings.index');
    Route::put('/cargo-settings/{cargoSetting}', [CargoSettingController::class, 'update'])->name('cargo-settings.update');
    Route::get('/cargo-reconciliation', [CargoReconciliationController::class, 'index'])->name('cargo.reconciliation');
    Route::post('/cargo-reconciliation/{order}/paid', [CargoReconciliationController::class, 'markAsPaid'])->name('cargo.reconciliation.paid');
    Route::post('/cargo-reconciliation/{order}/unpaid', [CargoReconciliationController::class, 'unmarkAsPaid'])->name('cargo.reconciliation.unpaid');

    // Domain CRUD
    Route::get('/domains', [DomainController::class, 'index'])->name('domains');
    Route::get('/domains/create', [DomainController::class, 'create'])->name('domains.create');
    Route::get('/cloudflare/zones', [DomainController::class, 'cloudflareZones'])->name('cloudflare.zones');
    Route::post('/domains', [DomainController::class, 'store'])->name('domains.store');
    Route::post('/domains/{domain}/clone', [DomainController::class, 'clone'])->name('domains.clone');
    Route::post('/domains/{domain}/reset-stats', [DomainController::class, 'resetStats'])->name('domains.reset-stats');
    Route::post('/domains/{domain}/reset-infrastructure', [DomainController::class, 'resetInfrastructure'])->name('domains.reset-infrastructure');
    Route::get('/domains/{domain}/edit', [DomainController::class, 'edit'])->name('domains.edit');
    Route::put('/domains/{domain}', [DomainController::class, 'update'])->name('domains.update');
    Route::delete('/domains/{domain}', [DomainController::class, 'destroy'])->name('domains.destroy');
    Route::post('/domains/{domain}/cloudflare-onboarding', [DomainController::class, 'onboardingCloudflare'])->name('domains.cloudflare.onboarding');
    Route::post('/domains/cloudflare-onboarding-draft', [DomainController::class, 'onboardingCloudflareDraft'])->name('domains.cloudflare.onboarding-draft');
    Route::get('/domains/{domain}/cloudflare-status', [DomainController::class, 'checkCloudflareStatus'])->name('domains.cloudflare.status');
    Route::post('/domains/{domain}/cloudflare-finalize', [DomainController::class, 'finalizeCloudflare'])->name('domains.cloudflare');
    Route::post('/domains/{domain}/cloudflare-finalize-wizard', [DomainController::class, 'finalizeCloudflare'])->name('domains.cloudflare.finalize');
    Route::post('/domains/bunny/provision-draft', [DomainController::class, 'bunnyProvisionDraft'])->name('domains.bunny.provision-draft');
    Route::post('/domains/{domain}/bunny/provision', [DomainController::class, 'bunnyProvision'])->name('domains.bunny.provision');
    Route::get('/domains/{domain}/bunny/status', [DomainController::class, 'bunnyStatus'])->name('domains.bunny.status');
    Route::delete('/domains/{domain}', [DomainController::class, 'destroy'])->name('domains.destroy');

    // Domain Expenses
    Route::post('/domains/{domain}/expenses', [DomainController::class, 'storeExpense'])->name('domains.expenses.store');
    Route::delete('/expenses/{expense}', [DomainController::class, 'destroyExpense'])->name('domains.expenses.destroy');

    Route::get('/cloudflare-accounts', [CloudflareAccountController::class, 'index'])->name('cloudflare-accounts.index');
    Route::get('/cloudflare-accounts/create', [CloudflareAccountController::class, 'create'])->name('cloudflare-accounts.create');
    Route::post('/cloudflare-accounts', [CloudflareAccountController::class, 'store'])->name('cloudflare-accounts.store');
    Route::get('/cloudflare-accounts/{cloudflareAccount}/edit', [CloudflareAccountController::class, 'edit'])->name('cloudflare-accounts.edit');
    Route::put('/cloudflare-accounts/{cloudflareAccount}', [CloudflareAccountController::class, 'update'])->name('cloudflare-accounts.update');
    Route::delete('/cloudflare-accounts/{cloudflareAccount}', [CloudflareAccountController::class, 'destroy'])->name('cloudflare-accounts.destroy');

    // Catalog CRUD
    Route::get('/catalog', [ProductController::class, 'index'])->name('catalog');
    Route::get('/catalog/create', [ProductController::class, 'create'])->name('catalog.create');
    Route::post('/catalog', [ProductController::class, 'store'])->name('catalog.store');
    Route::get('/catalog/{catalog}/edit', [ProductController::class, 'edit'])->name('catalog.edit');
    Route::put('/catalog/{catalog}', [ProductController::class, 'update'])->name('catalog.update');
    Route::delete('/catalog/{catalog}', [ProductController::class, 'destroy'])->name('catalog.destroy');

    Route::get('/risk', [OrderController::class, 'risk'])->name('risk');

    // User Management System
    Route::get('/users', [UserController::class, 'index'])->name('users');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::post('/favorites/toggle', [UserController::class, 'toggleFavorite'])->name('favorites.toggle');

    // Kara Liste (Müşteri Engelleme)
    Route::post('/customers/{customer}/toggle-blacklist', [OrderController::class, 'toggleBlacklist'])->name('customers.toggle-blacklist');
    Route::post('/orders/{order}/add-note', [OrderController::class, 'addNote'])->name('orders.add-note');

    // Role Management
    Route::get('/roles', [RoleController::class, 'index'])->name('roles');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingsController::class, 'store'])->name('settings.store');
    Route::post('/settings/migrate', [SettingsController::class, 'runMigration'])->name('settings.migrate');
    Route::post('/settings/clear-cache', [SettingsController::class, 'clearCache'])->name('settings.clear-cache');
    Route::get('/settings/messages', [\App\Http\Controllers\Admin\MessageSettingController::class, 'index'])->name('settings.messages');
    Route::post('/settings/messages', [\App\Http\Controllers\Admin\MessageSettingController::class, 'update'])->name('settings.messages.update');
    Route::post('/orders/{order}/whatsapp-log', [OrderController::class, 'logWhatsAppMessage'])->name('orders.whatsapp-log');

    Route::get('/cdn-providers', [CdnProviderController::class, 'index'])->name('cdn-providers.index');
    Route::get('/cdn-providers/create', [CdnProviderController::class, 'create'])->name('cdn-providers.create');
    Route::post('/cdn-providers', [CdnProviderController::class, 'store'])->name('cdn-providers.store');
    Route::get('/cdn-providers/{cdnProvider}/edit', [CdnProviderController::class, 'edit'])->name('cdn-providers.edit');
    Route::put('/cdn-providers/{cdnProvider}', [CdnProviderController::class, 'update'])->name('cdn-providers.update');
    Route::delete('/cdn-providers/{cdnProvider}', [CdnProviderController::class, 'destroy'])->name('cdn-providers.destroy');
    Route::post('/cdn-providers/{cdnProvider}/activate', [CdnProviderController::class, 'activate'])->name('cdn-providers.activate');

    // Brand CRUD
    Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
    Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
    Route::put('/brands/{brand}', [BrandController::class, 'update'])->name('brands.update');
    Route::delete('/brands/{brand}', [BrandController::class, 'destroy'])->name('brands.destroy');

    // Payment Provider CRUD
    Route::get('/payment-providers', [\App\Http\Controllers\Admin\PaymentProviderController::class, 'index'])->name('payment-providers.index');
    Route::get('/payment-providers/create', [\App\Http\Controllers\Admin\PaymentProviderController::class, 'create'])->name('payment-providers.create');
    Route::post('/payment-providers', [\App\Http\Controllers\Admin\PaymentProviderController::class, 'store'])->name('payment-providers.store');
    Route::get('/payment-providers/{paymentProvider}/edit', [\App\Http\Controllers\Admin\PaymentProviderController::class, 'edit'])->name('payment-providers.edit');
    Route::put('/payment-providers/{paymentProvider}', [\App\Http\Controllers\Admin\PaymentProviderController::class, 'update'])->name('payment-providers.update');
    Route::delete('/payment-providers/{paymentProvider}', [\App\Http\Controllers\Admin\PaymentProviderController::class, 'destroy'])->name('payment-providers.destroy');

    // System Alerts
    Route::get('/alerts', [SystemAlertController::class, 'index'])->name('alerts.index');
    Route::get('/alerts/latest', [SystemAlertController::class, 'getLatest'])->name('alerts.latest');
    Route::post('/alerts/{alert}/mark-as-read', [SystemAlertController::class, 'markAsRead'])->name('alerts.mark-as-read');
    Route::post('/alerts/mark-all-read', [SystemAlertController::class, 'markAllAsRead'])->name('alerts.mark-all-read');
    Route::get('/users/{user}/alerts', [SystemAlertController::class, 'userStatus'])->name('users.alerts');

    // Affiliate Management
    Route::get('/affiliate/stats', [\App\Http\Controllers\Admin\AffiliateController::class, 'stats'])->name('affiliate.stats');
    Route::get('/affiliate/users', [\App\Http\Controllers\Admin\AffiliateController::class, 'users'])->name('affiliate.users');
    Route::post('/affiliate/users/{user}/status', [\App\Http\Controllers\Admin\AffiliateController::class, 'updateUserStatus'])->name('affiliate.users.status');
    Route::post('/affiliate/users/{user}/impersonate', [\App\Http\Controllers\Admin\AffiliateController::class, 'impersonate'])->name('affiliate.users.impersonate');
    Route::get('/affiliate/commissions', [\App\Http\Controllers\Admin\AffiliateController::class, 'commissions'])->name('affiliate.commissions');
    Route::get('/affiliate/commissions/export', [\App\Http\Controllers\Admin\AffiliateController::class, 'exportCommissions'])->name('affiliate.commissions.export');
    Route::post('/affiliate/commissions/{commission}/status', [\App\Http\Controllers\Admin\AffiliateController::class, 'updateCommissionStatus'])->name('affiliate.commissions.status');
    Route::get('/affiliate/withdrawals', [\App\Http\Controllers\Admin\AffiliateController::class, 'withdrawals'])->name('affiliate.withdrawals');
    Route::post('/affiliate/withdrawals/{withdrawal}/status', [\App\Http\Controllers\Admin\AffiliateController::class, 'updateWithdrawalStatus'])->name('affiliate.withdrawals.status');
    Route::get('/affiliate/settings', [\App\Http\Controllers\Admin\AffiliateController::class, 'settings'])->name('affiliate.settings');
    Route::post('/affiliate/settings', [\App\Http\Controllers\Admin\AffiliateController::class, 'storeSettings'])->name('affiliate.settings.store');
    Route::get('/affiliate/media', [\App\Http\Controllers\Admin\AffiliateController::class, 'media'])->name('affiliate.media.index');
    Route::post('/affiliate/media', [\App\Http\Controllers\Admin\AffiliateController::class, 'storeMedia'])->name('affiliate.media.store');
    Route::delete('/affiliate/media/{media}', [\App\Http\Controllers\Admin\AffiliateController::class, 'destroyMedia'])->name('affiliate.media.destroy');

});

// ----------------------------------------------------
// Affiliate Portal Routes
// ----------------------------------------------------
Route::get('/t/{short_code}', [App\Http\Controllers\AffiliateController::class, 'trackLink'])->name('affiliate.track');

Route::prefix('affiliate')->name('affiliate.')->group(function () {
    Route::get('/', [App\Http\Controllers\AffiliateController::class, 'welcome'])->name('welcome');

    Route::middleware('guest:affiliate')->group(function () {
        Route::get('/login', [App\Http\Controllers\AffiliateController::class, 'showLogin'])->name('login');
        Route::post('/login', [App\Http\Controllers\AffiliateController::class, 'login'])->name('login.attempt');
        Route::get('/register', [App\Http\Controllers\AffiliateController::class, 'showRegister'])->name('register');
        Route::post('/register', [App\Http\Controllers\AffiliateController::class, 'register'])->name('register.attempt');
    });

    Route::middleware('affiliate.auth')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\AffiliatePortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/campaigns', [App\Http\Controllers\AffiliatePortalController::class, 'campaigns'])->name('campaigns');
        Route::post('/links/generate', [App\Http\Controllers\AffiliatePortalController::class, 'generateLink'])->name('links.generate');
        Route::get('/links', [App\Http\Controllers\AffiliatePortalController::class, 'links'])->name('links');
        Route::delete('/links/{id}', [App\Http\Controllers\AffiliatePortalController::class, 'deleteLink'])->name('links.delete');
        Route::get('/media', [App\Http\Controllers\AffiliatePortalController::class, 'media'])->name('media');
        Route::get('/stats', [App\Http\Controllers\AffiliatePortalController::class, 'stats'])->name('stats');
        Route::get('/withdrawals', [App\Http\Controllers\AffiliatePortalController::class, 'withdrawals'])->name('withdrawals');
        Route::post('/withdrawals/request', [App\Http\Controllers\AffiliatePortalController::class, 'requestWithdrawal'])->name('withdrawals.request');
        Route::get('/settings', [App\Http\Controllers\AffiliatePortalController::class, 'settings'])->name('settings');
        Route::post('/settings', [App\Http\Controllers\AffiliatePortalController::class, 'updateSettings'])->name('settings.update');
        Route::post('/logout', [App\Http\Controllers\AffiliateController::class, 'logout'])->name('logout');
    });
});

