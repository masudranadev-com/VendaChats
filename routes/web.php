<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BotSettingsController;
use App\Http\Controllers\Admin\AdminProductsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\AdminPostController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminPackageController;
use App\Http\Controllers\Admin\CompetitionController;
use App\Http\Controllers\Admin\CoachController;
use App\Http\Controllers\Admin\CourierController;
use App\Http\Controllers\Admin\OrderCallController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\AdminWebsiteContentController;
use App\Http\Controllers\Admin\ShopSettingsController;
use App\Http\Controllers\Admin\AdminOnboardingController;
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
// Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::get('/features', [InfoController::class, 'features'])->name('features.index');
Route::get('/pricing', [PricingController::class, 'index'])->name('pricing.index');
Route::get('/how-it-works', [InfoController::class, 'howItWorks'])->name('how-it-works.index');
Route::get('/about', [InfoController::class, 'about'])->name('about.index');
Route::get('/contact', [InfoController::class, 'contact'])->name('contact.index');
Route::get('/login', [AccountController::class, 'index'])->name('login.index');
Route::post('/login', [AccountController::class, 'login'])->name('login.submit');
Route::get('/logout', [AccountController::class, 'logout'])->name('logout');
Route::get('/signup', [AccountController::class, 'signupIndex'])->name('signup.index');
Route::post('/signup', [AccountController::class, 'signup'])->name('signup.submit');
Route::get('/privacy-policy', [InfoController::class, 'privacy'])->name('privacy.index');
Route::get('/terms-and-conditions', [InfoController::class, 'terms'])->name('terms.index');
Route::get('/user-data-deletion', [DataDeletionController::class, 'index'])->name('data-deletion.index');
Route::post('/facebook/data-deletion', [DataDeletionController::class, 'callback'])->name('data-deletion.callback');
Route::get('/facebook/data-deletion/status/{code}', [DataDeletionController::class, 'status'])->name('data-deletion.status');

// webhook
// Route::any('/webhook', [HomeController::class, 'webhook'])->name('home.webhook');
// Route::prefix('facebook')->name('facebook.')->group(function () {
//     Route::get('/oauth', [FacebookAuthController::class, 'oauthPage'])->name('oauth');
//     Route::get('/auth/redirect', [FacebookAuthController::class, 'redirectToFacebook'])->name('auth.redirect');
//     Route::get('/auth/callback', [FacebookAuthController::class, 'handleFacebookCallback'])->name('auth.callback');
//     Route::get('/dashboard', [FacebookAuthController::class, 'dashboard'])->name('dashboard');
//     Route::get('/posts', [FacebookAuthController::class, 'getPagePosts'])->name('posts');
//     Route::get('/post-comments', [FacebookAuthController::class, 'getPostComments'])->name('post-comments');
//     Route::post('/send-message', [FacebookAuthController::class, 'sendMessage'])->name('send-message');
//     Route::post('/reply-comment', [FacebookAuthController::class, 'replyToComment'])->name('reply-comment');
//     Route::post('/subscribe-webhook', [FacebookAuthController::class, 'subscribeWebhook'])->name('subscribe-webhook');
//     Route::post('/disconnect', [FacebookAuthController::class, 'disconnect'])->name('disconnect');
//     Route::get('/webhook', [FacebookAuthController::class, 'verifyWebhook'])->name('webhook.verify');
//     Route::post('/webhook', [FacebookAuthController::class, 'receiveWebhook'])->name('webhook.receive');
// });

