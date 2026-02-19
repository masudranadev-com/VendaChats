<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\User\AccountController;
use App\Http\Controllers\User\DataDeletionController;
use App\Http\Controllers\User\FacebookAuthController;
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
Route::get('/user-data-deletion', [DataDeletionController::class, 'index'])->name('data-deletion.index');
Route::post('/facebook/data-deletion', [DataDeletionController::class, 'callback'])->name('data-deletion.callback');
Route::get('/facebook/data-deletion/status/{code}', [DataDeletionController::class, 'status'])->name('data-deletion.status');

// webhook
Route::any('/webhook', [HomeController::class, 'webhook'])->name('home.webhook');
Route::prefix('facebook')->name('facebook.')->group(function () {
    Route::get('/oauth', [FacebookAuthController::class, 'oauthPage'])->name('oauth');
    Route::get('/auth/redirect', [FacebookAuthController::class, 'redirectToFacebook'])->name('auth.redirect');
    Route::get('/auth/callback', [FacebookAuthController::class, 'handleFacebookCallback'])->name('auth.callback');
    Route::get('/dashboard', [FacebookAuthController::class, 'dashboard'])->name('dashboard');
    Route::get('/posts', [FacebookAuthController::class, 'getPagePosts'])->name('posts');
    Route::get('/post-comments', [FacebookAuthController::class, 'getPostComments'])->name('post-comments');
    Route::post('/send-message', [FacebookAuthController::class, 'sendMessage'])->name('send-message');
    Route::post('/reply-comment', [FacebookAuthController::class, 'replyToComment'])->name('reply-comment');
    Route::post('/subscribe-webhook', [FacebookAuthController::class, 'subscribeWebhook'])->name('subscribe-webhook');
    Route::post('/disconnect', [FacebookAuthController::class, 'disconnect'])->name('disconnect');
    Route::get('/webhook', [FacebookAuthController::class, 'verifyWebhook'])->name('webhook.verify');
    Route::post('/webhook', [FacebookAuthController::class, 'receiveWebhook'])->name('webhook.receive');
});

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
