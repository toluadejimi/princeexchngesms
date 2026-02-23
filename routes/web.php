<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\PricingController;
use App\Http\Controllers\Admin\ServerController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TransactionController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\NotificationAdminController;
use App\Http\Controllers\Admin\WalletAdminController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\FundWalletController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RentalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/fund-wallet', [FundWalletController::class, 'index'])->name('fund-wallet.index');
    Route::post('/generate-account', [FundWalletController::class, 'generateAccount'])->name('fund-wallet.generate');
    Route::post('/fund-now', [FundWalletController::class, 'fundNow'])->name('fund-wallet.fund-now')->middleware('throttle:30,1');
    Route::post('/fund-manual-submit', [FundWalletController::class, 'fundManualSubmit'])->name('fund-wallet.manual-submit')->middleware('throttle:15,1');
});
Route::post('/webhook/sprintpay', [FundWalletController::class, 'webhook'])->name('webhook.sprintpay')->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/rent', [RentalController::class, 'create'])->name('rentals.create');
    Route::get('/rent/usa', [RentalController::class, 'createUsa'])->name('rentals.create.usa');
    Route::get('/rent/countries', [RentalController::class, 'createCountries'])->name('rentals.create.countries');
    Route::post('/rent', [RentalController::class, 'store'])->name('rentals.store')->middleware('throttle:30,1');
    Route::post('/rentals/{id}/cancel', [RentalController::class, 'cancel'])->name('rentals.cancel');
    Route::post('/rentals/{id}/expire', [RentalController::class, 'expireIfOverdue'])->name('rentals.expire');
    Route::get('/rentals/{id}/status', [RentalController::class, 'status'])->name('rentals.status');
    Route::post('/rentals/{id}/resend', [RentalController::class, 'resend'])->name('rentals.resend');
    Route::post('/rentals/{id}/activate', [RentalController::class, 'activate'])->name('rentals.activate');
    Route::post('/rentals/{id}/reactivate', [RentalController::class, 'reactivate'])->name('rentals.reactivate');
    Route::get('/api/services', [RentalController::class, 'services'])->name('rentals.services')->middleware('throttle:120,1');
    Route::get('/api/countries', [RentalController::class, 'countries'])->name('rentals.countries')->middleware('throttle:120,1');
    Route::get('/api/price', [RentalController::class, 'price'])->name('rentals.price')->middleware('throttle:120,1');
    Route::get('/api/pools', [RentalController::class, 'pools'])->name('rentals.pools')->middleware('throttle:120,1');
    Route::get('/api/notifications', [NotificationController::class, 'index'])->name('api.notifications.index');
    Route::post('/api/notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');

    Route::get('/support', [\App\Http\Controllers\SupportController::class, 'index'])->name('support.index');
    Route::get('/support/create', [\App\Http\Controllers\SupportController::class, 'create'])->name('support.create');
    Route::post('/support', [\App\Http\Controllers\SupportController::class, 'store'])->name('support.store');
    Route::get('/support/{id}', [\App\Http\Controllers\SupportController::class, 'show'])->name('support.show');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('users/{user}', [UserManagementController::class, 'show'])->name('users.show');
    Route::post('users/{user}/block', [UserManagementController::class, 'block'])->name('users.block');
    Route::post('users/{user}/unblock', [UserManagementController::class, 'unblock'])->name('users.unblock');
    Route::post('users/fund', [UserManagementController::class, 'fund'])->name('users.fund');
    Route::delete('users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    Route::get('users/{user}/receipt/{fundRequest}', [UserManagementController::class, 'receipt'])->name('users.receipt');
    Route::post('users/{user}/login-as', [UserManagementController::class, 'loginAs'])->name('users.login-as');
    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('verifications', [\App\Http\Controllers\Admin\VerificationLogController::class, 'index'])->name('verifications.index');
    Route::get('servers', [ServerController::class, 'index'])->name('servers.index');
    Route::get('servers/{server}/edit', [ServerController::class, 'edit'])->name('servers.edit');
    Route::put('servers/{server}', [ServerController::class, 'update'])->name('servers.update');
    Route::post('servers/{server}/toggle', [ServerController::class, 'toggle'])->name('servers.toggle');
    Route::get('pricing', [PricingController::class, 'index'])->name('pricing.index');
    Route::post('pricing', [PricingController::class, 'store'])->name('pricing.store');
    Route::delete('pricing/{pricing}', [PricingController::class, 'destroy'])->name('pricing.destroy');
    Route::post('wallet/adjust', [WalletAdminController::class, 'adjust'])->name('wallet.adjust');
    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingsController::class, 'store'])->name('settings.store');
    Route::get('notifications', [NotificationAdminController::class, 'index'])->name('notifications.index');
    Route::post('notifications', [NotificationAdminController::class, 'store'])->name('notifications.store');
    Route::get('support', [\App\Http\Controllers\Admin\SupportTicketController::class, 'index'])->name('support.index');
    Route::get('support/{ticket}', [\App\Http\Controllers\Admin\SupportTicketController::class, 'show'])->name('support.show');
    Route::post('support/{ticket}/reply', [\App\Http\Controllers\Admin\SupportTicketController::class, 'reply'])->name('support.reply');
    Route::post('support/{ticket}/close', [\App\Http\Controllers\Admin\SupportTicketController::class, 'close'])->name('support.close');
    Route::get('logs', [\Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index'])->name('logs');
});

require __DIR__.'/auth.php';
