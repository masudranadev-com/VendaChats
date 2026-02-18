<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\User\AccountController;
use App\Http\Controllers\User\HomeController;
use App\Http\Controllers\User\InfoController;
use App\Http\Controllers\User\PricingController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Users
Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::get('/features', [InfoController::class, 'features'])->name('features.index');
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing.index');
Route::get('/how-it-works', [InfoController::class, 'howItWorks'])->name('how-it-works.index');
Route::get('/about', [InfoController::class, 'about'])->name('about.index');
Route::get('/contact', [InfoController::class, 'contact'])->name('contact.index');
Route::get('/login', [AccountController::class, 'index'])->name('login.index');
Route::get('/privacy-policy', [InfoController::class, 'privacy'])->name('privacy.index');
Route::get('/terms-and-conditions', [InfoController::class, 'terms'])->name('terms.index');

// Admin
Route::redirect('/admin', '/admin/dashboard');
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [AdminDashboardController::class, 'analytics'])->name('analytics');
    Route::get('/orders', [AdminDashboardController::class, 'orders'])->name('orders');
    Route::get('/conversations', [AdminDashboardController::class, 'conversations'])->name('conversations');
    Route::get('/customers', [AdminDashboardController::class, 'customers'])->name('customers');
    Route::get('/products', [AdminDashboardController::class, 'products'])->name('products');
    Route::get('/bot-settings', [AdminDashboardController::class, 'botSettings'])->name('bot-settings');
    Route::get('/bargaining', [AdminDashboardController::class, 'bargaining'])->name('bargaining');
    Route::get('/whatsapp-recovery', [AdminDashboardController::class, 'whatsappRecovery'])->name('whatsapp-recovery');
    Route::get('/campaigns', [AdminDashboardController::class, 'campaigns'])->name('campaigns');
    Route::get('/competition', [AdminDashboardController::class, 'competition'])->name('competition');
    Route::get('/coach', [AdminDashboardController::class, 'coach'])->name('coach');
    Route::get('/courier', [AdminDashboardController::class, 'courier'])->name('courier');
    Route::get('/settings', [AdminDashboardController::class, 'settings'])->name('settings');
    Route::get('/billing', [AdminDashboardController::class, 'billing'])->name('billing');
});
