<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BotSettingsController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\AdminPostController;
use App\Http\Controllers\Admin\CompetitionController;
use App\Http\Controllers\Admin\CoachController;
use App\Http\Controllers\Admin\CourierController;
use App\Http\Controllers\Admin\ShopSettingsController;
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

if (app()->environment('local')) {
    Route::prefix('_debug')->name('debug.')->group(function () {
        Route::get('/session', function (Request $request) {
            $sessionData = $request->session()->all();
            ksort($sessionData);

            return view('debug.session-inspector', [
                'sessionData' => $sessionData,
                'sessionId' => $request->session()->getId(),
                'sessionDriver' => config('session.driver'),
            ]);
        })->name('session.index');

        Route::post('/session/set', function (Request $request) {
            $validated = $request->validate([
                'key' => ['required', 'string', 'max:255'],
                'value' => ['nullable', 'string'],
            ]);

            $rawValue = $validated['value'] ?? '';
            $decoded = json_decode($rawValue, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : $rawValue;

            $request->session()->put($validated['key'], $value);

            return redirect()
                ->route('debug.session.index')
                ->with('status', "Session key '{$validated['key']}' updated.");
        })->name('session.set');

        Route::post('/session/forget', function (Request $request) {
            $validated = $request->validate([
                'key' => ['required', 'string', 'max:255'],
            ]);

            $request->session()->forget($validated['key']);

            return redirect()
                ->route('debug.session.index')
                ->with('status', "Session key '{$validated['key']}' removed.");
        })->name('session.forget');

        Route::post('/session/flush', function (Request $request) {
            $request->session()->flush();

            return redirect()
                ->route('debug.session.index')
                ->with('status', 'Session flushed.');
        })->name('session.flush');
    });
}

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
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');
    Route::get('/orders', [DashboardController::class, 'orders'])->name('orders');
    Route::get('/conversations', [DashboardController::class, 'conversations'])->name('conversations');
    Route::get('/customers', [DashboardController::class, 'customers'])->name('customers');
    Route::get('/products', [DashboardController::class, 'products'])->name('products');

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

    // Shop Settings 
    Route::get('/shop-settings', [ShopSettingsController::class, 'index'])->name('shop-settings');
    Route::get('/shop-settings/domain', [ShopSettingsController::class, 'domain'])->name('shop-settings.domain');
    Route::get('/shop-settings/theme', [ShopSettingsController::class, 'theme'])->name('shop-settings.theme');
    Route::get('/shop-settings/category', [ShopSettingsController::class, 'category'])->name('shop-settings.category');
    Route::get('/shop-settings/offers', [ShopSettingsController::class, 'offers'])->name('shop-settings.offers');
    Route::get('/shop-settings/content', [ShopSettingsController::class, 'content'])->name('shop-settings.content');
});
