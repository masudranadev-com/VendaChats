<?php

use App\Http\Controllers\EcommerceController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| E-Commerce Routes
|--------------------------------------------------------------------------
*/

Route::get('/', [EcommerceController::class, 'index']);
Route::get('/404', [EcommerceController::class, 'notFound']);
Route::get('/bestseller', [EcommerceController::class, 'bestseller']);
Route::get('/cart', [EcommerceController::class, 'cart']);
Route::get('/cheackout', [EcommerceController::class, 'cheackout']);
Route::get('/contact', [EcommerceController::class, 'contact']);
Route::get('/shop', [EcommerceController::class, 'shop']);
Route::get('/product/{slug}', [EcommerceController::class, 'productDetails']);