// Admin php artisan make:controller Admin/AdminOrderController
Route::redirect('/admin', '/admin/dashboard');
Route::prefix('admin')->name('admin.')->middleware('admin.auth')->group(function () {
    Route::post('/logout', [AccountController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::get('/settings', [ProfileController::class, 'settings'])->name('settings');
    Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');
    Route::get('/conversations', [DashboardController::class, 'conversations'])->name('conversations');
    Route::get('/customers', [DashboardController::class, 'customers'])->name('customers');

    // products
    Route::get('/products', [AdminProductsController::class, 'products'])->name('products');
    Route::get('/products/create', [AdminProductsController::class, 'productCreate'])->name('products.create');
    Route::get('/products/{productId}/edit', [AdminProductsController::class, 'productEdit'])->name('products.edit');
    Route::get('/categories', [AdminProductsController::class, 'categories'])->name('categories');
    Route::get('/order-call', [OrderCallController::class, 'index'])->name('order-call');

    // orders
    Route::get('/orders', [AdminOrderController::class, 'orders'])->name('orders');
    Route::get('/orders/{orderId}', [AdminOrderController::class, 'view'])->name('orders.view');
    Route::post('/orders/{orderId}/discount', [AdminOrderController::class, 'applyDiscount'])->name('orders.discount.apply');
    Route::post('/orders/{orderId}/discount/remove', [AdminOrderController::class, 'removeDiscount'])->name('orders.discount.remove');
    Route::post('/orders/{orderId}/confirm', [AdminOrderController::class, 'confirm'])->name('orders.confirm');
    Route::get('/orders/{orderId}/invoice', [AdminOrderController::class, 'invoice'])->name('orders.invoice');

    // bot-settings
    Route::get('/bot-settings', [BotSettingsController::class, 'index'])->name('bot-settings');
    Route::get('/bot-settings/facebook/connect', [BotSettingsController::class, 'connectFacebook'])->name('bot-settings.facebook.connect');
    Route::get('/bot-settings/facebook/disconnect', [BotSettingsController::class, 'disconnectFacebook'])->name('bot-settings.facebook.disconnect');
    Route::get('/bot-settings/facebook/callback', [BotSettingsController::class, 'callbackFacebook'])->name('bot-settings.facebook.callback');
    Route::get('/bot-settings/facebook/info', [BotSettingsController::class, 'infoFacebook'])->name('bot-settings.facebook.info');

    // users
    Route::get('/users', [AdminUsersController::class, 'users'])->name('users');
    Route::get('/users/views', [AdminUsersController::class, 'usersViews'])->name('users.views');

    // posts
    Route::get('/posts', [AdminPostController::class, 'posts'])->name('posts');

    Route::get('/bargaining', [DashboardController::class, 'bargaining'])->name('bargaining');
    Route::get('/campaigns', [DashboardController::class, 'campaigns'])->name('campaigns');
    Route::get('/competition', [CompetitionController::class, 'index'])->name('competition');
    Route::post('/competition/competitors', [CompetitionController::class, 'store'])->name('competition.store');
    Route::post('/competition/{competitor}/sync', [CompetitionController::class, 'sync'])->name('competition.sync');
    Route::get('/competition/{competitor}/view', [CompetitionController::class, 'view'])->name('competition.view');
    Route::get('/coach', [CoachController::class, 'index'])->name('coach');
    Route::get('/courier', [CourierController::class, 'index'])->name('courier');

    // packages
    Route::get('/packages', [AdminPackageController::class, 'packages'])->name('packages');

    // Shop Settings
    Route::get('/shop-settings', [ShopSettingsController::class, 'index'])->name('shop-settings');
    Route::get('/shop-settings/domain', [ShopSettingsController::class, 'domain'])->name('shop-settings.domain');
    Route::get('/shop-settings/theme', [ShopSettingsController::class, 'theme'])->name('shop-settings.theme');
    Route::get('/shop-settings/offers', [ShopSettingsController::class, 'offers'])->name('shop-settings.offers');
    Route::get('/shop-settings/content', [AdminWebsiteContentController::class, 'slider'])->name('shop-settings.content');
    Route::get('/shop-settings/content/page-editor', [AdminWebsiteContentController::class, 'pageEditor'])->name('shop-settings.content.page-editor');
    Route::get('/shop-settings/content/contact', [AdminWebsiteContentController::class, 'contact'])->name('shop-settings.content.contact');
    Route::get('/shop-settings/content/footer', [AdminWebsiteContentController::class, 'footer'])->name('shop-settings.content.footer');
});

// onboarding
Route::prefix('admin')->name('admin.')->middleware('admin.without_onboarding')->group(function () {
    Route::get('/onboarding', [AdminOnboardingController::class, 'onboarding'])->name('onboarding');
    Route::post('/onboarding-continue', [AdminOnboardingController::class, 'onboardingContinue'])->name('onboardingContinue');
});
