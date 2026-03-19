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
        Route::get('bestseller', 'bestseller')->name('bestseller');
        Route::get('cart', 'cart')->name('cart');
        Route::get('checkout', 'cheackout')->name('cheackout');
        Route::get('contact', 'contact')->name('contact');
        Route::get('404', 'notFound')->name('not-found');
        Route::get('product/{slug}', 'productDetails')
            ->where('slug', '[A-Za-z0-9][A-Za-z0-9-]*')
            ->name('product.show');
    });

Route::get('cheackout', static function () {
    return redirect()->route('ecommerce.cheackout', [], 301);
});

Route::fallback(static function () {
    return response()->view('ecommerce.theme1.404', [], 404);
});
