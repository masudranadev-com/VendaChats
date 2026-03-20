<?php

use App\Http\Controllers\EcommerceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| E-Commerce Routes
|--------------------------------------------------------------------------
*/

Route::name('ecommerce.')
    ->controller(EcommerceController::class)
    ->group(function (): void {
        Route::get('/', 'index')->name('index');
        Route::get('shop', 'shop')->name('shop');
        Route::prefix('shop')->group(function (): void {
            Route::get('login', 'login')->name('login');
            Route::get('signup', 'signup')->name('signup');
            Route::get('returns-warranty', 'returnsWarranty')->name('returns-warranty');
            Route::get('terms-and-conditions', 'termsConditions')->name('terms-conditions');
            Route::get('privacy-policy', 'privacyPolicy')->name('privacy-policy');
            Route::get('about-us', 'aboutUs')->name('about-us');
            Route::get('faq', 'faq')->name('faq');
            Route::get('gift-vouchers', 'giftVouchers')->name('gift-vouchers');
            Route::get('wishlist', 'wishlist')->name('wishlist');
            Route::get('track-your-order', 'trackOrder')->name('track-order');
            Route::get('order-history', 'orderHistory')->name('order-history');
            Route::get('my-account', 'myAccount')->name('my-account');
            Route::get('notifications', 'notifications')->name('notifications');
            Route::get('category/{slug}', 'category')->where('slug', '[A-Za-z0-9][A-Za-z0-9-]*')->name('category.show');
            Route::get('bestseller', 'bestseller')->name('bestseller');
            Route::get('cart', 'cart')->name('cart');
            Route::get('checkout', 'cheackout')->name('cheackout');
            Route::get('contact', 'contact')->name('contact');
            Route::get('404', 'notFound')->name('not-found');
            Route::get('product/{slug}', 'productDetails')->where('slug', '[A-Za-z0-9][A-Za-z0-9-]*')->name('product.show');
        });
    });
Route::fallback([EcommerceController::class, 'fallback']);
